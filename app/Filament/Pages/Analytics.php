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
            Action::make('resetFilters')
                ->label('Reset Filters')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->filters = [];
                    $this->filtersForm->fill();
                }),
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
            ->columns(['default' => 1, 'sm' => 2, 'lg' => 5])
            ->components([
                Select::make('period')
                    ->label('Quick Period')
                    ->placeholder('Custom Range')
                    ->options([
                        'this_month' => 'This Month (' . date('M Y') . ')',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year (' . date('Y') . ')',
                        'all_time' => 'All Time',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'this_month') {
                            $set('startDate', now()->startOfMonth()->toDateString());
                            $set('endDate', now()->endOfMonth()->toDateString());
                        } elseif ($state === 'last_month') {
                            $set('startDate', now()->subMonth()->startOfMonth()->toDateString());
                            $set('endDate', now()->subMonth()->endOfMonth()->toDateString());
                        } elseif ($state === 'this_year') {
                            $set('startDate', now()->startOfYear()->toDateString());
                            $set('endDate', now()->endOfYear()->toDateString());
                        } elseif ($state === 'all_time') {
                            $set('startDate', null);
                            $set('endDate', null);
                        }
                    }),
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
                    ->placeholder('All Statuses')
                    ->label('Trip status'),
                Select::make('department')
                    ->options(function () {
                        $official = [
                            'Office of the CEO' => 'Office of the CEO',
                            'Administration Office' => 'Administration Office',
                            'HRMO' => 'HRMO',
                            'Accounting Office' => 'Accounting Office',
                            'Budget Office' => 'Budget Office',
                            'Property and Supply Office' => 'Property and Supply Office',
                            'Records Office' => 'Records Office',
                            'Planning Office' => 'Planning Office',
                            'MIS Office' => 'MIS Office',
                            'Office of the Campus Registrar' => 'Office of the Campus Registrar',
                            'Campus Admission Office' => 'Campus Admission Office',
                            'Campus Publication Office' => 'Campus Publication Office',
                            'University Library' => 'University Library',
                            'CICS' => 'CICS (Computing Sciences)',
                            'CTE' => 'CTE / CTED (Teacher Education)',
                            'CHM' => 'CHM (Hospitality Management)',
                            'COA' => 'COA (Agriculture)',
                            'Café Valena' => 'Café Valena',
                            'Campus Student Council' => 'Campus Student Council (CSC)',
                        ];

                        $fromDb = \App\Models\VehicleRequest::whereNotNull('department')
                            ->where('department', '!=', '')
                            ->distinct()
                            ->pluck('department', 'department')
                            ->toArray();

                        return array_merge($official, $fromDb);
                    })
                    ->searchable()
                    ->placeholder('All Departments')
                    ->label('Department'),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AnalyticsOverview::class,
            \App\Filament\Widgets\BookingsOverTimeChart::class,
            \App\Filament\Widgets\PassengerGrowthChart::class,
            \App\Filament\Widgets\RequestsPerDepartmentChart::class,
            \App\Filament\Widgets\DriverTripsChart::class,
            \App\Filament\Widgets\VehicleUsageChart::class,
            \App\Filament\Widgets\FuelExpensesChart::class,
            \App\Filament\Widgets\AnalyticsTripLogsWidget::class,
        ];
    }
}
