<?php

namespace App\Filament\Driver\Resources\TripTickets\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TripTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Trip ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('vehicleRequest.destination')
                    ->label('Destination')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('vehicleRequest.date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('vehicleRequest.time')
                    ->label('Time')
                    ->time('h:i A')
                    ->sortable(),
                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->formatStateUsing(fn ($state) => \App\Models\Vehicle::getVehicleName($state))
                    ->tooltip(function ($state) {
                        $name = \App\Models\Vehicle::getVehicleName($state);
                        $plate = \App\Models\Vehicle::getPlateNumber($state);
                        return ($plate && $plate !== $name) ? "{$name} (Plate: {$plate})" : $name;
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'On Trip',
                        'on_trip' => 'On Trip',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn ($record) => \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('view', ['record' => $record]))
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('View Details'),
                    Action::make('acknowledge')
                        ->label('Acknowledge Trip')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->action(function ($record) {
                            $record->update(['status' => 'active']);
                            $record->driver?->update(['status' => 'on_trip']);
                            $record->vehicleRequest?->update(['status' => 'approved']);

                            \Filament\Notifications\Notification::make()
                                ->title('Trip Acknowledged')
                                ->body("Trip {$record->ticket_number} has been acknowledged. You are now On Trip.")
                                ->success()
                                ->send();
                        }),
                    Action::make('print')
                        ->label('View Ticket & QR Code')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->url(fn ($record) => route('trip-tickets.print', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('view_signed_document')
                        ->label('View Signed Document')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->document))
                        ->url(fn ($record) => route('trip-tickets.view-signed-document', $record->id))
                        ->openUrlInNewTab(),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->iconButton(),
            ])
            ->toolbarActions([
                // Drivers don't need bulk delete tools
            ]);
    }
}

