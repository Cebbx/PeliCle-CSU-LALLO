<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
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
                        'Accounting Office' => 'Accounting Office',
                        'Administration Office' => 'Administration Office',
                        'Budget Office' => 'Budget Office',
                        'Café Valena' => 'Café Valena',
                        'Campus Admission Office' => 'Campus Admission Office',
                        'Campus Publication Office' => 'Campus Publication Office',
                        'Campus Student Council' => 'Campus Student Council',
                        'COA' => 'College of Agriculture',
                        'CHM' => 'College of Hospitality Management',
                        'CICS' => 'College of Information and Computing Sciences',
                        'CTE' => 'College of Teacher Education',
                        'DMST Office' => 'Department of Military Science and Tactics Office',
                        'HRMO' => 'Human Resource Management Office',
                        'MIS Office' => 'Management Information System Office',
                        'Office of the CEO' => 'Office of the Campus Executive Officer',
                        'Office of the Campus Registrar' => 'Office of the Campus Registrar',
                        'Planning Office' => 'Planning Office',
                        'Property and Supply Office' => 'Property and Supply Office',
                        'Records Office' => 'Records Office',
                        'University Library' => 'University Library',
                    ])
                    ->visible(fn (callable $get) => in_array($get('role'), ['employee', 'staff']))
                    ->required(fn (callable $get) => in_array($get('role'), ['employee', 'staff']))
                    ->placeholder('Select an office')
                    ->label('Offices')
                    ->searchable(),
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
