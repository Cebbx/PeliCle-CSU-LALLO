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
                TextInput::make('type')
                    ->label('Type')
                    ->placeholder('e.g. SUV, Van, Pickup, Coaster, Bus, Multicab')
                    ->datalist([
                        'SUV',
                        'Van',
                        'Pickup',
                        'Coaster',
                        'Bus',
                        'Jeep',
                        'Multicab',
                        'Sedan',
                    ])
                    ->required(),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'maintenance' => 'Under Maintenance',
                    ])
                    ->required()
                    ->default('available'),
                DatePicker::make('last_pms_date')
                    ->label('Last Maintenance / PMS Date')
                    ->placeholder('Select last PMS date')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('next_pms_date', \Carbon\Carbon::parse($state)->addMonths(6)->toDateString());
                        }
                    }),
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
