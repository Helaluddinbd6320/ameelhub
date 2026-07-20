<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Pages;

use App\Filament\Agent\Resources\MyJobPosts\MyJobPostsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyJobPosts extends CreateRecord
{
    protected static string $resource = MyJobPostsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid']         = (string) str()->uuid();
        $data['posted_by_id'] = auth()->id();
        $data['status']       = 'draft';

        return $data;
    }
}