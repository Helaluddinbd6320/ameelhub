<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * URL এ ?ref={code} থাকলে সেটা session এ সংরক্ষণ করি — যাতে ইউজার
     * ফর্ম fill করতে করতে দেরি করলেও (বা page reload হলেও) কোডটা হারিয়ে না যায়।
     */
    public function create(Request $request): View
    {
        if ($request->filled('ref')) {
            session(['referral_code' => strtoupper(trim($request->query('ref')))]);
        }

        return view('auth.register', [
            'referralCode' => session('referral_code'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role'     => ['required', 'in:worker,agent'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'ref'      => ['nullable', 'string', 'max:20'],
        ]);

        $user = new User([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // role and account_source are guarded on the User model,
        // so we must use forceFill to bypass mass-assignment protection.
        $user->forceFill([
            'password'       => Hash::make($request->password),
            'role'           => $request->role,
            'account_source' => 'self_registered',
        ])->save();

        // Referral: form এ hidden input থেকে (POST body), না থাকলে session
        // fallback থেকে (GET /register?ref= করে সরাসরি submit বাটনে ক্লিক করলে)।
        $referralCode = $request->input('ref') ?: session('referral_code');

        if ($referralCode) {
            app(ReferralService::class)->processRegistration($user, $referralCode);
            session()->forget('referral_code');
        }

        // This fires Laravel's built-in SendEmailVerificationNotification
        // listener automatically now that User implements MustVerifyEmail
        // (added in this same audit) — no extra code needed here for that.
        event(new Registered($user));

        Auth::login($user);

        // BUG FIX (Step 10.9 audit — same class of bug already found and
        // fixed in SocialAuthController): App\Providers\RouteServiceProvider
        // does not exist in Laravel 11+/12's slim bootstrap structure, so
        // every successful email/password registration was throwing "Class
        // ... not found" right after Auth::login() — this was very likely
        // silently breaking ALL normal (non-social) registrations in
        // production. Redirect by role instead, same pattern used
        // everywhere else in this project (User::canAccessPanel(),
        // SocialAuthController::callback()).
        return redirect()->intended(match ($user->role) {
            'super_admin', 'admin', 'staff' => '/admin',
            'agent' => '/agent',
            default => '/worker',
        });
    }
}