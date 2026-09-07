<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
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
                TextInput::make('request_number')
                    ->label('Request Number')
                    ->default(function () {
                        $lastRecord = \App\Models\VehicleRequest::latest('id')->first();
                        $nextId = $lastRecord ? ($lastRecord->id + 1) : 1;
                        return 'VR-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                    })
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                TextInput::make('employee_name')
                    ->label('Requester Name')
                    ->default(fn () => auth()->user()?->name ?? 'Employee User')
                    ->required(),

                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),

                TextInput::make('department')
                    ->label('Department / Office')
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
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Radio::make('is_urgent')
                    ->label('Priority Level')
                    ->options([
                        0 => 'Regular',
                        1 => 'Urgent',
                    ])
                    ->default(0)
                    ->inline()
                    ->helperText('Select "Urgent" for immediate official business or emergency dispatch.'),

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
                            ->placeholder('e.g., Building Name, Sitio, or Street')
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
                            ->label('Full Destination Address (Preview)')
                            ->helperText('Auto-generated from your address selections above.')
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
                            ->label('Purpose of Trip')
                            ->options([
                                'Meeting' => 'Meeting',
                                'Seminar' => 'Seminar',
                                'Workshop' => 'Workshop',
                                'Outreach' => 'Outreach',
                                'Business Visit' => 'Business Visit',
                                'Emergency' => 'Emergency',
                                'Others' => 'Others (Specify below)',
                            ])
                            ->default('Meeting')
                            ->live()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record) {
                                    $predefined = ['Meeting', 'Seminar', 'Workshop', 'Outreach', 'Business Visit', 'Emergency'];
                                    if (in_array($record->purpose, $predefined)) {
                                        $set('purpose_select', $record->purpose);
                                    } elseif ($record->purpose) {
                                        $set('purpose_select', 'Others');
                                        $set('other_purpose', $record->purpose);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'Emergency') {
                                    $set('is_urgent', 1);
                                }
                                if ($state !== 'Others') {
                                    $set('purpose', $state);
                                } else {
                                    $set('purpose', null);
                                }
                            })
                            ->required(),

                        TextInput::make('other_purpose')
                            ->label('Specify Custom Purpose')
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
                            ->helperText('Tip: Please allocate a 1 to 2-hour buffer for traffic and unexpected travel delays.')
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

                Fieldset::make('Passenger Manifest / Companions')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('passenger_names')
                            ->schema([
                                TextInput::make('name')
                                    ->placeholder('Enter Passenger Full Name (e.g. Dr. Juan Dela Cruz)')
                                    ->required()
                                    ->columnSpanFull()
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
                            ->label('Passenger List')
                            ->addActionLabel('+ Add Passenger')
                            ->default(function () {
                                $user = auth()->user();
                                return [['name' => $user?->name ?? '']];
                            })
                            ->reorderable()
                            ->reorderAction(fn (\Filament\Actions\Action $action) => $action
                                ->icon(Heroicon::ArrowDown)
                                ->label('')
                                ->tooltip(null)
                            )
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
