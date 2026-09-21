<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'type',
        'status',
        'last_pms_date',
        'next_pms_date',
        'maintenance_notes',
    ];

    public function isPmsOverdue(): bool
    {
        return $this->next_pms_date && \Carbon\Carbon::parse($this->next_pms_date)->isPast();
    }

    public function isPmsUpcoming(): bool
    {
        if (!$this->next_pms_date) return false;
        $dueDate = \Carbon\Carbon::parse($this->next_pms_date);
        return $dueDate->isFuture() && $dueDate->diffInDays(now()) <= 14;
    }

    public static function getVehicleName(?string $raw): string
    {
        if (empty($raw)) {
            return 'To be assigned';
        }

        // Strip "BRAND - PLATE" if formatted like "FORTUNER - SBA1749"
        if (str_contains($raw, ' - ')) {
            $parts = explode(' - ', $raw);
            return trim($parts[0]);
        }

        // Match plate number against vehicles in database
        static $plateMap = null;
        if ($plateMap === null) {
            $plateMap = static::all()->pluck('brand', 'plate_number')->toArray();
        }

        if (isset($plateMap[$raw])) {
            return $plateMap[$raw];
        }

        // Case-insensitive match on plate
        foreach ($plateMap as $plate => $brand) {
            if (strcasecmp($plate, $raw) === 0) {
                return $brand;
            }
        }

        return $raw;
    }

    public static function getPlateNumber(?string $raw): ?string
    {
        if (empty($raw)) return null;

        if (str_contains($raw, ' - ')) {
            $parts = explode(' - ', $raw);
            return trim(end($parts));
        }

        static $brandMap = null;
        if ($brandMap === null) {
            $brandMap = static::all()->pluck('plate_number', 'brand')->toArray();
        }

        if (isset($brandMap[$raw])) {
            return $brandMap[$raw];
        }

        return $raw;
    }

    public function tripTickets(): HasMany
    {
        return $this->hasMany(TripTicket::class);
    }
}
