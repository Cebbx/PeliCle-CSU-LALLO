<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class EnsureSinglePanelSession
{
    /**
     * Registered panel guards and their human-readable labels.
     */
    protected array $panelGuards = [
        'admin' => 'Admin',
        'employee' => 'Employee',
        'driver' => 'Driver',
    ];

    /**
     * Handle an incoming request to prevent simultaneous cross-panel logins without logging out.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return $next($request);
        }

        // Always allow login and logout requests through so users can freely authenticate or terminate sessions
        if ($request->is('*/logout') || $request->routeIs('*.logout') || $request->is('*/login') || $request->routeIs('*.login')) {
            return $next($request);
        }

        $currentGuard = $panel->getAuthGuard();

        // If currently authenticated in this panel, clean up any lingering sessions from other guards
        if ($currentGuard && auth($currentGuard)->check()) {
            foreach (array_keys($this->panelGuards) as $otherGuard) {
                if ($otherGuard !== $currentGuard && auth($otherGuard)->check()) {
                    auth($otherGuard)->logout();
                }
            }
        }

        return $next($request);
    }
}
