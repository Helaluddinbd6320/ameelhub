<?php

namespace App\Filament\Admin\Resources\AgentVerifications\Pages;

use App\Filament\Admin\Resources\AgentVerifications\AgentVerificationResource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;

class ListAgentVerifications extends ListRecords
{
    protected static string $resource = AgentVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('সব'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn ($query) => $query->where('is_verified', false))
                ->badge(fn () => \App\Models\AgentProfile::where('is_verified', false)
                    ->whereNotNull('passport_copy')
                    ->whereNotNull('nid_copy')
                    ->count()),

            'verified' => Tab::make('Verified')
                ->modifyQueryUsing(fn ($query) => $query->where('is_verified', true)),
        ];
    }
}
