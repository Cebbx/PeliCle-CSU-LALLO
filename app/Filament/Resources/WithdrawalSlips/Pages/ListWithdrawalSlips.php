<?php

namespace App\Filament\Resources\WithdrawalSlips\Pages;

use App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawalSlips extends ListRecords
{
    protected static string $resource = WithdrawalSlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('All'),
            'pending' => \Filament\Schemas\Components\Tabs\Tab::make('Pending')
                ->badge(\App\Models\WithdrawalSlip::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending')),
            'approved' => \Filament\Schemas\Components\Tabs\Tab::make('Approved')
                ->badge(\App\Models\WithdrawalSlip::where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'approved')),
            'rejected' => \Filament\Schemas\Components\Tabs\Tab::make('Rejected')
                ->badge(\App\Models\WithdrawalSlip::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'rejected')),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $tab = request()->query('tab');
        if ($tab && array_key_exists($tab, $this->getCachedTabs())) {
            $this->activeTab = $tab;
        }
    }

    public function getDefaultActiveTab(): string | int | null
    {
        $tab = request()->query('tab');
        if ($tab && array_key_exists($tab, $this->getCachedTabs())) {
            return $tab;
        }
        return parent::getDefaultActiveTab();
    }
}
