{{-- resources/views/mail/escrow.blade.php --}}
@component('mail::message')
# Escrow Hold নিশ্চিত হয়েছে

Deal **#{{ $deal->uuid }}** এর টাকা নিরাপদে Escrow তে হোল্ড করা হয়েছে। Milestone সম্পন্ন হওয়ার সাথে সাথে ধাপে ধাপে রিলিজ হবে।

@component('mail::button', ['url' => config('app.url')])
Deal দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent