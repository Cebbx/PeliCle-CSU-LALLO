<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'employee' => 'info',
                        'staff' => 'warning',
                        'driver' => 'success',
                        'guard' => 'purple',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('plain_password')
                    ->label('Password')
                    ->badge()
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Password copied!')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-m-key')
                    ->searchable()
                    ->placeholder('password')
                    ->tooltip('Click to copy password'),
                TextColumn::make('created_at')
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
                \Filament\Actions\Action::make('reset_password')
                    ->label('Change Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->modalHeading(fn ($record) => "Change Password: {$record->name}")
                    ->modalDescription('Enter a new password for this user account.')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->required()
                            ->password()
                            ->revealable()
                            ->default(fn ($record) => $record->role === 'employee' && str_contains($record->email, '@csu.edu.ph') ? explode('@', $record->email)[0] : 'password'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'password' => \Illuminate\Support\Facades\Hash::make($data['new_password']),
                            'plain_password' => $data['new_password'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Password Updated')
                            ->body("Password for {$record->name} has been set to: {$data['new_password']}")
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->modalHeading('Archive User Account')
                    ->modalDescription('Are you sure you want to archive this user account? The record can be restored anytime.')
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
