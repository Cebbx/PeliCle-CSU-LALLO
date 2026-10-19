<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
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
                Hidden::make('status')
                    ->default('available')
                    ->dehydrated(),
            ]);
    }
}
