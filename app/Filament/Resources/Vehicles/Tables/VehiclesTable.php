<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_number')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state, $record): string => 
                        $state === 'maintenance' 
                            ? 'danger' 
                            : (\App\Models\TripTicket::where('vehicle', 'like', '%' . $record->plate_number . '%')->where('status', 'active')->exists() ? 'info' : 'success')
                    )
                    ->formatStateUsing(fn (string $state, $record): string => 
                        $state === 'maintenance' 
                            ? 'Under Maintenance' 
                            : (\App\Models\TripTicket::where('vehicle', 'like', '%' . $record->plate_number . '%')->where('status', 'active')->exists() ? 'On Trip' : 'Available')
                    )
                    ->searchable(),
                TextColumn::make('next_pms_date')
                    ->label('Next PMS Schedule')
                    ->date('M d, Y')
                    ->badge()
                    ->color(function ($state, $record) {
                        if (!$state) return 'gray';
                        if ($record->isPmsOverdue()) return 'danger';
                        if ($record->isPmsUpcoming()) return 'warning';
                        return 'success';
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return 'Not Set';
                        $formatted = \Carbon\Carbon::parse($state)->format('M d, Y');
                        if ($record->isPmsOverdue()) return "{$formatted} (Overdue)";
                        if ($record->isPmsUpcoming()) return "{$formatted} (Due Soon)";
                        return $formatted;
                    })
                    ->sortable(),
                TextColumn::make('last_pms_date')
                    ->label('Last Serviced')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('maintenance_notes')
                    ->label('PMS Notes')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('mark_serviced')
                    ->label('Mark Serviced / Ready')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'maintenance')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'available',
                            'last_pms_date' => now()->toDateString(),
                            'next_pms_date' => now()->addMonths(6)->toDateString(),
                            'maintenance_notes' => 'PMS maintenance completed on ' . now()->format('M d, Y'),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Vehicle Serviced & Ready')
                            ->body("{$record->brand} ({$record->plate_number}) is now Available for dispatch.")
                            ->success()
                            ->send();
                    }),
                Action::make('send_to_maintenance')
                    ->label('Set Maintenance')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'maintenance')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('maintenance_notes')
                            ->label('Maintenance Reason / Issues')
                            ->placeholder('e.g. Scheduled PMS, Brake check, Change oil')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'maintenance',
                            'maintenance_notes' => $data['maintenance_notes'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Vehicle Under Maintenance')
                            ->body("{$record->brand} ({$record->plate_number}) is now under maintenance.")
                            ->danger()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                // No delete action allowed
            ]);
    }
}
