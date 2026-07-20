<?php

namespace App\Filament\Admin\Resources\JobPostResource\Pages;

use App\Filament\Admin\Resources\JobPostResource\JobPostResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJobPost extends ViewRecord
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}