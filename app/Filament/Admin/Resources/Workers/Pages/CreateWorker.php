<?php

namespace App\Filament\Admin\Resources\Workers\Pages;

use App\Filament\Admin\Resources\WorkerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateWorker extends CreateRecord
{
    protected static string $resource = WorkerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid']            = (string) Str::uuid();
        $data['submitted_by_id'] = auth()->id();

        // status গুার্ডেড, তাই এখানে set করি
        if (! isset($data['status'])) {
            $data['status'] = 'draft';
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // guarded fields আলাদা করে forceFill দিয়ে save করুন
        $guarded = [
            'status'       => $data['status'] ?? 'draft',
            'is_verified'  => $data['is_verified'] ?? false,
            'is_featured'  => $data['is_featured'] ?? false,
            'featured_until' => $data['featured_until'] ?? null,
        ];

        // guarded ফিল্ড fillable data থেকে বাদ দিন
        foreach (array_keys($guarded) as $key) {
            unset($data[$key]);
        }

        $record = static::getModel()::create($data);
        $record->forceFill($guarded)->save();

        return $record;
    }
}