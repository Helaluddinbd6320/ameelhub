<?php

namespace App\Filament\Admin\Resources\JobPostResource;

use App\Filament\Admin\Resources\JobPostResource\Pages\CreateJobPost;
use App\Filament\Admin\Resources\JobPostResource\Pages\EditJobPost;
use App\Filament\Admin\Resources\JobPostResource\Pages\ListJobPosts;
use App\Filament\Admin\Resources\JobPostResource\Pages\ViewJobPost;
use App\Filament\Admin\Resources\JobPostResource\Schemas\JobPostForm;
use App\Filament\Admin\Resources\JobPostResource\Tables\JobPostTable;
use App\Models\JobPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class JobPostResource extends Resource
{
    protected static ?string $model = JobPost::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $modelLabel = 'Job Post';

    protected static ?string $pluralModelLabel = 'Job Posts';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'job_title';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.jobs');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.job_post');
    }

    public static function form(Schema $schema): Schema
    {
        return JobPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobPostTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListJobPosts::route('/'),
            'create' => CreateJobPost::route('/create'),
            'view'   => ViewJobPost::route('/{record}'),
            'edit'   => EditJobPost::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->job_title;
    }
}