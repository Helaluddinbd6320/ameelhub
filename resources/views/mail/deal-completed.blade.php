{{-- resources/views/mail/deal-completed.blade.php --}}
@component('mail::message')
# Deal সম্পন্ন হয়েছে ✅

Deal **#{{ $deal->uuid }}** সফলভাবে সম্পন্ন হয়েছে। সবগুলো Milestone Release হয়ে গেছে।

@if($pdfPath)
সম্পূর্ণ রশিদ এই ইমেইলের সাথে সংযুক্ত আছে।
@endif

ধন্যবাদ AmeelHub ব্যবহার করার জন্য।

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent