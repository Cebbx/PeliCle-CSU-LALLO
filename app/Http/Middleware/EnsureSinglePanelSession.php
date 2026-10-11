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

        // Always allow logout requests through so users can safely terminate sessions
        if ($request->is('*/logout') || $request->routeIs('*.logout')) {
            return $next($request);
        }

        $currentGuard = $panel->getAuthGuard();

        // 1. If currently authenticated in this panel, clean up any lingering sessions from other guards
        if ($currentGuard && auth($currentGuard)->check()) {
            foreach (array_keys($this->panelGuards) as $otherGuard) {
                if ($otherGuard !== $currentGuard && auth($otherGuard)->check()) {
                    auth($otherGuard)->logout();
                }
            }

            return $next($request);
        }

        // 2. If NOT authenticated in this panel, check if another panel session is currently active
        foreach ($this->panelGuards as $otherGuard => $otherRoleLabel) {
            if ($otherGuard !== $currentGuard && auth($otherGuard)->check()) {
                $user = auth($otherGuard)->user();
                $userName = $user ? $user->name : $otherRoleLabel;

                // Send notification explaining the single-session requirement
                Notification::make()
                    ->title("Active {$otherRoleLabel} Session Detected")
                    ->body("Naka-login ka pa bilang {$userName} ({$otherRoleLabel}). Paki-log out muna ang iyong account bago mag-login o pumasok sa ibang portal.")
                    ->warning()
                    ->persistent()
                    ->send();

                return redirect()->to('/' . $otherGuard);
            }
        }

        return $next($request);
    }
}
