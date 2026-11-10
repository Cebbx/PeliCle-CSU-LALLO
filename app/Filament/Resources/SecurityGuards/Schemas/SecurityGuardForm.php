<?php

namespace App\Filament\Resources\SecurityGuards\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\SecurityGuard;

class SecurityGuardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Guard Full Name')
                    ->placeholder('e.g. Edward Cabbat')
                    ->required()
                    ->maxLength(255),

                TextInput::make('guard_id')
                    ->label('Guard ID')
                    ->placeholder('e.g. 1004')
                    ->helperText('Used for gate scanner authentication and PIN bypass')
                    ->required()
                    ->unique('users', 'guard_id', ignoreRecord: true)
                    ->default(function () {
                        $lastGuard = SecurityGuard::orderBy('guard_id', 'desc')->first();
                        if ($lastGuard && is_numeric($lastGuard->guard_id)) {
                            return (string) ((int) $lastGuard->guard_id + 1);
                        }
                        return '1004';
                    })
                    ->maxLength(50),

                TextInput::make('email')
                    ->label('Official Email Address')
                    ->placeholder('e.g. guard@gmail.com')
                    ->helperText('Used for 6-digit OTP verification code when signing on duty')
                    ->email()
                    ->required()
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('position')
                    ->label('Position')
                    ->placeholder('e.g. Gate Security Officer')
                    ->default('Gate Security Officer')
                    ->required()
                    ->maxLength(255),

                TextInput::make('contact_number')
                    ->label('Contact Number')
                    ->placeholder('e.g. 09123456789')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('password')
                    ->label('System Password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->default('password')
                    ->placeholder(fn (string $context): string => $context === 'edit' ? 'Leave blank to keep current password' : 'Enter login password')
                    ->maxLength(255),
            ]);
    }
}
