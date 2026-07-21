<?php

namespace App\Filament\Admin\Resources\RechargeRequests\Pages;

use App\Filament\Admin\Resources\RechargeRequests\RechargeRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRechargeRequest extends EditRecord
{
    protected static string $resource = RechargeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
