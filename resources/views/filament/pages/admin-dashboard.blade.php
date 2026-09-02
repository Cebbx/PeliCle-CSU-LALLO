<x-filament-panels::page class="fi-dashboard-page space-y-6">

    <!-- Top Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-display tracking-tight text-white flex items-center gap-2">
                Dashboard
            </h1>
            <p class="mt-1 text-sm font-medium text-stone-300">
                Welcome back, <span class="text-white font-bold">{{ auth()->user()->name ?? 'Admin User' }}</span>! 👋
            </p>
            <p class="text-xs text-stone-400 mt-0.5">
                Here is the current operational status of the CSU Lal-lo Campus Vehicle & Trip Management System.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto bg-slate-900/80 border border-slate-800 px-3.5 py-1.5 rounded-full shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-medium text-stone-300 tracking-wide">
                System Live • <span class="text-white font-semibold">{{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}</span>
            </span>
        </div>
    </div>

    <!-- ROW 1: 4 Executive Status Cards with Bottom Neon Waves -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- Card 1: Driver Availability -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-emerald-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-stone-300">Driver Availability</span>
                </div>
                <svg class="w-5 h-5 text-emerald-500/40 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-2xl sm:text-3xl font-extrabold font-display text-white tracking-tight">
                    {{ $driverStats['available'] }} / {{ $driverStats['total'] }} <span class="text-xl sm:text-2xl font-bold text-stone-200">Available</span>
                </div>
                <p class="text-[11px] font-medium text-emerald-400/90 mt-0.5">
                    Drivers ready for dispatch
                </p>
            </div>
            <!-- Smooth Green Sparkline Wave -->
            <div class="w-full h-8 -mx-5 -mb-5 mt-2 overflow-hidden opacity-85">
                <svg viewBox="0 0 300 60" class="w-full h-full preserve-3d" fill="none" preserveAspectRatio="none">
                    <path d="M0,55 C60,50 100,20 160,25 C220,30 260,5 300,10 L300,60 L0,60 Z" fill="url(#greenGradient)" />
                    <path d="M0,55 C60,50 100,20 160,25 C220,30 260,5 300,10" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="greenGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Card 2: Vehicle Availability -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-emerald-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-stone-300">Vehicle Availability</span>
                </div>
                <svg class="w-5 h-5 text-emerald-500/40 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 16v3a2 2 0 01-2 2H7a2 2 0 01-2-2v-3M14 6l-2-2-2 2M12 4v12" />
                </svg>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-2xl sm:text-3xl font-extrabold font-display text-white tracking-tight">
                    {{ $vehicleStats['available'] }} / {{ $vehicleStats['total'] }} <span class="text-xl sm:text-2xl font-bold text-stone-200">Available</span>
                </div>
                <p class="text-[11px] font-medium text-emerald-400/90 mt-0.5">
                    Vehicles ready for dispatch
                </p>
            </div>
            <!-- Smooth Green Sparkline Wave -->
            <div class="w-full h-8 -mx-5 -mb-5 mt-2 overflow-hidden opacity-85">
                <svg viewBox="0 0 300 60" class="w-full h-full" fill="none" preserveAspectRatio="none">
                    <path d="M0,45 C80,35 140,5 200,15 C260,25 280,10 300,5 L300,60 L0,60 Z" fill="url(#greenGradient2)" />
                    <path d="M0,45 C80,35 140,5 200,15 C260,25 280,10 300,5" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="greenGradient2" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Card 3: Pending Vehicle Requests -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-sky-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-500/15 text-sky-400 flex items-center justify-center border border-sky-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-stone-300">Pending Vehicle Requests</span>
                </div>
                <svg class="w-5 h-5 text-slate-600 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-2xl sm:text-3xl font-extrabold font-display text-white tracking-tight">
                    {{ $pendingRequests }}
                </div>
                <p class="text-[11px] font-medium text-stone-400 mt-0.5">
                    Requests awaiting admin review
                </p>
            </div>
            <!-- Dark / Slate Smooth Sparkline Wave -->
            <div class="w-full h-8 -mx-5 -mb-5 mt-2 overflow-hidden opacity-70">
                <svg viewBox="0 0 300 60" class="w-full h-full" fill="none" preserveAspectRatio="none">
                    <path d="M0,50 C80,45 160,20 220,30 C260,38 280,45 300,50 L300,60 L0,60 Z" fill="url(#slateGradient)" />
                    <path d="M0,50 C80,45 160,20 220,30 C260,38 280,45 300,50" stroke="#64748b" stroke-width="2" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="slateGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#64748b" stop-opacity="0.25" />
                            <stop offset="100%" stop-color="#64748b" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Card 4: Approved Vehicle Requests -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-emerald-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-stone-300">Approved Vehicle Requests</span>
                </div>
                <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-2xl sm:text-3xl font-extrabold font-display text-white tracking-tight">
                    {{ $approvedRequests }}
                </div>
                <p class="text-[11px] font-medium text-emerald-400/90 mt-0.5">
                    Requests approved and ticketed
                </p>
            </div>
            <!-- Smooth Green Wave -->
            <div class="w-full h-8 -mx-5 -mb-5 mt-2 overflow-hidden opacity-85">
                <svg viewBox="0 0 300 60" class="w-full h-full" fill="none" preserveAspectRatio="none">
                    <path d="M0,50 C70,40 120,10 180,15 C240,20 270,45 300,50 L300,60 L0,60 Z" fill="url(#greenGradient4)" />
                    <path d="M0,50 C70,40 120,10 180,15 C240,20 270,45 300,50" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="greenGradient4" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

    </div>

    <!-- ROW 2: 3 Wide Executive Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
        
        <!-- Card 1: Active Trips (On Trip) -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-blue-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/15 text-blue-400 flex items-center justify-center font-black font-display text-base border border-blue-500/20">
                        A
                    </div>
                    <span class="text-xs font-semibold text-stone-300">Active Trips (On Trip)</span>
                </div>
                <svg class="w-5 h-5 text-blue-500/50 group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-3xl font-extrabold font-display text-white tracking-tight">
                    {{ $activeTrips }}
                </div>
                <p class="text-[11px] font-medium text-blue-400 mt-0.5">
                    Trips currently on the road
                </p>
            </div>
            <!-- Blue Smooth Sparkline Wave -->
            <div class="w-full h-8 -mx-5 -mb-5 mt-2 overflow-hidden opacity-85">
                <svg viewBox="0 0 300 60" class="w-full h-full" fill="none" preserveAspectRatio="none">
                    <path d="M0,50 C80,48 140,25 200,32 C260,38 280,48 300,50 L300,60 L0,60 Z" fill="url(#blueGradient)" />
                    <path d="M0,50 C80,48 140,25 200,32 C260,38 280,48 300,50" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="blueGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Card 2: Pending Withdrawal Slips -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-amber-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center border border-amber-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-stone-300">Pending Withdrawal Slips</span>
                </div>
                <div class="w-6 h-4 rounded bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-[10px] text-amber-300 font-bold">
                    💵
                </div>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-3xl font-extrabold font-display text-white tracking-tight">
                    {{ $pendingSlips }}
                </div>
                <p class="text-[11px] font-medium text-amber-400/90 mt-0.5">
                    Fuel slips awaiting approval
                </p>
            </div>
            <!-- Bottom Amber Line Bar -->
            <div class="w-full h-1 bg-amber-500/20 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full" style="width: {{ min(100, $pendingSlips * 15) }}%"></div>
            </div>
        </div>

        <!-- Card 3: This Month's Gas Expenses -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 flex flex-col justify-between shadow-lg group hover:border-red-500/40 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-500/15 text-red-400 flex items-center justify-center border border-red-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-stone-300">This Month's Gas Expenses</span>
                </div>
                <span class="text-red-500 text-lg">🔥</span>
            </div>
            <div class="mt-4 mb-3">
                <div class="text-3xl font-extrabold font-display text-white tracking-tight">
                    ₱{{ number_format($gasExpenses['month'], 2) }}
                </div>
                <p class="text-[11px] font-medium text-stone-400 mt-0.5">
                    Today: <span class="text-stone-300">₱{{ number_format($gasExpenses['today'], 2) }}</span> | Week: <span class="text-stone-300">₱{{ number_format($gasExpenses['week'], 2) }}</span>
                </p>
            </div>
            <!-- Bottom Red Accent Bar -->
            <div class="w-full h-1 bg-red-500/20 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-red-500 to-rose-400 rounded-full" style="width: 100%"></div>
            </div>
        </div>

    </div>

    <!-- ROW 3: 3 Visual Analytics & Recent Requests Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Column 1: Trip Activity (This Week) Area Chart (4 / 12 cols) -->
        <div class="lg:col-span-4 rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <h2 class="text-sm font-bold text-white font-display">Trip Activity <span class="text-xs font-normal text-stone-400">(This Week)</span></h2>
                </div>
                <div class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700 text-[11px] text-stone-300 font-medium flex items-center gap-1">
                    <span>This Week</span>
                    <svg class="w-3 h-3 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>

            <!-- Custom Interactive Neon Area Line Chart (SVG Canvas) -->
            <div class="relative w-full h-48 sm:h-52 flex flex-col justify-between">
                <!-- Y-axis markers -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20 border-b border-slate-700">
                    <div class="border-b border-slate-700 w-full"></div>
                    <div class="border-b border-slate-700 w-full"></div>
                    <div class="border-b border-slate-700 w-full"></div>
                    <div class="border-b border-slate-700 w-full"></div>
                </div>

                <!-- Smooth Area SVG -->
                <div class="relative w-full h-40">
                    <svg viewBox="0 0 350 140" class="w-full h-full overflow-visible" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="tripAreaGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.45" />
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0" />
                            </linearGradient>
                            <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur stdDeviation="3" result="blur" />
                                <feComposite in="SourceGraphic" in2="blur" operator="over" />
                            </filter>
                        </defs>
                        
                        <!-- Smooth Area Fill -->
                        <path d="M 0,130 Q 30,120 60,95 T 120,35 T 175,40 T 235,80 T 295,100 T 350,130 L 350,140 L 0,140 Z" fill="url(#tripAreaGradient)" />
                        
                        <!-- Smooth Line -->
                        <path d="M 0,130 Q 30,120 60,95 T 120,35 T 175,40 T 235,80 T 295,100 T 350,130" fill="none" stroke="#3b82f6" stroke-width="3" filter="url(#glow)" />
                        
                        <!-- Glowing Data Dots -->
                        <circle cx="0" cy="130" r="3.5" fill="#60a5fa" stroke="#1e3a8a" stroke-width="2" />
                        <circle cx="60" cy="95" r="3.5" fill="#60a5fa" stroke="#1e3a8a" stroke-width="2" />
                        <circle cx="120" cy="35" r="4.5" fill="#ffffff" stroke="#3b82f6" stroke-width="2.5" />
                        <circle cx="175" cy="40" r="4" fill="#ffffff" stroke="#3b82f6" stroke-width="2.5" />
                        <circle cx="235" cy="80" r="3.5" fill="#60a5fa" stroke="#1e3a8a" stroke-width="2" />
                        <circle cx="295" cy="100" r="3.5" fill="#60a5fa" stroke="#1e3a8a" stroke-width="2" />
                        <circle cx="350" cy="130" r="3.5" fill="#60a5fa" stroke="#1e3a8a" stroke-width="2" />
                    </svg>
                </div>

                <!-- X-Axis Days Labels -->
                <div class="flex justify-between text-[10px] sm:text-[11px] font-medium text-stone-400 pt-1">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span class="text-blue-400 font-bold">Wed</span>
                    <span class="text-blue-400 font-bold">Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>
            </div>

            <div class="flex items-center justify-center gap-2 mt-3 text-[11px] text-stone-400 font-medium">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                <span>Trips Dispatched</span>
            </div>
        </div>

        <!-- Column 2: Vehicle Request Status Donut Chart (4 / 12 cols) -->
        <div class="lg:col-span-4 rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 shadow-lg flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                <h2 class="text-sm font-bold text-white font-display">Vehicle Request Status</h2>
            </div>

            <!-- Donut Chart & Legend in Flex Row -->
            <div class="flex items-center justify-between gap-4 my-auto py-2">
                
                <!-- Donut Chart SVG -->
                <div class="relative w-32 h-32 sm:w-36 sm:h-36 flex-shrink-0 flex items-center justify-center">
                    <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                        <!-- Background Ring -->
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#1e293b" stroke-width="14" />
                        
                        <!-- Completed Segment (Orange / Amber) -->
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#f59e0b" stroke-width="14"
                            stroke-dasharray="238.76"
                            stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['completed_pct'] / 100)) }}" />
                        
                        <!-- Approved Segment (Blue) -->
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#0ea5e9" stroke-width="14"
                            stroke-dasharray="238.76"
                            stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['approved_pct'] / 100)) }}"
                            transform="rotate({{ ($statusBreakdown['completed_pct'] / 100) * 360 }} 50 50)" />
                        
                        <!-- Pending Segment (Green) -->
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#10b981" stroke-width="14"
                            stroke-dasharray="238.76"
                            stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['pending_pct'] / 100)) }}"
                            transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct']) / 100) * 360 }} 50 50)" />
                        
                        <!-- Rejected Segment (Red) -->
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#ef4444" stroke-width="14"
                            stroke-dasharray="238.76"
                            stroke-dashoffset="{{ 238.76 - (238.76 * max(0.03, $statusBreakdown['rejected_pct'] / 100)) }}"
                            transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct'] + $statusBreakdown['pending_pct']) / 100) * 360 }} 50 50)" />
                    </svg>

                    <!-- Center Total Counter -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] uppercase tracking-wider text-stone-400 font-semibold">Total</span>
                        <span class="text-xl font-black font-display text-white leading-none mt-0.5">
                            {{ $statusBreakdown['total'] }}
                        </span>
                    </div>
                </div>

                <!-- Right Legend Breakdown -->
                <div class="flex flex-col gap-2.5 flex-grow text-xs">
                    <!-- Pending -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-stone-300 font-medium">Pending</span>
                        </div>
                        <span class="text-stone-400 font-semibold">{{ $statusBreakdown['pending'] }} <span class="text-[10px] text-stone-500">({{ $statusBreakdown['pending_pct'] }}%)</span></span>
                    </div>

                    <!-- Approved -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <span class="text-stone-300 font-medium">Approved</span>
                        </div>
                        <span class="text-stone-400 font-semibold">{{ $statusBreakdown['approved'] }} <span class="text-[10px] text-stone-500">({{ $statusBreakdown['approved_pct'] }}%)</span></span>
                    </div>

                    <!-- Completed -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-stone-300 font-medium">Completed</span>
                        </div>
                        <span class="text-stone-400 font-semibold">{{ $statusBreakdown['completed'] }} <span class="text-[10px] text-stone-500">({{ $statusBreakdown['completed_pct'] }}%)</span></span>
                    </div>

                    <!-- Rejected -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <span class="text-stone-300 font-medium">Rejected</span>
                        </div>
                        <span class="text-stone-400 font-semibold">{{ $statusBreakdown['rejected'] }} <span class="text-[10px] text-stone-500">({{ $statusBreakdown['rejected_pct'] }}%)</span></span>
                    </div>
                </div>

            </div>

            <div class="text-[11px] text-stone-500 text-center pt-2 border-t border-slate-800">
                Live Distribution Ratio across Campus Departments
            </div>
        </div>

        <!-- Column 3: Recent Vehicle Requests List (4 / 12 cols) -->
        <div class="lg:col-span-4 rounded-2xl bg-gradient-to-b from-slate-900/90 to-slate-950 border border-slate-800/80 p-5 shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-white font-display">Recent Vehicle Requests</h2>
                <a href="/admin/vehicle-requests" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-[11px] text-stone-300 font-medium transition-colors">
                    View All
                </a>
            </div>

            <!-- List of 5 Recent Requests -->
            <div class="flex flex-col divide-y divide-slate-800/80 my-auto">
                @forelse($recentRequests as $req)
                    @php
                        $dotColor = match($req->status) {
                            'pending' => 'bg-red-500',
                            'approved' => 'bg-sky-500',
                            'on_trip' => 'bg-blue-500',
                            'completed' => 'bg-emerald-500',
                            default => 'bg-stone-500',
                        };
                        $badgeBg = match($req->status) {
                            'pending' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                            'approved' => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
                            'on_trip' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                            'completed' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                            'rejected' => 'bg-red-500/15 text-red-400 border-red-500/30',
                            default => 'bg-slate-800 text-stone-400 border-slate-700',
                        };
                        $statusLabel = match($req->status) {
                            'on_trip' => 'On Trip',
                            default => ucfirst($req->status),
                        };
                    @endphp
                    <div class="py-2.5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-2 h-2 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white tracking-wide truncate">
                                    {{ $req->request_number }}
                                </div>
                                <div class="text-[11px] text-stone-400 truncate max-w-[140px] sm:max-w-[160px]">
                                    {{ $req->purpose ?? $req->destination ?? $req->employee_name }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $badgeBg }}">
                                {{ $statusLabel }}
                            </span>
                            <span class="text-[10px] text-stone-500">
                                {{ $req->created_at ? $req->created_at->diffForHumans(null, true) : 'recent' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-stone-500">
                        No vehicle requests recorded yet.
                    </div>
                @endforelse
            </div>

            <div class="text-[11px] text-stone-500 text-center pt-2 border-t border-slate-800">
                Auto-refreshed via Livewire Realtime Dispatch
            </div>
        </div>

    </div>

</x-filament-panels::page>
