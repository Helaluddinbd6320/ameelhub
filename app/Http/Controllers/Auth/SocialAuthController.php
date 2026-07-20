<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                $user->update([$column => $socialId]);
            }
        }

        // 3. Create new user (default role = worker)
        if (! $user) {
            $user = User::create([
                'name'             => $name,
                'email'            => $email ?? $socialId . '@social.placeholder',
                'password'         => bcrypt(Str::random(32)),
                'role'             => 'worker', // default role for social signup
                $column            => $socialId,
                'avatar'           => $avatar,
                'email_verified_at'=> now(), // social = already verified
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}