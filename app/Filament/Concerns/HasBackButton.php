<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;

trait HasBackButton
{
    protected function getBackButtonAction(): Action
    {
        return Action::make('back')
            ->label('Back')
            ->icon('heroicon-m-arrow-left')
            ->color('gray')
            ->url(fn () => static::getResource()::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
        ];
    }

    public function getFormContentComponent(): Component
    {
        $headerComponent = Actions::make([
            $this->getBackButtonAction(),
        ]);

        if (! $this->hasFormWrapper()) {
            return Group::make([
                $headerComponent,
                EmbeddedSchema::make('form'),
                $this->getFormActionsContentComponent(),
            ]);
        }

        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName())
            ->header([
                $headerComponent,
            ])
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Back')
            ->icon('heroicon-m-arrow-left');
    }
}
