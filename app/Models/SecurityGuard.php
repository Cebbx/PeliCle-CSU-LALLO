<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class SecurityGuard extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('guard', function (Builder $builder) {
            $builder->where('role', 'guard');
        });

        static::creating(function ($model) {
            $model->role = 'guard';
            if (empty($model->department)) {
                $model->department = 'Security & Safety Office';
            }
            if (empty($model->position)) {
                $model->position = 'Gate Security Officer';
            }
        });
    }

    /**
     * Helper to get total clearance logs performed by this guard
     */
    public function getClearanceCountAttribute(): int
    {
        return TripTicket::where('scanned_by', $this->name)->count();
    }
}
