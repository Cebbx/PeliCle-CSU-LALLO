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
        // Log out only the current panel's guard
        Filament::auth()->logout();

        session()->forget('last_active_portal');

        // Refresh CSRF token for security without invalidating the other panels' sessions
        session()->regenerateToken();

        return new class implements LogoutResponse {
            public function toResponse($request): RedirectResponse | Redirector
            {
                return redirect('/?home=1');
            }
        };
    }
}
