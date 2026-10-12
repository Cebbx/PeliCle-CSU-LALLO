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

    protected ?string $maxHeight = '260px';

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
        } else {
            // Default: Bilangin lamang ang mga balido at aprubadong biyahe (approved, on_trip, completed)
            $query->whereIn('status', ['approved', 'on_trip', 'completed']);
        }
        if ($filterDept) {
            $variants = match ($filterDept) {
                'CICS' => ['CICS', 'College of Information and Computing Sciences'],
                'CTE' => ['CTE', 'CTED', 'College of Teacher Education'],
                'CHM' => ['CHM', 'College of Hospitality Management'],
                'COA' => ['COA', 'College of Agriculture'],
                'Office of the CEO' => ['Office of the CEO', 'Office of the Campus Executive Officer'],
                'HRMO' => ['HRMO', 'Human Resource Management Office'],
                'MIS Office' => ['MIS Office', 'Management Information System Office'],
                'Campus Student Council' => ['Campus Student Council', 'Campus Student Council (CSC)', 'CSC'],
                default => [$filterDept],
            };
            $query->whereIn('department', $variants);
        }

        // I-exclude ang mga hindi sakop na colleges (CBA, CAS, CCJE, COT)
        $query->whereNotIn('department', ['CBA', 'CAS', 'CCJE', 'COT']);

        // Kunin ang lahat ng requests na may department mula sa 18/19 official offices
        $records = $query->whereNotNull('department')
            ->where('department', '!=', '')
            ->select('department')
            ->get();

        $data = [];
        foreach ($records as $record) {
            $dept = trim($record->department);
            if ($dept === '') continue;
            // I-normalize ang CTED sa CTE para tugma ang Teacher Education
            if ($dept === 'CTED') {
                $dept = 'CTE';
            }
            $data[$dept] = ($data[$dept] ?? 0) + 1;
        }

        arsort($data);

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

        $labels = array_keys($data);
        $chartData = array_values($data);

        $colorMap = [
            'CICS' => '#f97316', // Orange (User requested)
            'CTE' => '#3b82f6',  // Blue (User requested)
            'COA' => '#10b981',  // Green (User requested)
            'CHM' => '#ec4899',  // Pink (User requested)
            'University Library' => '#8b5cf6', // Violet
            'Records Office' => '#f59e0b',     // Amber / Gold
            'Campus Student Council' => '#06b6d4', // Cyan
            'Office of the CEO' => '#6366f1',  // Indigo
            'HRMO' => '#14b8a6',              // Teal
            'Accounting Office' => '#84cc16',  // Lime
            'Budget Office' => '#64748b',      // Slate
            'Property and Supply Office' => '#f43f5e', // Rose
            'Planning Office' => '#d946ef',    // Fuchsia
            'MIS Office' => '#0ea5e9',         // Sky Blue
            'Office of the Campus Registrar' => '#a855f7', // Purple
            'Campus Admission Office' => '#eab308', // Yellow
            'Campus Publication Office' => '#78716c', // Stone
            'Café Valena' => '#b45309',        // Warm Bronze
            'Administration Office' => '#71717a', // Neutral Gray
        ];

        $fallbackPalette = [
            '#0284c7', '#059669', '#d97706', '#7c3aed', '#db2777', '#4b5563', '#e11d48'
        ];

        $backgroundColors = [];
        $fallbackIdx = 0;
        foreach ($labels as $label) {
            if (isset($colorMap[$label])) {
                $backgroundColors[] = $colorMap[$label];
            } else {
                $backgroundColors[] = $fallbackPalette[$fallbackIdx % count($fallbackPalette)];
                $fallbackIdx++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Requests',
                    'data' => $chartData,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): RawJs
    {
        $officeMapJs = "{
            'CICS': 'College of Information and Computing Sciences',
            'CHM': 'College of Hospitality Management',
            'CTE': 'College of Teacher Education',
            'CTED': 'College of Teacher Education',
            'COA': 'College of Agriculture',
            'University Library': 'University Library',
            'Office of the CEO': 'Office of the Campus Executive Officer',
            'HRMO': 'Human Resource Management Office',
            'Accounting Office': 'Accounting Office',
            'Budget Office': 'Budget Office',
            'Property and Supply Office': 'Property and Supply Office',
            'Records Office': 'Records Office',
            'Planning Office': 'Planning Office',
            'MIS Office': 'Management Information System Office',
            'Office of the Campus Registrar': 'Office of the Campus Registrar',
            'Campus Admission Office': 'Campus Admission Office',
            'Campus Publication Office': 'Campus Publication Office',
            'Café Valena': 'Café Valena (CoffeeHub Café)',
            'Campus Student Council': 'Campus Student Council (CSC)',
            'Administration Office': 'Administration Office'
        }";

        return RawJs::make("{
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        padding: 8,
                        font: { size: 11 }
                    },
                    onHover: function(e, item, leg) {
                        if (e.native && e.native.target) {
                            e.native.target.style.cursor = 'pointer';
                        }
                        var c = leg.chart;
                        if (c && c.tooltip) {
                            c.tooltip.setActiveElements([{ datasetIndex: 0, index: item.index }], { x: e.x, y: e.y });
                            c.update();
                        }
                    },
                    onLeave: function(e, item, leg) {
                        if (e.native && e.native.target) {
                            e.native.target.style.cursor = 'default';
                        }
                        var c = leg.chart;
                        if (c && c.tooltip) {
                            c.tooltip.setActiveElements([], { x: 0, y: 0 });
                            c.update();
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            if (!items || !items.length) return '';
                            var lbl = items[0].label || '';
                            var map = {$officeMapJs};
                            return (map[lbl] && map[lbl] !== lbl) ? (lbl + ' - ' + map[lbl]) : lbl;
                        },
                        label: function(ctx) {
                            var v = ctx.raw || 0;
                            var ds = ctx.dataset && ctx.dataset.data ? ctx.dataset.data : [];
                            var sum = 0;
                            for (var i = 0; i !== ds.length; i++) { sum += (ds[i] || 0); }
                            var p = sum !== 0 ? ((v / sum) * 100).toFixed(1) : '0';
                            return ' Total Requests: ' + v + ' (' + p + '%)';
                        }
                    }
                }
            }
        }");
    }
}
