<?php

namespace App\Filament\Admin\Resources\JobDeals\Pages;

use App\Filament\Admin\Resources\JobDeals\JobDealResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditJobDeal extends EditRecord
{
    protected static string $resource = JobDealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
