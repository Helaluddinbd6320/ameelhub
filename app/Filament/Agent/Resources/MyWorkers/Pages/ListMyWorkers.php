<?php

namespace App\Filament\Agent\Resources\MyWorkers\Pages;

use App\Filament\Agent\Resources\MyWorkers\MyWorkersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyWorkers extends ListRecords
{
    protected static string $resource = MyWorkersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('নতুন Worker CV যোগ করুন'),
        ];
    }
}