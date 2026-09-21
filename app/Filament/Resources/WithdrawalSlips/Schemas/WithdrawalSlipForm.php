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
                    ->label('Control / Slip No.')
                    ->default(function () {
                        $lastRecord = \App\Models\WithdrawalSlip::latest('id')->first();
                        $nextId = $lastRecord ? ($lastRecord->id + 1) : 1;
                        return 'WS-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                    })
                    ->unique('withdrawal_slips', 'slip_number', ignoreRecord: true)
                    ->required(),
                Select::make('trip_ticket_id')
                    ->label('Trip Ticket')
                    ->placeholder('Select a Trip Ticket')
                    ->default(fn () => request()->query('trip_ticket_id'))
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
                    ->disabled(fn (string $operation) => $operation === 'edit' || request()->has('trip_ticket_id'))
                    ->dehydrated()
                    ->required(),
                TextInput::make('driver_name')
                    ->label('Driver Assigned')
                    ->placeholder('Enter or edit driver name')
                    ->helperText('Pre-filled from Trip Ticket. Editable if different.')
                    ->default(function (callable $get, ?\App\Models\WithdrawalSlip $record) {
                        if ($record && $record->driver_name) {
                            return $record->driver_name;
                        }
                        $tripId = $get('trip_ticket_id') ?? request()->query('trip_ticket_id') ?? ($record?->trip_ticket_id);
                        if ($tripId) {
                            $ticket = \App\Models\TripTicket::with('driver')->find($tripId);
                            return $ticket?->driver?->name ?? null;
                        }
                        return null;
                    })
                    ->dehydrated(),
                TextInput::make('vehicle_name')
                    ->label('Vehicle Assigned')
                    ->placeholder('Enter or edit vehicle details')
                    ->helperText('Pre-filled from Trip Ticket. Editable if different.')
                    ->default(function (callable $get, ?\App\Models\WithdrawalSlip $record) {
                        if ($record && $record->vehicle_name) {
                            return $record->vehicle_name;
                        }
                        $tripId = $get('trip_ticket_id') ?? request()->query('trip_ticket_id') ?? ($record?->trip_ticket_id);
                        if ($tripId) {
                            $ticket = \App\Models\TripTicket::find($tripId);
                            if ($ticket) {
                                $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
                                return $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
                            }
                        }
                        return null;
                    })
                    ->dehydrated(),
                TextInput::make('destination_address')
                    ->label('Destination')
                    ->placeholder('Enter or edit destination')
                    ->helperText('Pre-filled from Trip Ticket. Editable if different.')
                    ->default(function (callable $get, ?\App\Models\WithdrawalSlip $record) {
                        if ($record && $record->destination_address) {
                            return $record->destination_address;
                        }
                        $tripId = $get('trip_ticket_id') ?? request()->query('trip_ticket_id') ?? ($record?->trip_ticket_id);
                        if ($tripId) {
                            $ticket = \App\Models\TripTicket::with('vehicleRequest')->find($tripId);
                            return $ticket?->vehicleRequest?->destination ?? null;
                        }
                        return null;
                    })
                    ->dehydrated(),
                TextInput::make('purpose')
                    ->label('Purpose')
                    ->placeholder('Enter or edit purpose')
                    ->helperText('Pre-filled from Trip Ticket. Editable if different.')
                    ->default(function (callable $get, ?\App\Models\WithdrawalSlip $record) {
                        if ($record && $record->purpose) {
                            return $record->purpose;
                        }
                        $tripId = $get('trip_ticket_id') ?? request()->query('trip_ticket_id') ?? ($record?->trip_ticket_id);
                        if ($tripId) {
                            $ticket = \App\Models\TripTicket::with(['vehicleRequest'])->find($tripId);
                            return $ticket?->vehicleRequest?->purpose ?? 'Official Business';
                        }
                        return 'Official Business';
                    })
                    ->dehydrated()
                    ->required(),
                Repeater::make('requested_items')
                    ->label('Requested Items (Drag to reorder or add items)')
                    ->helperText('Select the fuel, oil, or fluid items required. Drag items to reorder.')
                    ->reorderableWithDragAndDrop(true)
                    ->reorderable(true)
                    ->collapsible()
                    ->defaultItems(1)
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $state = $decoded;
                            } else {
                                return [
                                    ['item' => 'diesel', 'quantity' => 20]
                                ];
                            }
                        }

                        if (is_array($state)) {
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
                                            'quantity' => (float)$v,
                                        ];
                                    }
                                }
                                return !empty($converted) ? $converted : [['item' => 'diesel', 'quantity' => 20]];
                            }

                            return $state;
                        }

                        return [
                            ['item' => 'diesel', 'quantity' => 20]
                        ];
                    })
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
