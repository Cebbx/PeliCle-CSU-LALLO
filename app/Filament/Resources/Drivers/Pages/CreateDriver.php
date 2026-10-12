<?php

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\Drivers\DriverResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDriver extends CreateRecord
{
    use HasBackButton;

    protected static string $resource = DriverResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }
}
