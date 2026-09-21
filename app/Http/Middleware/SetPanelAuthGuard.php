<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class SetPanelAuthGuard
{
    /**
     * Handle an incoming request and set default auth guard based on the active Filament panel.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $panel = Filament::getCurrentPanel();

        if ($panel) {
            $guard = $panel->getAuthGuard();

            if ($guard && config("auth.guards.{$guard}")) {
                auth()->shouldUse($guard);
            }
        }

        return $next($request);
    }
}
