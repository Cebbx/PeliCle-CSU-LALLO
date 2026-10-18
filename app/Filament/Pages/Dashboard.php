<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Driver;
use App\Models\TripTicket;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\WithdrawalSlip;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected ?string $pollingInterval = '30s';

    protected string $view = 'filament.pages.admin-dashboard';

    public string $tripPeriod = 'this_week';

    public function getMaxContentWidth(): \Filament\Support\Enums\Width | string | null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getViewData(): array
    {
        return [
            'driverStats' => $this->getDriverStats(),
            'vehicleStats' => $this->getVehicleStats(),
            'pendingRequests' => $this->getPendingRequestsCount(),
            'approvedRequests' => $this->getApprovedRequestsCount(),
            'activeTrips' => $this->getActiveTripsCount(),
            'pendingSlips' => $this->getPendingWithdrawalSlipsCount(),
            'mostTravelers' => $this->getMostTravelers(),
            'statusBreakdown' => $this->getVehicleRequestStatusBreakdown(),
            'recentRequests' => $this->getRecentRequests(),
            'tripActivity' => $this->getTripActivityData(),
            'emergencyIncidents' => $this->getRecentEmergencyIncidents(),
            'latestTransactions' => $this->getLatestTransactions(),
            'widgets' => $this->getWidgets(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('download_backup')
                ->label('Backup Database')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('admin.backup.download')),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getDriverStats(): array
    {
        $total = Driver::count();
        $activeTripDriverIds = TripTicket::where('status', 'active')
            ->pluck('driver_id')
            ->filter()
            ->unique()
            ->toArray();
        $driverTableOnTripIds = Driver::where('status', 'on_trip')->pluck('id')->toArray();
        $allBusyIds = array_unique(array_merge($activeTripDriverIds, $driverTableOnTripIds));
        $onTripCount = count($allBusyIds);

        $offDutyCount = Driver::whereIn('status', ['off_duty', 'unavailable'])->count();
        $available = Driver::where('status', 'available')
            ->whereNotIn('id', $allBusyIds)
            ->count();

        return [
            'total' => $total,
            'available' => $available,
            'on_trip' => $onTripCount,
            'unavailable' => $offDutyCount,
            'off_duty' => $offDutyCount,
        ];
    }

    public function getVehicleStats(): array
    {
        $total = Vehicle::count();
        $activePlates = TripTicket::where('status', 'active')
            ->pluck('vehicle')
            ->filter()
            ->map(fn ($v) => str_contains($v, ' - ') ? trim(explode(' - ', $v)[1] ?? $v) : trim($v))
            ->unique()
            ->toArray();

        $maintenanceCount = Vehicle::where('status', 'maintenance')->count();
        $onTripCount = count($activePlates);
        $available = Vehicle::where('status', 'available')
            ->whereNotIn('plate_number', $activePlates)
            ->count();

        return [
            'total' => $total,
            'available' => $available,
            'on_trip' => $onTripCount,
            'maintenance' => $maintenanceCount,
        ];
    }

    public function getPendingRequestsCount(): int
    {
        VehicleRequest::expirePastPendingRequests();
        return VehicleRequest::where('status', 'pending')->count();
    }

    public function getApprovedRequestsCount(): int
    {
        return VehicleRequest::where('status', 'approved')->count();
    }

    public function getActiveTripsCount(): int
    {
        return TripTicket::where('status', 'active')->count();
    }

    public function getPendingWithdrawalSlipsCount(): int
    {
        return WithdrawalSlip::where('status', 'pending')->count();
    }



    public function getTripActivityData(): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $counts = [];
        $dates = [];

        $startOfWeek = Carbon::now('Asia/Manila')->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            $count = TripTicket::whereDate('created_at', $currentDay->toDateString())->count();
            $counts[] = $count;
            $dates[] = $currentDay->format('M d');
        }

        $maxVal = max(1, max($counts));
        $chartMax = max(4, $maxVal + 1);

        $coords = [];
        $width = 350;
        $padX = 20;
        $usableWidth = $width - ($padX * 2);
        $topY = 25;
        $bottomY = 120;
        $usableHeight = $bottomY - $topY;

        for ($i = 0; $i < 7; $i++) {
            $x = round($padX + ($i * ($usableWidth / 6)), 1);
            $val = $counts[$i];
            $y = round($bottomY - (($val / $chartMax) * $usableHeight), 1);
            $coords[] = [
                'x' => $x,
                'y' => $y,
                'count' => $val,
                'day' => $days[$i],
                'date' => $dates[$i],
            ];
        }

        $linePath = "M " . $coords[0]['x'] . "," . $coords[0]['y'];
        for ($i = 1; $i < count($coords); $i++) {
            $prev = $coords[$i - 1];
            $curr = $coords[$i];
            $midX = ($prev['x'] + $curr['x']) / 2;
            $linePath .= " C {$midX},{$prev['y']} {$midX},{$curr['y']} {$curr['x']},{$curr['y']}";
        }

        $areaPath = $linePath . " L {$coords[6]['x']},{$bottomY} L {$coords[0]['x']},{$bottomY} Z";

        return [
            'labels' => $days,
            'data' => $counts,
            'total' => array_sum($counts),
            'max' => $chartMax,
            'points' => $coords,
            'linePath' => $linePath,
            'areaPath' => $areaPath,
        ];
    }

    public function getVehicleRequestStatusBreakdown(): array
    {
        VehicleRequest::expirePastPendingRequests();
        $total = VehicleRequest::count();

        $pending = VehicleRequest::where('status', 'pending')->count();
        $approved = VehicleRequest::where('status', 'approved')->count();
        $completed = VehicleRequest::where('status', 'completed')->count();
        $rejected = VehicleRequest::where('status', 'rejected')->count();
        $expired = VehicleRequest::where('status', 'expired')->count();

        $pendingPct = $total > 0 ? round(($pending / $total) * 100, 1) : 0;
        $approvedPct = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
        $completedPct = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        $rejectedPct = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;
        $expiredPct = $total > 0 ? round(($expired / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'pending_pct' => $pendingPct,
            'approved' => $approved,
            'approved_pct' => $approvedPct,
            'completed' => $completed,
            'completed_pct' => $completedPct,
            'rejected' => $rejected,
            'rejected_pct' => $rejectedPct,
            'expired' => $expired,
            'expired_pct' => $expiredPct,
        ];
    }

    public function getRecentRequests()
    {
        VehicleRequest::expirePastPendingRequests();
        return VehicleRequest::latest('id')->take(7)->get();
    }

    public function getMostTravelers(int $limit = 5): array
    {
        $requests = VehicleRequest::whereNotIn('status', ['rejected', 'cancelled', 'expired'])
            ->get();

        if ($requests->isEmpty()) {
            $requests = VehicleRequest::whereNotIn('status', ['rejected', 'cancelled'])->get();
        }

        $counts = [];

        foreach ($requests as $req) {
            $seenInThisTrip = [];

            // 1. Lead Requester
            $emp = trim($req->employee_name ?? '');
            if ($emp !== '') {
                $key = mb_strtolower($emp);
                $seenInThisTrip[$key] = [
                    'name' => $emp,
                    'department' => $req->department ?: 'General',
                ];
            }

            // 2. Passengers
            $passengers = $req->passenger_names;
            if (is_string($passengers)) {
                $decoded = json_decode($passengers, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $passengers = $decoded;
                }
            }

            if (is_array($passengers)) {
                foreach ($passengers as $p) {
                    $pName = trim(is_string($p) ? $p : ($p['name'] ?? ''));
                    if ($pName !== '') {
                        $key = mb_strtolower($pName);
                        if (!isset($seenInThisTrip[$key])) {
                            $seenInThisTrip[$key] = [
                                'name' => $pName,
                                'department' => $req->department ?: 'General',
                            ];
                        }
                    }
                }
            }

            foreach ($seenInThisTrip as $key => $data) {
                if (!isset($counts[$key])) {
                    $counts[$key] = [
                        'name' => $data['name'],
                        'department' => $data['department'],
                        'trips' => 0,
                    ];
                }
                $counts[$key]['trips']++;
                if ($counts[$key]['department'] === 'General' && $data['department'] !== 'General') {
                    $counts[$key]['department'] = $data['department'];
                }
            }
        }

        uasort($counts, fn($a, $b) => $b['trips'] <=> $a['trips']);

        return array_values(array_slice($counts, 0, $limit));
    }

    public function getRecentEmergencyIncidents()
    {
        $dismissed = session('dismissed_incident_ids', []);
        return ActivityLog::where(function ($query) {
                $query->where('action', 'Emergency Breakdown Reported')
                      ->orWhere('action', 'Breakdown Reported')
                      ->orWhere('details', 'like', '%Breakdown%')
                      ->orWhere('details', 'like', '%Flat Tire%')
                      ->orWhere('details', 'like', '%nasiraan%');
            })
            ->where('created_at', '>=', Carbon::now('Asia/Manila')->subDays(3))
            ->whereNotIn('id', is_array($dismissed) ? $dismissed : [])
            ->latest('created_at')
            ->take(5)
            ->get();
    }

    public function getLatestTransactions()
    {
        return ActivityLog::latest('created_at')->take(15)->get();
    }

    public function dismissIncident(int $logId): void
    {
        $dismissed = session('dismissed_incident_ids', []);
        if (!is_array($dismissed)) {
            $dismissed = [];
        }
        $dismissed[] = $logId;
        session(['dismissed_incident_ids' => array_unique($dismissed)]);

        \Filament\Notifications\Notification::make()
            ->title('Incident Acknowledged')
            ->body('The emergency breakdown report has been marked as acknowledged.')
            ->success()
            ->send();
    }
}
