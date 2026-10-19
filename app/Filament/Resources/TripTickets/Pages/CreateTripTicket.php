<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\TripTickets\TripTicketResource;
use App\Models\VehicleRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateTripTicket extends CreateRecord
{
    use HasBackButton;

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['ticket_number']) || \App\Models\TripTicket::withTrashed()->where('ticket_number', $data['ticket_number'])->exists()) {
            $data['ticket_number'] = \App\Models\TripTicket::generateNextTicketNumber();
        }

        if (!empty($data['vehicle_request_id']) && empty($data['document'])) {
            $leadReq = VehicleRequest::find($data['vehicle_request_id']);
            if ($leadReq && !empty($leadReq->document)) {
                $data['document'] = $leadReq->document;
            }
        }

        return $data;
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
                if (empty($record->document) && !empty($req->document)) {
                    $record->updateQuietly(['document' => $req->document]);
                }

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

        if ($record->driver_id && !empty($record->document)) {
            try {
                $record->sendSmsNotification();
            } catch (\Throwable $e) {}
        }
    }
}
