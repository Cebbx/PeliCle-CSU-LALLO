<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleRequest;
use App\Models\WithdrawalSlip;
use App\Models\TripTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterVehicle = $this->filters['vehicle'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;

        $totalDrivers = Driver::count();
        $activeDriverIds = TripTicket::where('status', 'active')
            ->pluck('driver_id')
            ->filter()
            ->unique()
            ->toArray();
        $activeDriversCount = count($activeDriverIds);
        $unavailableDriversCount = Driver::where('status', 'unavailable')->count();
        $availDrivers = max(0, $totalDrivers - $activeDriversCount - $unavailableDriversCount);

        $totalVehicles = Vehicle::count();
        $activeVehiclePlates = TripTicket::where('status', 'active')
            ->pluck('vehicle')
            ->filter()
            ->map(function ($v) {
                if (str_contains($v, ' - ')) {
                    $parts = explode(' - ', $v);
                    return trim(end($parts));
                }
                return trim($v);
            })
            ->unique()
            ->toArray();
        $activeVehiclesCount = count($activeVehiclePlates);
        $maintenanceVehiclesCount = Vehicle::where('status', 'maintenance')->count();
        $availVehicles = max(0, $totalVehicles - $activeVehiclesCount - $maintenanceVehiclesCount);

        // 1. Pending Vehicle Requests
        $pendingQuery = VehicleRequest::where('status', 'pending');
        if ($startDate) {
            $pendingQuery->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $pendingQuery->where('date', '<=', $endDate);
        }
        if ($filterVehicle) {
            $pendingQuery->where('vehicle', 'like', '%' . $filterVehicle . '%');
        }
        $pendingRequests = $pendingQuery->count();

        // 2. Approved Vehicle Requests
        $approvedQuery = VehicleRequest::where('status', 'approved');
        if ($startDate) {
            $approvedQuery->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $approvedQuery->where('date', '<=', $endDate);
        }
        if ($filterVehicle) {
            $approvedQuery->where('vehicle', 'like', '%' . $filterVehicle . '%');
        }
        $approvedRequests = $approvedQuery->count();

        // 3. Active Trips
        $activeTripsQuery = TripTicket::where('status', 'active');
        if ($startDate) {
            $activeTripsQuery->whereHas('vehicleRequest', function ($q) use ($startDate) {
                $q->where('date', '>=', $startDate);
            });
        }
        if ($endDate) {
            $activeTripsQuery->whereHas('vehicleRequest', function ($q) use ($endDate) {
                $q->where('date', '<=', $endDate);
            });
        }
        if ($filterVehicle) {
            $activeTripsQuery->where('vehicle', 'like', '%' . $filterVehicle . '%');
        }
        $activeTrips = $activeTripsQuery->count();

        // 4. Pending Slips
        $pendingSlipsQuery = WithdrawalSlip::where('status', 'pending');
        if ($startDate) {
            $pendingSlipsQuery->whereHas('tripTicket.vehicleRequest', function ($q) use ($startDate) {
                $q->where('date', '>=', $startDate);
            });
        }
        if ($endDate) {
            $pendingSlipsQuery->whereHas('tripTicket.vehicleRequest', function ($q) use ($endDate) {
                $q->where('date', '<=', $endDate);
            });
        }
        if ($filterVehicle) {
            $pendingSlipsQuery->whereHas('tripTicket', function ($q) use ($filterVehicle) {
                $q->where('vehicle', 'like', '%' . $filterVehicle . '%');
            });
        }
        $pendingSlips = $pendingSlipsQuery->count();

        // 5. Gas Expenses Calculations
        $todayGas = WithdrawalSlip::where('status', 'approved')
            ->whereDate('created_at', today())
            ->sum('amount');

        $thisWeekGas = WithdrawalSlip::where('status', 'approved')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $thisMonthGas = WithdrawalSlip::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $todayGasFormatted = number_format($todayGas, 2);
        $thisWeekGasFormatted = number_format($thisWeekGas, 2);
        $thisMonthGasFormatted = number_format($thisMonthGas, 2);

        return [
            Stat::make('Driver Availability', "{$availDrivers} / {$totalDrivers} Available")
                ->description('Drivers ready for dispatch')
                ->descriptionIcon('heroicon-m-users')
                ->chart([$totalDrivers - $availDrivers, $totalDrivers, $availDrivers, $totalDrivers, $availDrivers])
                ->color('success')
                ->url(\App\Filament\Resources\Drivers\DriverResource::getUrl()),

            Stat::make('Vehicle Availability', "{$availVehicles} / {$totalVehicles} Available")
                ->description('Vehicles ready for dispatch')
                ->descriptionIcon('heroicon-m-truck')
                ->chart([$totalVehicles - $availVehicles, $totalVehicles, $availVehicles, $totalVehicles, $availVehicles])
                ->color('success')
                ->url(\App\Filament\Resources\Vehicles\VehicleResource::getUrl()),
                
            Stat::make('Pending Vehicle Requests', $pendingRequests)
                ->description('Requests awaiting admin review')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([1, $pendingRequests + 2, max(0, $pendingRequests - 1), $pendingRequests])
                ->color($pendingRequests > 0 ? 'warning' : 'gray')
                ->url(\App\Filament\Resources\VehicleRequests\VehicleRequestResource::getUrl()),

            Stat::make('Approved Vehicle Requests', $approvedRequests)
                ->description('Requests approved and ticketed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([1, 2, 0, $approvedRequests])
                ->color('success')
                ->url(\App\Filament\Resources\VehicleRequests\VehicleRequestResource::getUrl()),
                
            Stat::make('Active Trips (On Trip)', $activeTrips)
                ->description('Trips currently on the road')
                ->descriptionIcon('heroicon-m-truck')
                ->chart([1, 2, $activeTrips + 1, $activeTrips])
                ->color('info')
                ->url(\App\Filament\Resources\TripTickets\TripTicketResource::getUrl()),
                
            Stat::make('Pending Slips', $pendingSlips)
                ->description('Fuel slips awaiting approval')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([2, $pendingSlips + 1, $pendingSlips])
                ->color($pendingSlips > 0 ? 'warning' : 'gray')
                ->url(\App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource::getUrl()),

            Stat::make('This Month\'s Gas Expenses', "₱{$thisMonthGasFormatted}")
                ->description("Today: ₱{$todayGasFormatted} | Week: ₱{$thisWeekGasFormatted}")
                ->descriptionIcon('heroicon-m-fire')
                ->chart([$todayGas, $thisWeekGas / 7, $thisWeekGas, $thisMonthGas])
                ->color('danger')
                ->url(\App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource::getUrl()),
        ];
    }
}
