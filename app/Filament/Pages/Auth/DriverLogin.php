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
        $driverNames = Driver::pluck('name')->toArray();

        return $schema
            ->components([
                TextInput::make('license_number')
                    ->label('License ID or Contact Number')
                    ->placeholder('e.g. N01-24-123456 or 09171234567')
                    ->datalist($driverNames)
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

        $driver = Driver::where('license_number', $input)
            ->orWhereRaw('LOWER(license_number) = ?', [strtolower($input)])
            ->orWhere('contact_number', $input)
            ->when(!empty($cleanPhone), fn ($q) => $q->orWhere('contact_number', $cleanPhone))
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($input) . '%'])
            ->first();

        if (! $driver) {
            Notification::make()
                ->title('Driver Not Found')
                ->body('No driver record found matching "' . e($input) . '". Please verify your License ID or contact number.')
                ->danger()
                ->send();

            return null;
        }

        // Find or create a user account for the driver
        $user = User::firstOrCreate(
            ['email' => $driver->license_number],
            [
                'name' => $driver->name,
                'password' => Hash::make($driver->license_number),
                'role' => 'driver'
            ]
        );

        if ($user->role !== 'driver') {
            $user->update(['role' => 'driver']);
        }

        Filament::auth()->login($user, remember: true);

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
