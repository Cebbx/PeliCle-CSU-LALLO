<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->is_urgent ? '🚨 URGENT' : null),
                TextColumn::make('employee_name')
                    ->label('Requester')
                    ->limit(13)
                    ->tooltip(fn ($record) => $record->employee_name)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department')
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->department)
                    ->searchable(),
                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->limit(12)
                    ->tooltip(fn ($record) => $record->vehicle)
                    ->searchable(),
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
                        'cancelled' => 'danger',
                        'completed' => 'success',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'on_trip' => 'On Trip',
                        'rejected' => 'Disapproved',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                        default => ucfirst($state),
                    })
                    ->tooltip(function ($record) {
                        if ($record->status === 'rejected' && $record->rejection_reason) {
                            return 'Disapproval Reason: ' . $record->rejection_reason;
                        }
                        if ($record->status === 'cancelled' && $record->cancellation_reason) {
                            return 'Reason: ' . $record->cancellation_reason;
                        }
                        if ($record->status === 'expired' && $record->cancellation_reason) {
                            return 'Reason: ' . $record->cancellation_reason;
                        }
                        return null;
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('request_number', 'desc')
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn ($record) => !$record->trashed() && $record->status === 'pending' && !$record->document),
                    Action::make('view_signed_document')
                        ->label('View Signed Document')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->document))
                        ->url(fn ($record) => route('vehicle-requests.view-signed-document', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('print')
                        ->label('View Requisition Form')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn ($record) => route('vehicle-requests.print', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('print_trip_ticket')
                        ->label('Print Trip Ticket (QR Code)')
                        ->icon('heroicon-o-ticket')
                        ->color('success')
                        ->visible(fn ($record) => $record->tripTicket()->exists())
                        ->url(fn ($record) => route('trip-tickets.print', $record->tripTicket->id))
                        ->openUrlInNewTab(),
                    Action::make('upload_document')
                        ->label('Upload Document')
                        ->icon('heroicon-o-document-arrow-up')
                        ->color('success')
                        ->visible(fn ($record) => !$record->trashed() && $record->status === 'approved' && !$record->document)
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
                    Action::make('reupload_document')
                        ->label('Replace Signed Document')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn ($record) => !$record->trashed() && !empty($record->document) && in_array($record->status, ['approved', 'on_trip']))
                        ->form([
                            \Filament\Forms\Components\FileUpload::make('document')
                                ->label('Upload Replacement CEO Signed Document')
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
                            if ($record->tripTicket) {
                                $record->tripTicket->updateQuietly([
                                    'document' => $data['document'],
                                ]);
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Document Replaced')
                                ->body('Signed document replaced successfully.')
                                ->success()
                                ->send();
                        }),
                    Action::make('view_reason')
                        ->label('View Reason')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->color('info')
                        ->visible(fn ($record) => in_array($record->status, ['rejected', 'cancelled', 'expired']) && ($record->rejection_reason || $record->cancellation_reason))
                        ->modalHeading(fn ($record) => match ($record->status) {
                            'rejected' => 'Disapproval Reason',
                            'cancelled' => 'Cancellation Reason',
                            'expired' => 'Expiration Reason',
                            default => 'Reason Details',
                        })
                        ->modalDescription(fn ($record) => $record->status === 'rejected' ? ($record->rejection_reason ?? 'No reason provided.') : ($record->cancellation_reason ?? 'No reason provided.'))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                    Action::make('cancel')
                        ->label('Cancel Request')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->modalHeading('⚠️ Are you sure you want to cancel this vehicle request?')
                        ->modalDescription('This action cannot be undone. Cancelling will withdraw this vehicle request.')
                        ->modalSubmitActionLabel('Yes, Cancel Request')
                        ->modalCancelActionLabel('No, Keep Request')
                        ->visible(fn ($record) => !$record->trashed() && $record->status === 'pending' && !$record->document)
                        ->form([
                            \Filament\Forms\Components\Select::make('reason_select')
                                ->label('Reason for Cancellation')
                                ->options([
                                    'Official event cancelled' => 'Official event cancelled',
                                    'Change of travel schedule' => 'Change of travel schedule',
                                    'Attendees no longer available' => 'Attendees no longer available',
                                    'Duplicate request' => 'Duplicate request',
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

                            \App\Models\ActivityLog::log('Cancelled Request', $record, "Employee cancelled request {$record->request_number}. Reason: {$reason}");

                            try {
                                $admins = \App\Models\User::where('role', 'admin')->get();
                                foreach ($admins as $admin) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('⚠️ Request Cancelled by Employee: ' . $record->request_number)
                                        ->body("{$record->employee_name} ({$record->department}) cancelled request to {$record->destination}. Reason: {$reason}")
                                        ->icon('heroicon-o-x-circle')
                                        ->iconColor('gray')
                                        ->sendToDatabase($admin);
                                }
                            } catch (\Throwable $e) {}

                            \Filament\Notifications\Notification::make()
                                ->title('Request Cancelled')
                                ->body("Vehicle request {$record->request_number} has been cancelled.")
                                ->warning()
                                ->send();
                        }),
                    Action::make('archive')
                        ->label('Archive Request')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->visible(fn ($record) => !$record->trashed() && in_array($record->status, ['completed', 'cancelled', 'rejected', 'expired']))
                        ->requiresConfirmation()
                        ->modalHeading('Archive Vehicle Request')
                        ->modalDescription('Are you sure you want to archive this request? It will be moved to your Archived tab.')
                        ->modalSubmitActionLabel('Archive')
                        ->action(function ($record) {
                            $record->delete();
                            \Filament\Notifications\Notification::make()
                                ->title('Request Archived')
                                ->body("Vehicle request {$record->request_number} has been archived.")
                                ->success()
                                ->send();
                        }),
                    Action::make('restore')
                        ->label('Restore Request')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->visible(fn ($record) => $record->trashed())
                        ->requiresConfirmation()
                        ->modalHeading('Restore Vehicle Request')
                        ->modalDescription('Do you want to restore this request back to your active list?')
                        ->modalSubmitActionLabel('Restore')
                        ->action(function ($record) {
                            $record->restore();
                            \Filament\Notifications\Notification::make()
                                ->title('Request Restored')
                                ->body("Vehicle request {$record->request_number} has been restored.")
                                ->success()
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
                        'rejected' => 'Disapproved',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                    ]),
                TernaryFilter::make('is_urgent')
                    ->label('Urgent Requests Only'),
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
