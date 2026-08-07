<?php
namespace App\Filament\Admin\Resources\PaymentAccounts\Pages;

use App\Filament\Admin\Resources\PaymentAccounts\PaymentAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentAccounts extends ListRecords
{
    protected static string $resource = PaymentAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}