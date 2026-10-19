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
            ->poll('10s')
            ->modifyQueryUsing(fn ($query) => $query->with(['vehicleRequest', 'vehicleRequests', 'driver', 'withdrawalSlips']))
            ->columns([
                TextColumn::make('ticket_number')
                    ->searchable(),
                TextColumn::make('vehicleRequest.request_number')
                    ->label('Vehicle Request')
                    ->formatStateUsing(function ($state, $record) {
                        $allReqs = $record->all_vehicle_requests;
                        if ($allReqs->count() > 1) {
                            return $allReqs
                                ->map(fn ($r) => preg_replace('/^VR-(?=\d{4}-)/', '', $r->request_number ?? ''))
                                ->filter()
                                ->join(', ');
                        }
                        return preg_replace('/^VR-(?=\d{4}-)/', '', $state ?? '');
                    })
                    ->description(function ($record) {
                        $allReqs = $record->all_vehicle_requests;
                        if ($allReqs->count() > 1) {
                            $depts = $allReqs->pluck('department')->filter()->unique()->join(' + ');
                            return '🚐 Consolidated (' . $allReqs->count() . ' Requests' . ($depts ? ": {$depts}" : '') . ')';
                        }
                        if ($record->canConsolidateOrEdit()) {
                            return '💡 Same Date & Route Match (Edit to Consolidate)';
                        }
                        return null;
                    })
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->default('Unassigned')
                    ->placeholder('Unassigned')
                    ->searchable(),
                TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->formatStateUsing(fn ($state) => \App\Models\Vehicle::getVehicleName($state))
                    ->tooltip(function ($state) {
                        $name = \App\Models\Vehicle::getVehicleName($state);
                        $plate = \App\Models\Vehicle::getPlateNumber($state);
                        return ($plate && $plate !== $name) ? "{$name} (Plate: {$plate})" : $name;
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
                    ->tooltip(function ($record) {
                        if ($record->status === 'cancelled' && $record->cancellation_reason) {
                            return 'Reason: ' . $record->cancellation_reason;
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
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make()
                    ->label('Archive Status'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->label('Edit Trip Ticket (Consolidate / Carpool)')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->visible(fn ($record) => $record->canConsolidateOrEdit()),
                    Action::make('upload_document')
                        ->label('Upload or Scan Document')
                        ->icon('heroicon-o-camera')
                        ->color('success')
                        ->modalHeading('📄 Upload or Scan CEO Signed Document')
                        ->modalDescription('Choose whether to scan using the camera or upload a document from your device.')
                        ->modalWidth('2xl')
                        ->modalSubmitActionLabel('Save Document')
                        ->visible(fn ($record) => !$record->trashed() && in_array($record->status, ['pending', 'active']) && !$record->hasSignedDocument())
                        ->form([
                            \Filament\Schemas\Components\Tabs::make('document_source')
                                ->tabs([
                                    \Filament\Schemas\Components\Tabs\Tab::make('camera_scan')
                                        ->label('Camera Scanner')
                                        ->icon('heroicon-o-camera')
                                        ->schema([
                                            \Filament\Forms\Components\ViewField::make('captured_image')
                                                ->view('filament.components.camera-scanner')
                                                ->columnSpanFull(),
                                        ]),
                                    \Filament\Schemas\Components\Tabs\Tab::make('file_upload')
                                        ->label('Upload Document')
                                        ->icon('heroicon-o-arrow-up-tray')
                                        ->schema([
                                            \Filament\Forms\Components\FileUpload::make('document')
                                                ->label('Attach Signed Document (Photo or PDF)')
                                                ->disk('public')
                                                ->directory('request-documents')
                                                ->visibility('public')
                                                ->imagePreviewHeight('250')
                                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                                ->helperText('Select the scanned document or photo from your computer or phone.')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ])
                        ->action(function ($record, array $data, $action) {
                            $finalPath = null;

                            if (!empty($data['captured_image']) && str_starts_with($data['captured_image'], 'data:image/')) {
                                $imageData = $data['captured_image'];
                                $type = 'jpg';
                                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                                    $type = strtolower($matches[1]);
                                    if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                                        $type = 'jpg';
                                    }
                                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                                }
                                $decoded = base64_decode($imageData);
                                if ($decoded !== false) {
                                    $fileName = 'scan_' . ($record->ticket_number ?? $record->id) . '_' . time() . '.' . $type;
                                    $path = 'request-documents/' . $fileName;
                                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decoded);
                                    $finalPath = $path;
                                }
                            }

                            if (!$finalPath && !empty($data['document'])) {
                                $finalPath = $data['document'];
                            }

                            if (!$finalPath) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Document Required')
                                    ->body('Please capture a scan using the camera or upload a document before saving.')
                                    ->danger()
                                    ->send();
                                $action->halt();
                                return;
                            }

                            $record->update([
                                'document' => $finalPath,
                            ]);

                            foreach ($record->all_vehicle_requests as $req) {
                                $req->updateQuietly([
                                    'document' => $finalPath,
                                    'status' => $record->status === 'active' ? 'on_trip' : 'approved',
                                ]);
                            }

                            $record->sendSmsNotification();

                            $driverUser = $record->driver?->getUserAccount();
                            if ($driverUser) {
                                try {
                                    $dest = $record->vehicleRequest?->destination ?? 'Assigned Destination';
                                    \Filament\Notifications\Notification::make()
                                        ->title('📄 CEO Document Ready (' . $record->ticket_number . ')')
                                        ->body("CEO signed document for trip to {$dest} has been uploaded. Your trip is cleared for scheduled departure.")
                                        ->icon('heroicon-o-document-check')
                                        ->iconColor('success')
                                        ->actions([
                                            \Filament\Actions\Action::make('view')
                                                ->label('View Trip')
                                                ->url('/driver/trip-tickets'),
                                        ])
                                        ->sendToDatabase($driverUser);
                                } catch (\Throwable $e) {}
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Document Uploaded & Driver Notified')
                                ->body("CEO Signed Document uploaded for {$record->ticket_number}. SMS notification has been sent to {$record->driver?->name}.")
                                ->success()
                                ->send();
                        }),
                    Action::make('start_trip')
                        ->label('Start Trip')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->modalWidth('2xl')
                        ->form(function ($record) {
                            if ($record->hasSignedDocument()) {
                                return [];
                            }
                            return [
                                \Filament\Schemas\Components\Tabs::make('document_source')
                                    ->tabs([
                                        \Filament\Schemas\Components\Tabs\Tab::make('camera_scan')
                                            ->label('Camera Scanner')
                                            ->icon('heroicon-o-camera')
                                            ->schema([
                                                \Filament\Forms\Components\ViewField::make('captured_image')
                                                    ->view('filament.components.camera-scanner')
                                                    ->columnSpanFull(),
                                            ]),
                                        \Filament\Schemas\Components\Tabs\Tab::make('file_upload')
                                            ->label('Upload Document')
                                            ->icon('heroicon-o-arrow-up-tray')
                                            ->schema([
                                                \Filament\Forms\Components\FileUpload::make('document')
                                                    ->label('Upload CEO Signed Document (Required)')
                                                    ->disk('public')
                                                    ->directory('request-documents')
                                                    ->visibility('public')
                                                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ];
                        })
                        ->action(function ($record, array $data, $action) {
                            $finalPath = null;

                            // 1. Process camera scanner capture if provided
                            if (!empty($data['captured_image']) && str_starts_with($data['captured_image'], 'data:image/')) {
                                $imageData = $data['captured_image'];
                                $type = 'jpg';
                                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                                    $type = strtolower($matches[1]);
                                    if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                                        $type = 'jpg';
                                    }
                                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                                }
                                $decoded = base64_decode($imageData);
                                if ($decoded !== false) {
                                    $fileName = 'scan_' . ($record->ticket_number ?? $record->id) . '_' . time() . '.' . $type;
                                    $path = 'request-documents/' . $fileName;
                                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decoded);
                                    $finalPath = $path;
                                }
                            }

                            // 2. Process file upload if provided
                            if (!$finalPath && !empty($data['document'])) {
                                $finalPath = $data['document'];
                            }

                            if ($finalPath) {
                                $record->update([
                                    'document' => $finalPath,
                                ]);
                                foreach ($record->all_vehicle_requests as $req) {
                                    $req->update([
                                        'document' => $finalPath,
                                        'status' => 'approved',
                                    ]);
                                }
                            }
                            
                            if (!$record->hasSignedDocument()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Upload Required')
                                    ->body('CEO Signed Document is required to start the trip.')
                                    ->danger()
                                    ->send();
                                $action->halt();
                                return;
                            }

                            if (!$record->driver_id) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Driver Required')
                                    ->body('Please assign a driver to this trip ticket before starting the trip.')
                                    ->danger()
                                    ->send();
                                $action->halt();
                                return;
                            }

                            $record->update(['status' => 'active']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Trip Started')
                                ->body("Trip {$record->ticket_number} has started! Driver is now On Trip.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(fn ($record) => $record->hasSignedDocument()),
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
                            foreach ($record->all_vehicle_requests as $req) {
                                $req->update(['status' => 'completed']);
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

                            foreach ($record->all_vehicle_requests as $req) {
                                $req->update([
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
                            
                            foreach ($record->all_vehicle_requests as $req) {
                                $requesterUser = $req->user;
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
                            }

                            $driverUser = $record->driver?->getUserAccount();
                            if ($driverUser) {
                                try {
                                    \Filament\Notifications\Notification::make()
                                        ->title('⚠️ Trip Ticket Cancelled: ' . $record->ticket_number)
                                        ->body("Trip ticket {$record->ticket_number} has been cancelled by Admin. Reason: {$reason}")
                                        ->icon('heroicon-o-x-mark')
                                        ->iconColor('danger')
                                        ->actions([
                                            \Filament\Actions\Action::make('view')
                                                ->label('View Trips')
                                                ->url('/driver/trip-tickets'),
                                        ])
                                        ->sendToDatabase($driverUser);
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
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'active']) && $record->hasSignedDocument())
                        ->action(function ($record) {
                            if (!$record->driver) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Walang Driver na Naka-assign')
                                    ->body('Hindi maipapadala ang SMS: Wala pang driver na naka-assign sa trip ticket na ito.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $record->sendSmsNotification(true);
                            \Filament\Notifications\Notification::make()
                                ->title('SMS Sent')
                                ->body("SMS notification resent to driver {$record->driver?->name}.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(fn ($record) => (bool) $record->driver),
                    Action::make('view_signed_document')
                        ->label('View Signed Document')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->visible(fn ($record) => $record->hasSignedDocument())
                        ->url(fn ($record) => route('trip-tickets.view-signed-document', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('download_signed_document')
                        ->label('Download Signed Document')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->visible(fn ($record) => $record->hasSignedDocument())
                        ->url(fn ($record) => route('trip-tickets.download-signed-document', $record->id)),
                    Action::make('print')
                        ->label('Print Trip Ticket')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn ($record) => route('trip-tickets.print', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('create_slip')
                        ->label('Create Fuel Slip')
                        ->icon('heroicon-o-document-plus')
                        ->color('warning')
                        ->visible(fn ($record) => !$record->withdrawalSlips()->exists() && in_array($record->status, ['pending', 'active']))
                        ->url(fn ($record) => \App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource::getUrl('create', [
                            'trip_ticket_id' => $record->id,
                        ])),
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
                ->iconButton(),
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
