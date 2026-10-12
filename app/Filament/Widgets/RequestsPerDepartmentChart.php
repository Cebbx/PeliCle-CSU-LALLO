<?php

namespace App\Filament\Widgets;

use App\Models\VehicleRequest;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class RequestsPerDepartmentChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Demand Share by Offices (Top Requesters)';
    
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '240px';

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

    protected function getOptions(): RawJs
    {
        $officeFullNames = json_encode([
            'CICS' => 'College of Information and Computing Sciences',
            'CHM' => 'College of Hospitality Management',
            'COT' => 'College of Technology',
            'CBA' => 'College of Business Administration',
            'CAS' => 'College of Arts and Sciences',
            'CCJE' => 'College of Criminal Justice Education',
            'CTE' => 'College of Teacher Education',
            'CTED' => 'College of Teacher Education',
            'COA' => 'College of Agriculture',
            'University Library' => 'University Library',
            'Office of the CEO' => 'Office of the Campus Executive Officer',
            'HRMO' => 'Human Resource Management Office',
            'Accounting Office' => 'Accounting Office',
            'Budget Office' => 'Budget Office',
            'Property and Supply Office' => 'Property and Supply Office',
            'Records Office' => 'Records Office',
            'Planning Office' => 'Planning Office',
            'MIS Office' => 'Management Information System Office',
            'Office of the Campus Registrar' => 'Office of the Campus Registrar',
            'Campus Admission Office' => 'Campus Admission Office',
            'Campus Publication Office' => 'Campus Publication Office',
            'Café Valena' => 'Café Valena (CoffeeHub Café)',
            'Campus Student Council' => 'Campus Student Council (CSC)',
            'Administration Office' => 'Administration Office',
        ]);

        return RawJs::make(<<<JS
            {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 8,
                            font: {
                                size: 11
                            }
                        },
                        onHover: function(event, legendItem, legend) {
                            if (event.native && event.native.target) {
                                event.native.target.style.cursor = 'pointer';
                            }
                            const chart = legend.chart;
                            if (chart && chart.tooltip) {
                                chart.tooltip.setActiveElements([{
                                    datasetIndex: 0,
                                    index: legendItem.index
                                }], {
                                    x: event.x,
                                    y: event.y
                                });
                                chart.update();
                            }
                        },
                        onLeave: function(event, legendItem, legend) {
                            if (event.native && event.native.target) {
                                event.native.target.style.cursor = 'default';
                            }
                            const chart = legend.chart;
                            if (chart && chart.tooltip) {
                                chart.tooltip.setActiveElements([], { x: 0, y: 0 });
                                chart.update();
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                if (!tooltipItems || !tooltipItems.length) return '';
                                const label = tooltipItems[0].label || '';
                                const fullNames = {$officeFullNames};
                                const fullName = fullNames[label];
                                if (fullName && fullName !== label) {
                                    return label + ' - ' + fullName;
                                }
                                return fullName || label;
                            },
                            label: function(context) {
                                const val = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ' Total Requests: ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        JS);
    }
}
