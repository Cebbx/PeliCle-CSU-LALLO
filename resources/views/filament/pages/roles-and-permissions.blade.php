<x-filament-panels::page>
    <div class="rounded-xl bg-slate-900 border border-slate-800 p-6 text-white">
        <h2 class="text-lg font-bold">System Roles & Access Control (RBAC)</h2>
        <p class="text-xs text-stone-400 mt-1">Manage user roles, permissions, and security scope across CSU Lal-lo portals.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="p-4 rounded-lg bg-slate-950 border border-slate-800">
                <div class="flex items-center gap-2 text-amber-400 font-bold text-sm">
                    <span>👑</span>
                    <span>Admin Role</span>
                </div>
                <p class="text-xs text-stone-400 mt-2">Full access to dispatch, fleet analytics, driver assignments, and gasoline slips approval.</p>
            </div>
            
            <div class="p-4 rounded-lg bg-slate-950 border border-slate-800">
                <div class="flex items-center gap-2 text-sky-400 font-bold text-sm">
                    <span>👤</span>
                    <span>Employee Role</span>
                </div>
                <p class="text-xs text-stone-400 mt-2">Access to vehicle reservation requests, department trip tracking, and passenger listings.</p>
            </div>
            
            <div class="p-4 rounded-lg bg-slate-950 border border-slate-800">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
                    <span>🚗</span>
                    <span>Driver Role</span>
                </div>
                <p class="text-xs text-stone-400 mt-2">Access to driver dashboard, fuel withdrawal slip requests, and gate clearance QR codes.</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
