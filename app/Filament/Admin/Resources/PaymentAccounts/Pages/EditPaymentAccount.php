<?php
namespace App\Filament\Admin\Resources\PaymentAccounts\Pages;

use App\Filament\Admin\Resources\PaymentAccounts\PaymentAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentAccount extends EditRecord
{
    protected static string $resource = PaymentAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}