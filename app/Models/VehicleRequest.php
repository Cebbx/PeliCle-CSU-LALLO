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
        'trip_ticket_id',
    ];

    protected $casts = [
        'passenger_names' => 'array',
        'has_other_passengers' => 'boolean',
        'is_urgent' => 'boolean',
        'is_manual_encoding' => 'boolean',
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
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('Review Request')
                                ->url(\App\Filament\Resources\VehicleRequests\VehicleRequestResource::getUrl('index')),
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

                // 2. On-time upload (future departure): Keep request approved and ticket pending
                $tripTicket = $vehicleRequest->tripTicket;
                if ($tripTicket) {
                    $tripTicket->document = $vehicleRequest->document;
                    $tripTicket->status = 'pending';
                    $tripTicket->saveQuietly();

                    // Ensure driver remains available
                    if ($tripTicket->driver_id) {
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

                    $vehicleRequest->status = 'approved';
                    $vehicleRequest->saveQuietly();
                } else {
                    $vehicleRequest->status = 'approved';
                    $vehicleRequest->saveQuietly();
                }
            }
        });
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
