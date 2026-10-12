<?php

namespace App\Filament\Widgets;

use App\Models\TripTicket;
use App\Models\Driver;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class DriverTripsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Driver Performance (Trips Completed)';
    
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = TripTicket::where('status', 'completed');

        if ($startDate) {
            $query->whereHas('vehicleRequest', fn ($q) => $q->where('date', '>=', $startDate));
        }
        if ($endDate) {
            $query->whereHas('vehicleRequest', fn ($q) => $q->where('date', '<=', $endDate));
        }

        $data = $query->groupBy('driver_id')
            ->select('driver_id', DB::raw('count(*) as count'))
            ->pluck('count', 'driver_id')
            ->toArray();

        $drivers = Driver::all();
        $labels = [];
        $chartData = [];

        foreach ($drivers as $driver) {
            $labels[] = $driver->name;
            $chartData[] = $data[$driver->id] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Completed Trips',
                    'data' => $chartData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.85)', // emerald
                    'borderColor' => '#059669',
                    'borderWidth' => 1.5,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'border' => [
                        'display' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Driver (CSU Fleet Drivers)',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                        'padding' => [
                            'top' => 10,
                        ],
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => true,
                    'beginAtZero' => true,
                    'suggestedMax' => 6,
                    'border' => [
                        'display' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Completed Trips (Count)',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                        'padding' => [
                            'bottom' => 10,
                        ],
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                        'font' => [
                            'size' => 12,
                            'weight' => '600',
                        ],
                    ],
                    'grid' => [
                        'display' => true,
                    ],
                ],
            ],
        ];
    }
}
