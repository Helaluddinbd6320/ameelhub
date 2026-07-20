{{-- resources/views/mail/withdrawal.blade.php --}}
@component('mail::message')
# {{ $withdrawal->amount }} SAR — Withdrawal

@if($status === 'requested')
একটি নতুন Withdrawal Request জমা পড়েছে, review এর অপেক্ষায়।
@elseif($status === 'approved')
আপনার **{{ $withdrawal->amount }} SAR** এর Withdrawal Request অনুমোদিত হয়েছে এবং প্রসেস করা হচ্ছে।
@else
আপনার Withdrawal Request বাতিল করা হয়েছে।

**কারণ:** {{ $reason }}
@endif

@component('mail::button', ['url' => config('app.url')])
Wallet দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent