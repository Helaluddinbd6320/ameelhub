{{-- resources/views/mail/nok.blade.php --}}
@component('mail::message')
# {{ $status === 'sent' ? 'নতুন Job Offer এসেছে' : 'আপনার Nok গ্রহণ করা হয়েছে' }}

@if($status === 'sent')
একজন এজেন্ট আপনাকে একটি Job Offer (Nok) পাঠিয়েছেন।

@if($nok->nok_message)
**বার্তা:** {{ $nok->nok_message }}
@endif

৪৮ ঘণ্টার মধ্যে সাড়া দিন, নাহলে অফার মেয়াদোত্তীর্ণ হয়ে যাবে।
@else
Worker আপনার job offer গ্রহণ করেছেন। এখন selection প্রক্রিয়া এগিয়ে নিতে পারেন।
@endif

@component('mail::button', ['url' => config('app.url')])
বিস্তারিত দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent