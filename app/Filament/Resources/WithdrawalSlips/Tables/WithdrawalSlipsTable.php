<?php

namespace App\Filament\Resources\WithdrawalSlips\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WithdrawalSlipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slip_number')
                    ->searchable()
                    ->label('Slip ID'),
                TextColumn::make('tripTicket.driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->default('N/A'),
                TextColumn::make('tripTicket.ticket_number')
                    ->label('Trip ID'),
                TextColumn::make('amount')
                    ->label('Amount Spent')
                    ->money('PHP')
                    ->sortable()
                    ->default('₱0.00'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make()
                    ->label('Archive Status'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('print')
                    ->label('Print Slip')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record) => route('withdrawal-slips.print', $record->id))
                    ->openUrlInNewTab(),
                \Filament\Actions\DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->modalHeading('Archive Withdrawal Slip')
                    ->modalDescription('Are you sure you want to archive this withdrawal slip? It can be restored at any time.')
                    ->modalSubmitActionLabel('Yes, Archive'),
                \Filament\Actions\RestoreAction::make()
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
                    \Filament\Actions\RestoreBulkAction::make()
                        ->label('Restore Selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                ]),
            ]);
    }
}
