<?php

namespace App\Filament\Widgets;

use App\Models\VehicleRequest;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsTripLogsWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Recent Trip Activity Logs';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.analytics-trip-logs-widget';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $filterStatus = $this->filters['status'] ?? null;
        $filterDept = $this->filters['department'] ?? null;

        $query = VehicleRequest::query()
            ->with(['tripTicket.driver']);

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
            ->defaultSort('date', 'desc')
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
            ->emptyStateHeading('No Trip Activity Logs Found')
            ->emptyStateDescription('No vehicle requests or trips match the selected filters. Try adjusting the date range, department, or status.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->recordAction('view_details')
            ->recordActionsAlignment('start')
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request #')
                    ->width('1%')
                    ->extraHeaderAttributes(['style' => 'white-space: nowrap;'])
                    ->extraCellAttributes(['style' => 'white-space: nowrap;'])
                    ->weight('bold')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->is_urgent ? '🚨 URGENT' : null),

                TextColumn::make('employee_name')
                    ->label('Requester')
                    ->extraHeaderAttributes(['style' => 'white-space: nowrap;'])
                    ->extraCellAttributes(['style' => 'white-space: nowrap;'])
                    ->weight('semibold')
                    ->tooltip(fn ($record) => $record->employee_name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department')
                    ->label('Office')
                    ->placeholder('—')
                    ->weight('medium')
                    ->extraHeaderAttributes(['style' => 'white-space: nowrap;'])
                    ->extraCellAttributes(['style' => 'white-space: nowrap;'])
                    ->tooltip(fn ($record) => $record->department)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('destination')
                    ->label('Destination')
                    ->tooltip(fn ($record) => $record->destination)
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Travel Date')
                    ->width('1%')
                    ->extraHeaderAttributes(['style' => 'white-space: nowrap;'])
                    ->extraCellAttributes(['style' => 'white-space: nowrap;'])
                    ->date('M d, Y')
                    ->description(fn ($record) => $record->time ? \Carbon\Carbon::parse($record->time)->format('g:i A') : null)
                    ->sortable(),

                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->width('1%')
                    ->extraHeaderAttributes(['style' => 'white-space: nowrap; text-align: center;'])
                    ->extraCellAttributes(['style' => 'white-space: nowrap; text-align: center;'])
                    ->alignCenter()
                    ->placeholder('To be assigned')
                    ->default('To be assigned')
                    ->formatStateUsing(function ($state, $record) {
                        $raw = !empty($state) ? $state : ($record->tripTicket?->vehicle ?? null);
                        if (empty($raw)) {
                            return 'To be assigned';
                        }
                        return \App\Models\Vehicle::getVehicleName($raw);
                    })
                    ->badge()
                    ->color(fn ($state, $record) => empty($record->vehicle) && (!$record->tripTicket || empty($record->tripTicket->vehicle)) ? 'gray' : 'info')
                    ->tooltip(function ($state, $record) {
                        $raw = !empty($state) ? $state : ($record->tripTicket?->vehicle ?? null);
                        $plate = \App\Models\Vehicle::getPlateNumber($raw);
                        return $plate ? "Plate: {$plate}" : null;
                    })
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->width('1%')
                    ->extraHeaderAttributes(['style' => 'white-space: nowrap; text-align: center;'])
                    ->extraCellAttributes(['style' => 'white-space: nowrap; text-align: center;'])
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'on_trip' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'rejected' => 'Disapproved',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    }),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip('Click to view full details')
                    ->modalHeading(fn (VehicleRequest $record) => "Trip Request Details — " . ($record->formatted_request_number ?? $record->request_number))
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (VehicleRequest $record) => view('filament.widgets.analytics-trip-details-modal', [
                        'record' => $record,
                        'tripTicket' => $record->tripTicket ?? \App\Models\TripTicket::where('vehicle_request_id', $record->id)->first(),
                    ])),
            ]);
    }
}
