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
        'status',
        'document',
    ];

    protected $casts = [
        'passenger_names' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function ($vehicleRequest) {
            \App\Models\ActivityLog::log('Created Request', $vehicleRequest, "Requested vehicle type: {$vehicleRequest->vehicle}. Destination: {$vehicleRequest->destination}");
        });

        static::deleting(function ($vehicleRequest) {
            \App\Models\ActivityLog::log('Deleted Request', $vehicleRequest, "Deleted request {$vehicleRequest->request_number}");
        });

        static::saved(function ($vehicleRequest) {
            // When document is uploaded, check if there is an associated TripTicket
            if ($vehicleRequest->document && $vehicleRequest->wasChanged('document')) {
                \App\Models\ActivityLog::log('Uploaded Document', $vehicleRequest, "Uploaded CEO signed document for request {$vehicleRequest->request_number}");
                $tripTicket = $vehicleRequest->tripTicket;
                if ($tripTicket) {
                    if ($tripTicket->status === 'pending') {
                        // Check if travel time has already arrived
                        $now = \Illuminate\Support\Carbon::now('Asia/Manila');
                        $tripDateTime = \Illuminate\Support\Carbon::parse($vehicleRequest->date . ' ' . $vehicleRequest->time, 'Asia/Manila');

                        if ($now->greaterThanOrEqualTo($tripDateTime)) {
                            // Travel time has already arrived, so activate it immediately!
                            $tripTicket->status = 'active';
                            $tripTicket->document = $vehicleRequest->document;
                            $tripTicket->saveQuietly();

                            // Sync driver status manually
                            if ($tripTicket->driver_id) {
                                $driver = Driver::find($tripTicket->driver_id);
                                if ($driver) {
                                    $driver->update(['status' => 'on_trip']);
                                }
                            }

                            // Send notification manually
                            $tripTicket->sendSmsNotification();

                            // Update Vehicle Request status quietly to on_trip
                            $vehicleRequest->status = 'on_trip';
                            $vehicleRequest->saveQuietly();
                        } else {
                            // Travel time has not arrived yet! Keep ticket as pending.
                            // Only update the request status to 'approved' and copy the document
                            $tripTicket->document = $vehicleRequest->document;
                            $tripTicket->saveQuietly();

                            $vehicleRequest->status = 'approved';
                            $vehicleRequest->saveQuietly();
                        }
                    }
                } else {
                    // No Trip Ticket exists yet, so request is approved
                    $vehicleRequest->status = 'approved';
                    $vehicleRequest->saveQuietly();
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tripTicket(): HasOne
    {
        return $this->hasOne(TripTicket::class);
    }

    public static function expirePastPendingRequests(): void
    {
        try {
            $now = \Illuminate\Support\Carbon::now('Asia/Manila');
            $pending = static::where('status', 'pending')->get();
            foreach ($pending as $req) {
                if ($req->date && $req->time) {
                    $tripDateTime = \Illuminate\Support\Carbon::parse($req->date . ' ' . $req->time, 'Asia/Manila');
                    if ($now->greaterThan($tripDateTime)) {
                        $req->updateQuietly(['status' => 'expired']);
                        if ($req->tripTicket && $req->tripTicket->status === 'pending') {
                            $req->tripTicket->updateQuietly(['status' => 'cancelled']);
                            if ($req->tripTicket->driver_id) {
                                Driver::where('id', $req->tripTicket->driver_id)->update(['status' => 'available']);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Quietly handle any parsing errors
        }
    }
}
