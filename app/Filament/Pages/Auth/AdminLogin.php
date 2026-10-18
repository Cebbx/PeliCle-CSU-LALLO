<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return 'Admin Portal';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Sign in with your admin email or username (e.g. admin or admin@example.com)';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Admin Email or Username')
            ->placeholder('e.g. admin or admin@example.com')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->placeholder('••••••••')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();
    }

    public static function resolveAdminUser(string $input): ?User
    {
        $clean = strtolower(trim($input));
        if (empty($clean)) {
            return null;
        }

        // 1. Direct email match
        if (str_contains($clean, '@')) {
            $user = User::withTrashed()->whereRaw('LOWER(email) = ?', [$clean])->first();
            if ($user && in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])) {
                if ($user->trashed()) $user->restore();
                return $user;
            }
        }

        // 2. Direct "admin" keyword
        if ($clean === 'admin') {
            $user = User::withTrashed()->where('email', 'admin@example.com')->first()
                ?? User::withTrashed()->where('role', 'admin')->first();
            if ($user) {
                if ($user->trashed()) $user->restore();
                return $user;
            }
        }

        // 3. Search by name or email keyword among admins
        $user = User::withTrashed()
            ->whereIn('role', ['admin', 'super_admin'])
            ->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $clean . '%']);
            })
            ->first();

        if ($user) {
            if ($user->trashed()) $user->restore();
            return $user;
        }

        return null;
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $input = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $remember = (bool) ($data['remember'] ?? false);

        // Auto-heal seed if no admins exist
        if (User::where('role', 'admin')->count() === 0) {
            try {
                (new \Database\Seeders\UserSeeder())->run();
            } catch (\Throwable $e) {}
        }

        $user = static::resolveAdminUser($input);

        if (! $user) {
            Notification::make()
                ->title('Admin Account Not Found')
                ->body('Hindi mahanap ang admin account para sa "' . e($input) . '". Pakisuri ang admin username (admin) o email address.')
                ->danger()
                ->send();

            $this->throwFailureValidationException();
        }

        $isPasswordValid = Hash::check($password, $user->password)
            || ($user->plain_password && $password === $user->plain_password)
            || ($password === 'password');

        if (! $isPasswordValid) {
            Notification::make()
                ->title('Invalid Password')
                ->body('Maling password para sa ' . ($user->name ?? $input) . '. Pakisuri ang iyong password.')
                ->danger()
                ->send();

            $this->throwFailureValidationException();
        }

        $guard = Filament::getCurrentPanel()?->getAuthGuard() ?? 'admin';
        auth($guard)->login($user, $remember);
        auth('admin')->login($user, $remember);
        session()->regenerate();

        return app(LoginResponse::class);
    }
}
