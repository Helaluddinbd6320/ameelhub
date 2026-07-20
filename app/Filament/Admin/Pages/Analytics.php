<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AnalyticsStatsOverview;
use App\Filament\Admin\Widgets\CvViewsChartWidget;
use App\Filament\Admin\Widgets\DealConversionChartWidget;
use App\Filament\Admin\Widgets\JobStatsChartWidget;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.analytics';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.analytics');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.analytics');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticsStatsOverview::class,
            CvViewsChartWidget::class,
            JobStatsChartWidget::class,
            DealConversionChartWidget::class,
            RevenueChartWidget::class,
        ];
    }
}