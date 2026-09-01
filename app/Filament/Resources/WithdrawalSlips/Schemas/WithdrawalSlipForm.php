<?php

namespace App\Filament\Resources\WithdrawalSlips\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class WithdrawalSlipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slip_number')
                    ->default(function () {
                        $lastRecord = \App\Models\WithdrawalSlip::latest('id')->first();
                        $nextId = $lastRecord ? ($lastRecord->id + 1) : 1;
                        return 'WS-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                    })
                    ->unique('withdrawal_slips', 'slip_number', ignoreRecord: true)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Select::make('trip_ticket_id')
                    ->default(function () {
                        $tripId = request()->query('trip_ticket_id');
                        if ($tripId) {
                            return $tripId;
                        }
                        return \App\Models\TripTicket::whereDoesntHave('withdrawalSlips')
                            ->orderBy('created_at', 'desc')
                            ->value('id');
                    })
                    ->relationship('tripTicket', 'ticket_number', function ($query, $record) {
                        $tripId = request()->query('trip_ticket_id');
                        return $query->with(['driver', 'vehicleRequest'])
                            ->where(function ($q) use ($tripId, $record) {
                                $q->whereDoesntHave('withdrawalSlips');
                                if ($tripId) {
                                    $q->orWhere('id', $tripId);
                                }
                                if ($record && $record->trip_ticket_id) {
                                    $q->orWhere('id', $record->trip_ticket_id);
                                }
                            })
                            ->orderBy('created_at', 'desc');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $driverName = $record->driver?->name ?? 'No Driver';
                        $destination = $record->vehicleRequest?->destination ?? 'No Destination';
                        $dbVehicle = \App\Models\Vehicle::where('plate_number', $record->vehicle)->first();
                        $vehicleName = $dbVehicle ? $dbVehicle->brand : $record->vehicle;
                        
                        // Limit destination length for clean UI display
                        if (strlen($destination) > 40) {
                            $destination = substr($destination, 0, 37) . '...';
                        }
                        
                        return "{$record->ticket_number} - {$driverName} ({$vehicleName}) to {$destination}";
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $ticket = \App\Models\TripTicket::with(['driver', 'vehicleRequest'])->find($state);
                            if ($ticket) {
                                $set('driver_name', $ticket->driver?->name ?? 'No Driver');
                                $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
                                $vehicleName = $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
                                $set('vehicle_name', $vehicleName);
                                $set('destination_address', $ticket->vehicleRequest?->destination ?? 'No Destination');
                                $set('purpose', $ticket->vehicleRequest?->purpose ?? 'Official Business');
                            }
                        } else {
                            $set('driver_name', null);
                            $set('vehicle_name', null);
                            $set('destination_address', null);
                            $set('purpose', null);
                        }
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state) {
                            $ticket = \App\Models\TripTicket::with(['driver', 'vehicleRequest'])->find($state);
                            if ($ticket) {
                                $set('driver_name', $ticket->driver?->name ?? 'No Driver');
                                $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
                                $vehicleName = $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
                                $set('vehicle_name', $vehicleName);
                                $set('destination_address', $ticket->vehicleRequest?->destination ?? 'No Destination');
                                $set('purpose', $ticket->vehicleRequest?->purpose ?? 'Official Business');
                            }
                        }
                    })
                    ->disabled(fn (string $operation) => $operation === 'edit' || request()->has('trip_ticket_id'))
                    ->dehydrated()
                    ->required(),
                TextInput::make('driver_name')
                    ->label('Driver Assigned')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Select a Trip Ticket to view driver details'),
                TextInput::make('vehicle_name')
                    ->label('Vehicle Assigned')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Select a Trip Ticket to view vehicle details'),
                TextInput::make('destination_address')
                    ->label('Destination')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Select a Trip Ticket to view destination details'),
                Hidden::make('purpose')
                    ->default(function () {
                        $tripId = request()->query('trip_ticket_id');
                        if (!$tripId) {
                            $tripId = \App\Models\TripTicket::whereDoesntHave('withdrawalSlips')
                                ->orderBy('created_at', 'desc')
                                ->value('id');
                        }
                        if ($tripId) {
                            $ticket = \App\Models\TripTicket::with(['vehicleRequest'])->find($tripId);
                            return $ticket?->vehicleRequest?->purpose ?? 'Official Business';
                        }
                        return 'Official Business';
                    })
                    ->dehydrated(),
                Repeater::make('requested_items')
                    ->label('Requested Items (Drag to reorder or add items)')
                    ->helperText('Select the fuel, oil, or fluid items required. Drag items to reorder.')
                    ->reorderableWithDragAndDrop(true)
                    ->reorderable(true)
                    ->collapsible()
                    ->defaultItems(1)
                    ->schema([
                        Select::make('item')
                            ->label('Item Type')
                            ->options([
                                'diesel' => '⛽ Diesel',
                                'gasoline_regular' => '⛽ Gasoline (Regular)',
                                'gasoline_premium' => '⛽ Gasoline (Premium)',
                                'lubricant_40' => '🛢️ Lubricant Oil 40',
                                'lubricant_30' => '🛢️ Lubricant Oil 30',
                                'brake_fluid' => '🛑 Brake Fluid',
                                'grease_atf' => '⚙️ 2T / Grease / ATF',
                                'gear_oil' => '🔧 Gear Oil',
                            ])
                            ->default('diesel')
                            ->required()
                            ->searchable(),
                        TextInput::make('quantity')
                            ->label('Quantity (Liters)')
                            ->numeric()
                            ->minValue(0.5)
                            ->maxValue(500)
                            ->step(1)
                            ->suffix('Liters')
                            ->default(20)
                            ->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('+ Add Requested Item')
                    ->itemLabel(fn (array $state): ?string => match ($state['item'] ?? null) {
                        'diesel' => '⛽ Diesel: ' . ($state['quantity'] ?? 0) . ' Liters',
                        'gasoline_regular' => '⛽ Gasoline (Regular): ' . ($state['quantity'] ?? 0) . ' Liters',
                        'gasoline_premium' => '⛽ Gasoline (Premium): ' . ($state['quantity'] ?? 0) . ' Liters',
                        'lubricant_40' => '🛢️ Lubricant Oil 40: ' . ($state['quantity'] ?? 0) . ' Liters',
                        'lubricant_30' => '🛢️ Lubricant Oil 30: ' . ($state['quantity'] ?? 0) . ' Liters',
                        'brake_fluid' => '🛑 Brake Fluid: ' . ($state['quantity'] ?? 0) . ' Liters',
                        'grease_atf' => '⚙️ 2T / Grease / ATF: ' . ($state['quantity'] ?? 0) . ' Liters',
                        'gear_oil' => '🔧 Gear Oil: ' . ($state['quantity'] ?? 0) . ' Liters',
                        default => null,
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (is_array($state)) {
                            // Convert old flat associative array if present
                            $isAssoc = false;
                            foreach ($state as $k => $v) {
                                if (is_string($k) && !is_numeric($k)) {
                                    $isAssoc = true;
                                    break;
                                }
                            }
                            if ($isAssoc) {
                                $converted = [];
                                foreach ($state as $k => $v) {
                                    if (!empty($v) && (float)$v > 0) {
                                        $converted[] = [
                                            'item' => $k,
                                            'quantity' => $v,
                                        ];
                                    }
                                }
                                $set('requested_items', !empty($converted) ? $converted : [['item' => 'diesel', 'quantity' => 20]]);
                            }
                        }
                    }),
                TextInput::make('amount')
                    ->label('Actual Amount Spent')
                    ->numeric()
                    ->prefix('₱')
                    ->placeholder('0.00'),
                Hidden::make('status')
                    ->default('approved')
                    ->dehydrated(),
            ]);
    }
}
