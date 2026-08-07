<?php
namespace App\Filament\Admin\Resources\PaymentAccounts\Pages;

use App\Filament\Admin\Resources\PaymentAccounts\PaymentAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentAccount extends CreateRecord
{
    protected static string $resource = PaymentAccountResource::class;
}