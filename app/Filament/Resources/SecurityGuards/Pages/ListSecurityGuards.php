<?php

namespace App\Filament\Resources\SecurityGuards\Pages;

use App\Filament\Resources\SecurityGuards\SecurityGuardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecurityGuards extends ListRecords
{
    protected static string $resource = SecurityGuardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Security Guard')
                ->icon('heroicon-o-plus'),
        ];
    }
}
