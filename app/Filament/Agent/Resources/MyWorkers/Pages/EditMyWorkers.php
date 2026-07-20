<?php

namespace App\Filament\Agent\Resources\MyWorkers\Pages;

use App\Filament\Agent\Resources\MyWorkers\MyWorkersResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditMyWorkers extends EditRecord
{
    protected static string $resource = MyWorkersResource::class;

    /**
     * Hard block: even a direct URL hit to /edit on a locked CV
     * returns 403, not just a hidden button.
     */
    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (! in_array($this->record->status, ['draft', 'rejected'], true)) {
            Log::warning('Blocked edit attempt on locked worker CV', [
                'worker_id'    => $this->record->id,
                'status'       => $this->record->status,
                'attempted_by' => auth()->id(),
            ]);

            abort(403, 'এই CV টি ' . $this->record->status . ' অবস্থায় আছে — এখন Edit করা যাবে না।');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => in_array($this->record->status, ['draft', 'rejected'], true)),
        ];
    }

    /**
     * Strip every guarded / admin-only field from the payload
     * before Filament's standard update() call.
     * These fields must only be written via forceFill() in service classes.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['status'],
            $data['is_verified'],
            $data['is_featured'],
            $data['featured_until'],
            $data['approved_by_id'],
            $data['approved_at'],
            $data['approval_fee_charged'],
            $data['view_count'],
            $data['rejection_reason'],
            $data['cv_notes'],
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}