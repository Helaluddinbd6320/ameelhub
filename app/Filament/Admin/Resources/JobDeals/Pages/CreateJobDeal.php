<?php

namespace App\Filament\Admin\Resources\JobDeals\Pages;

use App\Filament\Admin\Resources\JobDeals\JobDealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobDeal extends CreateRecord
{
    protected static string $resource = JobDealResource::class;
}
