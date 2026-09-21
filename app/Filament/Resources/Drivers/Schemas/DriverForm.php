<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Driver Profile & Contact Details')
                    ->description('Official driver credentials for CSU Lal-lo motorpool dispatch.')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->placeholder('e.g. Juan Dela Cruz')
                                    ->prefixIcon('heroicon-m-user')
                                    ->required()
                                    ->maxLength(100),

                                Select::make('status')
                                    ->label('Duty Status')
                                    ->options([
                                        'available' => 'Available',
                                        'on_trip' => 'On Trip',
                                        'off_duty' => 'Off Duty',
                                    ])
                                    ->formatStateUsing(fn ($state) => $state === 'unavailable' ? 'off_duty' : $state)
                                    ->prefixIcon('heroicon-m-signal')
                                    ->required()
                                    ->default('available'),

                                TextInput::make('license_number')
                                    ->label("Driver's License Number")
                                    ->placeholder('e.g. N01-27-556983')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->unique('drivers', 'license_number', ignoreRecord: true)
                                    ->required()
                                    ->maxLength(50)
                                    ->helperText('Official LTO driver license ID number.'),

                                TextInput::make('contact_number')
                                    ->label('Mobile Contact Number')
                                    ->placeholder('09XXXXXXXXX')
                                    ->prefixIcon('heroicon-m-phone')
                                    ->tel()
                                    ->required()
                                    ->regex('/^(09|\+639)\d{9}$/')
                                    ->validationMessages([
                                        'regex' => 'Please enter a valid Philippine mobile number (e.g. 09171234567 or +639171234567).',
                                    ])
                                    ->helperText('11-digit mobile number used for automatic SMS dispatch alerts.'),
                            ]),
                    ]),
            ]);
    }
}
