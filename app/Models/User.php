<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    public function canAccessPanel(Panel $panel): bool
    {
        $role = strtolower($this->role ?? '');

        if ($panel->getId() === 'admin') {
            return $role === 'admin';
        }

        if ($panel->getId() === 'driver') {
            return $role === 'driver';
        }

        if ($panel->getId() === 'employee') {
            return in_array($role, ['employee', 'staff', 'admin']);
        }

        return false;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'plain_password',
        'role',
        'department',
        'guard_id',
        'contact_number',
        'position',
        'signature',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function driver(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Driver::class, 'name', 'name');
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin';
    }

    public function isGuard(): bool
    {
        return strtolower($this->role ?? '') === 'guard';
    }

    public function getScannedTripsCountAttribute(): int
    {
        return \App\Models\TripTicket::where('scanned_by', $this->name)->count();
    }

    public static function getCeo(): ?self
    {
        return static::where('department', 'Office of the CEO')
            ->orWhere('email', 'ceo@csu.edu.ph')
            ->orWhere('position', 'like', '%CEO%')
            ->orWhere('position', 'like', '%Campus Executive Officer%')
            ->first();
    }

    public static function getCeoName(): string
    {
        $ceo = static::getCeo();
        return $ceo?->name ?? 'ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.';
    }

    public static function getCeoTitle(): string
    {
        $ceo = static::getCeo();
        return $ceo?->position ?? 'Campus Executive Officer';
    }

    public static function getGso(): ?self
    {
        return static::where('position', 'like', '%General Services%')
            ->orWhere('position', 'like', '%GSO%')
            ->orWhere('role', 'admin')
            ->first();
    }

    public static function getGsoName(): string
    {
        $gso = static::getGso();
        return $gso?->name ?? 'JOEL A. TUMAMAO';
    }

    public static function getGsoTitle(): string
    {
        $gso = static::getGso();
        return $gso?->position ?? 'General Services Officer';
    }
}
