<?php

namespace App\Filament\Admin\Resources\Workers\Pages;

use App\Filament\Admin\Resources\WorkerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => in_array(
                    $this->record->status,
                    ['draft', 'rejected']
                )),
        ];
    }

    protected function handleRecordUpdate(
        \Illuminate\Database\Eloquent\Model $record,
        array $data
    ): \Illuminate\Database\Eloquent\Model {
        // guarded fields আলাদা forceFill দিয়ে update
        $guarded = [];
        foreach (['status', 'is_verified', 'is_featured', 'featured_until', 'cv_notes', 'rejection_reason'] as $field) {
            if (array_key_exists($field, $data)) {
                $guarded[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        $record->update($data);

        if (! empty($guarded)) {
            $record->forceFill($guarded)->save();
        }

        return $record;
    }
}