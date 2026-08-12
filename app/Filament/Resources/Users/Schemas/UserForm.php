<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'employee' => 'Employee',
                        'staff' => 'Staff / Department Account',
                        'driver' => 'Driver',
                    ])
                    ->required()
                    ->live(),
                Select::make('department')
                    ->options([
                        'Office of the CEO' => 'Office of the CEO (Campus Executive Officer)',
                        'HRMO' => 'HRMO (Human Resource Management Office)',
                        'Accounting Office' => 'Accounting Office',
                        'Budget Office' => 'Budget Office',
                        'Property and Supply Office' => 'Property and Supply Office',
                        'Records Office' => 'Records Office',
                        'Planning Office' => 'Planning Office',
                        'MIS Office' => 'MIS Office (Management Information System / System Admin)',
                        'Office of the Campus Registrar' => 'Office of the Campus Registrar',
                        'Campus Admission Office' => 'Campus Admission Office',
                        'Campus Publication Office' => 'Campus Publication Office',
                        'University Library' => 'University Library',
                        'CICS' => 'CICS (College of Information and Computing Sciences)',
                        'CTE' => 'CTE (College of Teacher Education)',
                        'CHM' => 'CHM (College of Hospitality Management)',
                        'COA' => 'COA (College of Agriculture)',
                        'Café Valena' => 'Café Valena (CoffeeHub Café)',
                        'Campus Student Council' => 'Campus Student Council (CSC)',
                    ])
                    ->visible(fn (callable $get) => in_array($get('role'), ['employee', 'staff']))
                    ->required(fn (callable $get) => in_array($get('role'), ['employee', 'staff']))
                    ->placeholder('Select a department')
                    ->label('Department'),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->placeholder(fn (string $context): string => $context === 'edit' ? 'Leave blank to keep current password' : '')
                    ->maxLength(255),
            ]);
    }
}
