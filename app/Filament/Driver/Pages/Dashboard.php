<?php

namespace App\Filament\Driver\Pages;

use App\Models\ActivityLog;
use App\Models\Driver;
use App\Models\SmsLog;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WithdrawalSlip;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected ?string $pollingInterval = '2s';

    protected string $view = 'filament.driver.pages.driver-dashboard';

    public bool $showBreakdownModal = false;
    public string $breakdownCategory = 'Flat Tire';
    public string $breakdownComment = '';
    public string $breakdownLocation = '';
    public string $passengerStatus = 'Safe roadside with Driver';

    public function getDriverModel(): ?\App\Models\Driver
    {
        $user = \Filament\Facades\Filament::auth()->user() ?? auth('driver')->user() ?? auth()->user();
        return $user ? \App\Models\Driver::where('name', $user->name)->first() : null;
    }

    public function getAssignedTrips()
    {
        $driverId = $this->getDriverModel()?->id ?? 0;
        
        return TripTicket::where('driver_id', $driverId)
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'pending' THEN 2 ELSE 3 END")
            ->latest('updated_at')
            ->latest('created_at')
            ->limit(30)
            ->get();
    }

    public function getCompletedTripsCount()
    {
        $driverId = $this->getDriverModel()?->id ?? 0;
        return TripTicket::where('driver_id', $driverId)->where('status', 'completed')->count();
    }

    public function getActiveTrip()
    {
        $driverId = $this->getDriverModel()?->id ?? 0;
        return TripTicket::where('driver_id', $driverId)->where('status', 'active')->first();
    }

    public function toggleDutyStatus()
    {
        $driver = $this->getDriverModel();
        if (!$driver) {
            return;
        }

        if ($driver->status === 'on_trip') {
            \Filament\Notifications\Notification::make()
                ->title('Cannot Change Status')
                ->body('You cannot go offline while you have an active trip.')
                ->danger()
                ->send();
            return;
        }

        $isCurrentlyOffline = in_array($driver->status, ['off_duty', 'unavailable']);
        $newStatus = $isCurrentlyOffline ? 'available' : 'off_duty';
        $driver->update(['status' => $newStatus]);

        if (auth()->check()) {
            auth()->user()->unsetRelation('driver');
        }

        \Filament\Notifications\Notification::make()
            ->title('Status Updated')
            ->body("Your duty status is now set to " . ($newStatus === 'available' ? 'Available' : 'Off-Duty') . ".")
            ->success()
            ->send();
    }

    public function completeActiveTrip(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Action Restricted')
            ->body('Tanging Security Guard (gamit ang QR scanner sa gate) o GSO Admin lamang ang may pahintulot na mag-complete ng biyahe.')
            ->warning()
            ->send();
    }

    public function openBreakdownModal(): void
    {
        $this->showBreakdownModal = true;
        $this->breakdownCategory = 'Flat Tire';
        $this->breakdownComment = '';
        $this->breakdownLocation = '';
        $this->passengerStatus = 'Safe roadside with Driver';
    }

    public function closeBreakdownModal(): void
    {
        $this->showBreakdownModal = false;
    }

    public function submitBreakdownReport(): void
    {
        $activeTrip = $this->getActiveTrip();
        if (!$activeTrip) {
            $this->showBreakdownModal = false;
            return;
        }

        $category = trim($this->breakdownCategory ?: 'Flat Tire');
        $comment = trim($this->breakdownComment ?: 'Vehicle breakdown reported by driver.');
        $location = trim($this->breakdownLocation ?: 'En route / Location not specified');
        $passenger = trim($this->passengerStatus ?: 'Safe roadside with Driver');
        $driverUser = auth()->user();
        $driverName = $driverUser?->name ?? 'Driver';

        $fullReason = "Breakdown [{$category}] - {$comment} (Location: {$location} | Passengers: {$passenger})";

        // 1. Log in Activity Log with detailed structured text
        ActivityLog::log(
            'Emergency Breakdown Reported',
            $activeTrip,
            "Driver {$driverName} reported [{$category}] for Trip {$activeTrip->ticket_number} (Vehicle: {$activeTrip->vehicle}) at '{$location}'. Passenger Status: {$passenger}. Details: {$comment}"
        );

        // 2. Cancel Trip Ticket and save the breakdown details into cancellation_reason
        $activeTrip->update([
            'status' => 'cancelled',
            'cancellation_reason' => $fullReason,
        ]);

        // 3. Also update all associated vehicle requests and notify requesters
        $allRequests = $activeTrip->vehicleRequests()->get();
        if ($allRequests->isEmpty() && $activeTrip->vehicleRequest) {
            $allRequests = collect([$activeTrip->vehicleRequest]);
        }
        foreach ($allRequests as $req) {
            $req->updateQuietly([
                'status' => 'cancelled',
                'cancellation_reason' => "Trip aborted due to vehicle breakdown [{$category}]: {$comment} (Location: {$location})",
            ]);
            if ($req->user) {
                try {
                    Notification::make()
                        ->title("⚠️ Trip Aborted: Vehicle Breakdown Reported")
                        ->body("Ang inyong biyahe para sa Request #{$req->request_number} ay na-abort dahil sa aberya ({$category}) sa {$location}. Ligtas po ang mga pasahero kasama ang driver.")
                        ->danger()
                        ->persistent()
                        ->sendToDatabase($req->user);
                } catch (\Throwable $e) {}
            }
        }

        // 4. Mark vehicle under maintenance and append maintenance notes
        $parts = explode(' - ', $activeTrip->vehicle);
        $plate = end($parts);
        $vehicle = Vehicle::where('plate_number', trim($plate))->first();
        if ($vehicle) {
            $now = Carbon::now('Asia/Manila')->format('M d, Y h:i A');
            $note = "[{$now} EMERGENCY BREAKDOWN] Reported by Driver {$driverName}: {$category} - {$comment} (Location: {$location})";
            $existing = $vehicle->maintenance_notes ? $vehicle->maintenance_notes . "\n---\n" : '';
            $vehicle->update([
                'status' => 'maintenance',
                'maintenance_notes' => $existing . $note,
            ]);
        }

        // 5. Mark driver as unavailable
        $driver = $this->getDriverModel();
        if ($driver) {
            $driver->update(['status' => 'unavailable']);
        }

        // 6. Record SMS Log for emergency dispatch tracking
        try {
            SmsLog::create([
                'driver_id' => $driver?->id,
                'phone_number' => $driver?->contact_number ?? '0917-555-8888',
                'message' => "EMERGENCY: Driver {$driverName} reported [{$category}] at {$location}. Vehicle: {$activeTrip->vehicle}. TT: {$activeTrip->ticket_number}. Details: {$comment}",
            ]);
        } catch (\Throwable $e) {}

        // 7. Send Filament Database Notification to all Admins
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::make()
                ->title("🚨 EMERGENCY: {$category} Reported!")
                ->body("Driver: {$driverName} | Vehicle: {$activeTrip->vehicle}\n📍 Location: {$location}\n💬 Details: {$comment}")
                ->danger()
                ->persistent()
                ->actions([
                    Action::make('view_trip')
                        ->label('View Trip Ticket')
                        ->url("/admin/trip-tickets/{$activeTrip->id}"),
                ])
                ->sendToDatabase($admins);
        }

        $this->showBreakdownModal = false;

        Notification::make()
            ->title('🚨 Emergency Report Sent to Admin')
            ->body("Na-send na po sa GSO Admin ang inyong breakdown report ({$category}). Naka-record na ito sa system at naka-maintenance na ang sasakyan.")
            ->danger()
            ->persistent()
            ->send();
    }

    public function reportBreakdown($reason = 'Mechanical / Engine Breakdown'): void
    {
        $this->breakdownCategory = $reason;
        $this->submitBreakdownReport();
    }

    public function logDeparture()
    {
        $activeTrip = $this->getActiveTrip();
        if (!$activeTrip || $this->hasLoggedDeparture($activeTrip->id)) {
            return;
        }

        \App\Models\ActivityLog::log(
            'Departure Logged',
            $activeTrip,
            "Driver " . auth()->user()->name . " logged departure milestone for trip " . $activeTrip->ticket_number
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('🚗 Driver Departed: ' . $activeTrip->ticket_number)
                ->body("Driver " . auth()->user()->name . " has started trip {$activeTrip->ticket_number} with vehicle {$activeTrip->vehicle}.")
                ->icon('heroicon-o-paper-airplane')
                ->iconColor('info')
                ->sendToDatabase($admins);
        }

        if ($activeTrip->vehicleRequest && $activeTrip->vehicleRequest->user) {
            \Filament\Notifications\Notification::make()
                ->title('🚗 Your Trip Has Departed (' . $activeTrip->ticket_number . ')')
                ->body("Driver " . auth()->user()->name . " has departed for {$activeTrip->vehicleRequest->destination} using {$activeTrip->vehicle}.")
                ->icon('heroicon-o-paper-airplane')
                ->iconColor('info')
                ->sendToDatabase($activeTrip->vehicleRequest->user);
        }

        \Filament\Notifications\Notification::make()
            ->title('Departure Logged')
            ->body('Departure milestone has been digitally recorded.')
            ->success()
            ->send();
    }

    public function logArrival()
    {
        $activeTrip = $this->getActiveTrip();
        if (!$activeTrip || !$this->hasLoggedDeparture($activeTrip->id) || $this->hasLoggedArrival($activeTrip->id)) {
            return;
        }

        \App\Models\ActivityLog::log(
            'Arrival Logged',
            $activeTrip,
            "Driver " . auth()->user()->name . " logged arrival milestone at destination for trip " . $activeTrip->ticket_number
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('📍 Driver Arrived at Destination: ' . $activeTrip->ticket_number)
                ->body("Driver " . auth()->user()->name . " arrived at destination ({$activeTrip->vehicleRequest?->destination}) for trip {$activeTrip->ticket_number}.")
                ->icon('heroicon-o-map-pin')
                ->iconColor('success')
                ->sendToDatabase($admins);
        }

        if ($activeTrip->vehicleRequest && $activeTrip->vehicleRequest->user) {
            \Filament\Notifications\Notification::make()
                ->title('📍 Arrived at Destination (' . $activeTrip->ticket_number . ')')
                ->body("Driver " . auth()->user()->name . " has safely arrived at {$activeTrip->vehicleRequest->destination}.")
                ->icon('heroicon-o-map-pin')
                ->iconColor('success')
                ->sendToDatabase($activeTrip->vehicleRequest->user);
        }

        \Filament\Notifications\Notification::make()
            ->title('Arrival Logged')
            ->body('Arrival milestone has been digitally recorded.')
            ->success()
            ->send();
    }

    public function hasLoggedDeparture($tripId): bool
    {
        return \App\Models\ActivityLog::where('model_type', TripTicket::class)
            ->where('model_id', $tripId)
            ->where('action', 'Departure Logged')
            ->exists();
    }

    public function hasLoggedArrival($tripId): bool
    {
        return \App\Models\ActivityLog::where('model_type', TripTicket::class)
            ->where('model_id', $tripId)
            ->where('action', 'Arrival Logged')
            ->exists();
    }
}
