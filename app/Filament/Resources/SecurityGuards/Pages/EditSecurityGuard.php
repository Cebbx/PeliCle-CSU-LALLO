<?php

namespace App\Filament\Resources\SecurityGuards\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\SecurityGuards\SecurityGuardResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSecurityGuard extends EditRecord
{
    use HasBackButton;

    protected static string $resource = SecurityGuardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
            DeleteAction::make()
                ->label('Delete Guard')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->modalHeading('Delete Security Guard')
                ->modalDescription('Are you sure you want to delete this security guard? They will be removed from the Gate Clearance Portal and QR Scanner.')
                ->modalSubmitActionLabel('Yes, Delete Guard')
                ->using(function ($record) {
                    try {
                        return $record->forceDelete();
                    } catch (\Throwable $e) {
                        return $record->delete();
                    }
                }),
            RestoreAction::make()
                ->label('Restore Guard')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
