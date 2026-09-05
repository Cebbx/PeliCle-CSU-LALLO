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
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                        default => ucfirst($state),
                    })
                    ->description(function ($record) {
                        if ($record->status === 'rejected' && $record->rejection_reason) {
                            return 'Reason: ' . \Illuminate\Support\Str::limit($record->rejection_reason, 35);
                        }
                        if ($record->status === 'cancelled' && $record->cancellation_reason) {
                            return 'Reason: ' . \Illuminate\Support\Str::limit($record->cancellation_reason, 35);
                        }
                        if ($record->status === 'expired' && $record->cancellation_reason) {
                            return 'Reason: ' . \Illuminate\Support\Str::limit($record->cancellation_reason, 35);
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
                        ->visible(fn ($record) => $record->status === 'pending' && !$record->document),
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
                        ->visible(fn ($record) => $record->status === 'approved' && !$record->document)
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
                    Action::make('view_reason')
                        ->label('View Reason')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->color('info')
                        ->visible(fn ($record) => in_array($record->status, ['rejected', 'cancelled', 'expired']) && ($record->rejection_reason || $record->cancellation_reason))
                        ->modalHeading(fn ($record) => match ($record->status) {
                            'rejected' => 'Rejection Reason',
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
                        ->modalHeading('Cancel Vehicle Request')
                        ->modalDescription('Please select the reason why you are cancelling this request.')
                        ->modalSubmitActionLabel('Cancel Request')
                        ->visible(fn ($record) => $record->status === 'pending' && !$record->document)
                        ->form([
                            \Filament\Forms\Components\Select::make('reason_select')
                                ->label('Reason for Cancellation')
                                ->options([
                                    'Event or meeting postponed / cancelled' => 'Event or meeting postponed / cancelled',
                                    'Change of travel schedule / date' => 'Change of travel schedule / date',
                                    'Attendees or faculty no longer available' => 'Attendees or faculty no longer available',
                                    'Request created by mistake / duplicate' => 'Request created by mistake / duplicate',
                                    'Others' => 'Others (Specify below)',
                                ])
                                ->default('Event or meeting postponed / cancelled')
                                ->live()
                                ->required(),
                            \Filament\Forms\Components\Textarea::make('other_reason')
                                ->label('Specify Reason')
                                ->placeholder('Type custom cancellation reason here...')
                                ->visible(fn ($get) => $get('reason_select') === 'Others')
                                ->required(fn ($get) => $get('reason_select') === 'Others')
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            $reason = $data['reason_select'] === 'Others' ? ($data['other_reason'] ?? 'Others') : $data['reason_select'];
                            $record->update([
                                'status' => 'cancelled',
                                'cancellation_reason' => $reason,
                            ]);

                            \App\Models\ActivityLog::log('Cancelled Request', $record, "Employee cancelled request {$record->request_number}. Reason: {$reason}");

                            \Filament\Notifications\Notification::make()
                                ->title('Request Cancelled')
                                ->body("Vehicle request {$record->request_number} has been cancelled.")
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
                        'expired' => 'Expired',
                    ]),
                TernaryFilter::make('is_urgent')
                    ->label('Urgent / Immediate Only'),
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
