<?php

namespace App\Filament\Pages\Auth;

use App\Models\Driver;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;

class DriverLogin extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Driver Portal';
    }

    public function getSubheading(): string
    {
        return 'Sign in to access your assigned trips and vehicle schedules';
    }

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            $user = Filament::auth()->user();
            if ($user && strtolower($user->role ?? '') === 'driver') {
                redirect()->intended(Filament::getUrl());
                return;
            }
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('license_number')
                    ->label('License ID or Contact Number')
                    ->placeholder('e.g. N01-27-556983 or 09275569838')
                    ->autocomplete('off')
                    ->required()
                    ->autofocus(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $input = trim($data['license_number'] ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);
        $cleanLicense = strtoupper(str_replace(['-', ' '], '', $input));

        // Auto-heal drivers table if empty on a fresh deployment
        if (Driver::count() === 0) {
            try {
                (new \Database\Seeders\DriverSeeder())->run();
            } catch (\Throwable $e) {
                // Silently proceed
            }
        }

        $findDriver = function () use ($input, $cleanPhone, $cleanLicense) {
            return Driver::where('license_number', $input)
                ->orWhereRaw('LOWER(license_number) = ?', [strtolower($input)])
                ->orWhereRaw("REPLACE(REPLACE(UPPER(license_number), '-', ''), ' ', '') = ?", [$cleanLicense])
                ->orWhere('contact_number', $input)
                ->when(!empty($cleanPhone), fn ($q) => $q->orWhere('contact_number', $cleanPhone))
                ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($input) . '%'])
                ->first();
        };

        $driver = $findDriver();

        if (! $driver && Driver::count() === 0) {
            try {
                (new \Database\Seeders\DriverSeeder())->run();
                $driver = $findDriver();
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (! $driver) {
            $sampleDrivers = Driver::query()
                ->take(3)
                ->get()
                ->map(fn ($d) => "{$d->name} ({$d->license_number})")
                ->implode(', ');

            $hint = !empty($sampleDrivers) ? " (Mga halimbawa: {$sampleDrivers})" : "";

            Notification::make()
                ->title('Driver Not Found')
                ->body('Hindi mahanap ang driver para sa "' . e($input) . '". Pakisuri ang License ID o contact number.' . $hint)
                ->danger()
                ->send();

            return null;
        }

        // Find or create a user account for the driver safely handling any soft deletes
        $user = User::withTrashed()->where('email', $driver->license_number)->first();
        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
            $user->update([
                'name' => $driver->name,
                'role' => 'driver',
                'plain_password' => $driver->license_number,
            ]);
        } else {
            $user = User::create([
                'email' => $driver->license_number,
                'name' => $driver->name,
                'password' => Hash::make($driver->license_number),
                'plain_password' => $driver->license_number,
                'role' => 'driver'
            ]);
        }

        Filament::auth()->login($user, remember: true);

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
