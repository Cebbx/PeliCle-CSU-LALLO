<?php

namespace App\Filament\Resources\Drivers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('license_number')
                    ->searchable(),
                TextColumn::make('contact_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'on_trip' => 'info',
                        'off_duty' => 'gray',
                        'unavailable' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Available',
                        'on_trip' => 'On Trip',
                        'off_duty' => 'Off Duty',
                        'unavailable' => 'Off Duty',
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
                        \Filament\Notifications\Notification::make()
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
