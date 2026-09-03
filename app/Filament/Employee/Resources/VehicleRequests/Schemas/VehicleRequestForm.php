<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VehicleRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('request_number')
                    ->default(function () {
                        $lastRecord = \App\Models\VehicleRequest::latest('id')->first();
                        $nextId = $lastRecord ? ($lastRecord->id + 1) : 1;
                        return 'VR-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                    })
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                TextInput::make('employee_name')
                    ->default(fn () => auth()->user()?->name ?? 'Employee User')
                    ->required(),
                TextInput::make('department')
                    ->default(function () {
                        $user = auth()->user();
                        if (!empty($user?->department)) {
                            return $user->department;
                        }
                        $email = $user?->email ?? '';
                        $prefix = strtolower(explode('@', $email)[0]);
                        $validDepts = [
                            'employee' => 'CICS',
                            'admin' => 'Administration Office',
                            'ceo' => 'Office of the CEO',
                            'hrmo' => 'HRMO',
                            'accounting' => 'Accounting Office',
                            'budget' => 'Budget Office',
                            'property' => 'Property and Supply Office',
                            'records' => 'Records Office',
                            'planning' => 'Planning Office',
                            'mis' => 'MIS Office',
                            'registrar' => 'Office of the Campus Registrar',
                            'admission' => 'Campus Admission Office',
                            'publication' => 'Campus Publication Office',
                            'library' => 'University Library',
                            'cics' => 'CICS',
                            'cte' => 'CTE',
                            'chm' => 'CHM',
                            'coa' => 'COA',
                            'cafevalena' => 'Café Valena',
                            'csc' => 'Campus Student Council'
                        ];
                        return $validDepts[$prefix] ?? 'Campus Student Council';
                    })
                    ->readOnly()
                    ->required(),
                Select::make('vehicle')
                    ->options(function (Get $get) {
                        $date = $get('date');
                        $vehicleTypes = [
                            'FORTUNER' => 'FORTUNER',
                            'HIACE VAN' => 'HIACE VAN',
                            'PTIA JEEP' => 'PTIA JEEP',
                            'MULTICAB' => 'MULTICAB',
                        ];

                        foreach ($vehicleTypes as $type => $label) {
                            // Check if this vehicle is currently under maintenance
                            $isMaintenance = \App\Models\Vehicle::where('status', 'maintenance')
                                ->where(function ($q) use ($type) {
                                    $q->where('brand', 'like', '%' . $type . '%')
                                      ->orWhere('model', 'like', '%' . $type . '%');
                                })
                                ->exists();

                            if ($isMaintenance) {
                                $vehicleTypes[$type] = "{$type} (Under Maintenance)";
                                continue;
                            }

                            // Get all plates for this type
                            $plates = \App\Models\Vehicle::where('brand', 'like', '%' . $type . '%')
                                ->orWhere('model', 'like', '%' . $type . '%')
                                ->pluck('plate_number')
                                ->toArray();

                            // 1. Check if vehicle is active on the road right now (On Trip)
                            $isActiveNow = \App\Models\Vehicle::where('status', 'on_trip')
                                ->where(function ($q) use ($type) {
                                    $q->where('brand', 'like', '%' . $type . '%')
                                      ->orWhere('model', 'like', '%' . $type . '%');
                                })
                                ->exists()
                                || \App\Models\TripTicket::where('status', 'active')
                                ->where(function ($q) use ($type, $plates) {
                                    $q->where('vehicle', 'like', '%' . $type . '%');
                                    foreach ($plates as $plate) {
                                        $q->orWhere('vehicle', 'like', '%' . $plate . '%');
                                    }
                                })
                                ->exists();

                            if ($isActiveNow) {
                                $vehicleTypes[$type] = "{$type} (On Trip)";
                                continue;
                            }

                            // 2. Check if scheduled on this specific date (Pending or Active, NOT completed or cancelled)
                            if ($date) {
                                $isScheduled = \App\Models\TripTicket::whereIn('status', ['pending', 'active'])
                                    ->where(function ($q) use ($type, $plates) {
                                        $q->where('vehicle', 'like', '%' . $type . '%');
                                        foreach ($plates as $plate) {
                                            $q->orWhere('vehicle', 'like', '%' . $plate . '%');
                                        }
                                    })
                                    ->whereHas('vehicleRequest', function ($q) use ($date) {
                                        $q->where('date', $date);
                                    })
                                    ->exists();

                                if ($isScheduled) {
                                    $vehicleTypes[$type] = "{$type} (Scheduled on this date)";
                                }
                            }
                        }

                        return $vehicleTypes;
                    })
                    ->disableOptionWhen(function (string $value, Get $get) {
                        $date = $get('date');

                        // Check if under maintenance
                        $isMaintenance = \App\Models\Vehicle::where('status', 'maintenance')
                            ->where(function ($q) use ($value) {
                                $q->where('brand', 'like', '%' . $value . '%')
                                  ->orWhere('model', 'like', '%' . $value . '%');
                            })
                            ->exists();

                        if ($isMaintenance) {
                            return true;
                        }

                        // Get plates
                        $plates = \App\Models\Vehicle::where('brand', 'like', '%' . $value . '%')
                            ->orWhere('model', 'like', '%' . $value . '%')
                            ->pluck('plate_number')
                            ->toArray();

                        // Check if active on trip
                        $isActiveNow = \App\Models\Vehicle::where('status', 'on_trip')
                            ->where(function ($q) use ($value) {
                                $q->where('brand', 'like', '%' . $value . '%')
                                  ->orWhere('model', 'like', '%' . $value . '%');
                            })
                            ->exists()
                            || \App\Models\TripTicket::where('status', 'active')
                            ->where(function ($q) use ($value, $plates) {
                                $q->where('vehicle', 'like', '%' . $value . '%');
                                foreach ($plates as $plate) {
                                    $q->orWhere('vehicle', 'like', '%' . $plate . '%');
                                }
                            })
                            ->exists();

                        if ($isActiveNow) {
                            return true;
                        }

                        // Check if scheduled (Pending/Active only)
                        if ($date) {
                            $isScheduled = \App\Models\TripTicket::whereIn('status', ['pending', 'active'])
                                ->where(function ($q) use ($value, $plates) {
                                    $q->where('vehicle', 'like', '%' . $value . '%');
                                    foreach ($plates as $plate) {
                                        $q->orWhere('vehicle', 'like', '%' . $plate . '%');
                                    }
                                })
                                ->whereHas('vehicleRequest', function ($q) use ($date) {
                                    $q->where('date', $date);
                                })
                                ->exists();

                            if ($isScheduled) {
                                return true;
                            }
                        }

                        return false;
                    })
                    ->label('Requested Vehicle Type')
                    ->helperText('Select the type of vehicle you prefer for this trip. (Note: Vehicles on trip or under maintenance cannot be selected).')
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $date = $get('date');
                            
                            $isMaintenance = \App\Models\Vehicle::where('status', 'maintenance')
                                ->where(function ($q) use ($value) {
                                    $q->where('brand', 'like', '%' . $value . '%')
                                      ->orWhere('model', 'like', '%' . $value . '%');
                                })
                                ->exists();

                            if ($isMaintenance) {
                                $fail("The preferred vehicle type '{$value}' is currently under maintenance. Please select another vehicle.");
                            }

                            // Get plates
                            $plates = \App\Models\Vehicle::where('brand', 'like', '%' . $value . '%')
                                ->orWhere('model', 'like', '%' . $value . '%')
                                ->pluck('plate_number')
                                ->toArray();

                            // Check active now
                            $isActiveNow = \App\Models\TripTicket::where('status', 'active')
                                ->where(function ($q) use ($value, $plates) {
                                    $q->where('vehicle', 'like', '%' . $value . '%');
                                    foreach ($plates as $plate) {
                                        $q->orWhere('vehicle', 'like', '%' . $plate . '%');
                                    }
                                })
                                ->exists();

                            if ($isActiveNow) {
                                $fail("The preferred vehicle type '{$value}' is currently on a trip. Please select another vehicle.");
                            }

                            // Check scheduled
                            if ($date) {
                                $isScheduled = \App\Models\TripTicket::where('status', '!=', 'cancelled')
                                    ->where(function ($q) use ($value, $plates) {
                                        $q->where('vehicle', 'like', '%' . $value . '%');
                                        foreach ($plates as $plate) {
                                            $q->orWhere('vehicle', 'like', '%' . $plate . '%');
                                        }
                                    })
                                    ->whereHas('vehicleRequest', function ($q) use ($date) {
                                        $q->where('date', $date);
                                    })
                                    ->exists();

                                if ($isScheduled) {
                                    $fail("The preferred vehicle type '{$value}' is already scheduled for another trip on the selected travel date.");
                                }
                            }
                        }
                    ])
                    ->required(),
                Fieldset::make('Destination Address')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('region_code')
                            ->label('Region')
                            ->options(\App\Services\PhilippineAddressService::getRegions())
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set) {
                                $set('province_code', null);
                                $set('city_code', null);
                                $set('brgy_code', null);
                                $set('destination', null);
                            })
                            ->required(),
                        Select::make('province_code')
                            ->label('Province')
                            ->options(fn (Get $get) => \App\Services\PhilippineAddressService::getProvinces($get('region_code')))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set) {
                                $set('city_code', null);
                                $set('brgy_code', null);
                                $set('destination', null);
                            })
                            ->disabled(fn (Get $get) => empty($get('region_code')))
                            ->required(),
                        Select::make('city_code')
                            ->label('City/Municipality')
                            ->options(fn (Get $get) => \App\Services\PhilippineAddressService::getCities($get('province_code')))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set) {
                                $set('brgy_code', null);
                                $set('destination', null);
                            })
                            ->disabled(fn (Get $get) => empty($get('province_code')))
                            ->required(),
                        Select::make('brgy_code')
                            ->label('Barangay')
                            ->options(fn (Get $get) => \App\Services\PhilippineAddressService::getBarangays($get('city_code')))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $regionName = \App\Services\PhilippineAddressService::getRegions()[$get('region_code')] ?? '';
                                $provinceName = \App\Services\PhilippineAddressService::getProvinces($get('region_code'))[$get('province_code')] ?? '';
                                $cityName = \App\Services\PhilippineAddressService::getCities($get('province_code'))[$get('city_code')] ?? '';
                                $brgyName = \App\Services\PhilippineAddressService::getBarangays($get('city_code'))[$get('brgy_code')] ?? '';
                                
                                $addressParts = array_filter([$regionName, $provinceName, $cityName, $brgyName, $get('street_name')]);
                                $set('destination', implode(', ', $addressParts));
                            })
                            ->disabled(fn (Get $get) => empty($get('city_code')))
                            ->required(),
                        TextInput::make('street_name')
                            ->label('Street/Building/House No.')
                            ->live(onBlur: true)
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $regionName = \App\Services\PhilippineAddressService::getRegions()[$get('region_code')] ?? '';
                                $provinceName = \App\Services\PhilippineAddressService::getProvinces($get('region_code'))[$get('province_code')] ?? '';
                                $cityName = \App\Services\PhilippineAddressService::getCities($get('province_code'))[$get('city_code')] ?? '';
                                $brgyName = \App\Services\PhilippineAddressService::getBarangays($get('city_code'))[$get('brgy_code')] ?? '';
                                
                                $addressParts = array_filter([$regionName, $provinceName, $cityName, $brgyName, $state]);
                                $set('destination', implode(', ', $addressParts));
                            })
                            ->disabled(fn (Get $get) => empty($get('brgy_code')))
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('destination')
                            ->label('Full Destination Address')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull()
                            ->afterStateHydrated(function (Set $set, $state) {
                                if (empty($state)) return;
                                $parts = explode(', ', $state);
                                $regionName = $parts[0] ?? null;
                                $provinceName = $parts[1] ?? null;
                                $cityName = $parts[2] ?? null;
                                $brgyName = $parts[3] ?? null;
                                $streetName = isset($parts[4]) ? implode(', ', array_slice($parts, 4)) : null;

                                list($regionCode, $provinceCode, $cityCode, $brgyCode) = \App\Services\PhilippineAddressService::getCodesFromNames(
                                    $regionName, $provinceName, $cityName, $brgyName
                                );

                                $set('region_code', $regionCode);
                                $set('province_code', $provinceCode);
                                $set('city_code', $cityCode);
                                $set('brgy_code', $brgyCode);
                                $set('street_name', $streetName);
                            }),
                    ])
                    ->columns(2),

                Fieldset::make('Trip Purpose & Schedule')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('purpose_select')
                            ->label('Purpose')
                            ->options([
                                'Meeting' => 'Meeting',
                                'Seminar' => 'Seminar',
                                'Workshop' => 'Workshop',
                                'Outreach' => 'Outreach',
                                'Business Visit' => 'Business Visit',
                                'Others' => 'Others (Specify below)',
                            ])
                            ->default('Meeting')
                            ->live()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record) {
                                    $predefined = ['Meeting', 'Seminar', 'Workshop', 'Outreach', 'Business Visit'];
                                    if (in_array($record->purpose, $predefined)) {
                                        $set('purpose_select', $record->purpose);
                                    } elseif ($record->purpose) {
                                        $set('purpose_select', 'Others');
                                        $set('other_purpose', $record->purpose);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state !== 'Others') {
                                    $set('purpose', $state);
                                } else {
                                    $set('purpose', null);
                                }
                            })
                            ->required(),
                        TextInput::make('other_purpose')
                            ->label('Specify Purpose')
                            ->placeholder('Type custom purpose here')
                            ->visible(fn (callable $get) => $get('purpose_select') === 'Others')
                            ->required(fn (callable $get) => $get('purpose_select') === 'Others')
                            ->live(onBlur: true)
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('purpose', $state);
                            }),
                        Hidden::make('purpose')
                            ->default('Meeting')
                            ->dehydrated()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Travel Date')
                            ->default(now())
                            ->minDate(now()->startOfDay())
                            ->live()
                            ->required(),
                        TimePicker::make('time')
                            ->label('Travel Time')
                            ->default(now())
                            ->live()
                            ->required(),
                        DatePicker::make('return_date')
                            ->label('Expected Return Date')
                            ->default(now())
                            ->minDate(fn (callable $get) => \Carbon\Carbon::parse($get('date') ?? now())->startOfDay())
                            ->live()
                            ->required(),
                        TimePicker::make('return_time')
                            ->label('Expected Return Time')
                            ->default(now())
                            ->live()
                            ->required(),
                    ])
                    ->columns(2),
                \Filament\Forms\Components\Repeater::make('passenger_names')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->placeholder('Passenger Name')
                            ->required(),
                    ])
                    ->label('Passengers')
                    ->addActionLabel('+ Add Passenger')
                    ->default(fn () => [['name' => auth()->user()?->name ?? 'Requester']])
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $names = array_filter(array_map(fn ($item) => trim($item['name'] ?? ''), $state ?? []));
                        $set('number_of_passengers', count($names) ?: 1);
                    })
                    ->required(),
                TextInput::make('number_of_passengers')
                    ->label('Total Passengers')
                    ->numeric()
                    ->default(1)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Hidden::make('status')
                    ->default('pending'),
            ]);
    }
}

