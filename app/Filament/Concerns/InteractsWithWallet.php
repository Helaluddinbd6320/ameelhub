<?php

namespace App\Filament\Concerns;

use App\Exceptions\WalletException;
use App\Models\RechargeRequest;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\RechargeService;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

trait InteractsWithWallet
{
    use WithPagination;
    use WithFileUploads;

    // ─── Withdrawal Modal State ──────────────────────────────────────

    public bool $isWithdrawing = false;

    public ?string $amount = null;
    public ?string $paymentMethod = null;
    public ?string $accountDetails = null;

    // ─── Recharge Modal State ─────────────────────────────────────────

    public bool $isRecharging = false;

    public ?string $rechargeAmount = null;
    public ?string $rechargePaymentMethod = null;
    public ?string $referenceNumber = null;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $proofFile = null;

    // ─── Filter State ──────────────────────────────────────────────────

    public string $filterType = '';

    protected function walletRules(): array
    {
        return [
            'amount'         => ['required', 'numeric', 'min:1'],
            'paymentMethod'  => ['required', 'in:bank,bkash,nagad,stcpay,cash'],
            'accountDetails' => ['required', 'string', 'max:255'],
        ];
    }

    protected function walletMessages(): array
    {
        return [
            'amount.required'         => 'পরিমাণ লিখুন।',
            'amount.numeric'          => 'পরিমাণ অবশ্যই সংখ্যা হতে হবে।',
            'paymentMethod.required'  => 'একটি মাধ্যম নির্বাচন করুন।',
            'accountDetails.required' => 'অ্যাকাউন্ট নম্বর/ঠিকানা লিখুন।',
        ];
    }

