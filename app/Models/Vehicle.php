<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

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

    public function tripTickets(): HasMany
    {
        return $this->hasMany(TripTicket::class);
    }
}
