<?php

namespace App\Filament\Driver\Resources\TripTickets\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Driver\Resources\TripTickets\TripTicketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTripTicket extends EditRecord
{
    use HasBackButton;

    protected static string $resource = TripTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
            DeleteAction::make(),
        ];
    }
}
