<?php

namespace App\Http\Controllers\Auth;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Filament\Facades\Filament;

class PanelLogoutController
{
    public function __invoke(): LogoutResponse
    {
        // Log out only the current panel's guard
        Filament::auth()->logout();

        // Refresh CSRF token for security without invalidating the other panels' sessions
        session()->regenerateToken();

        return app(LogoutResponse::class);
    }
}
