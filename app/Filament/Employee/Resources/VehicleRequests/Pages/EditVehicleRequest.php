<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Employee\Resources\VehicleRequests\VehicleRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

use Filament\Notifications\Notification;

class EditVehicleRequest extends EditRecord
{
    use HasBackButton;

    protected static string $resource = VehicleRequestResource::class;

    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();
        if (in_array($record->status, ['rejected', 'cancelled', 'expired']) || $record->status !== 'pending' || !empty($record->document)) {
            $statusLabel = match ($record->status) {
                'rejected' => 'Disapproved',
                'cancelled' => 'Cancelled',
                'expired' => 'Expired',
                default => ucfirst($record->status),
            };

            Notification::make()
                ->title('Editing Prohibited')
                ->body("Hindi na maaaring i-edit ang request na ito dahil ito ay {$statusLabel}.")
                ->warning()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }

        parent::authorizeAccess();
    }

    protected function beforeSave(): void
    {
        if (in_array($this->record->status, ['rejected', 'cancelled', 'expired']) || $this->record->status !== 'pending' || !empty($this->record->document)) {
            Notification::make()
                ->title('Cannot Save Changes')
                ->body('Bawal baguhin ang form kapag ito ay Disapproved o Cancelled na.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record && $record->status === 'pending' && empty($record->document)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
