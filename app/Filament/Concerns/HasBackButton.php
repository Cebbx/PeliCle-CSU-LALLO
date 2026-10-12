<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;

trait HasBackButton
{
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Back')
            ->icon('heroicon-m-arrow-left')
            ->url(fn () => static::getResource()::getUrl('index'));
    }
}
