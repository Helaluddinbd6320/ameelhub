<?php

namespace App\Filament\Admin\Resources\WithdrawalRequests;

use App\Filament\Admin\Resources\WithdrawalRequests\Pages\ListWithdrawalRequests;
use App\Filament\Admin\Resources\WithdrawalRequests\Tables\WithdrawalRequestsTable;
use App\Models\WithdrawalRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class WithdrawalRequestResource extends Resource
{
    protected static ?string $model = WithdrawalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $modelLabel = 'Withdrawal Request';
    protected static ?string $pluralModelLabel = 'Withdrawal Requests';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.withdrawal_request');
    }

    public static function table(Table $table): Table
    {
        return WithdrawalRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWithdrawalRequests::route('/'),
        ];
    }

    // Withdrawal request শুধুমাত্র User panel (Agent/Worker) থেকে তৈরি হয় — Admin ম্যানুয়ালি তৈরি করে না
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}