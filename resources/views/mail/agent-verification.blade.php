{{-- resources/views/mail/agent-verification.blade.php --}}
@component('mail::message')
# {{ $status === 'verified' ? 'আপনার Agent Account Verified হয়েছে' : 'আপনার Agent Verification বাতিল হয়েছে' }}

@if($status === 'verified')
অভিনন্দন! আপনার Agent account যাচাই সম্পন্ন হয়েছে। এখন থেকে আপনি Job Post করতে পারবেন।
@else
দুঃখিত, আপনার Agent Verification বাতিল করা হয়েছে।

**কারণ:** {{ $reason }}
@endif

@component('mail::button', ['url' => config('app.url')])
AmeelHub এ যান
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent