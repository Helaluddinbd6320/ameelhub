<?php

namespace App\Filament\Admin\Resources\RechargeRequests;

use App\Filament\Admin\Resources\RechargeRequests\Pages\ListRechargeRequests;
use App\Filament\Admin\Resources\RechargeRequests\Tables\RechargeRequestsTable;
use App\Models\RechargeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class RechargeRequestResource extends Resource
{
    protected static ?string $model = RechargeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $modelLabel = 'Recharge Request';
    protected static ?string $pluralModelLabel = 'Recharge Requests';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.recharge_request');
    }

    public static function table(Table $table): Table
    {
        return RechargeRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRechargeRequests::route('/'),
        ];
    }

    // Recharge request শুধুমাত্র Worker/Agent panel থেকে তৈরি হয়
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