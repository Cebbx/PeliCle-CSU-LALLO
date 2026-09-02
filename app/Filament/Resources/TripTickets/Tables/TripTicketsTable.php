<?php

namespace App\Filament\Resources\TripTickets\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TripTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->searchable(),
                TextColumn::make('vehicleRequest.request_number')
                    ->label('Request Number')
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->searchable(),
                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->formatStateUsing(function ($state) {
                        $vehicle = \App\Models\Vehicle::where('plate_number', $state)->first();
                        return $vehicle ? "{$vehicle->brand} - {$vehicle->plate_number}" : $state;
                    })
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
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
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Issued Timestamp')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('start_trip')
                        ->label('Start Trip')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->form(function ($record) {
                            if ($record->document || $record->vehicleRequest?->document) {
                                return [];
                            }
                            return [
                                \Filament\Forms\Components\FileUpload::make('document')
                                    ->label('Upload CEO Signed Document (Required)')
                                    ->disk('public')
                                    ->directory('request-documents')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->required(),
                            ];
                        })
                        ->action(function ($record, array $data) {
                            if (isset($data['document'])) {
                                $record->update([
                                    'document' => $data['document'],
                                ]);
                                if ($record->vehicleRequest) {
                                    $record->vehicleRequest->update([
                                        'document' => $data['document'],
                                        'status' => 'approved',
                                    ]);
                                }
                            }
                            
                            if (!$record->document && !$record->vehicleRequest?->document) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Upload Required')
                                    ->body('CEO Signed Document is required to start the trip.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->update(['status' => 'active']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Trip Started')
                                ->body("Trip {$record->ticket_number} has started! Driver is now On Trip.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(fn ($record) => $record->document || $record->vehicleRequest?->document),
                    Action::make('complete')
                        ->label('Complete Trip')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'active')
                        ->requiresConfirmation()
                        ->modalHeading('Complete Trip')
                        ->modalDescription('Are you sure you want to mark this trip as completed?')
                        ->modalSubmitActionLabel('Yes, Complete Trip')
                        ->action(function ($record) {
                            $record->update(['status' => 'completed']);
                            if ($record->driver) {
                                $record->driver->update(['status' => 'available']);
                            }
                            if ($record->vehicleRequest) {
                                $record->vehicleRequest->update(['status' => 'completed']);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Trip Completed')
                                ->body("Trip {$record->ticket_number} marked as completed.")
                                ->success()
                                ->send();
                        }),
                    Action::make('cancel_trip')
                        ->label('Cancel Trip')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'active']))
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Trip Cancelled')
                                ->body("Trip {$record->ticket_number} has been cancelled.")
                                ->danger()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Action::make('resendSms')
                        ->label('Resend SMS')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'active']))
                        ->action(function ($record) {
                            $record->sendSmsNotification();
                            \Filament\Notifications\Notification::make()
                                ->title('SMS Sent')
                                ->body("SMS notification resent to driver {$record->driver?->name}.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Action::make('create_slip')
                        ->label('Create Withdrawal Slip')
                        ->icon('heroicon-o-document-plus')
                        ->color('warning')
                        ->visible(fn ($record) => !$record->withdrawalSlips()->exists() && in_array($record->status, ['pending', 'active']))
                        ->url(fn ($record) => \App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource::getUrl('create', [
                            'trip_ticket_id' => $record->id,
                        ])),
                    Action::make('print')
                        ->label('Print Trip Ticket')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn ($record) => route('trip-tickets.print', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('print_travel_order_employee')
                        ->label('Print Passenger TO')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->url(fn ($record) => route('trip-tickets.print-travel-order', [$record->id, 'type' => 'employee']))
                        ->openUrlInNewTab(),
                    Action::make('print_travel_order_driver')
                        ->label('Print Driver TO')
                        ->icon('heroicon-o-user')
                        ->color('success')
                        ->url(fn ($record) => route('trip-tickets.print-travel-order', [$record->id, 'type' => 'driver']))
                        ->openUrlInNewTab(),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
            ])
            ->toolbarActions([
                // No delete action allowed
            ]);
    }
}
