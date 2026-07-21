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

        // BUG FIX (Step 10.9 audit — Helal-reported): the registration page's
        // "আমি কে? / I am a" Worker/Agent selector was being completely lost
        // for social signups — the Google/Facebook buttons are plain links,
        // not tied to the radio state, and OAuth redirects away from our app
        // entirely, so nothing survived the round-trip to callback(). New
        // social users always ended up hardcoded as 'worker' regardless of
        // what was selected.
        //
        // Fix: read an optional ?role= query param (the registration view
        // must append it, e.g. /auth/google/redirect?role=agent) and stash
        // it in the session before leaving for the OAuth provider. Session
        // survives the redirect round-trip, callback() reads it back.
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

        // Pull the role stashed in redirect() above. Default to 'worker' if
        // it's missing/invalid (e.g. someone hits the callback URL directly
        // without going through redirect() first) — never trust unvalidated
        // input for a security-relevant field like role.
        $signupRole = session()->pull('social_signup_role');
        if (! in_array($signupRole, $this->allowedSignupRoles, true)) {
            $signupRole = 'worker';
        }

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

        // 3. Create new user (role = whatever was selected on the
        // registration form, worker or agent — see $signupRole above)
        if (! $user) {
            $user = new User([
                'name'   => $name,
                'email'  => $email ?? $socialId . '@social.placeholder',
                'avatar' => $avatar,
            ]);

            // role, account_source, social_id & email_verified_at are guarded, so forceFill is required
            $user->forceFill([
                'password'          => bcrypt(Str::random(32)),
                'role'              => $signupRole,
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