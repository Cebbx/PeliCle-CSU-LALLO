<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plate_number')
                    ->unique('vehicles', 'plate_number', ignoreRecord: true)
                    ->required(),
                TextInput::make('brand')
                    ->required(),
                TextInput::make('model')
                    ->required(),
                Select::make('type')
                    ->options([
                        'SUV' => 'SUV',
                        'Van' => 'Van',
                        'Jeep' => 'Jeep',
                        'Multicab' => 'Multicab',
                    ])
                    ->required()
                    ->default('SUV'),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'maintenance' => 'Under Maintenance',
                    ])
                    ->required()
                    ->default('available'),
                DatePicker::make('last_pms_date')
                    ->label('Last Maintenance / PMS Date')
                    ->placeholder('Select last PMS date'),
                DatePicker::make('next_pms_date')
                    ->label('Next PMS Due Date')
                    ->placeholder('Select next PMS due date')
                    ->helperText('System will alert when approaching or overdue.'),
                Textarea::make('maintenance_notes')
                    ->label('Maintenance & Service Notes')
                    ->placeholder('e.g. Change Oil, Brake Pad Replacement, Battery Check')
                    ->columnSpanFull(),
            ]);
    }
}
