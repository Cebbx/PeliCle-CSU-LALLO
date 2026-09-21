<?php

namespace App\Filament\Resources\SmsLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SmsLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('driver_name')
                    ->label('Driver')
                    ->default(fn ($record) => $record?->driver?->name ?? 'Unassigned Driver')
                    ->readOnly(),

                TextInput::make('phone_number')
                    ->label('Recipient Phone Number')
                    ->readOnly(),

                TextInput::make('sent_time')
                    ->label('Sent At')
                    ->default(fn ($record) => $record?->created_at ? $record->created_at->format('F d, Y • h:i A') : 'N/A')
                    ->readOnly(),

                TextInput::make('gateway')
                    ->label('Delivery Gateway')
                    ->default(fn () => !empty(config('services.semaphore.key')) ? 'Semaphore SMS API (Live)' : 'In-App Driver Simulator (Local/Testing)')
                    ->readOnly(),

                Textarea::make('message')
                    ->label('SMS Message Body')
                    ->readOnly()
                    ->columnSpanFull()
                    ->rows(6),
            ]);
    }
}
