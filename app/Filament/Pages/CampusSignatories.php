<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\CampusSetting;
use Filament\Notifications\Notification;
use BackedEnum;

class CampusSignatories extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string $routePath = '/campus-signatories';

    protected static ?string $title = 'Campus Signatories';

    protected static ?string $navigationLabel = 'Campus Signatories';

    protected static ?int $navigationSort = 12;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings & Management';

    protected string $view = 'filament.pages.campus-signatories';

    public ?string $ceo_name = '';
    public ?string $ceo_title = '';
    public ?string $gso_name = '';
    public ?string $gso_title = '';
    public ?string $cao_name = '';
    public ?string $cao_title = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        $this->ceo_name = CampusSetting::get('ceo_name', 'ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.');
        $this->ceo_title = CampusSetting::get('ceo_title', 'Campus Executive Officer');
        $this->gso_name = CampusSetting::get('gso_name', 'JOEL A. TUMAMAO');
        $this->gso_title = CampusSetting::get('gso_title', 'General Services Officer');
        $this->cao_name = CampusSetting::get('cao_name', 'DR. ARLENE D. TALAMAYAN');
        $this->cao_title = CampusSetting::get('cao_title', 'Chief Administrative Officer');
    }

    public function save(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized action. Only system administrators can update campus signatories.');
        }

        $this->validate([
            'ceo_name' => 'required|string|max:255',
            'ceo_title' => 'required|string|max:255',
            'gso_name' => 'required|string|max:255',
            'gso_title' => 'required|string|max:255',
        ]);

        CampusSetting::set('ceo_name', trim($this->ceo_name));
        CampusSetting::set('ceo_title', trim($this->ceo_title));
        CampusSetting::set('gso_name', trim($this->gso_name));
        CampusSetting::set('gso_title', trim($this->gso_title));
        if (!empty($this->cao_name)) {
            CampusSetting::set('cao_name', trim($this->cao_name));
        }
        if (!empty($this->cao_title)) {
            CampusSetting::set('cao_title', trim($this->cao_title));
        }

        Notification::make()
            ->title('Campus Signatories Updated')
            ->body('Official administration names have been successfully updated. All generated trip tickets, travel orders, withdrawal slips, and analytics reports will now reflect these changes.')
            ->success()
            ->send();
    }
}
