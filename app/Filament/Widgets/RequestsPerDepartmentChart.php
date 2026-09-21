<?php

namespace App\Filament\Widgets;

use App\Models\VehicleRequest;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class RequestsPerDepartmentChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Demand Share by Department (Top Requesters)';
    
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;
        $filterDept = $this->filters['department'] ?? null;

        $query = VehicleRequest::query();

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        if ($filterDept) {
            $query->where('department', $filterDept);
        }

        $data = $query->whereNotNull('department')
            ->where('department', '!=', '')
            ->groupBy('department')
            ->select('department', DB::raw('count(*) as count'))
            ->orderByDesc('count')
            ->pluck('count', 'department')
            ->toArray();

        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Total Requests',
                        'data' => [0],
                        'backgroundColor' => ['#94a3b8'],
                    ],
                ],
                'labels' => ['No Data'],
            ];
        }

        // Option 1: Top 6 Departments + "Other Offices" (Clean UI/UX standard)
        $limit = 6;
        if (count($data) > $limit && empty($filterDept)) {
            $topData = array_slice($data, 0, $limit, true);
            $others = array_slice($data, $limit, null, true);
            $otherSum = array_sum($others);

            $finalData = $topData;
            if ($otherSum > 0) {
                $otherCount = count($others);
                $finalData["Other Offices ({$otherCount})"] = $otherSum;
            }
        } else {
            $finalData = $data;
        }

        $labels = array_keys($finalData);
        $chartData = array_values($finalData);

        $colors = [
            '#3b82f6', // Electric Blue
            '#10b981', // Emerald Green
            '#f59e0b', // Amber / Gold
            '#8b5cf6', // Violet
            '#06b6d4', // Cyan
            '#ec4899', // Pink
            '#94a3b8', // Slate Gray for Others
            '#f97316', // Orange
            '#14b8a6', // Teal
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Total Requests',
                    'data' => $chartData,
                    'backgroundColor' => array_slice(array_merge($colors, $colors), 0, count($chartData)),
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
