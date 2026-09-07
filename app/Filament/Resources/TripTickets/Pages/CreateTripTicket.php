<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Resources\TripTickets\TripTicketResource;
use App\Models\VehicleRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateTripTicket extends CreateRecord
{
    protected static string $resource = TripTicketResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Approved Ticket');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $primaryId = $record->vehicle_request_id;
        $companionIds = $this->data['companion_requests'] ?? [];

        $allIds = array_unique(array_filter(array_merge([$primaryId], is_array($companionIds) ? $companionIds : [])));
        $status = $record->status === 'active' ? 'on_trip' : 'approved';

        foreach ($allIds as $reqId) {
            $req = VehicleRequest::find($reqId);
            if ($req) {
                $updateData = [
                    'trip_ticket_id' => $record->id,
                    'status' => $status,
                ];
                if ($record->vehicle) {
                    $updateData['vehicle'] = $record->vehicle;
                }
                if ($record->document && !$req->document) {
                    $updateData['document'] = $record->document;
                }
                $req->updateQuietly($updateData);
            }
        }
    }
}
