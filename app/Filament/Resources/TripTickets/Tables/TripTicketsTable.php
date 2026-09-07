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
                    ->description(function ($record) {
                        if ($record->status === 'cancelled' && $record->cancellation_reason) {
                            return 'Reason: ' . \Illuminate\Support\Str::limit($record->cancellation_reason, 35);
                        }
                        return null;
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
                \Filament\Tables\Filters\TrashedFilter::make()
                    ->label('Archive Status'),
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
                        ->form(function ($record) {
                            $hasSlip = $record->withdrawalSlips()->exists();
                            $slip = $record->withdrawalSlips()->first();

                            return [
                                \Filament\Forms\Components\Placeholder::make('summary')
                                    ->label('Trip Completion')
                                    ->content("Marking trip {$record->ticket_number} as completed. Driver will be set to Available."),
                                ...(
                                    $hasSlip ? [
                                        \Filament\Forms\Components\TextInput::make('actual_amount')
                                            ->label('Actual Gas Expense (₱)')
                                            ->numeric()
                                            ->prefix('₱')
                                            ->placeholder('0.00')
                                            ->default($slip && $slip->amount > 0 ? $slip->amount : null)
                                            ->helperText('Official receipt amount from the gas station. This will automatically approve the withdrawal slip.')
                                    ] : []
                                )
                            ];
                        })
                        ->modalHeading('Complete Trip')
                        ->modalSubmitActionLabel('Yes, Complete Trip')
                        ->action(function ($record, array $data) {
                            $record->update(['status' => 'completed']);
                            if ($record->driver) {
                                $record->driver->update(['status' => 'available']);
                            }
                            if ($record->vehicleRequest) {
                                $record->vehicleRequest->update(['status' => 'completed']);
                            }

                            if ($record->withdrawalSlips()->exists()) {
                                $amount = !empty($data['actual_amount']) ? (float)$data['actual_amount'] : 0;
                                foreach ($record->withdrawalSlips as $slip) {
                                    $slip->update([
                                        'amount' => $amount > 0 ? $amount : $slip->amount,
                                        'status' => 'approved',
                                    ]);
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Trip Completed')
                                ->body("Trip {$record->ticket_number} marked as completed.")
                                ->success()
                                ->send();
                        }),
                    Action::make('view_cancellation_reason')
                        ->label('View Cancellation Reason')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->color('info')
                        ->visible(fn ($record) => $record->status === 'cancelled' && $record->cancellation_reason)
                        ->modalHeading('Trip Cancellation Reason')
                        ->modalDescription(fn ($record) => $record->cancellation_reason)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                    Action::make('cancel_trip')
                        ->label('Cancel Trip')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->modalHeading('⚠️ Are you sure you want to cancel this trip?')
                        ->modalDescription('This action cannot be undone. Cancelling will notify the assigned driver via SMS and release the vehicle back to available status.')
                        ->modalSubmitActionLabel('Yes, Cancel Trip')
                        ->modalCancelActionLabel('No, Keep Trip')
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'active']))
                        ->form([
                            \Filament\Forms\Components\Select::make('reason_select')
                                ->label('Reason for Cancellation')
                                ->options([
                                    'Severe weather conditions' => 'Severe weather conditions',
                                    'Vehicle mechanical issue' => 'Vehicle mechanical issue',
                                    'Official event cancelled' => 'Official event cancelled',
                                    'Driver emergency' => 'Driver emergency',
                                    'Cancelled by requester' => 'Cancelled by requester',
                                    'Others' => 'Others (Specify below)',
                                ])
                                ->default('Official event cancelled')
                                ->live()
                                ->required(),
                            \Filament\Forms\Components\Textarea::make('other_reason')
                                ->label('Specify Reason')
                                ->placeholder('Type custom cancellation reason here...')
                                ->visible(fn ($get) => $get('reason_select') === 'Others')
                                ->required(fn ($get) => $get('reason_select') === 'Others')
                                ->rows(3),
                            \Filament\Forms\Components\Checkbox::make('confirm_cancellation')
                                ->label('Yes, I am sure and I confirm this cancellation.')
                                ->helperText('Please check this box to confirm that you want to proceed with cancellation.')
                                ->required()
                                ->accepted(),
                        ])
                        ->action(function ($record, array $data) {
                            $reason = $data['reason_select'] === 'Others' ? ($data['other_reason'] ?? 'Others') : $data['reason_select'];
                            $record->update([
                                'status' => 'cancelled',
                                'cancellation_reason' => $reason,
                            ]);

                            if ($record->vehicleRequest) {
                                $record->vehicleRequest->update([
                                    'status' => 'cancelled',
                                    'cancellation_reason' => $reason,
                                ]);
                            }

                            if (method_exists($record, 'sendCancellationSms')) {
                                $record->sendCancellationSms("Reason: {$reason}");
                            }

                            if ($record->driver) {
                                $record->driver->update(['status' => 'available']);
                            }
                            
                            // Cancel any pending withdrawal slips attached
                            if ($record->withdrawalSlips()->exists()) {
                                $record->withdrawalSlips()->where('status', 'pending')->update(['status' => 'rejected']);
                            }

                            \App\Models\ActivityLog::log('Cancelled Trip', $record, "Admin cancelled trip ticket {$record->ticket_number}. Reason: {$reason}");
                            
                            $requesterUser = $record->vehicleRequest?->user;
                            if ($requesterUser) {
                                try {
                                    \Filament\Notifications\Notification::make()
                                        ->title('⚠️ Trip Ticket Cancelled: ' . $record->ticket_number)
                                        ->body("Your approved trip ticket has been cancelled by Admin. Reason: {$reason}")
                                        ->icon('heroicon-o-x-mark')
                                        ->iconColor('danger')
                                        ->sendToDatabase($requesterUser);
                                } catch (\Throwable $e) {}
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Trip Cancelled')
                                ->body("Trip {$record->ticket_number} has been cancelled.")
                                ->danger()
                                ->send();
                        }),
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
                    Action::make('view_signed_document')
                        ->label('View Signed Document')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->document))
                        ->url(fn ($record) => route('trip-tickets.view-signed-document', $record->id))
                        ->openUrlInNewTab(),
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
                    \Filament\Actions\DeleteAction::make()
                        ->label('Archive Trip Ticket')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->modalHeading('Archive Trip Ticket')
                        ->modalDescription('Are you sure you want to archive this trip ticket? It can be restored at any time.')
                        ->modalSubmitActionLabel('Yes, Archive'),
                    \Filament\Actions\RestoreAction::make()
                        ->label('Restore Trip Ticket')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning'),
                    \Filament\Actions\RestoreBulkAction::make()
                        ->label('Restore Selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                ]),
            ]);
    }
}
