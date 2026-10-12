<?php

namespace App\Filament\Driver\Resources\TripTickets\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Driver\Resources\TripTickets\TripTicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTripTicket extends CreateRecord
{
    use HasBackButton;

    protected static string $resource = TripTicketResource::class;
}
