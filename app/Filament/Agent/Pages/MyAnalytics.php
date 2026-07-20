<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Widgets\AgentAnalyticsStatsOverview;
use App\Filament\Agent\Widgets\AgentMonthlyDealsChartWidget;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class MyAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected string $view = 'filament.agent.pages.my-analytics';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.deals_payments');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_analytics');
    }

    public function getTitle(): string
    {
        return __('messages.navigation.resources.my_analytics');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AgentAnalyticsStatsOverview::class,
            AgentMonthlyDealsChartWidget::class,
        ];
    }
}