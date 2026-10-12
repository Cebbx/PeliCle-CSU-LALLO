<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalSlip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slip_number',
        'trip_ticket_id',
        'driver_name',
        'vehicle_name',
        'destination_address',
        'purpose',
        'requested_items',
        'amount',
        'status',
    ];

    protected function requestedItems(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [
                        ['item' => 'diesel', 'quantity' => 20]
                    ];
                }

                if (is_array($value)) {
                    return $value;
                }

                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }

                    return [
                        ['item' => 'diesel', 'quantity' => 20]
                    ];
                }

                return [
                    ['item' => 'diesel', 'quantity' => 20]
                ];
            },
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode($value);
                }
                return $value;
            }
        );
    }

    public static function generateNextSlipNumber(?\Carbon\Carbon $date = null): string
    {
        $date = $date ?? now();
        $year = $date->format('Y');
        $prefix = "WS-{$year}-";

        $existing = static::withTrashed()
            ->where('slip_number', 'like', "{$prefix}%")
            ->pluck('slip_number');

        $maxSeq = 0;
        foreach ($existing as $slipNum) {
            $parts = explode('-', $slipNum);
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
        while (static::withTrashed()->where('slip_number', $candidate)->exists()) {
            $counter++;
            $candidate = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    protected static function booted(): void
    {
        static::creating(function ($withdrawalSlip) {
            if (empty($withdrawalSlip->slip_number)) {
                $withdrawalSlip->slip_number = static::generateNextSlipNumber();
            }
            if (empty($withdrawalSlip->purpose)) {
                $withdrawalSlip->loadMissing('tripTicket.vehicleRequest');
                $withdrawalSlip->purpose = $withdrawalSlip->tripTicket?->vehicleRequest?->purpose ?? 'Official Business';
            }
            if (empty($withdrawalSlip->attributes['requested_items'])) {
                $withdrawalSlip->attributes['requested_items'] = json_encode([
                    ['item' => 'diesel', 'quantity' => 'Full Tank']
                ]);
            }
        });
    }

    public function tripTicket(): BelongsTo
    {
        return $this->belongsTo(TripTicket::class);
    }
}
