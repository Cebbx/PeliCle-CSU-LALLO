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
        if ($filterDept) {
            $variants = match ($filterDept) {
                'CICS' => ['CICS', 'College of Information and Computing Sciences'],
                'CTE' => ['CTE', 'CTED', 'College of Teacher Education'],
                'CHM' => ['CHM', 'College of Hospitality Management'],
                'COA' => ['COA', 'College of Agriculture'],
                'Office of the CEO' => ['Office of the CEO', 'Office of the Campus Executive Officer'],
                'HRMO' => ['HRMO', 'Human Resource Management Office'],
                'MIS Office' => ['MIS Office', 'Management Information System Office'],
                'DMST Office', 'Department of Military Science and Tactics Office', 'DMST' => ['DMST Office', 'Department of Military Science and Tactics Office', 'Department of military Science and Tactics Offices', 'DMST'],
                'Campus Student Council' => ['Campus Student Council', 'Campus Student Council (CSC)', 'CSC'],
                default => [$filterDept],
            };
            $query->whereIn('department', $variants);
        }

        $labels = [];
        $passengerData = [];

        $startMonth = Carbon::now()->subMonths(6)->startOfMonth();
        for ($i = 0; $i < 7; $i++) {
            $monthObj = $startMonth->copy()->addMonths($i);
            $monthStr = $monthObj->format('Y-m');
            $labels[] = $monthObj->format('M Y');

            $monthQuery = (clone $query)->where(DB::raw("strftime('%Y-%m', date)"), $monthStr);
            
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Passengers / Trip',
                    ],
                ],
            ],
        ];
    }
}
