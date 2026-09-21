<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_name')
                    ->label('User')
                    ->icon('heroicon-m-user')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('System'),

                TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved & Ticketed', 'Completed Trip', 'Arrival Logged' => 'success',
                        'Started Trip', 'Uploaded Document' => 'info',
                        'Departure Logged', 'Updated Request' => 'warning',
                        'Breakdown Reported', 'Cancelled Trip', 'Cancelled Request', 'Disapproved Request', 'Deleted Request', 'Deleted Ticket' => 'danger',
                        'Created Request' => 'primary',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Approved & Ticketed' => 'heroicon-m-check-badge',
                        'Completed Trip' => 'heroicon-m-check-circle',
                        'Arrival Logged' => 'heroicon-m-building-office-2',
                        'Started Trip' => 'heroicon-m-truck',
                        'Departure Logged' => 'heroicon-m-arrow-right-start-on-rectangle',
                        'Breakdown Reported' => 'heroicon-m-exclamation-triangle',
                        'Cancelled Trip', 'Cancelled Request' => 'heroicon-m-x-circle',
                        'Disapproved Request' => 'heroicon-m-no-symbol',
                        'Auto-Declined Request' => 'heroicon-m-clock',
                        'Uploaded Document' => 'heroicon-m-arrow-up-tray',
                        'Created Request' => 'heroicon-m-plus-circle',
                        'Updated Request' => 'heroicon-m-pencil-square',
                        'Deleted Request', 'Deleted Ticket' => 'heroicon-m-trash',
                        default => 'heroicon-m-information-circle',
                    }),

                TextColumn::make('details')
                    ->label('Details')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y • h:i A')
                    ->sortable(),

                // Hidden by default to keep table clean and decluttered:
                TextColumn::make('model_type')
                    ->label('Target Module')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'App\Models\TripTicket' => 'Trip Ticket',
                        'App\Models\VehicleRequest' => 'Vehicle Request',
                        'App\Models\Driver' => 'Driver',
                        'App\Models\Vehicle' => 'Vehicle',
                        'App\Models\WithdrawalSlip' => 'Withdrawal Slip',
                        default => $state ? class_basename($state) : 'System',
                    })
                    ->color('gray')
                    ->description(fn ($record) => $record->model_id ? "#{$record->model_id}" : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Audit Records Found')
            ->emptyStateDescription('System events and user activities will appear here.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->recordAction('view_details')
            ->filters([
                SelectFilter::make('action')
                    ->label('Action')
                    ->options(fn () => ActivityLog::select('action')->distinct()->pluck('action', 'action')->toArray())
                    ->searchable(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('logged_from')->label('Logged From')->placeholder('dd/mm/yyyy'),
                        DatePicker::make('logged_until')->label('Logged Until')->placeholder('dd/mm/yyyy'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['logged_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['logged_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Audit Event Details — #' . $record->id)
                    ->modalWidth('lg')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.resources.activity-logs.audit-detail-modal', [
                        'record' => $record,
                    ])),
            ]);
    }
}
