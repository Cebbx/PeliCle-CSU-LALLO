<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'vehicle_request_id',
        'driver_id',
        'vehicle',
        'status',
        'cancellation_reason',
        'document',
        'document_verified_at',
        'start_odometer',
        'end_odometer',
        'gate_out_at',
        'gate_in_at',
        'scanned_by',
    ];

    protected $casts = [
        'gate_out_at' => 'datetime',
        'gate_in_at' => 'datetime',
        'document_verified_at' => 'datetime',
    ];

    public function getDisplayGateOutAttribute()
    {
        if ($this->gate_out_at) {
            return $this->gate_out_at;
        }
        if ($this->vehicleRequest && $this->vehicleRequest->date && $this->vehicleRequest->time) {
            try {
                return \Carbon\Carbon::parse($this->vehicleRequest->date . ' ' . $this->vehicleRequest->time, 'Asia/Manila');
            } catch (\Exception $e) {}
        }
        return $this->created_at;
    }

    public function getDisplayGateInAttribute()
    {
        return $this->gate_in_at ?? $this->updated_at;
    }

    public function getDisplayScannedByAttribute(): string
    {
        if (!empty($this->scanned_by)) {
            return $this->scanned_by;
        }
        $guardNames = \App\Models\User::where('role', 'guard')->pluck('name')->toArray();
        if (!empty($guardNames)) {
            return $guardNames[$this->id % count($guardNames)];
        }
        return 'Gate Security Officer';
    }

    public function getDistanceTraveledAttribute(): ?int
    {
        if ($this->start_odometer !== null && $this->end_odometer !== null && $this->end_odometer >= $this->start_odometer) {
            return (int) ($this->end_odometer - $this->start_odometer);
        }
        return null;
    }

    public function getScheduledDepartureDateTime(): ?\Carbon\Carbon
    {
        $primaryReq = $this->vehicleRequest ?? $this->vehicleRequests()->first();
        if (!$primaryReq || empty($primaryReq->date)) {
            return null;
        }
        $time = $primaryReq->time ?: '00:00:00';
        try {
            return \Carbon\Carbon::parse("{$primaryReq->date} {$time}", 'Asia/Manila');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isDepartureTimeArrived(): bool
    {
        $dep = $this->getScheduledDepartureDateTime();
        if (!$dep) {
            return false;
        }
        return \Carbon\Carbon::now('Asia/Manila')->greaterThanOrEqualTo($dep);
    }

    public function isDepartureTimePassed(int $graceMinutes = 0): bool
    {
        $dep = $this->getScheduledDepartureDateTime();
        if (!$dep) {
            return false;
        }
        return \Carbon\Carbon::now('Asia/Manila')->diffInMinutes($dep, false) < -$graceMinutes;
    }

    public static function estimateDistance(string $destination): int
    {
        $dest = strtolower($destination);

        if (str_contains($dest, 'tuguegarao')) {
            return 185;
        } elseif (str_contains($dest, 'aparri')) {
            return 65;
        } elseif (str_contains($dest, 'lal-lo') || str_contains($dest, 'lallo')) {
            return 90;
        } elseif (str_contains($dest, 'claveria')) {
            return 30;
        } elseif (str_contains($dest, 'sta ana') || str_contains($dest, 'santa ana')) {
            return 125;
        } elseif (str_contains($dest, 'solana')) {
            return 175;
        } elseif (str_contains($dest, 'manila') || str_contains($dest, 'quezon')) {
            return 620;
        } elseif (str_contains($dest, 'baguio')) {
            return 450;
        } elseif (str_contains($dest, 'ballesteros')) {
            return 40;
        } elseif (str_contains($dest, 'pamplona')) {
            return 20;
        } elseif (str_contains($dest, 'lasam')) {
            return 75;
        } elseif (str_contains($dest, 'allacapan')) {
            return 50;
        }

        return 45;
    }

    public static function generateNextTicketNumber(?\Carbon\Carbon $date = null): string
    {
        $date = $date ?? now();
        $year = $date->format('Y');
        $month = $date->format('m');
        $prefix = "TT-{$year}-{$month}-";

        $existing = static::withTrashed()
            ->where('ticket_number', 'like', "{$prefix}%")
            ->pluck('ticket_number');

        $maxSeq = 0;
        foreach ($existing as $ticketNum) {
            $parts = explode('-', $ticketNum);
            $seqStr = end($parts);
            if (is_numeric($seqStr)) {
                $seq = (int) $seqStr;
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = str_pad($maxSeq + 1, 2, '0', STR_PAD_LEFT);
        $candidate = "{$prefix}{$nextSeq}";

        $counter = $maxSeq + 1;
        while (static::withTrashed()->where('ticket_number', $candidate)->exists()) {
            $counter++;
            $candidate = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function getFormattedTicketNumberAttribute(): string
    {
        $clean = preg_replace('/^TT-?/i', '', $this->ticket_number ?? '');
        return "TT No. Lal-lo - {$clean}";
    }

    protected static array $smsDispatchedTicketIds = [];

    protected static function booted(): void
    {
        static::created(function ($tripTicket) {
            $driverName = $tripTicket->driver?->name ?? 'N/A';
            \App\Models\ActivityLog::log('Approved & Ticketed', $tripTicket, "Created trip ticket {$tripTicket->ticket_number} for request {$tripTicket->vehicleRequest?->request_number}. Assigned Driver: {$driverName}. Vehicle: {$tripTicket->vehicle}");

            try {
                $allRequests = $tripTicket->vehicleRequests()->get();
                if ($allRequests->isEmpty() && $tripTicket->vehicleRequest) {
                    $allRequests = collect([$tripTicket->vehicleRequest]);
                }
                foreach ($allRequests as $req) {
                    if ($req && $req->user) {
                        \Filament\Notifications\Notification::make()
                            ->title('✅ Request Approved & Ticketed')
                            ->body("Your request {$req->request_number} to {$req->destination} was approved. Driver: {$driverName}, Vehicle: {$tripTicket->vehicle}.")
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->actions([
                                \Filament\Actions\Action::make('view')
                                    ->label('View Status')
                                    ->url('/employee/vehicle-requests'),
                            ])
                            ->sendToDatabase($req->user);
                    }
                }

                // Only notify Driver and send SMS upon creation if CEO signed document is already attached AND verified
                if (!empty($tripTicket->document_verified_at) && (!empty($tripTicket->document) || !empty($tripTicket->vehicleRequest?->document))) {
                    $driverUser = $tripTicket->driver?->getUserAccount();
                    if ($driverUser) {
                        $dest = $tripTicket->vehicleRequest?->destination ?? 'Assigned Destination';
                        \Filament\Notifications\Notification::make()
                            ->title('🚐 New Trip Assigned: ' . $tripTicket->ticket_number)
                            ->body("CEO signed document is ready. You have been assigned to drive {$tripTicket->vehicle} to {$dest}.")
                            ->icon('heroicon-o-truck')
                            ->iconColor('info')
                            ->actions([
                                \Filament\Actions\Action::make('view')
                                    ->label('View Trip Ticket')
                                    ->url('/driver/trip-tickets'),
                            ])
                            ->sendToDatabase($driverUser);
                    }
                    $tripTicket->sendSmsNotification();
                }
            } catch (\Throwable $e) {
                // Ignore notification errors
            }
        });

        static::creating(function ($tripTicket) {
            $tripTicket->loadMissing('vehicleRequest');
            if ($tripTicket->vehicleRequest?->document && !$tripTicket->document) {
                $tripTicket->document = $tripTicket->vehicleRequest->document;
            }
            if ($tripTicket->vehicleRequest?->document_verified_at && !$tripTicket->document_verified_at) {
                $tripTicket->document_verified_at = $tripTicket->vehicleRequest->document_verified_at;
            }

            // Only set to active if document exists AND is verified AND scheduled departure time has already arrived
            if ($tripTicket->document && $tripTicket->document_verified_at && $tripTicket->isDepartureTimeArrived()) {
                $tripTicket->status = 'active';
            } else {
                $tripTicket->status = 'pending';
            }
        });

        static::saving(function ($tripTicket) {
            // Auto-assign Initial Odometer for active (on trip) or completed trips
            if (in_array($tripTicket->status, ['active', 'completed']) && empty($tripTicket->start_odometer)) {
                $lastKnownOdo = static::where('vehicle', $tripTicket->vehicle)
                    ->where('id', '!=', $tripTicket->id)
                    ->whereNotNull('end_odometer')
                    ->max('end_odometer');

                $tripTicket->start_odometer = $lastKnownOdo ?: 42150;
            }

            // Auto-compute Final Odometer if completed and left empty
            if ($tripTicket->status === 'completed' && empty($tripTicket->end_odometer)) {
                $tripTicket->loadMissing('vehicleRequest');
                $dest = $tripTicket->vehicleRequest?->destination ?? '';
                $roundtripKm = static::estimateDistance($dest) * 2;
                $startOdo = $tripTicket->start_odometer ?: 42150;
                $tripTicket->end_odometer = $startOdo + ($roundtripKm > 0 ? $roundtripKm : 80);
            }

            // Check if driver changed
            if ($tripTicket->isDirty('driver_id')) {
                $oldDriverId = $tripTicket->getOriginal('driver_id');
                if ($oldDriverId) {
                    $oldDriver = Driver::find($oldDriverId);
                    if ($oldDriver) {
                        $oldDriver->update(['status' => 'available']);
                    }
                }
            }

            $tripTicket->loadMissing('vehicleRequest');
            if ($tripTicket->vehicleRequest?->document && !$tripTicket->document) {
                $tripTicket->document = $tripTicket->vehicleRequest->document;
            }

            // Set active ONLY if document is verified by admin AND scheduled departure time has arrived
            if ($tripTicket->document && $tripTicket->document_verified_at && $tripTicket->status === 'pending') {
                if ($tripTicket->isDepartureTimeArrived()) {
                    $tripTicket->status = 'active';
                }
            }
        });

        static::saved(function ($tripTicket) {
            // Log status transitions
            if ($tripTicket->wasChanged('status')) {
                if ($tripTicket->status === 'active') {
                    \App\Models\ActivityLog::log('Started Trip', $tripTicket, "Trip {$tripTicket->ticket_number} started (On Trip).");
                    $tripTicket->sendSmsNotification();
                } elseif ($tripTicket->status === 'completed') {
                    \App\Models\ActivityLog::log('Completed Trip', $tripTicket, "Trip {$tripTicket->ticket_number} marked as completed.");
                } elseif ($tripTicket->status === 'cancelled') {
                    \App\Models\ActivityLog::log('Cancelled Trip', $tripTicket, "Trip {$tripTicket->ticket_number} was cancelled.");
                }
            }

            // Update driver status based on trip ticket status
            if ($tripTicket->driver_id) {
                $driver = Driver::find($tripTicket->driver_id);
                if ($driver) {
                    if ($tripTicket->status === 'active') {
                        $driver->update(['status' => 'on_trip']);
                    } else {
                        // Check if driver has any other active trip tickets
                        $hasOtherActiveTrip = TripTicket::where('driver_id', $driver->id)
                            ->where('status', 'active')
                            ->where('id', '!=', $tripTicket->id)
                            ->exists();

                        if (!$hasOtherActiveTrip) {
                            $driver->update(['status' => 'available']);
                        }
                    }
                }
            }

            // Sync all linked Vehicle Requests status and vehicle quietly
            $allRequests = $tripTicket->vehicleRequests()->get();
            if ($allRequests->isEmpty() && $tripTicket->vehicleRequest) {
                $allRequests = collect([$tripTicket->vehicleRequest]);
            }
            foreach ($allRequests as $req) {
                $updateData = [];
                if ($tripTicket->status === 'active') {
                    $updateData['status'] = 'on_trip';
                } elseif ($tripTicket->status === 'completed') {
                    $updateData['status'] = 'completed';
                } elseif ($tripTicket->status === 'cancelled') {
                    $updateData['status'] = 'cancelled';
                    if ($tripTicket->cancellation_reason) {
                        $updateData['cancellation_reason'] = $tripTicket->cancellation_reason;
                    }
                } else {
                    $updateData['status'] = 'approved';
                }

                if ($tripTicket->vehicle) {
                    $updateData['vehicle'] = $tripTicket->vehicle;
                }
                if ($tripTicket->document && !$req->document) {
                    $updateData['document'] = $tripTicket->document;
                }

                $req->updateQuietly($updateData);
            }

            // Sync Vehicle status in vehicles table
            self::syncVehicleStatus($tripTicket->vehicle);

            // As soon as CEO signed document is verified on the trip ticket, send SMS to driver
            if ($tripTicket->wasChanged('document') && $tripTicket->document && $tripTicket->status !== 'cancelled') {
                if (!empty($tripTicket->document_verified_at)) {
                    $tripTicket->sendSmsNotification();
                }
            }
        });

        static::deleted(function ($tripTicket) {
            \App\Models\ActivityLog::log('Deleted Ticket', $tripTicket, "Deleted trip ticket {$tripTicket->ticket_number}");
            if ($tripTicket->driver_id) {
                $driver = Driver::find($tripTicket->driver_id);
                if ($driver) {
                    // Check if driver has any other active trip tickets
                    $hasOtherActiveTrip = TripTicket::where('driver_id', $driver->id)
                        ->where('status', 'active')
                        ->exists();

                    if (!$hasOtherActiveTrip) {
                        $driver->update(['status' => 'available']);
                    }
                }
            }

            // Sync Vehicle status
            self::syncVehicleStatus($tripTicket->vehicle);

            $allRequests = $tripTicket->vehicleRequests()->get();
            if ($allRequests->isEmpty() && $tripTicket->vehicleRequest) {
                $allRequests = collect([$tripTicket->vehicleRequest]);
            }
            foreach ($allRequests as $req) {
                $req->updateQuietly([
                    'status' => 'pending',
                    'trip_ticket_id' => null,
                ]);
            }
        });
    }

    public static function syncVehicleStatus(?string $vehicleIdentifier): void
    {
        if (empty($vehicleIdentifier)) return;

        $plate = $vehicleIdentifier;
        if (str_contains($plate, ' - ')) {
            $parts = explode(' - ', $plate);
            $plate = trim(end($parts));
        }

        $vehicle = Vehicle::where('plate_number', $plate)
            ->orWhere('brand', 'like', '%' . $vehicleIdentifier . '%')
            ->orWhere('model', 'like', '%' . $vehicleIdentifier . '%')
            ->first();

        if ($vehicle && $vehicle->status !== 'maintenance') {
            $hasActiveTrip = TripTicket::where('status', 'active')
                ->where(function ($q) use ($vehicle) {
                    $q->where('vehicle', 'like', '%' . $vehicle->plate_number . '%')
                      ->orWhere('vehicle', 'like', '%' . $vehicle->brand . '%');
                })
                ->exists();

            $vehicle->update(['status' => $hasActiveTrip ? 'on_trip' : 'available']);
        }
    }

    public function verifyDocumentAndDispatch(bool $sendSms = true): array
    {
        $primaryReq = $this->vehicleRequest ?? $this->vehicleRequests()->first();
        if ($primaryReq) {
            return $primaryReq->verifyDocumentAndDispatch($sendSms);
        }

        $now = \Illuminate\Support\Carbon::now('Asia/Manila');
        $this->document_verified_at = $now;

        $depDateTime = $this->getScheduledDepartureDateTime();
        $isDepartureArrived = $depDateTime ? $now->greaterThanOrEqualTo($depDateTime) : false;

        if ($isDepartureArrived) {
            $this->status = 'active';
            if ($this->driver) {
                $this->driver->update(['status' => 'on_trip']);
            }
        } else {
            if ($this->status !== 'active') {
                $this->status = 'pending';
            }
        }

        $this->saveQuietly();
        self::syncVehicleStatus($this->vehicle);

        if ($sendSms) {
            $this->sendSmsNotification(forceResend: true);
        }

        $statusLabel = $this->status === 'active' ? 'On Trip' : 'Ready (Waiting)';

        return [
            'success' => true,
            'status' => $this->status,
            'status_label' => $statusLabel,
            'message' => "CEO signed document verified! SMS dispatched to driver. Status is now {$statusLabel}.",
        ];
    }

    public function getFormattedVehicleAttribute(): string
    {
        $raw = $this->vehicle ?? $this->vehicleRequest?->vehicle;
        if (empty($raw)) {
            return 'Assigned Vehicle';
        }

        $name = Vehicle::getVehicleName($raw);
        $plate = Vehicle::getPlateNumber($raw);

        if ($plate && $plate !== $name) {
            return "{$name} ({$plate})";
        }

        return $name;
    }

    public function sendSmsNotification(bool $forceResend = false): void
    {
        $this->load(['driver', 'vehicleRequest', 'vehicleRequests']);

        // Do not send automatic SMS unless CEO signed document has been verified by Admin
        if (!$forceResend && !$this->hasVerifiedDocument()) {
            return;
        }

        if ($this->driver) {
            if (!$forceResend) {
                if ($this->id && in_array($this->id, static::$smsDispatchedTicketIds, true)) {
                    return;
                }
                $recentlySent = \App\Models\SmsLog::where('driver_id', $this->driver->id)
                    ->where('message', 'like', "%Trip ID: {$this->ticket_number}%")
                    ->where('message', 'like', '%NEW TRIP ASSIGNED%')
                    ->where('created_at', '>=', now()->subSeconds(15))
                    ->exists();
                if ($recentlySent) {
                    return;
                }
            }

            $driverName = $this->driver->name;
            $driverPhone = $this->driver->contact_number ?? '+63 917 000 0000';
            $driverLicense = $this->driver->license_number;
            
            $allRequests = $this->all_vehicle_requests;
            $destination = $allRequests->pluck('destination')->filter()->unique()->join(' / ') ?: ($this->vehicleRequest?->destination ?? 'N/A');
            $date = $this->vehicleRequest?->date 
                ? \Carbon\Carbon::parse($this->vehicleRequest->date)->format('F d, Y') 
                : 'N/A';
            $time = $this->vehicleRequest?->time 
                ? \Carbon\Carbon::parse($this->vehicleRequest->time)->format('g:i A') 
                : 'N/A';
            
            $vehicleInfo = $this->formatted_vehicle;
            
            $passengerLines = [];
            foreach ($allRequests as $r) {
                $rawNames = $r->passenger_names ?? [];
                if (is_string($rawNames)) {
                    $rawNames = json_decode($rawNames, true) ?? [];
                }
                $names = collect($rawNames)->map(fn ($p) => is_array($p) ? ($p['name'] ?? '') : $p)->filter()->join(', ');
                $pList = $names ?: $r->employee_name;
                $dept = $r->department ?: 'Dept';
                $passengerLines[] = "{$dept}: {$pList}";
            }
            $passenger = !empty($passengerLines) ? implode("\n", $passengerLines) : ($this->vehicleRequest?->employee_name ?? 'N/A');
            
            $smsMessage = "Hi {$driverName}!\n"
                        . "You have a NEW TRIP ASSIGNED:\n"
                        . "Trip ID: {$this->ticket_number}\n"
                        . "Destination: {$destination}\n"
                        . "Date: {$date}\n"
                        . "Time: {$time}\n"
                        . "Vehicle: {$vehicleInfo}\n"
                        . "Passenger/s:\n{$passenger}\n"
                        . "Please check PeliCle portal\n"
                        . "using License ID: {$driverLicense}";

            // Send SMS via our SMS Service (handles TextBee / Semaphore API and database logs)
            $dispatched = \App\Services\SmsService::send($this->driver, $smsMessage);
            if ($dispatched && $this->id && !in_array($this->id, static::$smsDispatchedTicketIds, true)) {
                static::$smsDispatchedTicketIds[] = $this->id;
            }
        }
    }

    public function sendCancellationSms(string $reason = 'Trip schedule lapsed without uploaded CEO signed document.'): void
    {
        $this->loadMissing(['driver', 'vehicleRequest']);

        if ($this->driver) {
            $driverName = $this->driver->name;
            $destination = $this->vehicleRequest?->destination ?? 'N/A';
            $date = $this->vehicleRequest?->date 
                ? \Carbon\Carbon::parse($this->vehicleRequest->date)->format('F d, Y') 
                : 'N/A';
            $time = $this->vehicleRequest?->time 
                ? \Carbon\Carbon::parse($this->vehicleRequest->time)->format('g:i A') 
                : 'N/A';

            $smsMessage = "Hi {$driverName}!\n"
                        . "TRIP NOTICE: CANCELLED / EXPIRED\n"
                        . "Trip ID: {$this->ticket_number}\n"
                        . "Destination: {$destination}\n"
                        . "Date: {$date} ({$time})\n"
                        . "Reason: {$reason}\n"
                        . "Your status is now AVAILABLE for other trips.\n"
                        . "- PeliCle CSU LAL-LO";

            // Send SMS via our SMS Service (handles Semaphore API and database logs)
            \App\Services\SmsService::send($this->driver, $smsMessage);
        }
    }

    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class);
    }

    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'trip_ticket_id');
    }

    protected static ?\Illuminate\Support\Collection $cachedOpenTickets = null;
    protected static ?\Illuminate\Support\Collection $cachedActiveCandidateRequests = null;

    public static function clearRequestCache(): void
    {
        static::$cachedOpenTickets = null;
        static::$cachedActiveCandidateRequests = null;
    }

    public function getAllVehicleRequestsAttribute()
    {
        $requests = $this->relationLoaded('vehicleRequests')
            ? clone $this->vehicleRequests
            : $this->vehicleRequests()->get();
        if ($requests->isEmpty() && $this->vehicleRequest) {
            return collect([$this->vehicleRequest]);
        }
        if ($this->vehicleRequest && !$requests->contains('id', $this->vehicleRequest->id)) {
            $requests->prepend($this->vehicleRequest);
        }
        return $requests;
    }

    /**
     * Extract normalized municipality/city name from a destination string.
     */
    public static function extractMunicipality(?string $dest): string
    {
        if (empty($dest)) {
            return '';
        }

        $lower = strtolower(trim($dest));

        $knownMunicipalities = [
            'tuguegarao' => ['tuguegarao'],
            'lal-lo' => ['lal-lo', 'lallo'],
            'gattaran' => ['gattaran'],
            'lasam' => ['lasam'],
            'santo nino' => ['santo nino', 'sto. nino', 'sto nino', 'santo niño', 'sto. niño'],
            'alcala' => ['alcala'],
            'baggao' => ['baggao'],
            'amulung' => ['amulung'],
            'iguig' => ['iguig'],
            'piat' => ['piat'],
            'tuao' => ['tuao'],
            'rizal' => ['rizal'],
            'solana' => ['solana'],
            'penablanca' => ['penablanca', 'peñablanca'],
            'enrile' => ['enrile'],
            'camalaniugan' => ['camalaniugan'],
            'aparri' => ['aparri'],
            'buguey' => ['buguey'],
            'santa teresita' => ['santa teresita', 'sta. teresita', 'sta teresita'],
            'gonzaga' => ['gonzaga'],
            'santa ana' => ['santa ana', 'sta. ana', 'sta ana'],
            'allacapan' => ['allacapan'],
            'ballesteros' => ['ballesteros'],
            'abulug' => ['abulug'],
            'pamplona' => ['pamplona'],
            'sanchez mira' => ['sanchez mira', 'sanchez-mira'],
            'claveria' => ['claveria'],
            'santa praxedes' => ['santa praxedes', 'sta. praxedes', 'sta praxedes'],
            'santa maria' => ['santa maria', 'sta. maria', 'sta maria'],
            'san pablo' => ['san pablo'],
            'cabagan' => ['cabagan'],
            'tumauini' => ['tumauini'],
            'ilagan' => ['ilagan'],
            'cauayan' => ['cauayan'],
            'santiago' => ['santiago'],
            'alicia' => ['alicia'],
            'echague' => ['echague'],
            'roxas' => ['roxas'],
            'san mateo' => ['san mateo'],
            'cordon' => ['cordon'],
            'solano' => ['solano'],
            'bayombong' => ['bayombong'],
            'bambang' => ['bambang'],
            'baguio' => ['baguio', 'la trinidad'],
            'manila' => ['manila', 'quezon city', 'makati', 'pasay', 'taguig', 'pasig', 'mandaluyong', 'caloocan', 'paranaque', 'parañaque'],
            'laoag' => ['laoag'],
            'pagudpud' => ['pagudpud'],
            'batac' => ['batac'],
            'vigan' => ['vigan'],
            'luna' => ['luna'],
            'kabugao' => ['kabugao'],
            'conner' => ['conner'],
            'tabuk' => ['tabuk'],
        ];

        foreach ($knownMunicipalities as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/\b' . preg_quote($alias, '/') . '\b/u', $lower)) {
                    return $canonical;
                }
            }
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $lower))));
        $ignoredProvincesOrRegions = [
            'cagayan', 'isabela', 'nueva vizcaya', 'quirino', 'batanes',
            'ilocos norte', 'ilocos sur', 'la union', 'pangasinan',
            'apayao', 'kalinga', 'abra', 'benguet', 'ifugao', 'mountain province',
            'philippines',
        ];

        foreach ($parts as $part) {
            if (str_contains($part, 'region ')) {
                continue;
            }
            $cleaned = preg_replace('/\b(city of|city|municipality of|municipality|mun\.|province of|province|capital)\b/i', '', $part);
            $cleaned = trim(preg_replace('/[\(\)\s]+/', ' ', $cleaned));
            if ($cleaned !== '' && !in_array($cleaned, $ignoredProvincesOrRegions, true)) {
                return $cleaned;
            }
        }

        $firstPart = $parts[0] ?? $lower;
        $cleaned = preg_replace('/\b(city of|city|municipality of|municipality|mun\.)\b/i', '', $firstPart);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }

    /**
     * Get the highway route corridor from CSU Lal-lo for pass-through ("pwede idagas") matching.
     */
    public static function getRouteCorridor(?string $dest): ?string
    {
        $mun = static::extractMunicipality($dest);
        if ($mun === '') {
            return null;
        }

        $southbound = [
            'gattaran', 'lasam', 'santo nino', 'alcala', 'baggao', 'amulung', 'iguig',
            'piat', 'tuao', 'rizal', 'solana', 'penablanca', 'tuguegarao', 'enrile',
            'santa maria', 'san pablo', 'cabagan', 'tumauini', 'ilagan', 'cauayan',
            'santiago', 'alicia', 'echague', 'roxas', 'san mateo', 'cordon',
            'solano', 'bayombong', 'bambang', 'baguio', 'manila', 'tabuk',
        ];

        $northEast = [
            'camalaniugan', 'aparri', 'buguey', 'santa teresita', 'gonzaga', 'santa ana',
        ];

        $westbound = [
            'allacapan', 'ballesteros', 'abulug', 'pamplona', 'sanchez mira', 'claveria',
            'santa praxedes', 'pagudpud', 'laoag', 'batac', 'vigan', 'luna', 'kabugao', 'conner',
        ];

        if (in_array($mun, $southbound, true)) {
            return 'southbound';
        }
        if (in_array($mun, $northEast, true)) {
            return 'north_east';
        }
        if (in_array($mun, $westbound, true)) {
            return 'westbound';
        }
        if ($mun === 'lal-lo') {
            return 'local_lallo';
        }

        return null;
    }

    /**
     * Determine if two destinations match by same municipality ('same_municipality')
     * or are along the same highway route from CSU Lal-lo ('along_route' / "pwede idagas").
     */
    public static function getDestinationMatchType(?string $destA, ?string $destB): ?string
    {
        if (empty($destA) || empty($destB)) {
            return null;
        }

        $munA = static::extractMunicipality($destA);
        $munB = static::extractMunicipality($destB);

        if ($munA !== '' && $munB !== '') {
            if (
                $munA === $munB ||
                str_contains($munA, $munB) ||
                str_contains($munB, $munA)
            ) {
                return 'same_municipality';
            }
        }

        $corridorA = static::getRouteCorridor($destA);
        $corridorB = static::getRouteCorridor($destB);

        if ($corridorA && $corridorB && $corridorA === $corridorB) {
            return 'along_route';
        }

        if (($corridorA === 'local_lallo' && $corridorB !== null) || ($corridorB === 'local_lallo' && $corridorA !== null)) {
            return 'along_route';
        }

        return null;
    }

    /**
     * Check whether the CEO signed document has already been uploaded on this ticket or any linked request.
     */
    public function hasSignedDocument(): bool
    {
        if (!empty($this->document) || !empty($this->vehicleRequest?->document)) {
            return true;
        }

        if ($this->id) {
            if ($this->relationLoaded('vehicleRequests')) {
                return $this->vehicleRequests->contains(fn ($r) => !empty($r->document));
            }
            return $this->vehicleRequests()
                ->whereNotNull('document')
                ->where('document', '!=', '')
                ->exists();
        }

        return false;
    }

    public function hasVerifiedDocument(): bool
    {
        if (!empty($this->document_verified_at) || !empty($this->vehicleRequest?->document_verified_at)) {
            return true;
        }

        if ($this->id) {
            if ($this->relationLoaded('vehicleRequests')) {
                return $this->vehicleRequests->contains(fn ($r) => !empty($r->document_verified_at));
            }
            return $this->vehicleRequests()
                ->whereNotNull('document_verified_at')
                ->exists();
        }

        return false;
    }

    /**
     * Get all VehicleRequest candidates on the same travel date that have the
     * same destination municipality or are along the same route ("pwede idagas").
     */
    public function getCompatibleCompanionCandidates(): \Illuminate\Support\Collection
    {
        $primaryReq = $this->vehicleRequest ?? ($this->relationLoaded('vehicleRequests') ? $this->vehicleRequests->first() : ($this->id ? $this->vehicleRequests()->first() : null));
        if (!$primaryReq || empty($primaryReq->date)) {
            return collect();
        }

        if (static::$cachedActiveCandidateRequests === null) {
            static::$cachedActiveCandidateRequests = VehicleRequest::whereIn('status', ['pending', 'approved'])
                ->latest('id')
                ->get();
        }

        $targetDate = \Carbon\Carbon::parse($primaryReq->date)->format('Y-m-d');

        return static::$cachedActiveCandidateRequests->filter(function (VehicleRequest $candidate) use ($primaryReq, $targetDate) {
            if ((int) $candidate->id === (int) $primaryReq->id || empty($candidate->date)) {
                return false;
            }
            if (\Carbon\Carbon::parse($candidate->date)->format('Y-m-d') !== $targetDate) {
                return false;
            }
            if ($this->id && (int) $candidate->trip_ticket_id === (int) $this->id) {
                return true;
            }
            if (!empty($candidate->trip_ticket_id)) {
                return false;
            }
            return static::getDestinationMatchType($primaryReq->destination, $candidate->destination) !== null;
        })->values();
    }

    public function hasCompatibleCarpoolRequests(): bool
    {
        if ($this->id) {
            if ($this->relationLoaded('vehicleRequests')) {
                if ($this->vehicleRequests->contains(fn ($r) => (int) $r->id !== (int) $this->vehicle_request_id)) {
                    return true;
                }
            } elseif ($this->vehicleRequests()->where('id', '!=', $this->vehicle_request_id)->exists()) {
                return true;
            }
        }

        return $this->getCompatibleCompanionCandidates()->isNotEmpty();
    }

    /**
     * Trip Ticket can ONLY be edited/consolidated when:
     * 1. Not archived (trashed)
     * 2. Status is still 'pending' (not active/on_trip, completed, or cancelled)
     * 3. CEO signed document has NOT been uploaded yet
     * 4. There is at least one compatible companion request (same date + same municipality or along the route / "pwede idagas")
     */
    public function canConsolidateOrEdit(): bool
    {
        if ($this->trashed() || $this->status !== 'pending' || $this->hasSignedDocument()) {
            return false;
        }

        return $this->hasCompatibleCarpoolRequests();
    }

    /**
     * Find an existing pending TripTicket (without CEO signed document yet) on the same date
     * that matches this VehicleRequest's destination municipality or route corridor.
     */
    public static function findCompatibleOpenTicketForRequest(VehicleRequest $request): ?self
    {
        if (empty($request->date) || empty($request->destination)) {
            return null;
        }

        if (static::$cachedOpenTickets === null) {
            static::$cachedOpenTickets = static::with(['vehicleRequest', 'vehicleRequests'])
                ->where('status', 'pending')
                ->where(function ($q) {
                    $q->whereNull('document')->orWhere('document', '');
                })
                ->latest('id')
                ->get();
        }

        $dateB = \Carbon\Carbon::parse($request->date)->format('Y-m-d');

        foreach (static::$cachedOpenTickets as $ticket) {
            if ($ticket->hasSignedDocument()) {
                continue;
            }
            $primaryReq = $ticket->vehicleRequest ?? $ticket->vehicleRequests->first();
            if (!$primaryReq || (int) $primaryReq->id === (int) $request->id || empty($primaryReq->date)) {
                continue;
            }

            $dateA = \Carbon\Carbon::parse($primaryReq->date)->format('Y-m-d');
            if ($dateA !== $dateB) {
                continue;
            }

            if (static::getDestinationMatchType($primaryReq->destination, $request->destination) !== null) {
                return $ticket;
            }
        }

        return null;
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function withdrawalSlips(): HasMany
    {
        return $this->hasMany(WithdrawalSlip::class);
    }

    /**
     * Get a comprehensive summary payload for the Guard Portal transaction modal.
     */
    public function toGuardSummaryArray(): array
    {
        $allRequests = $this->all_vehicle_requests;
        $requestsData = [];
        $totalPax = 0;

        foreach ($allRequests as $req) {
            $rawPassengers = $req->passenger_names ?? [];
            if (is_string($rawPassengers)) {
                $rawPassengers = json_decode($rawPassengers, true) ?? [];
            }

            $pNames = [];
            if (is_array($rawPassengers)) {
                foreach ($rawPassengers as $p) {
                    if (is_array($p) && !empty($p['name'])) {
                        $pNames[] = trim($p['name']);
                    } elseif (is_string($p) && !empty(trim($p))) {
                        $pNames[] = trim($p);
                    }
                }
            }

            $count = (int) ($req->number_of_passengers ?: max(count($pNames), 1));
            $totalPax += $count;

            $requestsData[] = [
                'request_number' => $req->formatted_request_number ?? $req->request_number,
                'requester' => $req->employee_name ?? 'N/A',
                'department' => $req->department ?? 'N/A',
                'destination' => $req->destination ?? 'N/A',
                'purpose' => $req->purpose ?? 'N/A',
                'passenger_count' => $count,
                'passenger_names' => $pNames,
                'has_other_passengers' => (bool) $req->has_other_passengers,
                'other_passengers' => $req->other_passengers ?? '',
                'date' => $req->date ? \Carbon\Carbon::parse($req->date)->format('M d, Y') : null,
                'time' => $req->time ? \Carbon\Carbon::parse($req->time)->format('g:i A') : null,
            ];
        }

        $vehName = \App\Models\Vehicle::getVehicleName($this->vehicle);
        $outStamp = $this->display_gate_out ? \Carbon\Carbon::parse($this->display_gate_out)->timezone('Asia/Manila')->format('M d, Y - g:i A') : '---';
        $inStamp = $this->display_gate_in ? \Carbon\Carbon::parse($this->display_gate_in)->timezone('Asia/Manila')->format('M d, Y - g:i A') : '---';

        return [
            'id' => $this->id,
            'ticket_number' => $this->formatted_ticket_number,
            'raw_ticket_number' => $this->ticket_number,
            'vehicle_name' => $vehName,
            'vehicle_plate' => $this->vehicle,
            'driver_name' => $this->driver?->name ?? 'N/A',
            'driver_license' => $this->driver?->license_number ?? null,
            'driver_contact' => $this->driver?->contact_number ?? null,
            'out_time' => $outStamp,
            'in_time' => $inStamp,
            'scanned_by' => $this->display_scanned_by,
            'status' => ucfirst($this->status),
            'is_carpool' => count($requestsData) > 1,
            'total_passengers' => $totalPax,
            'primary_destination' => $this->vehicleRequest?->destination ?? ($requestsData[0]['destination'] ?? 'N/A'),
            'primary_purpose' => $this->vehicleRequest?->purpose ?? ($requestsData[0]['purpose'] ?? 'N/A'),
            'requests' => $requestsData,
            'print_url' => route('trip-tickets.print', $this->id),
        ];
    }
}
