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

    protected static function booted(): void
    {
        static::creating(function ($withdrawalSlip) {
            if (empty($withdrawalSlip->purpose)) {
                $withdrawalSlip->loadMissing('tripTicket.vehicleRequest');
                $withdrawalSlip->purpose = $withdrawalSlip->tripTicket?->vehicleRequest?->purpose ?? 'Official Business';
            }
            if (empty($withdrawalSlip->requested_items)) {
                $withdrawalSlip->requested_items = [
                    ['item' => 'diesel', 'quantity' => 20]
                ];
            }
        });
    }

    public function tripTicket(): BelongsTo
    {
        return $this->belongsTo(TripTicket::class);
    }
}
