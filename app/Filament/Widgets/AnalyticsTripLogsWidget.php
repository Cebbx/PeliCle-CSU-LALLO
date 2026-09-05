<?php

namespace App\Filament\Widgets;

use App\Models\VehicleRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Actions\Action;

class AnalyticsTripLogsWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Detailed Trip Activity & Passenger Logs';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;
        $filterDept = $this->filters['department'] ?? null;

        $query = VehicleRequest::with(['tripTicket.driver'])
            ->latest('date');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        if ($filterDept) {
            $query->where('department', $filterDept);
        }

        return $table
            ->query($query)
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request #')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('employee_name')
                    ->label('Requester (Client)')
                    ->description(fn ($record) => $record->department)
                    ->searchable(),

                TextColumn::make('destination')
                    ->label('Destination')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->destination)
                    ->searchable(),

                TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->purpose)
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Travel Schedule')
                    ->formatStateUsing(fn ($record) => \Carbon\Carbon::parse($record->date)->format('M d, Y') . ' (' . \Carbon\Carbon::parse($record->time)->format('g:i A') . ')')
                    ->sortable(),

                TextColumn::make('vehicle')
                    ->label('Vehicle & Driver')
                    ->formatStateUsing(function ($record) {
                        $driver = $record->tripTicket?->driver?->name ?? 'No Driver Assigned';
                        $vehicle = $record->vehicle ?? 'N/A';
                        return "{$vehicle} • {$driver}";
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('passenger_names')
                    ->label('Passengers')
                    ->formatStateUsing(function ($record) {
                        $passengers = $record->passenger_names ?? [];
                        if (is_string($passengers)) {
                            $passengers = json_decode($passengers, true) ?? [];
                        }
                        $names = collect($passengers)->pluck('name')->filter()->join(', ');
                        return $names ?: ($record->number_of_passengers . ' passenger(s)');
                    })
                    ->limit(30)
                    ->tooltip(function ($record) {
                        $passengers = $record->passenger_names ?? [];
                        if (is_string($passengers)) {
                            $passengers = json_decode($passengers, true) ?? [];
                        }
                        return collect($passengers)->pluck('name')->filter()->join(', ');
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'on_trip' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'rejected' => 'Disapproved',
                        'completed' => 'Completed',
                        default => ucfirst($state),
                    }),
            ]);
    }
}
