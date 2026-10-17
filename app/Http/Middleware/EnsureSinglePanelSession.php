<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class EnsureSinglePanelSession
{
    /**
     * Registered panel guards and their human-readable labels.
     */
    protected array $panelGuards = [
        'admin' => 'Admin / GSO',
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

        $currentGuard = $panel->getAuthGuard();

        // 1. Always allow logout requests through so users can freely terminate sessions
        if ($request->is('*/logout') || $request->routeIs('*.logout')) {
            return $next($request);
        }

        // 2. Check if currently authenticated in this panel
        if ($currentGuard && auth($currentGuard)->check()) {
            // Clean up any lingering or stale sessions from other panel guards in this browser session
            foreach (array_keys($this->panelGuards) as $otherGuard) {
                if ($otherGuard !== $currentGuard && auth($otherGuard)->check()) {
                    auth($otherGuard)->logout();
                }
            }

            return $next($request);
        }

        // 3. User is NOT authenticated in the current panel.
        // Check if user already has an active session in another panel in this browser:
        foreach ($this->panelGuards as $otherGuard => $otherRoleLabel) {
            if ($otherGuard !== $currentGuard && auth($otherGuard)->check()) {
                $user = auth($otherGuard)->user();
                $userName = $user ? $user->name : $otherRoleLabel;
                $currentPanelLabel = $this->panelGuards[$currentGuard] ?? ucfirst($currentGuard);

                Notification::make()
                    ->title("Active {$otherRoleLabel} Session Detected")
                    ->body("Naka-login ka pa bilang {$userName} ({$otherRoleLabel}). Paki-log out muna ang iyong account bago mag-login o pumasok sa {$currentPanelLabel} portal.")
                    ->warning()
                    ->persistent()
                    ->send();

                return redirect()->to('/' . $otherGuard);
            }
        }

        return $next($request);
    }
}
