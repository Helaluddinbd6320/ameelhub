<?php

namespace App\Filament\Agent\Resources\MyWorkers;

use App\Filament\Agent\Resources\MyWorkers\Pages\CreateMyWorkers;
use App\Filament\Agent\Resources\MyWorkers\Pages\EditMyWorkers;
use App\Filament\Agent\Resources\MyWorkers\Pages\ListMyWorkers;
use App\Filament\Agent\Resources\MyWorkers\Pages\ViewMyWorkers;
use App\Filament\Agent\Resources\MyWorkers\Schemas\MyWorkersForm;
use App\Filament\Agent\Resources\MyWorkers\Tables\MyWorkersTable;
use App\Models\Worker;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MyWorkersResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $modelLabel = 'Worker CV';

    protected static ?string $pluralModelLabel = 'My Workers';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.my_workers');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_workers');
    }

    public static function form(Schema $schema): Schema
    {
        return MyWorkersForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MyWorkersTable::configure($table);
    }

    // Agent শুধু নিজের submit করা CVs দেখতে পাবে
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('submitted_by_id', Filament::auth()->id());
    }

    // শুধু draft/rejected এ edit করা যাবে
    public static function canEdit(Model $record): bool
    {
        return in_array($record->status, ['draft', 'rejected'], true);
    }

    // শুধু draft/rejected এ delete করা যাবে
    public static function canDelete(Model $record): bool
    {
        return in_array($record->status, ['draft', 'rejected'], true);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMyWorkers::route('/'),
            'create' => CreateMyWorkers::route('/create'),
            'view'   => ViewMyWorkers::route('/{record}'),
            'edit'   => EditMyWorkers::route('/{record}/edit'),
        ];
    }
}