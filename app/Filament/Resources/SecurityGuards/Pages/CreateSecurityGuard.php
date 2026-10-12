<?php

namespace App\Filament\Resources\SecurityGuards\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\SecurityGuards\SecurityGuardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSecurityGuard extends CreateRecord
{
    use HasBackButton;

    protected static string $resource = SecurityGuardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'guard';
        if (empty($data['department'])) {
            $data['department'] = 'Security & Safety Office';
        }
        return $data;
    }

    public function canCreateAnother(): bool
    {
        return false;
    }
}
