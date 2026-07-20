<?php

namespace App\Filament\Admin\Resources\JobPostResource\Pages;

use App\Filament\Admin\Resources\JobPostResource\JobPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobPost extends CreateRecord
{
    protected static string $resource = JobPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = (string) str()->uuid();
        $data['posted_by_id'] = $data['posted_by_id'] ?? auth()->id();

        return $data;
    }
}