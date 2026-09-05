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

class DriverLogin extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Driver Portal';
    }

    public function getSubheading(): string
    {
        return 'Enter your Driver Name, License ID, or Contact Number';
    }

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            $user = Filament::auth()->user();
            if ($user && strtolower($user->role ?? '') === 'driver') {
                redirect()->intended(Filament::getUrl());
                return;
            }

            // If currently logged in as Employee or Admin, clear the session
            // so the driver login form displays cleanly without a 403 error
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $driverNames = Driver::pluck('name')->toArray();

        return $schema
            ->components([
                TextInput::make('license_number')
                    ->label('Driver Name, License ID, or Contact Number')
                    ->placeholder('e.g. Joel Tumamao, N01-27-556983, or 09275569838')
                    ->datalist($driverNames)
                    ->helperText('💡 Tip: Pwedeng i-type ang Pangalan ng driver (e.g. Joel Tumamao), License ID, o Mobile number.')
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
                ->body('Hindi mahanap ang driver gamit ang "' . $input . '". Subukang i-type: Joel Tumamao, Lucio Collado, o Norman Cristobal.')
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

        Auth::login($user, remember: true);

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
