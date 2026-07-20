<?php

namespace App\Filament\Agent\Resources\MyWorkers\Pages;

use App\Filament\Agent\Resources\MyWorkers\MyWorkersResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyWorkers extends CreateRecord
{
    protected static string $resource = MyWorkersResource::class;

    /**
     * Stamp ownership and lifecycle fields before the record hits the DB.
     * worker_user_id = null because this CV is agent-managed;
     * the worker has no panel login linked to it.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid']             = (string) str()->uuid();
        $data['submitted_by_id'] = auth()->id();
        $data['worker_user_id']  = null;
        $data['status']          = 'draft';
        $data['nationality']     = $data['nationality'] ?? 'Bangladeshi';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}