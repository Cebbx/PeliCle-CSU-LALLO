<?php

namespace App\Filament\Widgets;

use App\Models\VehicleRequest;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class VehicleUsageChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Vehicle Usage Frequency';

    protected ?string $description = 'Comparative trip frequency and utilization count per vehicle (X-Axis: Vehicles, Y-Axis: Usage Frequency)';
    
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterVehicle = $this->filters['vehicle'] ?? null;
        $filterDriver = $this->filters['driver'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;

        $matchedVehicleName = null;
        if ($filterVehicle) {
            $dbVehicle = \App\Models\Vehicle::where('plate_number', $filterVehicle)->first();
            $matchedVehicleName = $dbVehicle ? $dbVehicle->brand : $filterVehicle;
        }

        $query = VehicleRequest::whereIn('status', ['approved', 'on_trip', 'completed'])
            ->whereNotNull('vehicle');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        if ($matchedVehicleName) {
            $query->where('vehicle', 'like', '%' . $matchedVehicleName . '%');
        }
        if ($filterDriver) {
            $query->whereHas('tripTicket', function ($q) use ($filterDriver) {
                $q->where('driver_id', $filterDriver);
            });
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        $data = $query->groupBy('vehicle')
            ->select('vehicle', DB::raw('count(*) as count'))
            ->pluck('count', 'vehicle')
            ->toArray();

        $vehicles = \App\Models\Vehicle::all();
        $chartData = [];
        $labels = [];
        $backgroundColors = [];
        $borderColors = [];

        $palette = [
            ['bg' => 'rgba(245, 158, 11, 0.85)', 'border' => '#d97706'], // Amber / Gold
            ['bg' => 'rgba(59, 130, 246, 0.85)', 'border' => '#2563eb'],  // Electric Blue
            ['bg' => 'rgba(16, 185, 129, 0.85)', 'border' => '#059669'],  // Emerald Green
            ['bg' => 'rgba(139, 92, 246, 0.85)', 'border' => '#7c3aed'],  // Purple
            ['bg' => 'rgba(236, 72, 153, 0.85)', 'border' => '#db2777'],  // Pink
            ['bg' => 'rgba(14, 165, 233, 0.85)', 'border' => '#0284c7'],  // Cyan / Sky
        ];
        
        foreach ($vehicles as $index => $dbVehicle) {
            $vehiclePlate = $dbVehicle->plate_number;
            $label = "{$dbVehicle->brand} ({$vehiclePlate})";
            $labels[] = $label;
            
            // Find count by checking if the vehicle name in our query results contains the plate number
            $count = 0;
            foreach ($data as $vehicleKey => $val) {
                if (str_contains(strtoupper($vehicleKey), strtoupper($vehiclePlate))) {
                    $count += $val;
                }
            }
            $chartData[] = $count;

            $color = $palette[$index % count($palette)];
            $backgroundColors[] = $color['bg'];
            $borderColors[] = $color['border'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Trips / Bookings',
                    'data' => $chartData,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
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
                    'display' => false, // Hidden to remove the awkward single-color square clashing with multi-colored vehicle bars
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
                        'text' => 'Vehicle (Model & Plate Number)',
                        'font' => [
                            'size' => 13,
                            'weight' => 'bold',
                        ],
                        'padding' => [
                            'top' => 12,
                        ],
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                            'weight' => '600',
                        ],
                    ],
                ],
                'y' => [
                    'display' => true,
                    'beginAtZero' => true,
                    'suggestedMax' => 8, // Gives comfortable breathing room so the tallest bar does not hit the ceiling!
                    'border' => [
                        'display' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Usage Frequency (Number of Trips / Bookings)',
                        'font' => [
                            'size' => 13,
                            'weight' => 'bold',
                        ],
                        'padding' => [
                            'bottom' => 12,
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
