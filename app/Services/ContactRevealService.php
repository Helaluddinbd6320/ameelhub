<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\ContactReveal;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactRevealService
{
    public const VALID_PHONE_TYPES = ['primary', 'whatsapp', 'saudi'];

    public function __construct(protected WalletService $walletService)
    {
    }

    public function hasRevealed(Worker $worker, string $phoneType, User $user): bool
    {
        return ContactReveal::where('user_id', $user->id)
            ->where('worker_id', $worker->id)
            ->where('phone_type', $phoneType)
            ->exists();
    }

    public function getReveal(Worker $worker, string $phoneType, User $user): ?ContactReveal
    {
        return ContactReveal::where('user_id', $user->id)
            ->where('worker_id', $worker->id)
            ->where('phone_type', $phoneType)
            ->first();
    }

    /**
     * Worker এর ফোন নম্বর reveal করে — প্রথমবার হলে wallet কাটে (5 SAR),
     * আগে reveal করা থাকলে সেই রেকর্ডই ফেরত দেয় (idempotent)।
     * Worker নিজে নিজের CV দেখলে চার্জ হয় না।
     */
    public function reveal(Worker $worker, string $phoneType, User $user): ContactReveal
    {
        $this->validatePhoneType($phoneType);

        $phoneValue = $this->extractPhone($worker, $phoneType);

        if (blank($phoneValue)) {
            throw ValidationException::withMessages([
                'phone_type' => 'এই কর্মীর প্রোফাইলে এই ধরনের নম্বর যোগ করা নেই।',
            ]);
        }

        $isOwnProfile = $worker->worker_user_id === $user->id;

        return DB::transaction(function () use ($worker, $phoneType, $user, $isOwnProfile) {
            // SECURITY FIX (Step 10.7f audit — TOCTOU race):
            // Worker রো লক করে concurrent reveal attempt (একই worker এর
            // জন্য, ডাবল-ক্লিক বা দুই ট্যাব থেকে) সিরিয়ালাইজ করা হচ্ছে —
            // NokService-এ JobPost লক করার মতোই প্যাটার্ন। লক নেওয়ার পরে
            // আবার existing-check করা হচ্ছে যাতে দুটো concurrent request
            // একই সাথে "নেই" দেখে দুটোই চার্জ+insert করে ফেলতে না পারে।
            Worker::where('id', $worker->id)->lockForUpdate()->firstOrFail();

            $existing = $this->getReveal($worker, $phoneType, $user);
            if ($existing) {
                return $existing;
            }

            $cost = $isOwnProfile ? 0.0 : (float) setting('contact_reveal_fee', 5);

            if (!$isOwnProfile) {
                // SECURITY FIX (Step 10.7f audit):
                // HTTP route এর 'throttle:cv-reveal' middleware (10/day)
                // ব্যবহারই হয় না, কারণ এই ফিচার Livewire component method
                // call দিয়ে চলে (routes/web.php bypass হয়ে যায়)। তাই এখানে
                // service layer এ সমতুল্য daily-limit চেক যোগ করা হলো —
                // NokService/WithdrawalService এর মতোই DB-based, IP-independent
                // এবং route bypass হলেও কার্যকর।
                $this->assertWithinDailyRevealLimit($user);

                $this->walletService->chargeContactReveal($user, $worker->id);
            }

            return ContactReveal::create([
                'user_id'        => $user->id,
                'worker_id'      => $worker->id,
                'amount_charged' => $cost,
                'phone_type'     => $phoneType,
                'ip_address'     => request()->ip(),
                'revealed_at'    => now(),
            ]);
        });
    }

    /**
     * Reveal হয়ে থাকলে actual ফোন নম্বর ফেরত দাও, নাহলে null (masked দেখাতে হবে)
     */
    public function getRevealedPhone(Worker $worker, string $phoneType, User $user): ?string
    {
        if (!$this->hasRevealed($worker, $phoneType, $user)) {
            return null;
        }

        return $this->extractPhone($worker, $phoneType);
    }

    /**
     * masked preview বানায় — যেমন +৮৮০১৭xx-xxx৬৭৮
     */
    public function maskPhone(string $phone): string
    {
        $len = mb_strlen($phone);
        if ($len <= 4) {
            return str_repeat('•', $len);
        }

        return mb_substr($phone, 0, 4) . str_repeat('•', max($len - 7, 3)) . mb_substr($phone, -3);
    }

    private function extractPhone(Worker $worker, string $phoneType): ?string
    {
        return match ($phoneType) {
            'primary'  => $worker->phone_primary,
            'whatsapp' => $worker->phone_whatsapp,
            'saudi'    => $worker->phone_saudi,
            default    => null,
        };
    }

    private function validatePhoneType(string $phoneType): void
    {
        if (!in_array($phoneType, self::VALID_PHONE_TYPES, true)) {
            throw ValidationException::withMessages([
                'phone_type' => 'অবৈধ ফোন টাইপ।',
            ]);
        }
    }

    /**
     * দৈনিক সর্বোচ্চ contact-reveal সীমা (নতুন/চার্জযোগ্য reveal এর ক্ষেত্রে —
     * আগেই reveal করা নম্বর আবার দেখা ফ্রি এবং এই কাউন্টে ধরা হয় না, কারণ
     * সেটা reveal() এর শুরুতেই idempotent early-return হিসেবে হ্যান্ডেল হয়)।
     * settings key: cv_reveal_daily_limit (ডিফল্ট 10, blueprint অনুযায়ী)।
     */
    private function assertWithinDailyRevealLimit(User $user): void
    {
        $limit = (int) setting('cv_reveal_daily_limit', 10);

        $todayCount = ContactReveal::where('user_id', $user->id)
            ->whereDate('revealed_at', now()->toDateString())
            ->count();

        if ($todayCount >= $limit) {
            throw ValidationException::withMessages([
                'phone_type' => "আজকের জন্য কন্টাক্ট রিভিলের সীমা ({$limit}টি) শেষ হয়ে গেছে। আগামীকাল আবার চেষ্টা করুন।",
            ]);
        }
    }
}