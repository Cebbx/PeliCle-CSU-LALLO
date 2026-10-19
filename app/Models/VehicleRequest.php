<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'user_id',
        'vehicle',
        'employee_name',
        'requester_signature',
        'department',
        'destination',
        'purpose',
        'description',
        'date',
        'time',
        'return_date',
        'return_time',
        'number_of_passengers',
        'passenger_names',
        'has_other_passengers',
        'other_passengers',
        'status',
        'is_urgent',
        'is_manual_encoding',
        'manual_slip_number',
        'rejection_reason',
        'cancellation_reason',
        'document',
        'document_verified_at',
        'trip_ticket_id',
    ];

    protected $casts = [
        'passenger_names' => 'array',
        'has_other_passengers' => 'boolean',
        'is_urgent' => 'boolean',
        'is_manual_encoding' => 'boolean',
        'document_verified_at' => 'datetime',
    ];

    public static function generateNextRequestNumber(?\Carbon\Carbon $date = null): string
    {
        $date = $date ?? now();
        $year = $date->format('Y');
        $month = $date->format('m');
        $prefix = "{$year}-{$month}-";

        $existing = static::withTrashed()
            ->where(function ($q) use ($prefix) {
                $q->where('request_number', 'like', "{$prefix}%")
                  ->orWhere('request_number', 'like', "VR-{$prefix}%");
            })
            ->pluck('request_number');

        $maxSeq = 0;
        foreach ($existing as $reqNum) {
            $parts = explode('-', $reqNum);
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
        while (static::withTrashed()->where('request_number', $candidate)->orWhere('request_number', "VR-{$candidate}")->exists()) {
            $counter++;
            $candidate = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function getFormattedRequestNumberAttribute(): string
    {
        return preg_replace('/^VR-(?=\d{4}-)/', '', $this->request_number ?? '');
    }

    protected static function booted(): void
    {
        static::creating(function ($vehicleRequest) {
            if (empty($vehicleRequest->request_number) || static::withTrashed()->where('request_number', $vehicleRequest->request_number)->orWhere('request_number', 'VR-' . $vehicleRequest->request_number)->exists()) {
                $vehicleRequest->request_number = static::generateNextRequestNumber();
            }
        });

        static::created(function ($vehicleRequest) {
            \App\Models\ActivityLog::log('Created Request', $vehicleRequest, "Requested vehicle type: {$vehicleRequest->vehicle}. Destination: {$vehicleRequest->destination}");

            try {
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    \Filament\Notifications\Notification::make()
                        ->title('🚨 New Vehicle Request: ' . $vehicleRequest->request_number)
                        ->body("{$vehicleRequest->employee_name} ({$vehicleRequest->department}) submitted travel to {$vehicleRequest->destination}.")
                        ->icon('heroicon-o-document-text')
                        ->iconColor($vehicleRequest->is_urgent ? 'danger' : 'warning')
                        ->actions([
                            \Filament\Actions\Action::make('view')
                                ->label('Review Request')
                                ->url('/admin/vehicle-requests'),
                        ])
                        ->sendToDatabase($admin);
                }
            } catch (\Throwable $e) {
                // Ignore notification errors
            }
        });

        static::deleting(function ($vehicleRequest) {
            \App\Models\ActivityLog::log('Deleted Request', $vehicleRequest, "Deleted request {$vehicleRequest->request_number}");
        });

        static::saved(function ($vehicleRequest) {
            // When document is uploaded, check if there is an associated TripTicket
            if ($vehicleRequest->document && $vehicleRequest->wasChanged('document')) {
                \App\Models\ActivityLog::log('Uploaded Document', $vehicleRequest, "Uploaded CEO signed document for request {$vehicleRequest->request_number}");

                $now = \Illuminate\Support\Carbon::now('Asia/Manila');
                $depDateTime = $vehicleRequest->getScheduledDepartureDateTime();

                // 1. If upload is more than 24 hours late (trip schedule lapsed by over 1 day): Auto-Decline! (Skip for manual brownout encoding)
                if (! $vehicleRequest->is_manual_encoding && $depDateTime && $now->diffInHours($depDateTime, false) < -24) {
                    $formattedSchedule = $depDateTime->format('M d, Y h:i A');
                    $autoReason = "Auto-declined: CEO signed document was uploaded late after the scheduled departure date/time ({$formattedSchedule}).";

                    $vehicleRequest->status = 'rejected';
                    $vehicleRequest->rejection_reason = $autoReason;
                    $vehicleRequest->saveQuietly();

                    $tripTicket = $vehicleRequest->tripTicket;
                    if ($tripTicket) {
                        $tripTicket->status = 'cancelled';
                        $tripTicket->cancellation_reason = "Auto-cancelled: CEO signed document was uploaded late after scheduled departure ({$formattedSchedule}).";
                        $tripTicket->document = $vehicleRequest->document;
                        $tripTicket->saveQuietly();

                        if ($tripTicket->driver_id) {
                            $driver = Driver::find($tripTicket->driver_id);
                            if ($driver) {
                                $driver->update(['status' => 'available']);
                            }
                            if (method_exists($tripTicket, 'sendCancellationSms')) {
                                $tripTicket->sendCancellationSms("Trip schedule lapsed ({$formattedSchedule}). CEO signed document was uploaded late.");
                            }
                        }

                        TripTicket::syncVehicleStatus($tripTicket->vehicle);
                    }
                    return;
                }

                // 2. On-time upload (future departure): Sync document, keep for Admin verification, DO NOT send SMS yet
                $tripTicket = $vehicleRequest->tripTicket ?: TripTicket::where('vehicle_request_id', $vehicleRequest->id)->first();
                if ($tripTicket) {
                    $tripTicket->document = $vehicleRequest->document;
                    if ($tripTicket->status !== 'active' && empty($vehicleRequest->document_verified_at)) {
                        $tripTicket->status = 'pending';
                    }
                    $tripTicket->saveQuietly();

                    // Ensure driver remains available until trip is actually verified & active
                    if ($tripTicket->driver_id && $tripTicket->status !== 'active') {
                        $driver = Driver::find($tripTicket->driver_id);
                        if ($driver) {
                            $hasActive = TripTicket::where('driver_id', $driver->id)
                                ->where('status', 'active')
                                ->where('id', '!=', $tripTicket->id)
                                ->exists();
                            if (!$hasActive) {
                                $driver->update(['status' => 'available']);
                            }
                        }
                    }

                    $targetStatus = (!empty($vehicleRequest->document_verified_at) && $tripTicket->status === 'active') ? 'on_trip' : 'approved';
                    $vehicleRequest->status = $targetStatus;
                    $vehicleRequest->saveQuietly();

                    foreach ($tripTicket->all_vehicle_requests as $siblingReq) {
                        if ($siblingReq->id !== $vehicleRequest->id) {
                            $siblingReq->updateQuietly([
                                'document' => $vehicleRequest->document,
                                'status' => $targetStatus,
                            ]);
                        }
                    }
                } else {
                    if ($vehicleRequest->status !== 'pending') {
                        $vehicleRequest->status = 'approved';
                    }
                    $vehicleRequest->saveQuietly();
                }

                // Notify Admins that CEO document was uploaded and awaits Admin / GSO verification
                if (empty($vehicleRequest->document_verified_at)) {
                    try {
                        $admins = User::where('role', 'admin')->get();
                        foreach ($admins as $admin) {
                            \Filament\Notifications\Notification::make()
                                ->title('📄 CEO Document Uploaded: ' . $vehicleRequest->request_number)
                                ->body("{$vehicleRequest->employee_name} uploaded CEO signed document for request {$vehicleRequest->request_number}. Ready for Admin / GSO verification & driver SMS dispatch.")
                                ->icon('heroicon-o-document-check')
                                ->iconColor('warning')
                                ->actions([
                                    \Filament\Actions\Action::make('view_doc')
                                        ->label('Review Document')
                                        ->url(route('vehicle-requests.view-signed-document', $vehicleRequest->id))
                                        ->openUrlInNewTab(),
                                ])
                                ->sendToDatabase($admin);
                        }
                    } catch (\Throwable $e) {}
                }
            }
        });
    }

    public function isDocumentVerified(): bool
    {
        return !empty($this->document_verified_at);
    }

    public function verifyDocumentAndDispatch(bool $sendSms = true): array
    {
        if (empty($this->document)) {
            return ['success' => false, 'message' => 'Walang naka-attach na CEO signed document para sa request na ito.'];
        }

        $now = \Illuminate\Support\Carbon::now('Asia/Manila');
        $this->document_verified_at = $now;

        $depDateTime = $this->getScheduledDepartureDateTime();
        $isDepartureArrived = $depDateTime ? $now->greaterThanOrEqualTo($depDateTime) : false;

        $tripTicket = $this->tripTicket ?: TripTicket::where('vehicle_request_id', $this->id)->first();

        if ($tripTicket) {
            $tripTicket->document = $this->document;
            $tripTicket->document_verified_at = $now;

            if ($isDepartureArrived) {
                $tripTicket->status = 'active';
                $this->status = 'on_trip';
                if ($tripTicket->driver) {
                    $tripTicket->driver->update(['status' => 'on_trip']);
                }
            } else {
                if ($tripTicket->status !== 'active') {
                    $tripTicket->status = 'pending';
                }
                $this->status = 'approved';
            }

            $tripTicket->saveQuietly();
            TripTicket::syncVehicleStatus($tripTicket->vehicle);

            if ($sendSms) {
                $tripTicket->sendSmsNotification(forceResend: true);
            }

            // Sync siblings if carpool
            foreach ($tripTicket->all_vehicle_requests as $siblingReq) {
                if ($siblingReq->id !== $this->id) {
                    $siblingReq->updateQuietly([
                        'document' => $this->document,
                        'document_verified_at' => $now,
                        'status' => $this->status,
                    ]);
                }
            }
        } else {
            $this->status = 'approved';
        }

        $this->saveQuietly();

        $statusLabel = $this->status === 'on_trip' ? 'On Trip' : 'Ready (Waiting)';

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'Admin',
            'action' => 'Verified CEO Document & Dispatched',
            'model_type' => self::class,
            'model_id' => $this->id,
            'details' => "Admin verified CEO signed document for request {$this->request_number}. Status updated to {$statusLabel}. SMS dispatched to driver.",
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);

        // Notify Requester
        if ($this->user) {
            try {
                \Filament\Notifications\Notification::make()
                    ->title('✅ CEO Document Verified: ' . $this->request_number)
                    ->body("Your CEO signed document has been verified by Admin / GSO. Status: {$statusLabel}. Driver has been notified.")
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->sendToDatabase($this->user);
            } catch (\Throwable $e) {}
        }

        // Notify Driver
        if ($tripTicket?->driver) {
            $driverUser = $tripTicket->driver->getUserAccount();
            if ($driverUser) {
                try {
                    \Filament\Notifications\Notification::make()
                        ->title('🚐 Trip Dispatched: ' . $tripTicket->ticket_number)
                        ->body("CEO signed document verified by Admin. Status: {$statusLabel}. Destination: {$this->destination}.")
                        ->icon('heroicon-o-truck')
                        ->iconColor('info')
                        ->actions([
                            \Filament\Actions\Action::make('view')
                                ->label('View Trip Ticket')
                                ->url('/driver/trip-tickets'),
                        ])
                        ->sendToDatabase($driverUser);
                } catch (\Throwable $e) {}
            }
        }

        return [
            'success' => true,
            'status' => $this->status,
            'status_label' => $statusLabel,
            'message' => "CEO signed document verified! SMS dispatched to driver. Status is now {$statusLabel}.",
        ];
    }

    public function getScheduledDepartureDateTime(): ?\Carbon\Carbon
    {
        if (empty($this->date)) {
            return null;
        }
        $dateStr = $this->date instanceof \Carbon\CarbonInterface 
            ? $this->date->format('Y-m-d') 
            : (string)$this->date;
        $timeStr = $this->time instanceof \Carbon\CarbonInterface 
            ? $this->time->format('H:i:s') 
            : ($this->time ?: '00:00:00');

        try {
            return \Carbon\Carbon::parse("{$dateStr} {$timeStr}", 'Asia/Manila');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tripTicket(): BelongsTo
    {
        return $this->belongsTo(TripTicket::class, 'trip_ticket_id');
    }

    public static function expirePastPendingRequests(): void
    {
        try {
            $now = \Illuminate\Support\Carbon::now('Asia/Manila');

            // Check requests without document whose scheduled departure time has passed (past 30 mins)
            $unsubmittedRequests = static::whereNull('document')
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            foreach ($unsubmittedRequests as $req) {
                $depDateTime = $req->getScheduledDepartureDateTime();
                if ($depDateTime && $now->diffInHours($depDateTime, false) < -24) {
                    $formattedSchedule = $depDateTime->format('M d, Y h:i A');
                    $reason = "Auto-declined: Scheduled departure time ({$formattedSchedule}) passed by over 24 hours without uploaded CEO signed approval document.";

                    $req->updateQuietly([
                        'status' => 'rejected',
                        'rejection_reason' => $reason,
                    ]);

                    if ($req->tripTicket && $req->tripTicket->status === 'pending') {
                        $ticket = $req->tripTicket;
                        if ($ticket->driver_id) {
                            if (method_exists($ticket, 'sendCancellationSms')) {
                                $ticket->sendCancellationSms("Trip schedule lapsed ({$formattedSchedule}) without uploaded CEO signed document.");
                            }
                            Driver::where('id', $ticket->driver_id)->update(['status' => 'available']);
                        }
                        $ticket->updateQuietly([
                            'status' => 'cancelled',
                            'cancellation_reason' => $reason,
                        ]);
                        TripTicket::syncVehicleStatus($ticket->vehicle);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Quietly handle any parsing errors
        }
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if (empty($this->requester_signature)) {
            return null;
        }

        if (str_starts_with($this->requester_signature, 'data:image')) {
            return $this->requester_signature;
        }

        if (str_starts_with($this->requester_signature, 'http://') || str_starts_with($this->requester_signature, 'https://')) {
            return $this->requester_signature;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->requester_signature);
    }
}
