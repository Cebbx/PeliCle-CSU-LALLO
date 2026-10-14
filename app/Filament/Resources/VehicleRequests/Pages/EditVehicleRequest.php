<?php

namespace App\Filament\Resources\VehicleRequests\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\VehicleRequests\VehicleRequestResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleRequest extends EditRecord
{
    use HasBackButton;

    protected static string $resource = VehicleRequestResource::class;

    public function getTitle(): string
    {
        $reqNum = $this->record->formatted_request_number ?? $this->record->request_number;
        return "Edit Vehicle Request: VR-{$reqNum}";
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
            Action::make('approve_and_ticket')
                ->label('Approve & Create Trip Ticket')
                ->icon('heroicon-o-ticket')
                ->color('success')
                ->visible(fn () => !$this->record->trashed() && !$this->record->tripTicket()->exists() && in_array($this->record->status, ['pending', 'approved']))
                ->action(function () {
                    $this->record->update(['status' => 'approved']);
                    $this->redirect(\App\Filament\Resources\TripTickets\TripTicketResource::getUrl('create', [
                        'vehicle_request_id' => $this->record->id,
                    ]));
                }),
            Action::make('view_trip_ticket')
                ->label('View Trip Ticket')
                ->icon('heroicon-o-ticket')
                ->color('info')
                ->visible(fn () => $this->record->tripTicket()->exists())
                ->url(fn () => \App\Filament\Resources\TripTickets\TripTicketResource::getUrl('edit', [
                    'record' => $this->record->tripTicket->id,
                ])),
            Action::make('print')
                ->label('View Requisition Form')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => route('vehicle-requests.print', $this->record->id))
                ->openUrlInNewTab(),
            DeleteAction::make()
                ->label('Archive Request')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Vehicle Request')
                ->modalDescription('Are you sure you want to archive this request? It will be moved to archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Request')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
