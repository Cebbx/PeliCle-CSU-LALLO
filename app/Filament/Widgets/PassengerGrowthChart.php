<?php

namespace App\Filament\Widgets;

use App\Models\VehicleRequest;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PassengerGrowthChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Average Passengers per Trip (Monthly Trend)';
    
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonths(6)->startOfMonth()->format('Y-m-d');
        $endDate = $this->filters['endDate'] ?? now()->format('Y-m-d');
        $filterStatus = $this->filters['status'] ?? null;
        $filterDept = $this->filters['department'] ?? null;

        $query = VehicleRequest::query();
        if ($filterStatus) $query->where('status', $filterStatus);
        if ($filterDept) $query->where('department', $filterDept);

        $labels = [];
        $passengerData = [];

        $startMonth = Carbon::now()->subMonths(6)->startOfMonth();
        for ($i = 0; $i < 7; $i++) {
            $monthObj = $startMonth->copy()->addMonths($i);
            $monthStr = $monthObj->format('Y-m');
            $labels[] = $monthObj->format('M Y');

            $monthQuery = (clone $query)
                ->whereYear('date', $monthObj->year)
                ->whereMonth('date', $monthObj->month);
            
            $tripCount = $filterStatus 
                ? (clone $monthQuery)->count()
                : (clone $monthQuery)->whereIn('status', ['approved', 'on_trip', 'completed'])->count();

            $passengerSum = (clone $monthQuery)->sum('number_of_passengers');

            $avg = $tripCount > 0 ? round($passengerSum / $tripCount, 1) : 0;
            $passengerData[] = $avg;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Avg Passengers / Trip',
                    'data' => $passengerData,
                    'borderColor' => '#10b981', // emerald green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                    'borderWidth' => 2.5,
                    'tension' => 0.35,
                    'fill' => true,
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
                        'text' => 'Timeline (Month & Year)',
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
                    'border' => [
                        'display' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Average Passengers / Trip',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                        'padding' => [
                            'bottom' => 10,
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
