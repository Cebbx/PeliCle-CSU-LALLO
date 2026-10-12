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
                    ->label('Control No.')
                    ->placeholder(fn () => 'WS-' . date('Y') . '-01')
                    ->default(fn () => \App\Models\WithdrawalSlip::generateNextSlipNumber())
                    ->unique('withdrawal_slips', 'slip_number', ignoreRecord: true)
                    ->helperText('Halimbawa: WS-' . date('Y') . '-01. Auto-generated at editable.')
                    ->required(),
                Hidden::make('trip_ticket_id')
                    ->default(fn () => request()->query('trip_ticket_id'))
                    ->dehydrated(),
                Select::make('vehicle_name')
                    ->label('Vehicle Assigned')
                    ->placeholder('Select assigned vehicle')
                    ->options(function ($state) {
                        $vehicles = \App\Models\Vehicle::all();
                        $options = [];
                        foreach ($vehicles as $v) {
                            $label = "{$v->brand}" . ($v->model ? " {$v->model}" : "") . " ({$v->plate_number})";
                            $options[$label] = $label;
                        }
                        if (!empty($state) && !is_array($state) && !isset($options[$state])) {
                            $options[$state] = $state;
                        }
                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->createOptionUsing(fn (string $custom) => trim($custom))
                    ->default(function (?App\Models\WithdrawalSlip $record) {
                        if ($record && $record->vehicle_name) {
                            return $record->vehicle_name;
                        }
                        $tripId = request()->query('trip_ticket_id');
                        if ($tripId) {
                            $ticket = \App\Models\TripTicket::find($tripId);
                            if ($ticket) {
                                $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
                                return $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
                            }
                        }
                        return null;
                    })
                    ->helperText('Piliin ang nakatalagang sasakyan o i-type ang sariling detalye.')
                    ->dehydrated()
                    ->required(),
                TextInput::make('driver_name')
                    ->label('Driver Assigned')
                    ->placeholder('Enter or edit driver name')
                    ->datalist(fn () => class_exists('App\Models\Driver') ? \App\Models\Driver::pluck('name')->filter()->toArray() : [])
                    ->helperText('Driver authorized to withdraw the fuel.')
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
                TextInput::make('purpose')
                    ->label('Purpose')
                    ->placeholder('Enter purpose')
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
                    ->helperText('Select fuel or oil type and amount. Pwedeng pumili ng Full Tank o dami ng litro.')
                    ->reorderableWithDragAndDrop(true)
                    ->reorderable(true)
                    ->collapsible()
                    ->defaultItems(1)
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return [
                                ['item' => 'diesel', 'quantity' => 'Full Tank']
                            ];
                        }

                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $state = $decoded;
                            } else {
                                return [
                                    ['item' => 'diesel', 'quantity' => 'Full Tank']
                                ];
                            }
                        }

                        if (is_array($state)) {
                            // If it's already a list of repeater rows: [['item' => ..., 'quantity' => ...], ...]
                            $firstVal = reset($state);
                            if (is_array($firstVal) && isset($firstVal['item'])) {
                                return array_values($state);
                            }

                            // If it's a legacy associative array like ['diesel' => 20]
                            $converted = [];
                            foreach ($state as $k => $v) {
                                if (is_array($v) && isset($v['item'])) {
                                    $converted[] = $v;
                                } elseif (!empty($v) && !is_array($v)) {
                                    $converted[] = [
                                        'item' => (string) $k,
                                        'quantity' => is_numeric($v) ? (float) $v : (string) $v,
                                    ];
                                }
                            }
                            return !empty($converted) ? $converted : [['item' => 'diesel', 'quantity' => 'Full Tank']];
                        }

                        return [
                            ['item' => 'diesel', 'quantity' => 'Full Tank']
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
                        Select::make('quantity')
                            ->label('Quantity (Liters)')
                            ->options(function ($state) {
                                $options = [
                                    'Full Tank' => '⛽ Full Tank',
                                    '5' => '5 Liters',
                                    '10' => '10 Liters',
                                    '15' => '15 Liters',
                                    '20' => '20 Liters',
                                    '25' => '25 Liters',
                                    '30' => '30 Liters',
                                    '35' => '35 Liters',
                                    '40' => '40 Liters',
                                    '45' => '45 Liters',
                                    '50' => '50 Liters',
                                    '60' => '60 Liters',
                                    '70' => '70 Liters',
                                    '80' => '80 Liters',
                                    '90' => '90 Liters',
                                    '100' => '100 Liters',
                                    '120' => '120 Liters',
                                    '150' => '150 Liters',
                                    '200' => '200 Liters',
                                    '250' => '250 Liters',
                                    '300' => '300 Liters',
                                ];

                                if (!empty($state) && !is_array($state) && !isset($options[(string)$state])) {
                                    $options[(string)$state] = is_numeric($state) ? "{$state} Liters" : (string)$state;
                                }

                                return $options;
                            })
                            ->searchable()
                            ->createOptionUsing(fn (string $custom) => trim($custom))
                            ->default('Full Tank')
                            ->helperText('Pumili ng Full Tank o dami ng litro, o mag-type ng sariling numero.')
                            ->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('+ Add Requested Item')
                    ->itemLabel(function (array $state): ?string {
                        $item = $state['item'] ?? null;
                        $qty = $state['quantity'] ?? null;
                        $qtyText = '';
                        if (!empty($qty) && !is_array($qty)) {
                            $qtyText = $qty === 'Full Tank' ? 'Full Tank' : (is_numeric($qty) ? "{$qty} Liters" : (string)$qty);
                        }

                        $itemNames = [
                            'diesel' => '⛽ Diesel',
                            'gasoline_regular' => '⛽ Gasoline (Regular)',
                            'gasoline_premium' => '⛽ Gasoline (Premium)',
                            'lubricant_40' => '🛢️ Lubricant Oil 40',
                            'lubricant_30' => '🛢️ Lubricant Oil 30',
                            'brake_fluid' => '🛑 Brake Fluid',
                            'grease_atf' => '⚙️ 2T / Grease / ATF',
                            'gear_oil' => '🔧 Gear Oil',
                        ];

                        $label = $itemNames[$item] ?? $item;
                        return $label ? ($label . ($qtyText ? ": {$qtyText}" : '')) : null;
                    }),
                Hidden::make('status')
                    ->default('approved')
                    ->dehydrated(),
            ]);
    }
}
