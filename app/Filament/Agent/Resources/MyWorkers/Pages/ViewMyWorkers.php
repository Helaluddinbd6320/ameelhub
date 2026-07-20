<?php

namespace App\Filament\Agent\Resources\MyWorkers\Pages;

use App\Filament\Agent\Resources\MyWorkers\MyWorkersResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMyWorkers extends ViewRecord
{
    protected static string $resource = MyWorkersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['draft', 'rejected'], true)),
        ];
    }
}