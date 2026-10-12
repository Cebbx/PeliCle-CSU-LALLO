<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'campus',
        'license_number',
        'contact_number',
        'status',
    ];

    public function tripTickets(): HasMany
    {
        return $this->hasMany(TripTicket::class);
    }

    public function getCurrentLocationAttribute(): string
    {
        if ($this->status === 'on_trip') {
            $activeTrip = $this->tripTickets()
                ->where('status', 'active')
                ->latest('updated_at')
                ->with(['vehicleRequest', 'vehicleRequests'])
                ->first();

            $dest = $activeTrip?->vehicleRequest?->destination
                ?? $activeTrip?->vehicleRequests?->first()?->destination;

            return $dest ? "En Route: {$dest}" : 'On Trip';
        }

        if (in_array($this->status, ['off_duty', 'unavailable'])) {
            return 'Off Duty';
        }

        $base = !empty($this->campus) ? $this->campus : 'CSU Lal-lo Campus';
        return "{$base} (Standby)";
    }
}
