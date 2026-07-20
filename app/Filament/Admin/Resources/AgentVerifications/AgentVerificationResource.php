<?php

namespace App\Filament\Admin\Resources\AgentVerifications;

use App\Filament\Admin\Resources\AgentVerifications\Pages\EditAgentVerification;
use App\Filament\Admin\Resources\AgentVerifications\Pages\ListAgentVerifications;
use App\Filament\Admin\Resources\AgentVerifications\Schemas\AgentVerificationForm;
use App\Filament\Admin\Resources\AgentVerifications\Tables\AgentVerificationTable;
use App\Models\AgentProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AgentVerificationResource extends Resource
{
    protected static ?string $model = AgentProfile::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'Agent Verification';

    protected static ?string $pluralModelLabel = 'Agent Verifications';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.agents');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.agent_verification');
    }

    public static function form(Schema $schema): Schema
    {
        return AgentVerificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentVerificationTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('is_verified', false)
            ->whereNotNull('passport_copy')
            ->whereNotNull('nid_copy')
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgentVerifications::route('/'),
            'edit' => EditAgentVerification::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }
}