<?php

namespace App\Filament\Resources\SmsLogs\Tables;

use App\Models\Driver;
use App\Services\SmsService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SmsLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('driver.name')
                    ->label('Recipient Driver')
                    ->icon('heroicon-m-user')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('Unassigned Driver')
                    ->description(fn ($record) => $record->phone_number ? "📞 {$record->phone_number}" : null),

                TextColumn::make('type')
                    ->label('Alert Type')
                    ->badge()
                    ->state(function ($record) {
                        $msg = $record->message;
                        if (str_contains($msg, 'NEW TRIP ASSIGNED') || str_contains($msg, 'new trip dispatch')) {
                            return 'Trip Assigned';
                        }
                        if (str_contains($msg, 'CANCELLED') || str_contains($msg, 'EXPIRED')) {
                            return 'Trip Cancelled';
                        }
                        if (str_contains($msg, 'Inspection') || str_contains($msg, 'safety check')) {
                            return 'Safety Check';
                        }
                        if (str_contains($msg, 'Advisory') || str_contains($msg, 'weather')) {
                            return 'Weather Advisory';
                        }
                        return 'Direct Alert';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Trip Assigned' => 'info',
                        'Trip Cancelled' => 'danger',
                        'Safety Check' => 'warning',
                        'Weather Advisory' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Trip Assigned' => 'heroicon-m-truck',
                        'Trip Cancelled' => 'heroicon-m-x-circle',
                        'Safety Check' => 'heroicon-m-clipboard-document-check',
                        'Weather Advisory' => 'heroicon-m-cloud',
                        default => 'heroicon-m-chat-bubble-bottom-center-text',
                    }),

                TextColumn::make('message')
                    ->label('Notification Summary')
                    ->searchable()
                    ->weight('semibold')
                    ->formatStateUsing(function (string $state) {
                        $tripId = null;
                        $destination = null;

                        if (preg_match('/Trip ID:\s*([^\r\n]+)/i', $state, $m)) {
                            $tripId = trim($m[1]);
                        }
                        if (preg_match('/Destination:\s*([^\r\n]+)/i', $state, $m)) {
                            $destination = trim($m[1]);
                            if (str_contains($destination, ',')) {
                                $parts = array_map('trim', explode(',', $destination));
                                $destination = implode(', ', array_slice($parts, 0, 2));
                            }
                        }

                        if ($tripId && $destination) {
                            return "{$tripId} ➔ {$destination}";
                        } elseif ($destination) {
                            return "Destination: {$destination}";
                        } elseif ($tripId) {
                            return "Trip ID: {$tripId}";
                        }

                        $lines = array_filter(array_map('trim', explode("\n", $state)));
                        $firstLine = reset($lines);
                        return Str::limit($firstLine ?: $state, 55);
                    })
                    ->description(function ($record) {
                        $state = $record->message;
                        $vehicle = null;
                        $dateTime = null;

                        if (preg_match('/Vehicle:\s*([^\r\n]+)/i', $state, $m)) {
                            $vehicle = trim($m[1]);
                        }
                        if (preg_match('/Date:\s*([^\r\n]+)/i', $state, $m)) {
                            $dateTime = trim($m[1]);
                            if (preg_match('/Time:\s*([^\r\n]+)/i', $state, $mt)) {
                                $dateTime .= ' • ' . trim($mt[1]);
                            }
                        } elseif (preg_match('/Reason:\s*([^\r\n]+)/i', $state, $m)) {
                            $dateTime = 'Reason: ' . Str::limit(trim($m[1]), 40);
                        }

                        if ($vehicle && $dateTime) {
                            return "{$vehicle} • {$dateTime}";
                        }
                        return $dateTime ?: ($vehicle ?: null);
                    }),

                TextColumn::make('status')
                    ->label('Delivery')
                    ->badge()
                    ->state(fn () => !empty(config('services.semaphore.key')) ? 'Delivered' : 'Simulated')
                    ->color(fn ($state) => $state === 'Delivered' ? 'success' : 'info')
                    ->icon(fn ($state) => $state === 'Delivered' ? 'heroicon-m-check-circle' : 'heroicon-m-device-phone-mobile'),

                TextColumn::make('created_at')
                    ->label('Sent At')
                    ->date('M d, Y')
                    ->description(fn ($record) => $record->created_at ? $record->created_at->format('g:i A') : null)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No SMS Logs Found')
            ->emptyStateDescription('Outbound driver notifications, dispatch alerts, and reminders will appear here.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->recordAction('view_details')
            ->filters([
                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->options(fn () => Driver::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('sent_from')->label('Sent From')->placeholder('dd/mm/yyyy'),
                        DatePicker::make('sent_until')->label('Sent Until')->placeholder('dd/mm/yyyy'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['sent_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['sent_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Outbound SMS — ' . ($record->driver?->name ?? $record->phone_number))
                    ->modalWidth('md')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.resources.sms-logs.sms-detail-modal', [
                        'record' => $record,
                    ])),

                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Resend SMS to Driver')
                    ->modalDescription('Are you sure you want to resend this SMS alert message to the driver?')
                    ->modalSubmitActionLabel('Yes, Resend SMS')
                    ->action(function ($record) {
                        $driver = $record->driver;
                        if (!$driver) {
                            $driver = (object) [
                                'id' => $record->driver_id,
                                'contact_number' => $record->phone_number,
                                'name' => 'Driver (' . $record->phone_number . ')',
                            ];
                        }

                        SmsService::send($driver, $record->message);

                        Notification::make()
                            ->title('SMS re-sent successfully')
                            ->body('Message sent to ' . ($driver->name ?? $record->phone_number))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
