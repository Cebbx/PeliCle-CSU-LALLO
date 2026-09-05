<?php

namespace App\Filament\Driver\Resources\TripTickets\Pages;

use App\Filament\Driver\Resources\TripTickets\TripTicketResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTripTicket extends ViewRecord
{
    protected static string $resource = TripTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('acknowledge')
                ->label('Acknowledge Trip')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    $record->update(['status' => 'active']);
                    $record->driver?->update(['status' => 'on_trip']);
                    $record->vehicleRequest?->update(['status' => 'approved']);
                    
                    $this->fillForm();
                }),
            Action::make('print')
                ->label('View Ticket & QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->url(fn ($record) => route('trip-tickets.print', $record->id))
                ->openUrlInNewTab(),
        ];
    }
}
