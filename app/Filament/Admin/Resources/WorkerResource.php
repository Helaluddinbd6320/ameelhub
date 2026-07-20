<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Workers\Pages\CreateWorker;
use App\Filament\Admin\Resources\Workers\Pages\EditWorker;
use App\Filament\Admin\Resources\Workers\Pages\ListWorkers;
use App\Filament\Admin\Resources\Workers\Schemas\WorkerForm;
use App\Filament\Admin\Resources\Workers\Tables\WorkerTable;
use App\Models\Worker;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name_en';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.workers_cvs');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.worker');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return WorkerForm::configure($schema);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return WorkerTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkers::route('/'),
            'create' => CreateWorker::route('/create'),
            'edit'   => EditWorker::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }
}