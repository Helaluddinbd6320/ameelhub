<?php

namespace App\Filament\Admin\Resources\JobDeals;

use App\Filament\Admin\Resources\JobDeals\Pages\ListJobDeals;
use App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal;
use App\Filament\Admin\Resources\JobDeals\Tables\JobDealsTable;
use App\Models\JobDeal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class JobDealResource extends Resource
{
    protected static ?string $model = JobDeal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'ডিল';
    protected static ?string $pluralModelLabel = 'ডিল সমূহ';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.deals');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.job_deal');
    }

    public static function table(Table $table): Table
    {
        return JobDealsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobDeals::route('/'),
            'view'  => ViewJobDeal::route('/{record}'),
        ];
    }

    // Deal শুধুমাত্র system flow (Worker Accept → Escrow Hold) দিয়েই তৈরি হয় — ম্যানুয়াল CRUD নেই
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