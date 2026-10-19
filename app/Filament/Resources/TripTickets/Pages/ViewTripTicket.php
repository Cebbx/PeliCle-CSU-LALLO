<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\TripTickets\TripTicketResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTripTicket extends ViewRecord
{
    use HasBackButton;

    protected static string $resource = TripTicketResource::class;

    protected ?string $pollingInterval = '5s';

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
            Action::make('print')
                ->label('Print Trip Ticket')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn ($record) => route('trip-tickets.print', $record->id))
                ->openUrlInNewTab(),
        ];
    }
}
