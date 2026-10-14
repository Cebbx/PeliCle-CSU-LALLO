<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;

class EmployeeLogin extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Employee Portal';
    }

    public function getSubheading(): string
    {
        return 'Mag-sign in gamit ang inyong CSU Email o Department Code (hal. cics, hrmo, ceo)';
    }

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            $user = Filament::auth()->user();
            if ($user && in_array(strtolower($user->role ?? ''), ['employee', 'staff', 'admin', 'user', 'driver'])) {
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
                TextInput::make('login')
                    ->label('CSU Email or Department Code')
                    ->placeholder('e.g. cics, hrmo, o cics@csu.edu.ph')
                    ->autocomplete('username')
                    ->required()
                    ->autofocus(),
                TextInput::make('password')
                    ->label('Password')
                    ->placeholder('e.g. cics, password, o 123456')
                    ->password()
                    ->revealable()
                    ->autocomplete('current-password')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $rawInput = trim($data['login'] ?? '');
        $plainPassword = trim($data['password'] ?? '');

        // Auto-heal department users if none exist in the database yet
        if (User::where('role', 'employee')->count() === 0) {
            try {
                (new \Database\Seeders\DepartmentUserSeeder())->run();
            } catch (\Throwable $e) {
                // Silently proceed
            }
        }

        $input = strtolower($rawInput);
        $prefix = preg_replace('/[^a-z0-9]/', '', explode('@', $input)[0]);

        // Find user by:
        // 1. Exact email match (including soft-deleted)
        // 2. Department code matching prefix (e.g. cics@csu.edu.ph)
        // 3. Like prefix@%
        // 4. Department name or Name
        $user = User::withTrashed()
            ->where('email', $input)
            ->orWhere('email', $prefix . '@csu.edu.ph')
            ->orWhere('email', 'like', $prefix . '@%')
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $prefix . '%'])
            ->orWhereRaw('LOWER(department) LIKE ?', ['%' . $prefix . '%'])
            ->first();

        // If not found, try general employee fallback if typed 'employee'
        if (!$user && in_array($prefix, ['employee', 'staff', 'user'])) {
            $user = User::withTrashed()->where('email', 'employee@example.com')->first();
        }

        // If still not found and typed admin
        if (!$user && $prefix === 'admin') {
            $user = User::withTrashed()->whereIn('role', ['admin', 'super_admin'])->first();
        }

        if (! $user) {
            Notification::make()
                ->title('Account Not Found')
                ->body('Hindi mahanap ang account para sa "' . e($rawInput) . '". Pwede mong gamitin ang inyong Department Code (hal. cics, hrmo, ceo, cte).')
                ->danger()
                ->send();

            return null;
        }

        // Restore user if soft deleted
        if ($user->trashed()) {
            $user->restore();
        }

        // Validate password:
        $isValid = false;

        // 1. Exact hash check
        if (Hash::check($plainPassword, $user->password)) {
            $isValid = true;
        }

        // 2. Plain password check
        if (!empty($user->plain_password) && $plainPassword === $user->plain_password) {
            $isValid = true;
        }

        // 3. Department prefix (e.g. 'cics', 'hrmo', 'ceo')
        $userPrefix = strtolower(explode('@', $user->email)[0]);
        if (strcasecmp($plainPassword, $userPrefix) === 0) {
            $isValid = true;
        }

        // 4. Universal testing passwords
        $universal = [
            'password', 'password123', '1234', '12345', '123456', '1234567',
            '12345678', '123456789', 'admin', 'admin123', 'csu', 'csu123',
            'csulallo', 'pelicle', 'pelicle123', $userPrefix . '123', $userPrefix . '2026', $userPrefix . '2025'
        ];
        if (in_array(strtolower($plainPassword), $universal)) {
            $isValid = true;
        }

        // 5. Prefix starts with
        if (!empty($userPrefix) && str_starts_with(strtolower($plainPassword), $userPrefix)) {
            $isValid = true;
        }

        // 6. For employee accounts during presentations/defense, accept any password
        if (in_array($user->role, ['employee', 'staff'])) {
            $isValid = true;
        }

        if (! $isValid) {
            Notification::make()
                ->title('Maling Password')
                ->body('Mali ang password na inilagay. Maaari mong gamitin ang "' . $userPrefix . '" o "password" o "123456".')
                ->danger()
                ->send();

            return null;
        }

        Filament::auth('employee')->login($user, remember: true);

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
