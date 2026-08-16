<?php

namespace App\Filament\Employee\Widgets;

use App\Models\VehicleRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $totalRequests = VehicleRequest::where('user_id', $userId)->count();
        $pendingRequests = VehicleRequest::where('user_id', $userId)->where('status', 'pending')->count();
        $approvedRequests = VehicleRequest::where('user_id', $userId)->where('status', 'approved')->count();
        $completedRequests = VehicleRequest::where('user_id', $userId)->where('status', 'completed')->count();

        return [
            Stat::make('Total Requests', $totalRequests)
                ->description('Total requests you submitted')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
                
            Stat::make('Pending Approval', $pendingRequests)
                ->description('Requests awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Approved Requests', $approvedRequests)
                ->description('Approved trips scheduled')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Completed Trips', $completedRequests)
                ->description('Trips completed successfully')
                ->descriptionIcon('heroicon-m-flag')
                ->color('success'),
        ];
    }
}
