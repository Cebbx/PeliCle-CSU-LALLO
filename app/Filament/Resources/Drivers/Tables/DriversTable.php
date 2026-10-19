<?php

namespace App\Filament\Resources\Drivers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->modifyQueryUsing(fn ($query) => $query->with([
                'tripTickets' => fn ($tq) => $tq->where('status', 'active')->with(['vehicleRequest', 'vehicleRequests']),
            ]))
            ->columns([
                TextColumn::make('name')
                    ->label('Driver Name')
                    ->icon('heroicon-m-user')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('current_location')
                    ->label('Current Location / Destination')
                    ->icon(fn ($record): string => match ($record->status) {
                        'on_trip' => 'heroicon-m-map-pin',
                        'available' => 'heroicon-m-building-office-2',
                        default => 'heroicon-m-pause-circle',
                    })
                    ->badge()
                    ->color(fn ($record): string => match ($record->status) {
                        'on_trip' => 'warning',
                        'available' => 'success',
                        default => 'gray',
                    })
                    ->searchable(query: function ($query, string $search) {
                        $query->where('campus', 'like', "%{$search}%")
                            ->orWhereHas('tripTickets', function ($tq) use ($search) {
                                $tq->where('status', 'active')
                                    ->where(function ($sub) use ($search) {
                                        $sub->whereHas('vehicleRequest', fn ($vr) => $vr->where('destination', 'like', "%{$search}%"))
                                            ->orWhereHas('vehicleRequests', fn ($vr) => $vr->where('destination', 'like', "%{$search}%"));
                                    });
                            });
                    }),

                TextColumn::make('contact_number')
                    ->label('Contact Number')
                    ->icon('heroicon-m-phone')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Duty Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'on_trip' => 'info',
                        'off_duty', 'unavailable' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'available' => 'heroicon-m-check-circle',
                        'on_trip' => 'heroicon-m-truck',
                        'off_duty', 'unavailable' => 'heroicon-m-pause-circle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Available',
                        'on_trip' => 'On Trip',
                        'off_duty', 'unavailable' => 'Off Duty',
                        default => ucwords(str_replace('_', ' ', $state)),
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
            ->filters([
                SelectFilter::make('status')
                    ->label('Duty Status')
                    ->options([
                        'available' => 'Available',
                        'on_trip' => 'On Trip',
                        'off_duty' => 'Off Duty',
                    ]),

                SelectFilter::make('campus')
                    ->label('Campus Station')
                    ->options([
                        'CSU Lal-lo Campus' => 'CSU Lal-lo Campus',
                        'CSU Andrews Campus' => 'CSU Andrews Campus',
                        'CSU Carig Campus' => 'CSU Carig Campus',
                        'CSU Piat Campus' => 'CSU Piat Campus',
                        'CSU Aparri Campus' => 'CSU Aparri Campus',
                        'CSU Gonzaga Campus' => 'CSU Gonzaga Campus',
                        'CSU Sanchez Mira Campus' => 'CSU Sanchez Mira Campus',
                        'CSU Lasam Campus' => 'CSU Lasam Campus',
                    ]),

                TrashedFilter::make()
                    ->label('Archive Status'),
            ])
            ->recordActions([
                Action::make('toggle_duty')
                    ->label(fn ($record) => in_array($record->status, ['off_duty', 'unavailable']) ? 'Set Available' : 'Set Off Duty')
                    ->icon(fn ($record) => in_array($record->status, ['off_duty', 'unavailable']) ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle')
                    ->color(fn ($record) => in_array($record->status, ['off_duty', 'unavailable']) ? 'success' : 'gray')
                    ->visible(fn ($record) => $record->status !== 'on_trip')
                    ->action(function ($record) {
                        $newStatus = in_array($record->status, ['off_duty', 'unavailable']) ? 'available' : 'off_duty';
                        $record->update(['status' => $newStatus]);
                        Notification::make()
                            ->title('Driver Status Updated')
                            ->body("{$record->name} is now " . ($newStatus === 'available' ? 'Available' : 'Off Duty') . ".")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->modalHeading('Archive Driver')
                    ->modalDescription('Are you sure you want to archive this driver? The record can be restored anytime.')
                    ->modalSubmitActionLabel('Yes, Archive'),

                RestoreAction::make()
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning'),
                    RestoreBulkAction::make()
                        ->label('Restore Selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                ]),
            ]);
    }
}
