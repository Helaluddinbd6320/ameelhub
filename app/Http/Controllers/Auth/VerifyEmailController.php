<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * BUG FIX (Helal-reported, Step 10.9 audit): the stock Breeze
     * VerifyEmailController type-hints Illuminate\Foundation\Auth\
     * EmailVerificationRequest, whose authorize() requires the CURRENT
     * browser session to already be logged in as this exact user
     * (hash_equals($this->user()->getKey(), $this->route('id'))).
     *
     * That fails constantly in real usage for this platform — workers/
     * agents very commonly open the verification link on a different
     * device/browser than the one they registered on (e.g. checking Gmail
     * on their phone after signing up on a desktop), or have a different
     * account logged in in that browser (common during testing too). The
     * stock behavior 403s ("This action is unauthorized") in all of these
     * completely normal cases.
     *
     * Fix: the route's `signed` + `throttle:6,1` middleware (routes/auth.php)
     * already cryptographically verifies the link hasn't been tampered
     * with or expired, and we additionally re-check the hash matches this
     * user's email below — that combination is itself sufficient proof of
     * email ownership. We deliberately do NOT also require the browser to
     * already be authenticated as this specific user; instead we log them
     * in as this user once ownership is confirmed (overriding whatever
     * session/account was previously active in that browser).
     *
     * This also removes the same App\Providers\RouteServiceProvider
     * dependency that was already found broken (and fixed) in
     * SocialAuthController and RegisteredUserController — Laravel 11+/12's
     * slim bootstrap structure no longer has that class.
     */
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'অবৈধ ভেরিফিকেশন লিংক।');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Log them in as the verified account, regardless of whichever
        // session/account (if any) was previously active in this browser —
        // they've just proven ownership of this email via the signed link.
        Auth::login($user);

        return redirect()->intended(match ($user->role) {
            'super_admin', 'admin', 'staff' => '/admin',
            'agent' => '/agent',
            default => '/worker',
        })->with('status', 'email-verified');
 
        }
}