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
    
    protected static ?int $sort = 2;

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

        $allVehicles = \App\Models\Vehicle::pluck('plate_number')->toArray();
        $chartData = [];
        $labels = [];
        
        foreach ($allVehicles as $vehiclePlate) {
            $dbVehicle = \App\Models\Vehicle::where('plate_number', $vehiclePlate)->first();
            $label = $dbVehicle ? "{$dbVehicle->brand} - {$vehiclePlate}" : $vehiclePlate;
            $labels[] = $label;
            
            // Find count by checking if the vehicle name in our query results contains the plate number
            $count = 0;
            foreach ($data as $vehicleKey => $val) {
                if (str_contains(strtoupper($vehicleKey), strtoupper($vehiclePlate))) {
                    $count += $val;
                }
            }
            $chartData[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Bookings',
                    'data' => $chartData,
                    'backgroundColor' => '#f59e0b', // amber
                    'borderColor' => '#d97706',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
