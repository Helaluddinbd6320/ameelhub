{{-- resources/views/mail/iqama-expiry-digest.blade.php --}}
@component('mail::message')
# Iqama মেয়াদ শেষের কাছাকাছি

নিচের **{{ $workers->count() }}** জন Worker এর Iqama আগামী ৩০ দিনের মধ্যে মেয়াদোত্তীর্ণ হবে:

@component('mail::table')
| নাম | Iqama Expiry |
|:----|:------------|
@foreach($workers as $w)
| {{ $w->full_name_bn }} | {{ optional($w->iqama_expiry)->format('d M, Y') }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => config('app.url').'/admin/workers'])
Admin Panel এ দেখুন
@endcomponent

ধন্যবাদ,<br>
{{ config('app.name') }}
@endcomponent