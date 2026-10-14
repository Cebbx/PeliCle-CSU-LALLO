<?php

namespace App\Filament\Resources\WithdrawalSlips\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class WithdrawalSlipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slip_number')
                    ->searchable()
                    ->label('Control No.')
                    ->sortable(),
                TextColumn::make('vehicle_name')
                    ->label('Vehicle')
                    ->searchable()
                    ->default('N/A'),
                TextColumn::make('driver_name')
                    ->label('Driver')
                    ->state(fn ($record) => $record->driver_name ?: ($record->tripTicket?->driver?->name ?? 'N/A'))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('driver_name', 'like', "%{$search}%")
                            ->orWhereHas('tripTicket.driver', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->default('N/A'),
                TextColumn::make('created_at')
                    ->label('Date Issued')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved', 'issued' => 'success',
                        'pending' => 'warning',
                        'rejected', 'cancelled', 'void' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved', 'issued' => 'Issued',
                        'pending' => 'Pending',
                        'rejected', 'cancelled', 'void' => 'Void',
                        default => ucfirst($state),
                    })
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make()
                    ->label('Archive Status'),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('View Slip')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('withdrawal-slips.print', $record->id))
                    ->openUrlInNewTab(),
                Action::make('approve_slip')
                    ->label('Approve Slip')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Fuel Withdrawal Slip')
                    ->modalDescription(fn ($record) => "Are you sure you want to approve fuel slip {$record->slip_number}? This will officially authorize fuel withdrawal.")
                    ->modalSubmitActionLabel('Yes, Approve Slip')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        \Filament\Notifications\Notification::make()
                            ->title('Withdrawal Slip Approved')
                            ->body("Fuel withdrawal slip {$record->slip_number} is now approved.")
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->modalHeading('Archive Withdrawal Slip')
                    ->modalDescription('Are you sure you want to archive this withdrawal slip? It can be restored at any time.')
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
            ])
            ->poll(app()->isLocal() ? '1s' : '2s');
    }
}
