<?php

namespace App\Filament\Resources\SecurityGuards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\TripTicket;

class SecurityGuardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guard_id')
                    ->label('Badge ID')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Officer Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Official Email')
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position')
                    ->label('Position / Role')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Gate Security Officer'),

                TextColumn::make('contact_number')
                    ->label('Contact No.')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('clearance_count')
                    ->label('Gate Clearances')
                    ->state(fn ($record) => TripTicket::where('scanned_by', $record->name)->count())
                    ->badge()
                    ->color('success')
                    ->suffix(' trips')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Archive Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->modalHeading('Archive Security Guard')
                    ->modalDescription('Archived guards will no longer appear on the Gate Clearance Portal or be able to scan QR codes.')
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
