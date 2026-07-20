<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Pages;

use App\Filament\Agent\Resources\MyJobPosts\MyJobPostsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyJobPosts extends ListRecords
{
    protected static string $resource = MyJobPostsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('নতুন জব পোস্ট'),
        ];
    }
}