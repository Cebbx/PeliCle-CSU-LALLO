<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive User')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive User Account')
                ->modalDescription('Are you sure you want to archive this user account? The record will be moved to the archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore User')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }
}
