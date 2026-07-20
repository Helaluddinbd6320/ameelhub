<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Pages;

use App\Filament\Agent\Resources\MyJobPosts\MyJobPostsResource;
use Filament\Resources\Pages\EditRecord;

class EditMyJobPosts extends EditRecord
{
    protected static string $resource = MyJobPostsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}