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

    protected ?string $heading = 'Passenger Demand & Fleet Growth';
    
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
        if ($filterDept) $query->where('department', $filterDept);

        $labels = [];
        $passengerData = [];

        $startMonth = Carbon::now()->subMonths(6)->startOfMonth();
        for ($i = 0; $i < 7; $i++) {
            $monthObj = $startMonth->copy()->addMonths($i);
            $monthStr = $monthObj->format('Y-m');
            $labels[] = $monthObj->format('M Y');

            $passengerSum = (clone $query)
                ->where(DB::raw("strftime('%Y-%m', date)"), $monthStr)
                ->sum('number_of_passengers');

            $passengerData[] = (int) $passengerSum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Passengers Carried',
                    'data' => $passengerData,
                    'borderColor' => '#10b981', // electric emerald green matching screenshot
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
}
