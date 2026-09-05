<?php

namespace App\Filament\Resources\VehicleRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
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
                    ->unique('vehicle_requests', 'request_number', ignoreRecord: true)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('employee_name')
                    ->required(),
                Select::make('department')
                    ->options([
                        'Office of the CEO' => 'Office of the CEO (Campus Executive Officer)',
                        'HRMO' => 'HRMO (Human Resource Management Office)',
                        'Accounting Office' => 'Accounting Office',
                        'Budget Office' => 'Budget Office',
                        'Property and Supply Office' => 'Property and Supply Office',
                        'Records Office' => 'Records Office',
                        'Planning Office' => 'Planning Office',
                        'MIS Office' => 'MIS Office (Management Information System / System Admin)',
                        'Office of the Campus Registrar' => 'Office of the Campus Registrar',
                        'Campus Admission Office' => 'Campus Admission Office',
                        'Campus Publication Office' => 'Campus Publication Office',
                        'University Library' => 'University Library',
                        'CICS' => 'CICS (College of Information and Computing Sciences)',
                        'CTE' => 'CTE (College of Teacher Education)',
                        'CHM' => 'CHM (College of Hospitality Management)',
                        'COA' => 'COA (College of Agriculture)',
                        'Café Valena' => 'Café Valena (CoffeeHub Café)',
                        'Campus Student Council' => 'Campus Student Council (CSC)',
                    ])
                    ->required(),
                \Filament\Forms\Components\Checkbox::make('show_all_vehicles')
                    ->label('Do you want to see/request other cars currently on trip?')
                    ->live(),

                Select::make('vehicle')
                    ->options(function (callable $get, ?\App\Models\VehicleRequest $record) {
                        $allVehicles = [
                            'FORTUNER - SBA1749' => 'FORTUNER - SBA1749',
                            'HIACE VAN - SBA3790' => 'HIACE VAN - SBA3790',
                            'PTIA JEEP - SDV868' => 'PTIA JEEP - SDV868',
                            'MULTICAB - NAJI987' => 'MULTICAB - NAJI987',
                        ];

                        $onTripVehicles = \App\Models\TripTicket::where('status', 'active')
                            ->when($record, function ($q) use ($record) {
                                $ticketId = \App\Models\TripTicket::where('vehicle_request_id', $record->id)->value('id');
                                if ($ticketId) {
                                    $q->where('id', '!=', $ticketId);
                                }
                            })
                            ->pluck('vehicle')
                            ->filter()
                            ->toArray();

                        $onTripDbPlates = \App\Models\Vehicle::where('status', 'on_trip')->pluck('plate_number')->toArray();

                        $underMaintenanceVehicles = \App\Models\Vehicle::where('status', 'maintenance')
                            ->pluck('plate_number')
                            ->toArray();

                        $allVehiclesFormatted = [];
                        foreach ($allVehicles as $key => $value) {
                            $parts = explode(' - ', $key);
                            $brand = trim($parts[0] ?? '');
                            $plate = trim($parts[1] ?? $key);

                            $isOnTrip = in_array($plate, $onTripDbPlates);
                            if (!$isOnTrip) {
                                foreach ($onTripVehicles as $activeV) {
                                    if (str_contains($activeV, $plate) || (filled($brand) && str_contains($activeV, $brand))) {
                                        $isOnTrip = true;
                                        break;
                                    }
                                }
                            }

                            $isMaintenance = in_array($plate, $underMaintenanceVehicles) || \App\Models\Vehicle::where('status', 'maintenance')->where('brand', 'like', "%{$brand}%")->exists();

                            if ($isMaintenance) {
                                $allVehiclesFormatted[$key] = "{$value} (Under Maintenance)";
                            } elseif ($isOnTrip) {
                                $allVehiclesFormatted[$key] = "{$value} (On Trip)";
                            } else {
                                $allVehiclesFormatted[$key] = $value;
                            }
                        }

                        return $allVehiclesFormatted;
                    })
                    ->label('Vehicle')
                    ->disableOptionWhen(function (string $value, ?\App\Models\VehicleRequest $record) {
                        $onTripVehicles = \App\Models\TripTicket::where('status', 'active')
                            ->when($record, function ($q) use ($record) {
                                $ticketId = \App\Models\TripTicket::where('vehicle_request_id', $record->id)->value('id');
                                if ($ticketId) {
                                    $q->where('id', '!=', $ticketId);
                                }
                            })
                            ->pluck('vehicle')
                            ->filter()
                            ->toArray();

                        $onTripDbPlates = \App\Models\Vehicle::where('status', 'on_trip')->pluck('plate_number')->toArray();

                        $underMaintenanceVehicles = \App\Models\Vehicle::where('status', 'maintenance')
                            ->pluck('plate_number')
                            ->toArray();

                        $parts = explode(' - ', $value);
                        $brand = trim($parts[0] ?? '');
                        $plate = trim($parts[1] ?? $value);

                        if (in_array($plate, $onTripDbPlates)) {
                            return true;
                        }

                        foreach ($onTripVehicles as $activeV) {
                            if (str_contains($activeV, $plate) || (filled($brand) && str_contains($activeV, $brand))) {
                                return true;
                            }
                        }

                        if (in_array($plate, $underMaintenanceVehicles) || \App\Models\Vehicle::where('status', 'maintenance')->where('brand', 'like', "%{$brand}%")->exists()) {
                            return true;
                        }

                        return false;
                    })
                    ->helperText(function (callable $get, ?\App\Models\VehicleRequest $record) {
                        $vehicle = $get('vehicle');
                        $travelDate = $get('date');
                        $travelTime = $get('time');
                        $returnDate = $get('return_date');
                        $returnTime = $get('return_time');

                        if ($vehicle && $travelDate && $travelTime && $returnDate && $returnTime) {
                            $startDt = \Carbon\Carbon::parse("$travelDate $travelTime");
                            $endDt = \Carbon\Carbon::parse("$returnDate $returnTime");

                            $isOverlapping = \App\Models\VehicleRequest::where('status', 'approved')
                                ->where('vehicle', $vehicle)
                                ->whereNotNull('return_date')
                                ->whereNotNull('return_time')
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->get()
                                ->filter(function ($request) use ($startDt, $endDt) {
                                    $reqStart = \Carbon\Carbon::parse("{$request->date} {$request->time}");
                                    $reqEnd = \Carbon\Carbon::parse("{$request->return_date} {$request->return_time}");
                                    return $startDt->lt($reqEnd) && $endDt->gt($reqStart);
                                })
                                ->isNotEmpty();

                            if ($isOverlapping) {
                                return new \Illuminate\Support\HtmlString('<span style="color: #dc2626; font-weight: bold; display: block; margin-top: 4px;">⚠️ Conflict: This vehicle is already booked during these hours. Please select another!</span>');
                            }
                        }

                        if ($get('show_all_vehicles')) {
                            return "Showing all vehicles. (Note: Some of these might currently be on a trip or under maintenance).";
                        }
                        return "Showing only available vehicles.";
                    })
                    ->rules([
                        fn (callable $get, ?\App\Models\VehicleRequest $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            $onTripVehicles = \App\Models\TripTicket::where('status', 'active')
                                ->when($record, function ($q) use ($record) {
                                    $ticketId = \App\Models\TripTicket::where('vehicle_request_id', $record->id)->value('id');
                                    if ($ticketId) {
                                        $q->where('id', '!=', $ticketId);
                                    }
                                })
                                ->pluck('vehicle')
                                ->filter()
                                ->toArray();

                            $parts = explode(' - ', $value);
                            $plate = $parts[1] ?? $value;

                            if (in_array($plate, $onTripVehicles)) {
                                $fail("This vehicle is currently on a trip. Please select another vehicle.");
                                return;
                            }

                            $isMaintenance = \App\Models\Vehicle::where('plate_number', $plate)
                                ->where('status', 'maintenance')
                                ->exists();

                            if ($isMaintenance) {
                                $fail("This vehicle is currently under maintenance. Please select another vehicle.");
                                return;
                            }

                            $travelDate = $get('date');
                            $travelTime = $get('time');
                            $returnDate = $get('return_date');
                            $returnTime = $get('return_time');

                            if ($value && $travelDate && $travelTime && $returnDate && $returnTime) {
                                $startDt = \Carbon\Carbon::parse("$travelDate $travelTime");
                                $endDt = \Carbon\Carbon::parse("$returnDate $returnTime");

                                $isOverlapping = \App\Models\VehicleRequest::where('status', 'approved')
                                    ->where('vehicle', $value)
                                    ->whereNotNull('return_date')
                                    ->whereNotNull('return_time')
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->get()
                                    ->filter(function ($request) use ($startDt, $endDt) {
                                        $reqStart = \Carbon\Carbon::parse("{$request->date} {$request->time}");
                                        $reqEnd = \Carbon\Carbon::parse("{$request->return_date} {$request->return_time}");
                                        return $startDt->lt($reqEnd) && $endDt->gt($reqStart);
                                    })
                                    ->isNotEmpty();

                                if ($isOverlapping) {
                                    $fail("Conflict: The vehicle '{$value}' is already booked during these hours. Please select another!");
                                }
                            }
                        },
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
                        \Filament\Forms\Components\Hidden::make('purpose')
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
                    ->default([['name' => '']])
                    ->reorderable()
                    ->reorderAction(fn (\Filament\Actions\Action $action) => $action
                        ->icon(\Filament\Support\Icons\Heroicon::ArrowsUpDown)
                        ->tooltip('↕ Move / Reorder')
                    )
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
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
