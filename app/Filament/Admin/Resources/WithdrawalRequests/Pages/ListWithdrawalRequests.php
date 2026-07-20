<?php

namespace App\Filament\Admin\Resources\WithdrawalRequests\Pages;

use App\Filament\Admin\Resources\WithdrawalRequests\WithdrawalRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawalRequests extends ListRecords
{
    protected static string $resource = WithdrawalRequestResource::class;
}