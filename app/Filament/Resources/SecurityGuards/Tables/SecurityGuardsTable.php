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
            ->poll('10s')
            ->columns([
                TextColumn::make('guard_id')
                    ->label('Guard ID')
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
                    ->label('Position')
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
                    ->suffix(' trips'),

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
                EditAction::make()
                    ->label('Edit'),
                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Delete Security Guard')
                    ->modalDescription('Are you sure you want to delete this security guard? They will be removed from the Gate Clearance Portal and QR Scanner.')
                    ->modalSubmitActionLabel('Yes, Delete Guard')
                    ->using(function ($record) {
                        try {
                            return $record->forceDelete();
                        } catch (\Throwable $e) {
                            return $record->delete();
                        }
                    }),
                RestoreAction::make()
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('Restore Selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                ]),
            ]);
    }
}
