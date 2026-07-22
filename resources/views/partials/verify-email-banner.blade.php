{{--
    resources/views/partials/verify-email-banner.blade.php

    Shown inside Worker/Agent panels (via PanelsRenderHook::CONTENT_START)
    when the logged-in user's email is not yet verified. Login/panel
    browsing stays open on purpose (business decision) — this banner is
    just a persistent, impossible-to-miss nudge with a one-click resend,
    and the actual blocking happens at the action level (CV submit, Job
    post submit, Withdrawal request, Recharge request).
--}}
@auth
    @unless (auth()->user()->hasVerifiedEmail())
        <div class="fi-verify-email-banner rounded-xl px-4 py-3 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
             style="background-color:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.35);">
            <div class="flex items-center gap-2 text-sm" style="color:#92400e;">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l6.28 11.18c.75 1.334-.213 2.987-1.744 2.987H3.72c-1.53 0-2.493-1.653-1.744-2.987l6.28-11.18zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>
                    আপনার ইমেইল এখনো ভেরিফাই করা হয়নি। CV/Job জমা দেওয়া, Withdrawal ও Recharge অনুরোধ করার আগে ইমেইল ভেরিফাই করুন।
                </span>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" class="shrink-0">
                @csrf
                <button type="submit"
                        class="rounded-lg px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap"
                        style="background-color:#C9974C; color:#0B4F3F;">
                    ভেরিফিকেশন লিংক পাঠান
                </button>
            </form>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="rounded-xl px-4 py-2.5 text-xs mb-4" style="background-color:rgba(16,185,129,0.12); color:#065f46; border:1px solid rgba(16,185,129,0.3);">
                ✓ আপনার ইমেইলে একটি নতুন ভেরিফিকেশন লিংক পাঠানো হয়েছে।
            </div>
        @endif
    @endunless
@endauth