<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;

trait HasBackButton
{
    public function getBackButtonAction(): Action
    {
        return Action::make('back')
            ->label('Back')
            ->icon('heroicon-m-arrow-left')
            ->color('gray')
            ->url(fn () => static::getResource()::getUrl('index'))
            ->visible(fn () => $this instanceof \Filament\Resources\Pages\ViewRecord);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Back')
            ->icon('heroicon-m-arrow-left')
            ->url(fn () => static::getResource()::getUrl('index'));
    }
}
