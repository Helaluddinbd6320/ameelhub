<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected array $allowedProviders = ['google', 'facebook'];

    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders)) {
            abort(404);
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

        // 3. Create new user (default role = worker)
        if (! $user) {
            $user = new User([
                'name'   => $name,
                'email'  => $email ?? $socialId . '@social.placeholder',
                'avatar' => $avatar,
            ]);

            // role, account_source, social_id & email_verified_at are guarded, so forceFill is required
            $user->forceFill([
                'password'          => bcrypt(Str::random(32)),
                'role'              => 'worker', // Default role for social signup
                'account_source'    => 'self_registered',
                $column             => $socialId,
                'email_verified_at' => now(), // Social = already verified
            ])->save();
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}