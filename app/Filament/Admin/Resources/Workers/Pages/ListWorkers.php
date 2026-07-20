<?php

namespace App\Filament\Admin\Resources\Workers\Pages;

use App\Filament\Admin\Resources\WorkerResource;
use App\Models\Worker;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWorkers extends ListRecords
{
    protected static string $resource = WorkerResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('সব')
                ->badge(fn () => Worker::count()),

            'pending' => Tab::make('Pending অনুমোদন')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => Worker::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => Worker::where('status', 'active')->count())
                ->badgeColor('success'),

            'featured' => Tab::make('Featured')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'featured'))
                ->badge(fn () => Worker::where('status', 'featured')->count())
                ->badgeColor('primary'),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge(fn () => Worker::where('status', 'rejected')->count())
                ->badgeColor('danger'),

            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'inactive')),
        ];
    }
}