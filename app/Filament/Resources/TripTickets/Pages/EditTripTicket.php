<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Resources\TripTickets\TripTicketResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTripTicket extends EditRecord
{
    protected static string $resource = TripTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive Trip Ticket')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Trip Ticket')
                ->modalDescription('Are you sure you want to archive this trip ticket? It will be moved to archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Trip Ticket')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }
}
