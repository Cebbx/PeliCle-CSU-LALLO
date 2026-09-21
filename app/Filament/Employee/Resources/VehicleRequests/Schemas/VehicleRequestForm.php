<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class VehicleRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('request_number')
                            ->label('Vehicle Request')
                            ->default(fn () => \App\Models\VehicleRequest::generateNextRequestNumber())
                            ->helperText('Auto-generated tracking number')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        TextInput::make('department')
                            ->label('Department / Office')
                            ->default(function () {
                                $user = \Filament\Facades\Filament::auth()->user() ?? auth('employee')->user() ?? auth()->user();
                                if (!empty($user?->department)) {
                                    return $user->department;
                                }
                                $name = $user?->name ?? '';
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
                                if (isset($validDepts[$prefix])) {
                                    return $validDepts[$prefix];
                                }
                                foreach ($validDepts as $key => $deptName) {
                                    if (stripos($name, $deptName) !== false || stripos($name, $key) !== false) {
                                        return $deptName;
                                    }
                                }
                                return 'Campus Student Council';
                            })
                            ->helperText('Auto-filled from your department')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        TextInput::make('employee_name')
                            ->label('Requester Name')
                            ->placeholder('e.g. Dr. Juan Dela Cruz / Full Name')
                            ->helperText('Please enter the full name of requester')
                            ->default(function () {
                                $user = \Filament\Facades\Filament::auth()->user() ?? auth('employee')->user() ?? auth()->user();
                                $name = $user?->name ?? '';
                                $deptIndicators = ['College of', 'Office of', 'Department', 'Administration Office', 'Campus', 'Café Valena', 'CICS', 'CTE', 'CHM', 'COA', 'HRMO', 'MIS', 'Employee User'];
                                foreach ($deptIndicators as $ind) {
                                    if (stripos($name, $ind) !== false) {
                                        return null;
                                    }
                                }
                                return $name ?: null;
                            })
                            ->required(),
                    ]),

                Hidden::make('user_id')
                    ->default(fn () => \Filament\Facades\Filament::auth()->id() ?? auth('employee')->id() ?? auth()->id()),

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
                                $set('destination', null);
                            })
                            ->disabled(fn (Get $get) => empty($get('region_code')))
                            ->required(),
                        Select::make('city_code')
                            ->label('City / Municipality')
                            ->options(fn (Get $get) => \App\Services\PhilippineAddressService::getCities($get('province_code')))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $cityName = \App\Services\PhilippineAddressService::getCities($get('province_code'))[$state] ?? '';
                                $provinceName = \App\Services\PhilippineAddressService::getProvinces($get('region_code'))[$get('province_code')] ?? '';
                                $addressParts = array_filter([$cityName, $provinceName]);
                                $set('destination', implode(', ', $addressParts));
                            })
                            ->disabled(fn (Get $get) => empty($get('province_code')))
                            ->required(),
                        TextInput::make('destination')
                            ->label('Destination Preview')
                            ->placeholder('Auto-generated based on City & Province')
                            ->suffixIcon('heroicon-m-map-pin')
                            ->suffixIconColor('gray')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->afterStateHydrated(function (Set $set, $state) {
                                if (empty($state)) return;
                                $parts = array_map('trim', explode(', ', $state));
                                $regions = \App\Services\PhilippineAddressService::getRegions();

                                if (isset($parts[0]) && in_array($parts[0], $regions)) {
                                    $regionName = $parts[0] ?? null;
                                    $provinceName = $parts[1] ?? null;
                                    $cityName = $parts[2] ?? null;
                                    list($regionCode, $provinceCode, $cityCode) = \App\Services\PhilippineAddressService::getCodesFromNames(
                                        $regionName, $provinceName, $cityName, null
                                    );
                                } else {
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
                                }

                                $set('region_code', $regionCode);
                                $set('province_code', $provinceCode);
                                $set('city_code', $cityCode);
                            }),
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

                Fieldset::make('Passenger Manifest / Companions')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('passenger_names')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Passenger Full Name'),
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->placeholder('e.g. Dr. Juan Dela Cruz')
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
                            ->addActionLabel('+ Add Passenger')
                            ->default([['name' => '']])
                            ->reorderable()
                            ->reorderAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-m-arrow-down'))
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $names = array_filter(array_map(fn ($item) => trim($item['name'] ?? ''), $state ?? []));
                                $set('number_of_passengers', count($names) ?: 1);
                            })
                            ->columnSpanFull()
                            ->required(),

                        Grid::make(2)->schema([
                            TextInput::make('number_of_passengers')
                                ->label('Total Passengers')
                                ->numeric()
                                ->default(1)
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->helperText('Automatically computed based on the passenger list above.'),

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
                        ]),

                        Textarea::make('other_passengers')
                            ->label('Specify Other Passengers / Students')
                            ->placeholder('e.g., 10 CICS Students for Regional Competition, Guest Speaker')
                            ->visible(fn (Get $get) => (bool) $get('has_other_passengers'))
                            ->required(fn (Get $get) => (bool) $get('has_other_passengers'))
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Hidden::make('status')
                    ->default('pending'),
            ]);
    }
}
