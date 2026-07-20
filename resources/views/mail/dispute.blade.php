{{-- resources/views/mail/dispute.blade.php --}}
@component('mail::message')
# {{ $status === 'raised' ? '⚠ Dispute উত্থাপিত হয়েছে' : 'Dispute সমাধান হয়েছে' }}

@if($status === 'raised')
Deal #{{ $milestone->job_deal_id }}, Milestone #{{ $milestone->milestone_number }} এ dispute তৈরি হয়েছে।

**কারণ:** {{ $milestone->dispute_reason }}

দয়া করে এখনই Admin Panel এ গিয়ে review করুন।
@else
Milestone #{{ $milestone->milestone_number }} এর dispute সমাধান করা হয়েছে।

**সিদ্ধান্ত:** {{ $milestone->resolution }}

@if($milestone->resolution_notes)
**নোট:** {{ $milestone->resolution_notes }}
@endif
@endif

@component('mail::button', ['url' => config('app.url')])
বিস্তারিত দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent