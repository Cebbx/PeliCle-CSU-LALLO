<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('All'),
            'available' => \Filament\Schemas\Components\Tabs\Tab::make('Available')
                ->badge(\App\Models\Vehicle::where('status', 'available')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'available')),
            'on_trip' => \Filament\Schemas\Components\Tabs\Tab::make('On Trip')
                ->badge(\App\Models\Vehicle::where('status', 'on_trip')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'on_trip')),
            'maintenance' => \Filament\Schemas\Components\Tabs\Tab::make('Under Maintenance')
                ->badge(\App\Models\Vehicle::where('status', 'maintenance')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'maintenance')),
        ];
    }
}
