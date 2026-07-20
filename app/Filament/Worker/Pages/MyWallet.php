<?php

namespace App\Filament\Worker\Pages;

use App\Filament\Concerns\InteractsWithWallet;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class MyWallet extends Page
{
    use InteractsWithWallet;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';

    protected string $view = 'filament.worker.pages.my-wallet';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.deals_payments');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_wallet');
    }

    public function getTitle(): string
    {
        return __('messages.navigation.resources.my_wallet');
    }
}