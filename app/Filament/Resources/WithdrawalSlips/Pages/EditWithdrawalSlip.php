<?php

namespace App\Filament\Resources\WithdrawalSlips\Pages;

use App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWithdrawalSlip extends EditRecord
{
    protected static string $resource = WithdrawalSlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['requested_items']) && !is_array($data['requested_items'])) {
            $decoded = json_decode($data['requested_items'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['requested_items'] = $decoded;
            } else {
                $data['requested_items'] = [
                    ['item' => 'diesel', 'quantity' => 20]
                ];
            }
        }
        return $data;
    }
}
