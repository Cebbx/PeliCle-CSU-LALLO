<?php

namespace App\Filament\Widgets;

use App\Models\TripTicket;
use App\Models\WithdrawalSlip;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class AnalyticsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterVehicle = $this->filters['vehicle'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;

        // Base query for trip tickets
        $tripQuery = TripTicket::query();

        if ($filterVehicle) {
            $tripQuery->where('vehicle', 'like', '%' . $filterVehicle . '%');
        }
        if ($filterStatus) {
            $tripQuery->where('status', $filterStatus);
        }
        if ($startDate || $endDate) {
            $tripQuery->whereHas('vehicleRequest', function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->where('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
            });
        }

        $trips = $tripQuery->get();
        $totalTrips = $trips->count();
        
        // Active/On Trip right now
        $activeTrips = $trips->where('status', 'active')->count();

        // Calculate unique drivers utilized
        $uniqueDrivers = $trips->pluck('driver_id')->filter()->unique()->count();

        // Calculate total gas expenses within filtered trips
        $tripIds = $trips->pluck('id')->toArray();
        $totalGas = 0;
        if (!empty($tripIds)) {
            $totalGas = WithdrawalSlip::whereIn('trip_ticket_id', $tripIds)
                ->sum('amount');
        }

        // Daily, Weekly, and Monthly fuel expenses
        $now = Carbon::now('Asia/Manila');
        $gasToday = WithdrawalSlip::whereDate('created_at', $now->toDateString())->sum('amount');
        $gasThisWeek = WithdrawalSlip::where('created_at', '>=', $now->copy()->startOfWeek()->toDateTimeString())->sum('amount');
        $gasThisMonth = WithdrawalSlip::where('created_at', '>=', $now->copy()->startOfMonth()->toDateTimeString())->sum('amount');

        return [
            Stat::make('Total Trips Assigned', $totalTrips)
                ->description('Total trips during this period')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
                
            Stat::make('Drivers Utilized', $uniqueDrivers)
                ->description('Unique active drivers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Total Fuel Expenses', '₱' . number_format($totalGas, 2))
                ->description('Gas spent (filtered period)')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Fuel Expenses Today', '₱' . number_format($gasToday, 2))
                ->description('Total gas spent today')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Fuel Expenses This Week', '₱' . number_format($gasThisWeek, 2))
                ->description('Total gas spent this week')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Fuel Expenses This Month', '₱' . number_format($gasThisMonth, 2))
                ->description('Total gas spent this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
        ];
    }
}
