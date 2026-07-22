<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected array $allowedProviders = ['google', 'facebook'];

    // Only these two roles are selectable at public registration
    // (agent/worker) — admin/staff/super_admin are never created this way.
    protected array $allowedSignupRoles = ['worker', 'agent'];

    public function redirect(string $provider, Request $request): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders)) {
            abort(404);
        }

        // The registration page's "আমি কে? / I am a" Worker/Agent selector
        // is carried through as ?role=worker|agent (see register.blade.php).
        // The login page's Google/Facebook buttons intentionally do NOT send
        // this param — see callback() below for why that distinction matters.
        $role = $request->query('role');

        if (in_array($role, $this->allowedSignupRoles, true)) {
            session(['social_signup_role' => $role]);
        } else {
            session()->forget('social_signup_role');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['social' => 'Social login failed. Please try again.']);
        }

        $column    = $provider . '_id'; // google_id or facebook_id
        $socialId  = $socialUser->getId();
        $email     = $socialUser->getEmail();
        $name      = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
        $avatar    = $socialUser->getAvatar();

        // Pull whatever redirect() stashed. We deliberately keep the RAW
        // pulled value (rather than defaulting straight to 'worker') so we
        // can tell apart "came from register page with an explicit role"
        // from "came from login page with no role context at all" — see
        // the no-account-found branch below.
        $signupRoleRaw  = session()->pull('social_signup_role');
        $hasExplicitRole = in_array($signupRoleRaw, $this->allowedSignupRoles, true);

        // 1. Find by social ID
        $user = User::where($column, $socialId)->first();

        // 2. Find by email (link social to existing account)
        if (! $user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                // update guarded columns securely
                $user->forceFill([$column => $socialId])->save();
            }
        }

        // 3. No existing account found.
        if (! $user) {
            // BUG FIX (Step 10.9 audit — Helal-reported): previously this
            // always silently created a new user with role forced to
            // 'worker'. That's wrong in two ways:
            //   a) if they came from the registration page but somehow
            //      picked "agent", their choice must be honored (handled
            //      via $signupRoleRaw below);
            //   b) if they came from the LOGIN page (no role context at
            //      all — login has no "who are you" selector, nor should
            //      it), we must NOT guess a role and auto-create an
            //      account. Instead, send them to registration with their
            //      name/email prefilled so they can consciously choose
            //      Worker or Agent and complete signup properly.
            if (! $hasExplicitRole) {
                return redirect()->route('register')
                    ->withInput(['name' => $name, 'email' => $email])
                    ->withErrors([
                        'social' => 'এই ইমেইল/অ্যাকাউন্ট দিয়ে কোনো প্রোফাইল পাওয়া যায়নি। অনুগ্রহ করে আপনি Worker নাকি Agent তা বেছে নিয়ে রেজিস্ট্রেশন সম্পন্ন করুন।',
                    ]);
            }

            $user = new User([
                'name'   => $name,
                'email'  => $email ?? $socialId . '@social.placeholder',
                'avatar' => $avatar,
            ]);

            // role, account_source, social_id & email_verified_at are guarded, so forceFill is required
            $user->forceFill([
                'password'          => bcrypt(Str::random(32)),
                'role'              => $signupRoleRaw, // validated above via $hasExplicitRole
                'account_source'    => 'self_registered',
                $column             => $socialId,
                'email_verified_at' => now(), // Social = already verified
            ])->save();
        }

        Auth::login($user, remember: true);

        // BUG FIX (production log, Step 10.9 audit): RouteServiceProvider::HOME
        // was used here, but Laravel 11+/12's slim bootstrap structure removed
        // App\Providers\RouteServiceProvider entirely — every social login
        // attempt was throwing "Class ... not found" (production.ERROR log,
        // 2026-07-21). Since AmeelHub has three separate panels (admin/agent/
        // worker) rather than one single "home", redirect by role instead of
        // a single HOME constant — same role-based approach used in
        // User::canAccessPanel().
        return redirect()->intended(match ($user->role) {
            'super_admin', 'admin', 'staff' => '/admin',
            'agent' => '/agent',
            default => '/worker',
        });
    }
}