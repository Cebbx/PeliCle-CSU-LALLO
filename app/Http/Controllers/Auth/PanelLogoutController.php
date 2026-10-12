<?php

namespace App\Http\Controllers\Auth;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PanelLogoutController
{
    public function __invoke(): LogoutResponse
    {
        // Log out of all portal guards so user is 100% clean and free to choose any portal
        auth('admin')->logout();
        auth('employee')->logout();
        auth('driver')->logout();
        auth()->logout();

        session()->forget(['last_active_portal', 'guard_authenticated', 'guard_id', 'guard_name']);
        session()->invalidate();
        session()->regenerateToken();

        return new class implements LogoutResponse {
            public function toResponse($request): RedirectResponse | Redirector
            {
                return redirect('/');
            }
        };
    }
}
