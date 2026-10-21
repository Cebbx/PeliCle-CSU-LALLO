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
            ->url(fn () => $this->getSafeBackUrl())
            ->alpineClickHandler(null)
            ->visible(fn () => $this instanceof \Filament\Resources\Pages\ViewRecord);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Back')
            ->icon('heroicon-m-arrow-left')
            ->color('gray')
            ->url(fn () => $this->getSafeBackUrl())
            ->alpineClickHandler(null);
    }

    protected function getSafeBackUrl(): string
    {
        $previousUrl = $this->previousUrl ?? url()->previous();
        $currentUrl = request()->fullUrl();

        if (
            $previousUrl &&
            $previousUrl !== $currentUrl &&
            str_starts_with($previousUrl, request()->getSchemeAndHttpHost())
        ) {
            return $previousUrl;
        }

        return static::getResource()::getUrl('index');
    }
}
