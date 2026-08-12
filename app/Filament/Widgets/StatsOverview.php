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

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterVehicle = $this->filters['vehicle'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;

        $totalDrivers = Driver::count();
        $availDrivers = Driver::where('status', 'available')->count();

        // 1. Pending Vehicle Requests
        $pendingQuery = VehicleRequest::where('status', 'pending');
        if ($startDate) {
            $pendingQuery->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $pendingQuery->where('date', '<=', $endDate);
        }
        if ($filterVehicle) {
            $pendingQuery->where('vehicle', $filterVehicle);
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
            $approvedQuery->where('vehicle', $filterVehicle);
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
            $activeTripsQuery->where('vehicle', $filterVehicle);
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
                $q->where('vehicle', $filterVehicle);
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
            Stat::make('Active Drivers', "{$availDrivers} / {$totalDrivers} Available")
                ->description('Drivers currently active')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->url(\App\Filament\Resources\Drivers\DriverResource::getUrl()),
                
            Stat::make('Pending Vehicle Requests', $pendingRequests)
                ->description('Requests awaiting admin review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($pendingRequests > 0 ? 'warning' : 'gray')
                ->url(\App\Filament\Resources\VehicleRequests\VehicleRequestResource::getUrl()),

            Stat::make('Approved Vehicle Requests', $approvedRequests)
                ->description('Requests approved and ticketed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(\App\Filament\Resources\VehicleRequests\VehicleRequestResource::getUrl()),
                
            Stat::make('Active Trips (On Trip)', $activeTrips)
                ->description('Trips currently on the road')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->url(\App\Filament\Resources\TripTickets\TripTicketResource::getUrl()),
                
            Stat::make('Pending Slips', $pendingSlips)
                ->description('Fuel slips awaiting approval')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingSlips > 0 ? 'warning' : 'gray')
                ->url(\App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource::getUrl()),

            Stat::make('This Month\'s Gas Expenses', "₱{$thisMonthGasFormatted}")
                ->description("Today: ₱{$todayGasFormatted} | Week: ₱{$thisWeekGasFormatted}")
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger')
                ->url(\App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource::getUrl()),
        ];
    }
}
