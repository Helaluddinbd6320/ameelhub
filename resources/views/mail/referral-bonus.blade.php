{{-- resources/views/mail/referral-bonus.blade.php --}}
@component('mail::message')
# Referral Bonus পেয়েছেন 🎉

আপনি **{{ $amount }} SAR** referral bonus পেয়েছেন! আপনার রেফার করা ব্যক্তির প্রথম Deal সম্পন্ন হওয়ার কারণে এই বোনাস দেওয়া হয়েছে।

@component('mail::button', ['url' => config('app.url')])
Wallet দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent