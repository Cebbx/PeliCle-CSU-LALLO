<?php

namespace App\Filament\Resources\VehicleRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use App\Models\VehicleRequest;

class VehicleRequestForm
{
    public static function resolveAddressCodes(?string $destination): array
    {
        if (empty($destination)) {
            return ['region_code' => null, 'province_code' => null, 'city_code' => null];
        }

        $parts = array_map('trim', explode(', ', $destination));
        $regions = \App\Services\PhilippineAddressService::getRegions();

        if (isset($parts[0]) && in_array($parts[0], $regions)) {
            $regionName = $parts[0] ?? null;
            $provinceName = $parts[1] ?? null;
            $cityName = $parts[2] ?? null;
            list($regionCode, $provinceCode, $cityCode) = \App\Services\PhilippineAddressService::getCodesFromNames(
                $regionName, $provinceName, $cityName, null
            );
            return compact('regionCode', 'provinceCode', 'cityCode');
        }

        $cityName = $parts[0] ?? null;
        $provinceName = $parts[1] ?? null;
        $regionCode = null;
        $provinceCode = null;
        $cityCode = null;

        if ($provinceName) {
            foreach ($regions as $rCode => $rName) {
                $provs = \App\Services\PhilippineAddressService::getProvinces($rCode);
                $foundPCode = array_search($provinceName, $provs);
                if ($foundPCode) {
                    $regionCode = $rCode;
                    $provinceCode = $foundPCode;
                    $cities = \App\Services\PhilippineAddressService::getCities($provinceCode);
                    $cityCode = array_search($cityName, $cities) ?: null;
                    break;
                }
            }
        }

        return compact('regionCode', 'provinceCode', 'cityCode');
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('request_number')
                    ->label('Vehicle Request Number')
                    ->default(fn () => \App\Models\VehicleRequest::generateNextRequestNumber())
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('employee_name')
                    ->label('Requester Name')
                    ->required(),
                Select::make('department')
                    ->label('Offices')
                    ->options([
                        'Accounting Office' => 'Accounting Office',
                        'Administration Office' => 'Administration Office',
                        'Budget Office' => 'Budget Office',
                        'Café Valena' => 'Café Valena',
                        'Campus Admission Office' => 'Campus Admission Office',
                        'Campus Publication Office' => 'Campus Publication Office',
                        'Campus Student Council' => 'Campus Student Council',
                        'COA' => 'College of Agriculture',
                        'CHM' => 'College of Hospitality Management',
                        'CICS' => 'College of Information and Computing Sciences',
                        'CTE' => 'College of Teacher Education',
                        'DMST Office' => 'Department of Military Science and Tactics Office',
                        'HRMO' => 'Human Resource Management Office',
                        'MIS Office' => 'Management Information System Office',
                        'Office of the CEO' => 'Office of the Campus Executive Officer',
                        'Office of the Campus Registrar' => 'Office of the Campus Registrar',
                        'Planning Office' => 'Planning Office',
                        'Property and Supply Office' => 'Property and Supply Office',
                        'Records Office' => 'Records Office',
                        'University Library' => 'University Library',
                    ])
                    ->searchable()
                    ->required(),
                \Filament\Forms\Components\Placeholder::make('carpool_info')
                    ->label('🚗 Destination Consolidation & Carpool Status')
                    ->columnSpanFull()
                    ->content(function (callable $get, ?\App\Models\VehicleRequest $record) {
                        $travelDate = $get('date') ?? $record?->date;
                        $dest = $get('destination') ?? $record?->destination;
                        if (!$travelDate) {
                            return null;
                        }

                        $cleanCity = function(?string $d): string {
                            if (empty($d)) return '';
                            $firstPart = explode(',', $d)[0] ?? '';
                            $clean = strtolower(trim($firstPart));
                            $clean = preg_replace('/\b(city of|city|municipality of|municipality|mun\.)\b/i', '', $clean);
                            return trim(preg_replace('/\s+/', ' ', $clean));
                        };

                        $myCity = $cleanCity($dest);
                        $recordId = $record?->id;

                        $otherReqs = \App\Models\VehicleRequest::when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                            ->where('date', $travelDate)
                            ->whereIn('status', ['pending', 'approved', 'on_trip'])
                            ->get();

                        if ($otherReqs->isEmpty()) {
                            return new \Illuminate\Support\HtmlString("
                                <div class='p-3 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2'>
                                    <span class='text-sm'>ℹ️</span>
                                    <span><strong>Solo Trip Schedule:</strong> No other requests on " . \Carbon\Carbon::parse($travelDate)->format('M d, Y') . ".</span>
                                </div>
                            ");
                        }

                        $sameDestReqs = $otherReqs->filter(function ($r) use ($myCity, $cleanCity, $dest) {
                            $otherCity = $cleanCity($r->destination);
                            return ($myCity !== '' && $otherCity !== '') && (
                                $myCity === $otherCity ||
                                str_contains($myCity, $otherCity) ||
                                str_contains($otherCity, $myCity) ||
                                str_contains(strtolower($r->destination), $myCity) ||
                                str_contains(strtolower($dest ?? ''), $otherCity)
                            );
                        });

                        $html = "<div class='p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-xl text-xs space-y-2'>";
                        if ($sameDestReqs->isNotEmpty()) {
                            $html .= "<div class='text-amber-800 dark:text-amber-300 font-bold flex items-center gap-1.5 text-sm'>
                                <span>⭐ Found " . $sameDestReqs->count() . " Other Request(s) to Same Destination on " . \Carbon\Carbon::parse($travelDate)->format('M d, Y') . "!</span>
                            </div>";
                            $html .= "<p class='text-slate-600 dark:text-slate-300'>You can consolidate these requests into one trip ticket using <strong>'Approve & Create Trip Ticket'</strong> in the header actions above:</p>";
                            $html .= "<ul class='list-disc list-inside space-y-1 text-slate-700 dark:text-slate-200 font-medium'>";
                            foreach ($sameDestReqs as $sr) {
                                $pax = $sr->number_of_passengers ?: 1;
                                $pWord = $pax > 1 ? 'persons' : 'person';
                                $t = $sr->time ? \Carbon\Carbon::parse($sr->time)->format('g:i A') : 'No time';
                                $html .= "<li><strong>{$sr->request_number}</strong> &mdash; {$sr->department} ({$sr->employee_name}) [{$pax} {$pWord}] [Leaves @ {$t}]</li>";
                            }
                            $html .= "</ul>";
                        } else {
                            $html .= "<div class='text-slate-700 dark:text-slate-300 font-semibold'>📅 Other requests scheduled on " . \Carbon\Carbon::parse($travelDate)->format('M d, Y') . ":</div>";
                            $html .= "<ul class='list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400'>";
                            foreach ($otherReqs as $otr) {
                                $html .= "<li><strong>{$otr->request_number}</strong> ({$otr->department}) &rarr; {$otr->destination}</li>";
                            }
                            $html .= "</ul>";
                        }
                        $html .= "</div>";

                        return new \Illuminate\Support\HtmlString($html);
                    }),
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
                            if (empty($value)) {
                                return;
                            }

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
                    ->nullable()
                    ->placeholder('Select vehicle (or leave unassigned for Trip Ticket)'),
                Fieldset::make('Destination Address')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('region_code')
                            ->label('Region')
                            ->options(\App\Services\PhilippineAddressService::getRegions())
                            ->live()
                            ->dehydrated(false)
                            ->default(fn (?VehicleRequest $record) => self::resolveAddressCodes($record?->destination)['region_code'] ?? null)
                            ->afterStateUpdated(function (Set $set) {
                                $set('province_code', null);
                                $set('city_code', null);
                            })
                            ->nullable(),
                        Select::make('province_code')
                            ->label('Province')
                            ->options(fn (Get $get, ?VehicleRequest $record) => \App\Services\PhilippineAddressService::getProvinces($get('region_code') ?? (self::resolveAddressCodes($record?->destination)['region_code'] ?? null)))
                            ->live()
                            ->dehydrated(false)
                            ->default(fn (?VehicleRequest $record) => self::resolveAddressCodes($record?->destination)['province_code'] ?? null)
                            ->afterStateUpdated(function (Set $set) {
                                $set('city_code', null);
                            })
                            ->nullable(),
                        Select::make('city_code')
                            ->label('City / Municipality')
                            ->options(fn (Get $get, ?VehicleRequest $record) => \App\Services\PhilippineAddressService::getCities($get('province_code') ?? (self::resolveAddressCodes($record?->destination)['province_code'] ?? null)))
                            ->live()
                            ->dehydrated(false)
                            ->default(fn (?VehicleRequest $record) => self::resolveAddressCodes($record?->destination)['city_code'] ?? null)
                            ->afterStateUpdated(function (Get $get, Set $set, $state, ?VehicleRequest $record) {
                                $pCode = $get('province_code') ?? (self::resolveAddressCodes($record?->destination)['province_code'] ?? null);
                                $rCode = $get('region_code') ?? (self::resolveAddressCodes($record?->destination)['region_code'] ?? null);
                                $cityName = \App\Services\PhilippineAddressService::getCities($pCode)[$state] ?? '';
                                $provinceName = \App\Services\PhilippineAddressService::getProvinces($rCode)[$pCode] ?? '';
                                $addressParts = array_filter([$cityName, $provinceName]);
                                if (!empty($addressParts)) {
                                    $set('destination', implode(', ', $addressParts));
                                }
                            })
                            ->nullable(),
                        TextInput::make('destination')
                            ->label('Destination (City / Province / Landmark)')
                            ->placeholder('e.g. Tuguegarao City, Cagayan')
                            ->suffixIcon('heroicon-m-map-pin')
                            ->suffixIconColor('primary')
                            ->dehydrated()
                            ->required()
                            ->helperText('Auto-updates when selecting City/Province, or type/edit custom route directly.'),
                    ])
                    ->columns(2),

