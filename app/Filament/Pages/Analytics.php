<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use BackedEnum;

class Analytics extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $routePath = '/analytics';

    protected static ?string $title = 'Fleet Analytics Dashboard';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?int $navigationSort = 5;

    public function getColumns(): int | array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printReport')
                ->label('Export / Print Report')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(function () {
                    $filters = $this->filters ?? [];
                    return route('analytics.print', $filters);
                })
                ->openUrlInNewTab(),
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                DatePicker::make('startDate')
                    ->label('Start date')
                    ->placeholder('dd/mm/yyyy'),
                DatePicker::make('endDate')
                    ->label('End date')
                    ->placeholder('dd/mm/yyyy'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'completed' => 'Completed',
                        'rejected' => 'Disapproved',
                    ])
                    ->placeholder('Select an option')
                    ->label('Trip status'),
                Select::make('department')
                    ->options([
                        'COT' => 'COT',
                        'CBA' => 'CBA',
                        'CAS' => 'CAS',
                        'CTED' => 'CTED',
                        'CHM' => 'CHM',
                        'CCJE' => 'CCJE',
                        'Office of the CEO' => 'Office of the CEO',
                    ])
                    ->placeholder('Select an option')
                    ->label('Department'),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AnalyticsOverview::class,
            \App\Filament\Widgets\BookingsOverTimeChart::class,
            \App\Filament\Widgets\PassengerGrowthChart::class,
            \App\Filament\Widgets\VehicleUsageChart::class,
            \App\Filament\Widgets\FuelExpensesChart::class,
            \App\Filament\Widgets\AnalyticsTripLogsWidget::class,
        ];
    }
}
