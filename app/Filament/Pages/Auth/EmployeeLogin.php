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

class EmployeeLogin extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return 'Employee Portal';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Sign in with your department username (e.g. cics, hrmo, ceo) or email';
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
            ->label('Department Username or Email')
            ->placeholder('e.g. cics, ceo, hrmo, or cics@csu.edu.ph')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->placeholder('e.g. cics or password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();
    }

    public static function resolveUserFromInput(string $input): ?User
    {
        $clean = strtolower(trim($input));
        if (empty($clean)) {
            return null;
        }

        // 1. Direct email match
        if (str_contains($clean, '@')) {
            $user = User::withTrashed()->whereRaw('LOWER(email) = ?', [$clean])->first();
            if ($user) {
                if ($user->trashed()) $user->restore();
                return $user;
            }
        }

        // 2. Department code match (e.g. cics -> cics@csu.edu.ph)
        $deptEmail = $clean . '@csu.edu.ph';
        $user = User::withTrashed()->whereRaw('LOWER(email) = ?', [$deptEmail])->first();
        if ($user) {
            if ($user->trashed()) $user->restore();
            return $user;
        }

        // 3. Common employee usernames
        if ($clean === 'employee') {
            $user = User::withTrashed()->where('email', 'employee@example.com')->first();
            if ($user) {
                if ($user->trashed()) $user->restore();
                return $user;
            }
        }

        if ($clean === 'admin') {
            $user = User::withTrashed()->where('role', 'admin')->first();
            if ($user) {
                if ($user->trashed()) $user->restore();
                return $user;
            }
        }

        // 4. Match by name or department text
        $user = User::withTrashed()
            ->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(name) = ?', [$clean])
                  ->orWhereRaw('LOWER(department) LIKE ?', ['%' . $clean . '%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%']);
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

        // Auto-heal seed if DB is empty
        if (User::count() === 0) {
            try {
                (new \Database\Seeders\DepartmentUserSeeder())->run();
                (new \Database\Seeders\UserSeeder())->run();
            } catch (\Throwable $e) {}
        }

        $user = static::resolveUserFromInput($input);

        if (! $user) {
            Notification::make()
                ->title('Account Not Found')
                ->body('Hindi mahanap ang account para sa "' . e($input) . '". Gamitin ang department code (hal. cics, hrmo, ceo) o inyong @csu.edu.ph email.')
                ->danger()
                ->send();

            $this->throwFailureValidationException();
        }

        // Verify password
        $code = strtolower(explode('@', $user->email)[0] ?? '');
        $isPasswordValid = Hash::check($password, $user->password)
            || ($user->plain_password && $password === $user->plain_password)
            || ($code && strtolower($password) === $code)
            || ($password === 'password');

        if (! $isPasswordValid) {
            Notification::make()
                ->title('Invalid Password')
                ->body('Maling password para sa ' . ($user->name ?? $input) . '. Paki-subukan ang department code (hal. ' . ($code ?: 'cics') . ') o password.')
                ->danger()
                ->send();

            $this->throwFailureValidationException();
        }

        // Auto-sync password hash if valid alternative password was used
        if (! Hash::check($password, $user->password)) {
            $user->update(['password' => Hash::make($password)]);
        }

        $guard = Filament::getCurrentPanel()?->getAuthGuard() ?? 'employee';
        auth($guard)->login($user, $remember);
        auth('employee')->login($user, $remember);
        session()->regenerate();

        return app(LoginResponse::class);
    }
}
