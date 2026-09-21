<?php

namespace App\Filament\Resources\SmsLogs\Pages;

use App\Filament\Resources\SmsLogs\SmsLogResource;
use App\Models\Driver;
use App\Services\SmsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSmsLogs extends ListRecords
{
    protected static string $resource = SmsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendSms')
                ->label('Send SMS Alert')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading('Send SMS Alert to Driver')
                ->modalDescription('Compose and dispatch an official SMS alert directly to a registered CSU driver.')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Send SMS Now')
                ->form([
                    Select::make('driver_id')
                        ->label('Select Driver')
                        ->options(fn () => Driver::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($driver = Driver::find($state)) {
                                $set('phone_number', $driver->contact_number);
                            }
                        }),

                    TextInput::make('phone_number')
                        ->label('Recipient Mobile Number')
                        ->required()
                        ->placeholder('09XXXXXXXXX'),

                    Select::make('template')
                        ->label('Quick Message Template')
                        ->options([
                            'custom' => 'Custom Message',
                            'dispatch' => 'Trip Dispatch Notice',
                            'inspection' => 'Pre-Trip Inspection Reminder',
                            'weather' => 'Weather / Road Caution Advisory',
                            'standby' => 'Motorpool Standby Notice',
                        ])
                        ->default('custom')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $driverName = Driver::find($get('driver_id'))?->name ?? 'Driver';
                            match ($state) {
                                'dispatch' => $set('message', "Hi {$driverName}! You have a new trip dispatch assignment. Please review your scheduled route in the PeliCle portal. - PeliCle CSU Lal-lo"),
                                'inspection' => $set('message', "Hi {$driverName}! Reminder to conduct pre-trip vehicle safety check (fuel, brakes, engine oil, tires) before departure. - PeliCle CSU Lal-lo"),
                                'weather' => $set('message', "CSU Motorpool Advisory: Inclement weather / wet road conditions reported. Please observe safe driving and speed limits. - PeliCle"),
                                'standby' => $set('message', "Hi {$driverName}! Please report to the CSU Lal-lo Motorpool Office on standby for administrative dispatch. - PeliCle"),
                                default => null,
                            };
                        }),

                    Textarea::make('message')
                        ->label('SMS Message Body')
                        ->required()
                        ->rows(4)
                        ->placeholder('Type your notification message here...')
                        ->helperText('Standard 1 credit = 160 characters. Message will be logged and visible in driver portal.'),
                ])
                ->action(function (array $data) {
                    $driver = Driver::find($data['driver_id']);
                    if (!$driver) {
                        $driver = (object) [
                            'id' => null,
                            'name' => 'Driver (' . $data['phone_number'] . ')',
                            'contact_number' => $data['phone_number'],
                        ];
                    }

                    if ($driver instanceof Driver && !empty($data['phone_number']) && $driver->contact_number !== $data['phone_number']) {
                        $driver->update(['contact_number' => $data['phone_number']]);
                    }

                    SmsService::send($driver, $data['message']);

                    Notification::make()
                        ->title('SMS Sent Successfully')
                        ->body('Alert dispatched to ' . ($driver->name ?? $data['phone_number']))
                        ->success()
                        ->send();
                }),
        ];
    }
}
