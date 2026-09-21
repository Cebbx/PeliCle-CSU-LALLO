<?php

namespace App\Filament\Pages;

use App\Models\TripTicket;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class TripLogbook extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string $routePath = '/trip-logbook';

    protected static ?string $title = 'Vehicle Trip Logbook';

    protected static ?string $navigationLabel = 'Trip Logbook';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.trip-logbook';

    public function getMaxContentWidth(): \Filament\Support\Enums\Width | string | null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Export to Excel (.CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('logbook.export-csv', [
                    'year' => now()->format('Y'),
                    'month' => now()->format('m'),
                ])),

            Action::make('print_logbook')
                ->label('Print Monthly Logbook Sheet')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('logbook.print', [
                    'year' => now()->format('Y'),
                    'month' => now()->format('m'),
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function getSummaryStats(): array
    {
        $now = now();
        $trips = TripTicket::where(function ($q) use ($now) {
            $month = $now->format('m');
            $year = $now->format('Y');
            $q->where('ticket_number', 'like', "%-{$month}-%")
              ->orWhere(function ($sub) use ($month, $year) {
                  $sub->whereMonth('created_at', $month)
                      ->whereYear('created_at', $year);
              });
        })->with('vehicleRequests')->get();

        $totalTrips = $trips->count();
        $completed = $trips->where('status', 'completed')->count();
        $onTrip = $trips->where('status', 'active')->count();
        $pax = 0;
        foreach ($trips as $t) {
            foreach ($t->all_vehicle_requests as $r) {
                $pax += ($r->number_of_passengers ?: 1);
            }
        }

        return [
            'period' => $now->format('F Y'),
            'total' => $totalTrips,
            'completed' => $completed,
            'onTrip' => $onTrip,
            'passengers' => $pax,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TripTicket::query()->with(['vehicleRequest', 'vehicleRequests', 'driver'])
            )
            ->heading('Monthly GSO Vehicle Dispatch Logbook')
            ->description('Official record of all vehicle trips organized by Month and Control Sequence Number.')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                TextColumn::make('ticket_number')
                    ->label('TT Control No.')
                    ->formatStateUsing(fn ($record) => $record->formatted_ticket_number)
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicleRequest.date')
                    ->label('Date')
                    ->formatStateUsing(function ($state, $record) {
                        return $state ?? ($record->created_at ? $record->created_at->format('Y-m-d') : '---');
                    })
                    ->fontFamily(FontFamily::Mono)
                    ->sortable(),

                TextColumn::make('vehicleRequest.time')
                    ->label('Time')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('g:i A') : '---')
                    ->fontFamily(FontFamily::Mono),

                TextColumn::make('department_passengers')
                    ->label('Dept & Passenger')
                    ->state(function ($record) {
                        $all = $record->all_vehicle_requests;
                        if ($all->count() > 1) {
                            $depts = $all->pluck('department')->unique()->filter()->join(', ');
                            $totalPax = 0;
                            foreach ($all as $r) { $totalPax += ($r->number_of_passengers ?: 1); }
                            return "Carpool ({$depts}) [{$totalPax} pax]";
                        }
                        $primary = $record->vehicleRequest;
                        $dept = $primary?->department ?? 'General';
                        $emp = $primary?->employee_name ?? 'N/A';
                        $pax = $primary?->number_of_passengers ?: 1;
                        return "{$dept} - {$emp} ({$pax} pax)";
                    })
                    ->limit(32)
                    ->tooltip(function ($record) {
                        $all = $record->all_vehicle_requests;
                        if ($all->count() > 1) {
                            $depts = $all->pluck('department')->unique()->filter()->join(', ');
                            $passengers = $all->pluck('employee_name')->filter()->join(', ');
                            return "Carpool [{$depts}]: {$passengers}";
                        }
                        $primary = $record->vehicleRequest;
                        return ($primary?->department ?? 'General') . ' - ' . ($primary?->employee_name ?? 'N/A');
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('vehicleRequests', function ($q) use ($search) {
                            $q->where('department', 'like', "%{$search}%")
                              ->orWhere('employee_name', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('vehicleRequest.destination')
                    ->label('Destination')
                    ->limit(28)
                    ->tooltip(fn ($record) => $record->vehicleRequest?->destination)
                    ->searchable(),

                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->weight(FontWeight::SemiBold)
                    ->formatStateUsing(fn ($state) => \App\Models\Vehicle::getVehicleName($state))
                    ->tooltip(function ($state) {
                        $name = \App\Models\Vehicle::getVehicleName($state);
                        $plate = \App\Models\Vehicle::getPlateNumber($state);
                        return ($plate && $plate !== $name) ? "{$name} (Plate: {$plate})" : $name;
                    })
                    ->searchable(),

                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->default('Unassigned')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending',
                        'active' => 'On Trip',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    }),
            ])
            ->defaultSort('ticket_number', 'desc')
            ->defaultPaginationPageOption(15)
            ->paginationPageOptions([10, 15, 25, 50])
            ->filters([
                SelectFilter::make('month')
                    ->label('Filter Month')
                    ->options([
                        '01' => 'January (01)',
                        '02' => 'February (02)',
                        '03' => 'March (03)',
                        '04' => 'April (04)',
                        '05' => 'May (05)',
                        '06' => 'June (06)',
                        '07' => 'July (07)',
                        '08' => 'August (08)',
                        '09' => 'September (09)',
                        '10' => 'October (10)',
                        '11' => 'November (11)',
                        '12' => 'December (12)',
                    ])
                    ->default(now()->format('m'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where(function ($q) use ($data) {
                                $q->where('ticket_number', 'like', "%-{$data['value']}-%")
                                  ->orWhereMonth('created_at', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('year')
                    ->label('Filter Year')
                    ->options([
                        '2024' => '2024',
                        '2025' => '2025',
                        '2026' => '2026',
                        '2027' => '2027',
                        '2028' => '2028',
                    ])
                    ->default(now()->format('Y'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where(function ($q) use ($data) {
                                $q->where('ticket_number', 'like', "%-{$data['value']}-%")
                                  ->orWhereYear('created_at', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Completed',
                        'active' => 'On Trip',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('print_ticket')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->button()
                    ->size('xs')
                    ->url(fn ($record) => route('trip-tickets.print', $record->id))
                    ->openUrlInNewTab(),

                Action::make('print_order')
                    ->label('Order')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->button()
                    ->size('xs')
                    ->url(fn ($record) => route('trip-tickets.print-travel-order', $record->id))
                    ->openUrlInNewTab(),
            ]);
    }
}