                Fieldset::make('Travel Schedule')
                    ->columnSpan(1)
                    ->schema([
                        DatePicker::make('date')
                            ->label('Travel Departure Date')
                            ->default(now())
                            ->minDate(fn (?string $operation = null) => $operation === 'create' ? now()->startOfDay() : null)
                            ->live()
                            ->required(),
                        TimePicker::make('time')
                            ->label('Travel Departure Time')
                            ->seconds(false)
                            ->default(now())
                            ->live()
                            ->rules([
                                fn (Get $get, ?string $operation = null): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $operation) {
                                    if ($operation === 'create' || empty($operation)) {
                                        $date = $get('date');
                                        if ($date && \Carbon\Carbon::parse($date, 'Asia/Manila')->isToday()) {
                                            $selectedDateTime = \Carbon\Carbon::parse($date . ' ' . $value, 'Asia/Manila');
                                            if ($selectedDateTime->lessThan(\Carbon\Carbon::now('Asia/Manila')->subMinutes(5))) {
                                                $fail('Travel departure time cannot be in the past for today’s date.');
                                            }
                                        }
                                    }
                                },
                            ])
                            ->required(),
                        DatePicker::make('return_date')
                            ->label('Expected Return Date')
                            ->default(now())
                            ->minDate(fn (callable $get) => \Carbon\Carbon::parse($get('date') ?? now())->startOfDay())
                            ->live()
                            ->required(),
                        TimePicker::make('return_time')
                            ->label('Expected Return Time')
                            ->seconds(false)
                            ->default(fn () => now()->addHours(4))
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $depDate = $get('date');
                                    $depTime = $get('time');
                                    $retDate = $get('return_date');
                                    if ($depDate && $depTime && $retDate && $value) {
                                        $departure = \Carbon\Carbon::parse($depDate . ' ' . $depTime, 'Asia/Manila');
                                        $return = \Carbon\Carbon::parse($retDate . ' ' . $value, 'Asia/Manila');
                                        if ($return->lessThanOrEqualTo($departure)) {
                                            $fail('Expected return time must be later than the departure travel time.');
                                        }
                                    }
                                },
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Fieldset::make('Trip Purpose')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('purpose')
                            ->label('Purpose of Trip')
                            ->placeholder('Enter the reason or purpose of the trip...')
                            ->rows(3)
                            ->helperText('Please describe the official business, event, or academic purpose of the trip.')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                \Filament\Forms\Components\Repeater::make('passenger_names')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->placeholder('Passenger Name')
                            ->required()
                            ->live(onBlur: true)
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $all = $get('../../passenger_names') ?? [];
                                    if (!is_array($all)) return;
                                    $target = strtolower(trim((string)$value));
                                    if ($target === '') return;
                                    $matches = 0;
                                    foreach ($all as $item) {
                                        $itemName = strtolower(trim((string)($item['name'] ?? '')));
                                        if ($itemName === $target) {
                                            $matches++;
                                        }
                                    }
                                    if ($matches > 1) {
                                        $fail("Duplicate passenger: '{$value}' is already in the list. Please differentiate (e.g. Jr./Sr.) or enter a different name.");
                                    }
                                },
                            ]),
                    ])
                    ->label('Passengers')
                    ->addActionLabel('+ Add Passenger')
                    ->default([['name' => '']])
                    ->reorderable()
                    ->reorderAction(fn (\Filament\Actions\Action $action) => $action
                        ->icon(\Filament\Support\Icons\Heroicon::ArrowDown)
                        ->label('')
                        ->tooltip(null)
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
                    ->dehydrated()
                    ->required()
                    ->helperText('Auto-calculated from passenger list, or adjust directly if needed.'),
                Checkbox::make('has_other_passengers')
                    ->label('Others (Include Students / External Passengers)')
                    ->helperText('Check if the trip includes students, guests, or non-employee passengers.')
                    ->live()
                    ->default(false)
                    ->afterStateUpdated(function (callable $set, $state) {
                        if (!$state) {
                            $set('other_passengers', null);
                        }
                    }),
                Textarea::make('other_passengers')
                    ->label('Specify Other Passengers / Students')
                    ->placeholder('e.g., 10 CICS Students for Regional Competition, Guest Speaker')
                    ->visible(fn (Get $get) => (bool) $get('has_other_passengers'))
                    ->required(fn (Get $get) => (bool) $get('has_other_passengers'))
                    ->columnSpanFull()
                    ->rows(2),
                Fieldset::make('Requester Electronic Signature')
                    ->columnSpanFull()
                    ->schema([
                        ViewField::make('requester_signature')
                            ->hiddenLabel()
                            ->view('filament.components.signature-pad')
                            ->default(function (callable $get) {
                                $userId = $get('user_id');
                                if ($userId) {
                                    return \App\Models\User::find($userId)?->signature;
                                }
                                $user = \Filament\Facades\Filament::auth()->user() ?? auth()->user();
                                return $user?->signature;
                            })
                            ->columnSpanFull(),
                    ]),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Disapproved',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