    protected function rechargeRules(): array
    {
        return [
            'rechargeAmount'         => ['required', 'numeric', 'min:1'],
            'rechargePaymentMethod'  => ['required', 'in:bank,bkash,nagad,stcpay,cash'],
            // অন্তত একটা থাকতেই হবে — রেফারেন্স নম্বর অথবা প্রুফ স্ক্রিনশট।
            // দুটোই না থাকলে Admin ম্যানুয়ালি ব্যাংক/বিকাশ স্টেটমেন্টে
            // মিলিয়ে verify করতে পারবেন না।
            'referenceNumber'        => ['nullable', 'required_without:proofFile', 'string', 'max:100'],
            'proofFile'              => ['nullable', 'required_without:referenceNumber', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    protected function rechargeMessages(): array
    {
        return [
            'rechargeAmount.required'          => 'পরিমাণ লিখুন।',
            'rechargeAmount.numeric'           => 'পরিমাণ অবশ্যই সংখ্যা হতে হবে।',
            'rechargePaymentMethod.required'   => 'একটি মাধ্যম নির্বাচন করুন।',
            'referenceNumber.required_without' => 'রেফারেন্স নম্বর অথবা প্রুফ স্ক্রিনশট — অন্তত একটা দিন।',
            'proofFile.required_without'       => 'প্রুফ স্ক্রিনশট অথবা রেফারেন্স নম্বর — অন্তত একটা দিন।',
            'proofFile.mimes'                  => 'শুধু JPG, PNG অথবা PDF ফাইল দেওয়া যাবে।',
            'proofFile.max'                    => 'ফাইলের আকার সর্বোচ্চ 5MB হতে হবে।',
        ];
    }

    // ─── Computed Properties ─────────────────────────────────────────

    public function getBalancesProperty(): array
    {
        return app(WalletService::class)->getBalances(Auth::user());
    }

    public function getTransactionsProperty()
    {
        return app(WalletService::class)
            ->transactionsQuery(Auth::user(), $this->filterType ?: null)
            ->paginate(10);
    }

    public function getMinWithdrawalProperty(): float
    {
        return (float) setting('min_withdrawal_sar', 50);
    }

    public function getDailyLimitProperty(): int
    {
        return (int) setting('withdrawal_daily_limit', 3);
    }

    public function getTodayWithdrawalCountProperty(): int
    {
        return WithdrawalRequest::query()
            ->where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->count();
    }

    public function getMinRechargeProperty(): float
    {
        return (float) setting('min_recharge_sar', 10);
    }

    public function getRechargeDailyLimitProperty(): int
    {
        return (int) setting('recharge_daily_limit', 5);
    }

    public function getTodayRechargeCountProperty(): int
    {
        return RechargeRequest::query()
            ->where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->count();
    }

    /** Filter dropdown-এ দেখানোর জন্য সব transaction type + বাংলা লেবেল */
    public function getAvailableTypesProperty(): array
    {
        return [
            'wallet_recharge'      => 'রিচার্জ',
            'cv_approval_fee'      => 'CV অনুমোদন ফি',
            'cv_rejection_refund'  => 'CV প্রত্যাখ্যান রিফান্ড',
            'job_fee_reveal'       => 'জব ফি রিভিল',
            'contact_reveal'       => 'যোগাযোগ রিভিল',
            'escrow_hold'          => 'এসক্রো হোল্ড',
            'escrow_release_agent' => 'এসক্রো রিলিজ',
            'escrow_deduct_worker' => 'এসক্রো কর্তন',
            'escrow_refund'        => 'এসক্রো রিফান্ড',
            'referral_bonus'       => 'রেফারেল বোনাস',
            'nok_fee'              => 'নক ফি',
            'withdrawal_debit'     => 'উত্তোলন',
            'manual_adjustment'    => 'ম্যানুয়াল সমন্বয়',
        ];
    }

    /** মোট জমা/কর্তন/নীট পরিবর্তন — WalletService::summaryByType() থেকে */
    public function getSummaryProperty(): array
    {
        $byType = app(WalletService::class)->summaryByType(Auth::user());

        $totalCredit = 0.0;
        $totalDebit  = 0.0;

        foreach ($byType as $directions) {
            $totalCredit += $directions['credit'] ?? 0;
            $totalDebit  += $directions['debit'] ?? 0;
        }

        return [
            'total_credit' => $totalCredit,
            'total_debit'  => $totalDebit,
            'net'          => $totalCredit - $totalDebit,
        ];
    }

    // ─── Withdrawal Modal Controls ────────────────────────────────────

    public function openWithdrawModal(): void
    {
        $this->reset(['amount', 'paymentMethod', 'accountDetails']);
        $this->resetErrorBag();
        $this->isWithdrawing = true;
    }

    public function closeWithdrawModal(): void
    {
        $this->isWithdrawing = false;
        $this->reset(['amount', 'paymentMethod', 'accountDetails']);
        $this->resetErrorBag();
    }

    // ─── Recharge Modal Controls ──────────────────────────────────────

    public function openRechargeModal(): void
    {
        $this->reset(['rechargeAmount', 'rechargePaymentMethod', 'referenceNumber', 'proofFile']);
        $this->resetErrorBag();
        $this->isRecharging = true;
    }

    public function closeRechargeModal(): void
    {
        $this->isRecharging = false;
        $this->reset(['rechargeAmount', 'rechargePaymentMethod', 'referenceNumber', 'proofFile']);
        $this->resetErrorBag();
    }

    // ─── Submit: Withdrawal ────────────────────────────────────────────

    public function submitWithdrawal(WithdrawalService $service): void
    {
        $validated = $this->validate($this->walletRules(), $this->walletMessages());

        try {
            $service->request(
                Auth::user(),
                (float) $validated['amount'],
                $validated['paymentMethod'],
                $validated['accountDetails']
            );

            Notification::make()
                ->title('Withdrawal request পাঠানো হয়েছে')
                ->body('আপনার অনুরোধ Admin পর্যালোচনা করবেন।')
                ->success()
                ->send();

            $this->closeWithdrawModal();
        } catch (WalletException $e) {
            Notification::make()
                ->title('অনুরোধ ব্যর্থ হয়েছে')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->addError('amount', $e->getMessage());
        }
    }

    // ─── Submit: Recharge ──────────────────────────────────────────────

    public function submitRecharge(RechargeService $service): void
    {
        $validated = $this->validate($this->rechargeRules(), $this->rechargeMessages());

        try {
            $proofPath = null;

            if ($this->proofFile) {
                // L10 file upload security: ULID filename, MIME already
                // whitelisted by validate() above, stored on the private
                // disk (not publicly symlinked) — same pattern as agent
                // verification documents.
                $ulid = (string) Str::ulid();
                $extension = $this->proofFile->getClientOriginalExtension();

                $proofPath = $this->proofFile->storeAs(
                    'recharge-proofs',
                    "{$ulid}.{$extension}",
                    'private_docs'
                );
            }

            $service->request(
                Auth::user(),
                (float) $validated['rechargeAmount'],
                $validated['rechargePaymentMethod'],
                $validated['referenceNumber'] ?? null,
                $proofPath
            );

            Notification::make()
                ->title('Recharge request পাঠানো হয়েছে')
                ->body('আপনার অনুরোধ Admin যাচাই করে অনুমোদন দেবেন।')
                ->success()
                ->send();

            $this->closeRechargeModal();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('অনুরোধ ব্যর্থ হয়েছে')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->addError('rechargeAmount', $e->getMessage());
        }
    }

    // ─── Blade Helpers (transaction badge labels) ────────────────────

    public static function transactionTypeLabel(string $type): string
    {
        return match ($type) {
            'wallet_recharge'      => 'রিচার্জ',
            'cv_approval_fee'      => 'CV অনুমোদন ফি',
            'cv_rejection_refund'  => 'CV প্রত্যাখ্যান রিফান্ড',
            'job_fee_reveal'       => 'জব ফি রিভিল',
            'contact_reveal'       => 'যোগাযোগ রিভিল',
            'escrow_hold'          => 'এসক্রো হোল্ড',
            'escrow_release_agent' => 'এসক্রো রিলিজ',
            'escrow_deduct_worker' => 'এসক্রো কর্তন',
            'escrow_refund'        => 'এসক্রো রিফান্ড',
            'referral_bonus'       => 'রেফারেল বোনাস',
            'nok_fee'              => 'নক ফি',
            'withdrawal_debit'     => 'উত্তোলন',
            'manual_adjustment'    => 'ম্যানুয়াল সমন্বয়',
            default                => $type,
        };
    }

    public static function transactionDirectionColor(string $direction): string
    {
        return $direction === 'credit' ? 'success' : 'danger';
    }

    // ─── Live Validation Clearing ─────────────────────────────────────

    public function updatedAmount(): void
    {
        $this->resetErrorBag('amount');
    }

    public function updatedPaymentMethod(): void
    {
        $this->resetErrorBag('paymentMethod');
    }

    public function updatedAccountDetails(): void
    {
        $this->resetErrorBag('accountDetails');
    }

    public function updatedRechargeAmount(): void
    {
        $this->resetErrorBag('rechargeAmount');
    }

    public function updatedRechargePaymentMethod(): void
    {
        $this->resetErrorBag('rechargePaymentMethod');
    }

    public function updatedReferenceNumber(): void
    {
        $this->resetErrorBag(['referenceNumber', 'proofFile']);
    }

    public function updatedProofFile(): void
    {
        $this->resetErrorBag(['proofFile', 'referenceNumber']);
    }

    /** ফিল্টার বদলালে pagination প্রথম পেজে রিসেট হবে */
    public function updatedFilterType(): void
    {
        $this->resetPage();
    }
}