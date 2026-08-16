<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 p-6 shadow-lg text-white font-display">
        <!-- Background decorative circles -->
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-xl"></div>
        <div class="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-blue-400/20 blur-lg"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Welcome back, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="mt-1 text-sm text-blue-100/90 max-w-xl">
                    Here is the current operational status of the CSU Lal-lo Campus Vehicle & Trip Management System. Manage requests, track active trips, and review gas expenses.
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-lg px-4 py-2 border border-white/10 self-start md:self-auto">
                <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs font-semibold tracking-wider uppercase text-blue-50">
                    System Live • {{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}
                </span>
            </div>
        </div>
        
        <!-- Inject hover card styles for statistics -->
        <style>
            .fi-wi-stats-overview-stat {
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                border: 1px solid rgba(0, 0, 0, 0.03) !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
            }
            .fi-wi-stats-overview-stat:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04) !important;
                border-color: rgba(59, 130, 246, 0.25) !important;
            }
        </style>
    </div>
</x-filament-widgets::widget>
