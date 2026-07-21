{{-- resources/views/mail/recharge.blade.php --}}
@component('mail::message')
# {{ $recharge->amount }} SAR — Recharge

@if($status === 'requested')
একটি নতুন Recharge Request জমা পড়েছে ({{ $recharge->payment_method }}, রেফারেন্স: {{ $recharge->reference_number ?? '—' }}), review এর অপেক্ষায়।
@elseif($status === 'approved')
আপনার **{{ $recharge->amount }} SAR** এর Recharge Request অনুমোদিত হয়েছে এবং আপনার Wallet এ যোগ করা হয়েছে।
@else
আপনার Recharge Request বাতিল করা হয়েছে।

**কারণ:** {{ $reason }}
@endif

@component('mail::button', ['url' => config('app.url')])
Wallet দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent