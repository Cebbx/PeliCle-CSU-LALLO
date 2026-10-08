<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Resources\TripTickets\TripTicketResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTripTicket extends ViewRecord
{
    protected static string $resource = TripTicketResource::class;

    protected ?string $pollingInterval = '5s';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('print')
                ->label('Print Trip Ticket')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn ($record) => route('trip-tickets.print', $record->id))
                ->openUrlInNewTab(),
            Action::make('print_travel_order_driver')
                ->label('Print Driver TO')
                ->icon('heroicon-o-user')
                ->color('success')
                ->url(fn ($record) => route('trip-tickets.print-travel-order', [$record->id, 'type' => 'driver']))
                ->openUrlInNewTab(),
        ];
    }
}
