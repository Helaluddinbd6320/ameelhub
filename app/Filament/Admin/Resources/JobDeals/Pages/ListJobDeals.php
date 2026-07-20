<?php

namespace App\Filament\Admin\Resources\JobDeals\Pages;

use App\Filament\Admin\Resources\JobDeals\JobDealResource;
use Filament\Resources\Pages\ListRecords;

class ListJobDeals extends ListRecords
{
    protected static string $resource = JobDealResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}