<x-filament-panels::page>
    @if ($worker->status === 'rejected' && $worker->rejection_reason)
        <div class="mb-4 p-4 rounded-lg bg-danger-50 border border-danger-300 text-danger-700">
            <strong>{{ __('messages.worker_cv_form.rejected_title') }}</strong><br>
            {{ __('messages.worker_cv_form.rejected_reason') }}: {{ $worker->rejection_reason }}<br>
            {{ __('messages.worker_cv_form.rejected_hint') }}
        </div>
    @endif

    @if ($worker->status === 'pending')
        <div class="mb-4 p-4 rounded-lg bg-warning-50 border border-warning-300 text-warning-700">
            {{ __('messages.worker_cv_form.pending_notice') }}
        </div>
    @endif

    @if (in_array($worker->status, ['active', 'featured']))
        <div class="mb-4 p-4 rounded-lg bg-success-50 border border-success-300 text-success-700">
            {{ __('messages.worker_cv_form.active_notice') }}
        </div>
    @endif

    <form wire:submit="saveDraft">
        {{ $this->form }}

        {{--
            BUG FIX (Helal-reported): the line that used to sit here —
            {{ $this->getFormActions()[0] ?? '' }} — rendered the first
            action (সংরক্ষণ করুন / Draft) a second time, on top of the
            @foreach below which already renders every action including
            that same first one. Removed; the @foreach alone is enough.
        --}}
        <div class="mt-6 flex gap-3">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>