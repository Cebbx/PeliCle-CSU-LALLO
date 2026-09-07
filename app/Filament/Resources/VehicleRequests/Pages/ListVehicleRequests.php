<?php

namespace App\Filament\Resources\VehicleRequests\Pages;

use App\Filament\Resources\VehicleRequests\VehicleRequestResource;
use App\Models\VehicleRequest;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Url;

class ListVehicleRequests extends ListRecords
{
    protected static string $resource = VehicleRequestResource::class;

    #[Url(as: 'archived')]
    public bool $isArchived = false;

    public function getTitle(): string | Htmlable
    {
        return $this->isArchived ? 'Archived Vehicle Requests' : 'Vehicle Requests';
    }

    protected function getHeaderActions(): array
    {
        $archivedCount = VehicleRequest::onlyTrashed()->count();

        return [
            CreateAction::make()
                ->hidden(fn () => $this->isArchived),
            Action::make('refresh')
                ->label('Refresh Table')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => null),
            Action::make('toggle_archived')
                ->label(fn () => $this->isArchived ? 'Back to Active Requests' : 'View Archived')
                ->icon(fn () => $this->isArchived ? 'heroicon-o-arrow-left' : 'heroicon-o-archive-box')
                ->color(fn () => $this->isArchived ? 'gray' : 'warning')
                ->badge(fn () => !$this->isArchived && $archivedCount > 0 ? $archivedCount : null)
                ->badgeColor('gray')
                ->url(fn () => $this->isArchived 
                    ? static::getResource()::getUrl('index') 
                    : static::getResource()::getUrl('index', ['archived' => 1])),
        ];
    }

    protected function getTableQuery(): Builder | Relation | null
    {
        $query = parent::getTableQuery();

        if ($this->isArchived) {
            return $query->onlyTrashed();
        }

        return $query->withoutTrashed();
    }

    public function getTabs(): array
    {
        VehicleRequest::expirePastPendingRequests();

        if ($this->isArchived) {
            return [];
        }

        return [
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending')
                ->badge(VehicleRequest::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'approved' => Tab::make('Approved')
                ->badge(VehicleRequest::where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            'on_trip' => Tab::make('On Trip')
                ->badge(VehicleRequest::where('status', 'on_trip')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'on_trip')),
            'completed' => Tab::make('Completed')
                ->badge(VehicleRequest::where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
            'cancelled' => Tab::make('Cancelled')
                ->badge(VehicleRequest::where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
            'rejected' => Tab::make('Disapproved')
                ->badge(VehicleRequest::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
            'expired' => Tab::make('Expired')
                ->badge(VehicleRequest::where('status', 'expired')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired')),
        ];
    }

    public function mount(): void
    {
        VehicleRequest::expirePastPendingRequests();
        $this->isArchived = request()->boolean('archived');
        parent::mount();

        if (! $this->isArchived) {
            $tab = request()->query('tab');
            if ($tab && array_key_exists($tab, $this->getCachedTabs())) {
                $this->activeTab = $tab;
            }
        } else {
            $this->activeTab = null;
        }
    }

    public function getDefaultActiveTab(): string | int | null
    {
        if ($this->isArchived) {
            return null;
        }

        $tab = request()->query('tab');
        if ($tab && array_key_exists($tab, $this->getCachedTabs())) {
            return $tab;
        }
        return parent::getDefaultActiveTab();
    }
}
