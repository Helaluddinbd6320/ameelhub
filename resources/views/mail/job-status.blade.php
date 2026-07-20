{{-- resources/views/mail/job-status.blade.php --}}
@component('mail::message')
# {{ $job->job_title }}

@if($status === 'approved')
আপনার Job Post অনুমোদিত হয়েছে এবং এখন পাবলিকভাবে দৃশ্যমান।
@elseif($status === 'rejected')
আপনার Job Post বাতিল করা হয়েছে।

**কারণ:** {{ $reason }}
@elseif($status === 'auto_closed')
মেয়াদ শেষ হওয়ায় আপনার Job Post স্বয়ংক্রিয়ভাবে বন্ধ করা হয়েছে।
@endif

@component('mail::button', ['url' => config('app.url').'/agent/my-job-posts'])
আমার Job Posts দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent