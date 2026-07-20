<?php

namespace App\Filament\Admin\Resources\JobPostResource\Pages;

use App\Filament\Admin\Resources\JobPostResource\JobPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJobPosts extends ListRecords
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}