{{-- resources/views/mail/milestone.blade.php --}}
@component('mail::message')
# Milestone #{{ $milestone->milestone_number }}: {{ $milestone->title }}

@if($status === 'worker_confirmed')
Worker এই milestone confirm করেছেন। Agent confirm করলে Admin release করবে।
@elseif($status === 'agent_confirmed')
Agent এই milestone confirm করেছেন। Release এর অপেক্ষায়।
@else
এই Milestone Release হয়ে গেছে — **{{ $milestone->agent_receives_sar }} SAR** জমা হয়েছে।

@if($pdfPath)
রশিদ (Receipt) এই ইমেইলের সাথে সংযুক্ত আছে।
@endif
@endif

@component('mail::button', ['url' => config('app.url')])
Deal দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent