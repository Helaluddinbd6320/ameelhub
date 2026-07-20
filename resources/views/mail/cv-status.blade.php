{{-- resources/views/mail/cv-status.blade.php --}}
@component('mail::message')
# {{ $status === 'approved' ? 'আপনার CV অনুমোদিত হয়েছে' : ($status === 'rejected' ? 'আপনার CV বাতিল হয়েছে' : 'নতুন CV জমা পড়েছে') }}

@if($status === 'approved')
অভিনন্দন! **{{ $worker->full_name_bn }}** এর CV অনুমোদিত হয়েছে এবং এখন AmeelHub এ পাবলিকভাবে দৃশ্যমান।
@elseif($status === 'rejected')
দুঃখিত, **{{ $worker->full_name_bn }}** এর CV বাতিল করা হয়েছে।

**কারণ:** {{ $reason }}

সমস্যা সমাধান করে আবার জমা দিতে পারবেন।
@else
**{{ $worker->full_name_bn }}** একটি নতুন CV জমা দিয়েছেন, অনুমোদনের অপেক্ষায় আছে।
@endif

@component('mail::button', ['url' => config('app.url')])
AmeelHub এ যান
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent