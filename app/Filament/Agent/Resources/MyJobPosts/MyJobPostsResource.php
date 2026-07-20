<?php

namespace App\Filament\Agent\Resources\MyJobPosts;

use App\Filament\Agent\Resources\MyJobPosts\Pages\CreateMyJobPosts;
use App\Filament\Agent\Resources\MyJobPosts\Pages\EditMyJobPosts;
use App\Filament\Agent\Resources\MyJobPosts\Pages\ListMyJobPosts;
use App\Filament\Agent\Resources\MyJobPosts\Pages\ViewMyJobPosts;
use App\Filament\Agent\Resources\MyJobPosts\Schemas\MyJobPostsForm;
use App\Filament\Agent\Resources\MyJobPosts\Tables\MyJobPostsTable;
use App\Models\JobPost;
use BackedEnum;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Agent\Resources\MyJobPosts\Pages\BrowseWorkers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MyJobPostsResource extends Resource
{
    protected static ?string $model = JobPost::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $modelLabel = 'Job Post';

    protected static ?string $pluralModelLabel = 'My Job Posts';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.my_job_posts');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_job_posts');
    }

    public static function form(Schema $schema): Schema
    {
        return MyJobPostsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MyJobPostsTable::configure($table);
    }

    // Agent শুধু নিজের পোস্ট করা Job গুলো দেখবে
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('posted_by_id', Filament::auth()->id());
    }

    // draft, pending, paused অবস্থায় Edit করা যাবে (active/closed এ না)
    public static function canEdit(Model $record): bool
    {
        return in_array($record->status, ['draft', 'pending', 'paused'], true);
    }

    // শুধু draft বা rejected অবস্থায় Delete করা যাবে
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
            'index'  => ListMyJobPosts::route('/'),
            'create' => CreateMyJobPosts::route('/create'),
            'view'   => ViewMyJobPosts::route('/{record}'),
            'edit'   => EditMyJobPosts::route('/{record}/edit'),
            'browse-workers'  => BrowseWorkers::route('/{record}/browse-workers'),
            'job-interests' => Pages\JobInterests::route('/{record}/interests'),

        ];
    }
}