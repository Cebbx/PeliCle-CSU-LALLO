<?php

namespace App\Filament\Resources\VehicleRequests\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehicleRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request #')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('employee_name')
                    ->label('Requester')
                    ->limit(13)
                    ->tooltip(fn ($record) => $record->employee_name)
                    ->searchable(),
                TextColumn::make('department')
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->department)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->limit(12)
                    ->tooltip(fn ($record) => $record->vehicle)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination')
                    ->limit(15)
                    ->tooltip(fn ($record) => $record->destination)
                    ->searchable(),
                TextColumn::make('date')
                    ->label('Schedule')
                    ->date('M d, Y')
                    ->description(fn ($record) => $record->time ? \Carbon\Carbon::parse($record->time)->format('h:i A') : null)
                    ->sortable(),
                TextColumn::make('return_date')
                    ->label('Return')
                    ->date('M d, Y')
                    ->description(fn ($record) => $record->return_time ? \Carbon\Carbon::parse($record->return_time)->format('h:i A') : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'on_trip' => 'info',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        'completed' => 'success',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                        'expired' => 'Expired / Forfeited',
                        default => ucfirst($state),
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending (New)',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                        'expired' => 'Expired / Forfeited',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve_and_ticket')
                        ->label('Approve & Ticket')
                        ->icon('heroicon-o-ticket')
                        ->color('success')
                        ->visible(fn ($record) => !$record->tripTicket()->exists() && ($record->status === 'pending' || $record->status === 'approved'))
                        ->action(function ($record) {
                            $record->update(['status' => 'approved']);
                        })
                        ->url(fn ($record) => \App\Filament\Resources\TripTickets\TripTicketResource::getUrl('create', [
                            'vehicle_request_id' => $record->id,
                        ])),
                    Action::make('print')
                        ->label('View Form Request')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn ($record) => route('vehicle-requests.print', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('upload_document')
                        ->label('Upload Document')
                        ->icon('heroicon-o-document-arrow-up')
                        ->color('success')
                        ->visible(fn ($record) => ($record->status === 'pending' || $record->status === 'approved') && !$record->document)
                        ->form([
                            \Filament\Forms\Components\FileUpload::make('document')
                                ->label('Upload CEO Signed Document')
                                ->disk('public')
                                ->directory('request-documents')
                                ->visibility('public')
                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'document' => $data['document'],
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Document Uploaded')
                                ->body('CEO Signed Document uploaded successfully! Trip ticket is now active!')
                                ->success()
                                ->send();
                        }),
                    Action::make('reject')
                        ->label('Reject Request')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Vehicle Request')
                        ->modalDescription('Are you sure you want to reject this pending vehicle request?')
                        ->modalSubmitActionLabel('Yes, Reject Request')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->action(function ($record) {
                            $record->update(['status' => 'rejected']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Request Rejected')
                                ->body("Request {$record->request_number} has been rejected.")
                                ->danger()
                                ->send();
                        }),
                    Action::make('cancel_request')
                        ->label('Cancel Request')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Approved Request')
                        ->modalDescription('Are you sure you want to cancel this approved request? Any scheduled trip ticket will also be cancelled.')
                        ->modalSubmitActionLabel('Yes, Cancel Request')
                        ->visible(fn ($record) => $record->status === 'approved')
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled']);
                            if ($record->tripTicket) {
                                $record->tripTicket->update(['status' => 'cancelled']);
                                if ($record->tripTicket->driver) {
                                    $record->tripTicket->driver->update(['status' => 'available']);
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Request Cancelled')
                                ->body("Approved request {$record->request_number} has been cancelled.")
                                ->warning()
                                ->send();
                        }),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending (New)',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
                \Filament\Tables\Filters\TrashedFilter::make()
                    ->label('Archive Status'),
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
