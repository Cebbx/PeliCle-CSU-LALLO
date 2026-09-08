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
                    ->formatStateUsing(function ($state, $record) {
                        if (!empty($state)) {
                            return $state;
                        }
                        if ($record->tripTicket && !empty($record->tripTicket->vehicle)) {
                            return $record->tripTicket->vehicle;
                        }
                        return 'To be assigned';
                    })
                    ->badge(fn ($state, $record) => empty($state) && (!$record->tripTicket || empty($record->tripTicket->vehicle)))
                    ->color('gray')
                    ->description(function ($record) {
                        if ($record->tripTicket) {
                            $otherCount = $record->tripTicket->vehicleRequests()->where('id', '!=', $record->id)->count();
                            if ($otherCount > 0) {
                                return '🚐 Carpool (' . ($otherCount + 1) . ' Depts)';
                            }
                        }
                        return null;
                    })
                    ->tooltip(function ($record) {
                        if ($record->tripTicket) {
                            $otherDepts = $record->tripTicket->vehicleRequests()->pluck('department')->filter()->unique()->join(', ');
                            if ($otherDepts) {
                                return "Consolidated Trip with: {$otherDepts}";
                            }
                        }
                        return $record->vehicle ?? 'Assigned by GSO Motorpool upon approval';
                    })
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
                        ->label('Upload / Scan Document')
                        ->icon('heroicon-o-camera')
                        ->color('success')
                        ->modalHeading('📄 Upload or Scan CEO Signed Document')
                        ->modalDescription('Pumili kung gagamit ng Live Camera Scanner o mag-a-upload ng PDF/larawan mula sa device.')
                        ->modalWidth('2xl')
                        ->modalSubmitActionLabel('Save & Activate Trip')
                        ->visible(fn ($record) => !$record->trashed() && $record->status === 'approved' && !$record->document)
                        ->form([
                            \Filament\Schemas\Components\Tabs::make('document_source')
                                ->tabs([
                                    \Filament\Schemas\Components\Tabs\Tab::make('camera_scan')
                                        ->label('📸 Live Camera / Scanner')
                                        ->icon('heroicon-o-camera')
                                        ->schema([
                                            \Filament\Forms\Components\ViewField::make('captured_image')
                                                ->view('filament.components.camera-scanner')
                                                ->columnSpanFull(),
                                        ]),
                                    \Filament\Schemas\Components\Tabs\Tab::make('file_upload')
                                        ->label('📁 Upload File (PDF / Larawan)')
                                        ->icon('heroicon-o-arrow-up-tray')
                                        ->schema([
                                            \Filament\Forms\Components\FileUpload::make('document')
                                                ->label('Attach Signed Document (Photo or PDF)')
                                                ->disk('public')
                                                ->directory('request-documents')
                                                ->visibility('public')
                                                ->imagePreviewHeight('250')
                                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                                ->helperText('Piliin ang na-scan na PDF o larawan mula sa iyong computer o cellphone.')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ])
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
                                    $fileName = 'scan_' . ($record->request_number ?? $record->id) . '_' . time() . '.' . $type;
                                    $path = 'request-documents/' . $fileName;
                                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decoded);
                                    $finalPath = $path;
                                }
                            }

                            // 2. Process file upload if provided
                            if (!$finalPath && !empty($data['document'])) {
                                $finalPath = $data['document'];
                            }

                            if (!$finalPath) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Kailangan ang Dokumento')
                                    ->body('Mangyaring kumuha ng scan gamit ang camera o mag-upload ng PDF / larawan bago i-save.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                                return;
                            }

                            $record->update([
                                'document' => $finalPath,
                            ]);

                            if ($record->tripTicket) {
                                $record->tripTicket->updateQuietly([
                                    'document' => $finalPath,
                                ]);
                            }

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
                        ->modalHeading('📄 Replace CEO Signed Document')
                        ->modalDescription('Pumili kung kukuha ng bagong scan gamit ang Live Camera o mag-a-upload ng bagong PDF/larawan.')
                        ->modalWidth('2xl')
                        ->modalSubmitActionLabel('Update Document')
                        ->visible(fn ($record) => !$record->trashed() && !empty($record->document) && in_array($record->status, ['approved', 'on_trip']))
                        ->form([
                            \Filament\Schemas\Components\Tabs::make('document_source')
                                ->tabs([
                                    \Filament\Schemas\Components\Tabs\Tab::make('camera_scan')
                                        ->label('📸 Live Camera / Scanner')
                                        ->icon('heroicon-o-camera')
                                        ->schema([
                                            \Filament\Forms\Components\ViewField::make('captured_image')
                                                ->view('filament.components.camera-scanner')
                                                ->columnSpanFull(),
                                        ]),
                                    \Filament\Schemas\Components\Tabs\Tab::make('file_upload')
                                        ->label('📁 Upload File (PDF / Larawan)')
                                        ->icon('heroicon-o-arrow-up-tray')
                                        ->schema([
                                            \Filament\Forms\Components\FileUpload::make('document')
                                                ->label('Upload Replacement CEO Signed Document')
                                                ->disk('public')
                                                ->directory('request-documents')
                                                ->visibility('public')
                                                ->imagePreviewHeight('250')
                                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                                ->helperText('Piliin ang na-scan na PDF o larawan mula sa iyong computer o cellphone.')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ])
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
                                    $fileName = 'scan_' . ($record->request_number ?? $record->id) . '_' . time() . '.' . $type;
                                    $path = 'request-documents/' . $fileName;
                                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decoded);
                                    $finalPath = $path;
                                }
                            }

                            // 2. Process file upload if provided
                            if (!$finalPath && !empty($data['document'])) {
                                $finalPath = $data['document'];
                            }

                            if (!$finalPath) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Kailangan ang Dokumento')
                                    ->body('Mangyaring kumuha ng scan gamit ang camera o mag-upload ng PDF / larawan bago i-save.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                                return;
                            }

                            $record->update([
                                'document' => $finalPath,
                            ]);
                            if ($record->tripTicket) {
                                $record->tripTicket->updateQuietly([
                                    'document' => $finalPath,
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
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('status')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('status')
                            ->label('Filter by Status')
                            ->options([
                                'pending' => 'Pending (New)',
                                'approved' => 'Approved',
                                'on_trip' => 'On Trip',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'rejected' => 'Disapproved',
                                'expired' => 'Expired',
                            ])
                            ->bulkToggleable(),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query->when(
                            !empty($data['status']),
                            fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereIn('status', $data['status'])
                        );
                    }),
                \Filament\Tables\Filters\Filter::make('is_urgent')
                    ->label('Urgent Requests Only')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_urgent', true)),
            ]);
    }
}
