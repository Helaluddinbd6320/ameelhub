{{-- resources/views/mail/selection.blade.php --}}
@component('mail::message')
# {{ $job->job_title }}

@if($status === 'selected')
আপনাকে **{{ $job->job_title }}** এর জন্য Select করা হয়েছে। অনুগ্রহ করে গ্রহণ অথবা প্রত্যাখ্যান করুন।
@else
**{{ $worker->full_name_bn }}** আপনার Selection গ্রহণ করেছেন। Escrow প্রক্রিয়া শুরু হবে।
@endif

@component('mail::button', ['url' => config('app.url')])
বিস্তারিত দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent