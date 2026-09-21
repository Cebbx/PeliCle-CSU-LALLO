<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSU Lal-lo - Security Gate Portal & Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top left, #0b111e, #020408 100%);
        }
        
        /* Forces the camera video feed to scale correctly without black letterbox bars */
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 1.5rem !important;
        }
        
        /* Hides default helper text/links injected by the html5-qrcode library */
        #reader img { display: none !important; }
        #reader span { display: none !important; }
        #reader a { display: none !important; }
        
        .scanner-laser {
            position: absolute;
            left: 6%;
            right: 6%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            box-shadow: 0 0 12px #10b981, 0 0 20px rgba(16, 185, 129, 0.5);
            animation: scan 2.5s ease-in-out infinite;
            z-index: 10;
        }
        
        @keyframes scan {
            0% { top: 10%; }
            50% { top: 90%; }
            100% { top: 10%; }
        }
        
        /* Shake animation for wrong pin */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake {
            animation: shake 0.4s ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex items-center justify-center p-2 sm:p-4 antialiased">

    <!-- Responsive Main Container: Adapts smoothly on mobile & desktop -->
    <div id="main-container" class="w-full max-w-md md:max-w-5xl min-h-screen md:min-h-[680px] bg-slate-900/10 md:bg-slate-900/50 md:backdrop-blur-2xl md:border md:border-slate-800/80 md:rounded-3xl p-3 sm:p-6 md:shadow-2xl relative overflow-hidden flex flex-col justify-between my-auto transition-all duration-300">
        
        <!-- Ambient Glow Effects (Visible on desktop view card) -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none hidden md:block"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none hidden md:block"></div>

        <!-- 1. OFFICIAL GUARD DUTY LOGIN SCREEN -->
        <div id="login-screen" style="background-color: #0b0f19;" class="absolute inset-0 z-50 flex flex-col justify-between p-4 sm:p-8 transition-opacity duration-300 {{ $isVerified ? 'hidden pointer-events-none' : '' }}">
            
            <!-- Exit / Back button -->
            <div class="flex justify-between items-center">
                <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors py-1.5 px-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Portals</span>
                </a>
                <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-emerald-400 bg-emerald-400/10 px-2.5 py-1 rounded-full border border-emerald-400/20">
                    Gate Clearance Station
                </span>
            </div>

            <!-- Login Form Box -->
            <div class="w-full max-w-sm mx-auto my-auto py-2">
                <!-- Portal Brand Icon & Title -->
                <div class="text-center mb-5">
                    <div class="relative w-16 h-16 mx-auto mb-3">
                        <div class="w-16 h-16 bg-slate-900 border border-emerald-500/30 rounded-3xl p-2.5 flex items-center justify-center shadow-[0_0_35px_rgba(16,185,129,0.2)]">
                            <img src="{{ asset('csu-logo-sm.png') }}" alt="CSU Lal-lo Logo" class="w-full h-full object-contain filter drop-shadow">
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 text-slate-950 rounded-full flex items-center justify-center border-2 border-[#0b0f19] shadow-md" title="CSU Gate Security Station">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-white tracking-tight uppercase">Security Guard Portal</h2>
                    <p class="text-xs text-slate-400 mt-0.5">CSU Lal-lo Campus &bull; Vehicle Clearance Station</p>

                    <!-- Real-Time Terminal Live Clock -->
                    <div class="inline-flex items-center gap-2 mt-2 px-3 py-1 bg-slate-950/80 border border-slate-800/90 rounded-full text-[11px] font-mono text-slate-300 shadow-inner">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span id="terminal-live-clock">Loading clock...</span>
                    </div>
                </div>

                <!-- Error Alert Box -->
                <div id="login-error-alert" class="hidden p-3 bg-red-500/15 border border-red-500/30 text-red-300 text-xs rounded-xl mb-4 text-center font-medium shadow-sm"></div>

                <!-- Login Form -->
                <form id="guard-login-form" onsubmit="handleGuardLogin(event)" class="space-y-4">
                    <!-- Guard ID / Badge No Field -->
                    <div>
                        <label for="guard_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                <span>Guard ID / Badge Number</span>
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono font-semibold">Duty Sign-in</span>
                        </label>
                        <input 
                            type="text" 
                            id="guard_id" 
                            name="guard_id" 
                            required 
                            autofocus 
                            inputmode="numeric" 
                            pattern="[0-9]*"
                            maxlength="6"
                            autocomplete="off"
                            placeholder="e.g. 1001" 
                            class="w-full px-4 py-3.5 bg-slate-950/90 border border-slate-700/90 rounded-2xl text-white placeholder-slate-600 text-center text-lg font-mono font-bold tracking-widest focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition shadow-inner"
                        />
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        id="login-submit-btn" 
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-950/40 cursor-pointer flex items-center justify-center gap-2 mt-4"
                    >
                        <span id="btn-text">Sign In to Gate Duty</span>
                        <svg id="btn-arrow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </form>

                <!-- Shift Roster Notice (Click-to-fill for Demo / Defense Reference) -->
                <div class="mt-5 p-3 rounded-2xl bg-slate-950/60 border border-slate-800/80 text-left">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 flex items-center gap-1">
                            <span>📋</span>
                            <span>Campus Guard Duty Roster</span>
                        </span>
                        <span class="text-[9px] font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            Demo Reference
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <button type="button" onclick="quickSelectGuard('1001')" title="Click to fill ID: 1001" class="group bg-slate-900/70 hover:bg-slate-800 border border-slate-800/80 hover:border-emerald-500/40 rounded-xl py-2 px-1 transition cursor-pointer active:scale-95 flex flex-col items-center">
                            <span class="block text-[11px] font-bold text-slate-200 group-hover:text-emerald-400 transition-colors">Guard 1</span>
                            <span class="block text-[10px] font-mono font-bold text-emerald-400">ID: 1001</span>
                        </button>
                        <button type="button" onclick="quickSelectGuard('1002')" title="Click to fill ID: 1002" class="group bg-slate-900/70 hover:bg-slate-800 border border-slate-800/80 hover:border-cyan-500/40 rounded-xl py-2 px-1 transition cursor-pointer active:scale-95 flex flex-col items-center">
                            <span class="block text-[11px] font-bold text-slate-200 group-hover:text-cyan-400 transition-colors">Guard 2</span>
                            <span class="block text-[10px] font-mono font-bold text-cyan-400">ID: 1002</span>
                        </button>
                        <button type="button" onclick="quickSelectGuard('1003')" title="Click to fill ID: 1003" class="group bg-slate-900/70 hover:bg-slate-800 border border-slate-800/80 hover:border-purple-500/40 rounded-xl py-2 px-1 transition cursor-pointer active:scale-95 flex flex-col items-center">
                            <span class="block text-[11px] font-bold text-slate-200 group-hover:text-purple-400 transition-colors">Guard 3</span>
                            <span class="block text-[10px] font-mono font-bold text-purple-400">ID: 1003</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500 text-center mt-2">
                        * Click a roster card or type your assigned Guard ID above.
                    </p>
                </div>
            </div>

            <div class="text-center text-[9px] text-slate-600 font-bold uppercase tracking-widest py-1">
                CSU Lal-lo Campus Security Management &bull; Official Dispatch System
            </div>
        </div>

        <!-- 2. MAIN SECURITY PORTAL CONTAINER -->
        <div id="scanner-screen" class="flex-1 flex flex-col justify-between {{ $isVerified ? '' : 'hidden' }}">
            
            <!-- Header with Portal Title, Active Guard Shift & Exit -->
            <div class="flex flex-wrap items-center justify-between border-b border-slate-800/80 pb-3 mb-3 gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-900 border border-emerald-500/30 rounded-xl p-1.5 flex items-center justify-center shadow-[0_0_20px_rgba(16,185,129,0.15)] shrink-0">
                        <img src="{{ asset('csu-logo-sm.png') }}" alt="CSU Logo" class="w-full h-full object-contain filter drop-shadow">
                    </div>
                    <div class="text-left">
                        <h1 class="text-sm sm:text-base font-bold text-white leading-tight">Security Gate Control</h1>
                        <p class="text-[10px] text-slate-400 leading-none mt-0.5">CSU Lal-lo Campus &bull; Vehicle Clearance</p>
                    </div>
                </div>

                <!-- Active Guard Shift Badge & Actions -->
                <div class="flex items-center gap-2">
                    <div class="inline-flex items-center gap-1.5 bg-slate-950/90 border border-slate-800 px-3 py-1.5 rounded-xl shadow-inner text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-slate-400 text-[11px]">Duty:</span>
                        <strong id="active-guard-label" class="text-emerald-400 font-extrabold text-xs">👮 {{ $guardName }}</strong>
                    </div>

                    <a href="{{ route('guard.logout') }}" title="Switch Guard / Change Duty Shift" class="text-[11px] bg-slate-950 border border-slate-800 hover:border-slate-700 px-2.5 py-1.5 rounded-xl text-slate-400 hover:text-white font-semibold transition-colors flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Switch</span>
                    </a>

                    <a href="/" class="text-[11px] bg-slate-950 border border-slate-800 px-2.5 py-1.5 rounded-xl text-slate-400 hover:text-white font-semibold transition-colors">
                        Exit
                    </a>
                </div>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="flex items-center gap-2 mb-4 p-1 bg-slate-950/80 border border-slate-800/90 rounded-2xl">
                <button id="tab-btn-scanner" onclick="switchTab('scanner')" class="flex-1 py-2 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 bg-emerald-600 text-white shadow-md cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Live Scanner</span>
                </button>
                <button id="tab-btn-logbook" onclick="switchTab('logbook')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Gate Logbook</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-800 text-emerald-400 border border-slate-700">{{ $monthCount }}</span>
                </button>
            </div>

            <!-- ========================================================
                 TAB 1: CAMERA SCANNER VIEW
                 ======================================================== -->
            <div id="tab-content-scanner" class="flex-1 flex flex-col justify-between">
                <!-- Scanner Viewport Area -->
                <div class="flex-1 flex flex-col items-center justify-center my-auto py-2">
                    <div class="scanner-container w-full aspect-square max-w-[280px] mx-auto bg-slate-950/60 rounded-3xl border border-slate-800/60 overflow-hidden relative shadow-2xl">
                        
                        <!-- Target Brackets Overlay -->
                        <div class="absolute inset-0 z-20 pointer-events-none flex items-center justify-center">
                            <div class="w-48 h-48 border border-white/5 rounded-3xl relative">
                                <div class="absolute -top-1 -left-1 w-6 h-6 border-t-[4px] border-l-[4px] border-emerald-500 rounded-tl-xl"></div>
                                <div class="absolute -top-1 -right-1 w-6 h-6 border-t-[4px] border-r-[4px] border-emerald-500 rounded-tr-xl"></div>
                                <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-[4px] border-l-[4px] border-emerald-500 rounded-bl-xl"></div>
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-[4px] border-r-[4px] border-emerald-500 rounded-br-xl"></div>
                                
                                <div class="absolute inset-0 flex items-center justify-center opacity-25">
                                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Animation Laser Line -->
                        <div id="laser" class="scanner-laser {{ $isVerified ? '' : 'hidden' }}"></div>

                        <!-- Camera stream container -->
                        <div id="reader" class="w-full h-full"></div>

                        <!-- Loading State / Permission Prompt -->
                        <div id="placeholder-prompt" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-slate-950/90 z-30 {{ $isVerified ? '' : 'hidden' }}">
                            <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-2xl flex items-center justify-center mb-3 shadow-[0_0_20px_rgba(16,185,129,0.1)]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-xs font-bold text-white mb-1">Camera Permission Required</p>
                            <p class="text-[10px] text-slate-400 mb-4 max-w-[200px] leading-relaxed">Please grant camera permissions to allow scanning QR codes on Trip Tickets.</p>
                            <button id="start-camera-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl text-[10px] uppercase tracking-wider transition-all shadow-lg shadow-emerald-950/50 cursor-pointer">
                                Allow Camera
                            </button>
                        </div>
                    </div>

                    <!-- Camera selector dropdown -->
                    <div class="w-full max-w-[280px] mt-3 hidden" id="camera-select-container">
                        <label for="camera-select" class="block text-[9px] uppercase font-bold tracking-wider text-slate-500 mb-1">Select Active Camera</label>
                        <select id="camera-select" class="w-full bg-slate-950 border border-slate-800/80 text-slate-300 text-xs rounded-xl p-2 focus:outline-none focus:border-emerald-500 transition-colors"></select>
                    </div>

                    <!-- Today's Cleared Vehicles Quick List -->
                    <div class="mt-4 w-full max-w-sm bg-slate-950/60 border border-slate-800/80 rounded-2xl p-3 text-left">
                        <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-800/60">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Today's Cleared Vehicles ({{ $todayCount }})</span>
                            <button onclick="switchTab('logbook')" class="text-[10px] text-emerald-400 hover:underline font-semibold cursor-pointer">View Logbook &rarr;</button>
                        </div>
                        @forelse($todayTrips->take(3) as $todayTrip)
                            @php
                                $vehName = \App\Models\Vehicle::getVehicleName($todayTrip->vehicle);
                                $inStamp = $todayTrip->display_gate_in ? \Carbon\Carbon::parse($todayTrip->display_gate_in)->timezone('Asia/Manila')->format('g:i A') : '';
                            @endphp
                            <div class="flex items-center justify-between py-2 border-b border-slate-800/40 last:border-0 text-xs">
                                <div class="text-left">
                                    <span class="font-bold text-white block">{{ $vehName }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $todayTrip->driver?->name ?? 'N/A' }} &bull; {{ $todayTrip->vehicleRequest?->destination ?? 'N/A' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-mono text-emerald-400 block font-bold">{{ $inStamp }}</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-300 border border-blue-500/20 font-semibold whitespace-nowrap">
                                        👮 {{ $todayTrip->display_scanned_by }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-[11px] text-slate-500 text-center py-2">No vehicles scanned yet today.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Footer / Status Info -->
                <div class="mt-4 border-t border-slate-800/80 pt-3 flex flex-col items-center">
                    <div class="inline-flex items-center gap-2 bg-slate-950/80 border border-slate-850 px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 shadow-md">
                        <span class="w-2 h-2 rounded-full bg-slate-600 animate-pulse" id="status-indicator"></span>
                        <span id="status-text">Scanner Ready</span>
                    </div>
                    <p class="text-[9px] text-slate-600 uppercase tracking-widest mt-2 font-bold">PeliCle Gate Clearance System</p>
                </div>
            </div>

            <!-- ========================================================
                 TAB 2: UNIFIED MASTER GATE LOGBOOK (SHARED BY ALL GUARDS)
                 ======================================================== -->
            <div id="tab-content-logbook" class="flex-1 flex flex-col justify-between hidden">
                <div>
                    <!-- Filter and Print Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 bg-slate-950/60 border border-slate-800/80 rounded-2xl p-3">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Month:</label>
                            <select onchange="changeFilter(this.value)" class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-1.5 focus:outline-none focus:border-emerald-500 font-semibold cursor-pointer">
                                @for($m = 1; $m <= 12; $m++)
                                    @php $mVal = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                    <option value="{{ $mVal }}" {{ $selectedMonth == $mVal ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate($selectedYear, $m, 1)->format('F Y') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <a href="{{ route('guard.logbook.print', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-950/30 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            <span>Print Monthly Log</span>
                        </a>
                    </div>

                    <!-- Monthly Stats Cards -->
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-4">
                        <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-2.5 sm:p-3 text-center">
                            <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400 block">Cleared This Month</span>
                            <span class="text-xl sm:text-2xl font-black text-emerald-400 font-mono mt-0.5 block">{{ $monthCount }}</span>
                            <span class="text-[9px] text-slate-500">{{ $monthName }}</span>
                        </div>
                        <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-2.5 sm:p-3 text-center">
                            <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400 block">Cleared Today</span>
                            <span class="text-xl sm:text-2xl font-black text-cyan-400 font-mono mt-0.5 block">{{ $todayCount }}</span>
                            <span class="text-[9px] text-slate-500">Vehicles</span>
                        </div>
                        <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-2.5 sm:p-3 text-center">
                            <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400 block">Total Passengers</span>
                            <span class="text-xl sm:text-2xl font-black text-amber-400 font-mono mt-0.5 block">{{ $paxCount }}</span>
                            <span class="text-[9px] text-slate-500">Persons</span>
                        </div>
                    </div>

                    <!-- Responsive Swipe Hint (Mobile only) -->
                    <div class="sm:hidden flex items-center justify-center gap-1.5 py-1 px-3 mb-2 bg-slate-900/60 border border-slate-800/80 rounded-xl text-[10px] text-slate-400 font-medium">
                        <span>⇄</span>
                        <span>Swipe table left/right to view full OUT &amp; IN logs</span>
                    </div>

                    <!-- UNIFIED MASTER LOGBOOK DATA TABLE (Mobile Responsive with horizontal scroll) -->
                    <div class="bg-slate-950/80 border border-slate-800/90 rounded-2xl overflow-hidden max-h-[420px] overflow-y-auto overflow-x-auto shadow-inner">
                        <table class="w-full text-left text-xs border-collapse min-w-[720px]">
                            <thead class="bg-slate-900/95 text-[10px] text-slate-400 uppercase tracking-wider sticky top-0 border-b border-slate-800 z-10">
                                <tr>
                                    <th class="p-2.5 font-bold whitespace-nowrap">Trip Ticket</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap">Vehicle</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap">Driver</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap">Destination</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap text-cyan-400">Date &amp; Time OUT</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap text-emerald-400">Date &amp; Time IN</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap text-center">Scanned By</th>
                                    <th class="p-2.5 font-bold whitespace-nowrap text-center">Clearance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                                @forelse($monthTrips as $trip)
                                    @php
                                        $vehName = \App\Models\Vehicle::getVehicleName($trip->vehicle);
                                        $outStamp = $trip->display_gate_out ? \Carbon\Carbon::parse($trip->display_gate_out)->timezone('Asia/Manila')->format('M d, Y - g:i A') : '---';
                                        $inStamp = $trip->display_gate_in ? \Carbon\Carbon::parse($trip->display_gate_in)->timezone('Asia/Manila')->format('M d, Y - g:i A') : '---';
                                        $guardWhoScanned = $trip->display_scanned_by;
                                    @endphp
                                    <tr class="hover:bg-slate-900/50 transition">
                                        <td class="p-2.5 font-mono font-bold text-amber-400 whitespace-nowrap">{{ $trip->formatted_ticket_number }}</td>
                                        <td class="p-2.5 font-bold text-white whitespace-nowrap">{{ $vehName }}</td>
                                        <td class="p-2.5 whitespace-nowrap text-slate-200">{{ $trip->driver?->name ?? 'N/A' }}</td>
                                        <td class="p-2.5 text-[11px] text-slate-400 max-w-[150px] truncate" title="{{ $trip->vehicleRequest?->destination }}">{{ $trip->vehicleRequest?->destination ?? 'N/A' }}</td>
                                        <td class="p-2.5 font-mono text-[11px] whitespace-nowrap text-slate-300">{{ $outStamp }}</td>
                                        <td class="p-2.5 font-mono text-[11px] whitespace-nowrap text-emerald-400 font-semibold">{{ $inStamp }}</td>
                                        <td class="p-2.5 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                                👮 {{ $guardWhoScanned }}
                                            </span>
                                        </td>
                                        <td class="p-2.5 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                &#10004; Cleared
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-8 text-center text-xs text-slate-500 font-semibold">
                                            No vehicle gate clearances recorded for {{ $monthName }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer info -->
                <div class="mt-4 border-t border-slate-800/80 pt-3 text-center">
                    <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold">Official Campus Gate Clearance Logbook &bull; CSU Lal-lo</p>
                </div>
            </div>

        </div>

    </div>

    <!-- Html5Qrcode Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let isVerified = {{ $isVerified ? 'true' : 'false' }};

            // Real-Time Terminal Clock
            function updateTerminalClock() {
                const clockEl = document.getElementById('terminal-live-clock');
                if (!clockEl) return;
                const now = new Date();
                const options = { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit', 
                    hour12: true 
                };
                clockEl.innerText = now.toLocaleString('en-US', options);
            }
            updateTerminalClock();
            setInterval(updateTerminalClock, 1000);

            // Quick select Guard ID from roster demo chips
            window.quickSelectGuard = function(id) {
                const guardIdInput = document.getElementById('guard_id');
                if (!guardIdInput) return;
                guardIdInput.value = id;
                guardIdInput.focus();
                guardIdInput.classList.add('ring-2', 'ring-emerald-500');
                setTimeout(() => {
                    guardIdInput.classList.remove('ring-2', 'ring-emerald-500');
                }, 500);
            };

            // Handle Guard ID duty login submission
            window.handleGuardLogin = function(e) {
                e.preventDefault();
                const guardIdInput = document.getElementById('guard_id');
                const errorAlert = document.getElementById('login-error-alert');
                const submitBtn = document.getElementById('login-submit-btn');
                const btnText = document.getElementById('btn-text');
                const btnSpinner = document.getElementById('btn-spinner');
                const btnArrow = document.getElementById('btn-arrow');
                const loginScreen = document.getElementById('login-screen');

                const guardId = guardIdInput ? guardIdInput.value.trim() : '';

                if (!guardId) {
                    errorAlert.innerText = "Please enter your Guard ID / Badge number.";
                    errorAlert.classList.remove('hidden');
                    return;
                }

                // UI loading state
                errorAlert.classList.add('hidden');
                submitBtn.disabled = true;
                btnText.innerText = "Signing in...";
                btnSpinner.classList.remove('hidden');
                btnArrow.classList.add('hidden');

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("guard.verify-pin") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ 
                        guard_id: guardId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Successfully authenticated!
                        if (data.guard_name) {
                            const guardLabel = document.getElementById('active-guard-label');
                            if (guardLabel) guardLabel.innerText = "👮 " + data.guard_name;
                        }

                        loginScreen.classList.add('opacity-0');
                        setTimeout(() => {
                            loginScreen.classList.add('hidden');
                            const scannerScreen = document.getElementById('scanner-screen');
                            scannerScreen.classList.remove('hidden');
                            isVerified = true;

                            const urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.get('tab') === 'logbook') {
                                switchTab('logbook');
                            } else {
                                initScanner();
                            }
                        }, 300);
                    } else {
                        // Failed authentication
                        errorAlert.innerText = data.message || "Guard ID not recognized. Please try again.";
                        errorAlert.classList.remove('hidden');
                        submitBtn.disabled = false;
                        btnText.innerText = "Sign In to Gate Duty";
                        btnSpinner.classList.add('hidden');
                        btnArrow.classList.remove('hidden');

                        if (guardIdInput) {
                            guardIdInput.select();
                            guardIdInput.focus();
                        }
                    }
                })
                .catch(() => {
                    errorAlert.innerText = "Network connection error! Please check your connection and try again.";
                    errorAlert.classList.remove('hidden');
                    submitBtn.disabled = false;
                    btnText.innerText = "Sign In to Gate Duty";
                    btnSpinner.classList.add('hidden');
                    btnArrow.classList.remove('hidden');
                });
            };
 
            // Scanner functionality
            const startBtn = document.getElementById('start-camera-btn');
            const placeholder = document.getElementById('placeholder-prompt');
            const laser = document.getElementById('laser');
            const statusInd = document.getElementById('status-indicator');
            const statusText = document.getElementById('status-text');
            const cameraSelect = document.getElementById('camera-select');
            const cameraSelectContainer = document.getElementById('camera-select-container');
 
            let html5QrCode = null;
            let currentCameraId = null;
 
            // Trigger scanner initialization
            startBtn.addEventListener('click', function() {
                initScanner();
            });
 
            // Auto-trigger if already verified
            if (isVerified) {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('tab') === 'logbook') {
                    switchTab('logbook');
                } else {
                    initScanner();
                }
            }
 
            function initScanner() {
                placeholder.classList.add('hidden');
                laser.classList.remove('hidden');
                
                statusInd.classList.remove('bg-slate-600', 'bg-red-500', 'bg-blue-500');
                statusInd.classList.add('bg-emerald-500');
                statusText.innerText = "Initializing Camera...";
 
                // Get list of cameras
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        cameraSelect.innerHTML = '';
                        devices.forEach(device => {
                            const opt = document.createElement('option');
                            opt.value = device.id;
                            opt.text = device.label || `Camera ${cameraSelect.length + 1}`;
                            cameraSelect.appendChild(opt);
                        });
 
                        let defaultCamera = devices[0].id;
                        // Prefer rear camera for scanning codes
                        const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('environment') || d.label.toLowerCase().includes('rear') || d.label.toLowerCase().includes('cobalt'));
                        if (backCam) {
                            defaultCamera = backCam.id;
                        }
 
                        cameraSelect.value = defaultCamera;
                        currentCameraId = defaultCamera;
 
                        if (devices.length > 1) {
                            cameraSelectContainer.classList.remove('hidden');
                        }
 
                        startScanning(currentCameraId);
                    } else {
                        showError("No cameras found on this device.");
                    }
                }).catch(err => {
                    showError("Camera permission denied.");
                    placeholder.classList.remove('hidden');
                });
            }
 
            function startScanning(cameraId) {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        launchCamera(cameraId);
                    }).catch(() => {
                        launchCamera(cameraId);
                    });
                } else {
                    launchCamera(cameraId);
                }
            }
 
            function launchCamera(cameraId) {
                html5QrCode = new Html5Qrcode("reader");
                const config = { 
                    fps: 15, 
                    qrbox: function(width, height) {
                        const size = Math.min(width, height) * 0.75;
                        return { width: size, height: size };
                    }
                };
 
                html5QrCode.start(
                    cameraId, 
                    config, 
                    (decodedText) => {
                        // Success scanning
                        statusInd.classList.remove('bg-emerald-500');
                        statusInd.classList.add('bg-blue-500');
                        statusText.innerText = "Ticket detected! Redirecting...";
                        
                        // Parse path to prevent cross-domain session loss
                        let path = "/";
                        try {
                            const parsedUrl = new URL(decodedText);
                            path = parsedUrl.pathname;
                        } catch (e) {
                            if (decodedText.includes('/trip-tickets/')) {
                                const idx = decodedText.indexOf('/trip-tickets/');
                                path = decodedText.substring(idx);
                            }
                        }
 
                        const targetUrl = window.location.origin + path;
                        
                        html5QrCode.stop().then(() => {
                            window.location.href = targetUrl;
                        }).catch(() => {
                            window.location.href = targetUrl;
                        });
                    },
                    (errorMessage) => {
                        // Verbose logs ignored
                    }
                ).then(() => {
                    statusInd.classList.remove('bg-emerald-500');
                    statusInd.classList.add('bg-emerald-400');
                    statusText.innerText = "Scanning Active...";
                }).catch(err => {
                    showError("Camera feed connection failed.");
                });
            }
 
            cameraSelect.addEventListener('change', function() {
                currentCameraId = this.value;
                startScanning(currentCameraId);
            });
 
            function showError(msg) {
                statusInd.classList.remove('bg-slate-600', 'bg-emerald-500', 'bg-emerald-400', 'bg-blue-500');
                statusInd.classList.add('bg-red-500');
                statusText.innerText = msg;
                laser.classList.add('hidden');
            }

            // Global tab switching function
            window.switchTab = function(tab) {
                const scannerContent = document.getElementById('tab-content-scanner');
                const logbookContent = document.getElementById('tab-content-logbook');
                const btnScanner = document.getElementById('tab-btn-scanner');
                const btnLogbook = document.getElementById('tab-btn-logbook');

                if (tab === 'scanner') {
                    scannerContent.classList.remove('hidden');
                    logbookContent.classList.add('hidden');
                    
                    btnScanner.classList.add('bg-emerald-600', 'text-white', 'shadow-md');
                    btnScanner.classList.remove('text-slate-400');
                    btnLogbook.classList.remove('bg-emerald-600', 'text-white', 'shadow-md');
                    btnLogbook.classList.add('text-slate-400');

                    if (isVerified && currentCameraId) {
                        startScanning(currentCameraId);
                    }
                } else {
                    scannerContent.classList.add('hidden');
                    logbookContent.classList.remove('hidden');

                    btnLogbook.classList.add('bg-emerald-600', 'text-white', 'shadow-md');
                    btnLogbook.classList.remove('text-slate-400');
                    btnScanner.classList.remove('bg-emerald-600', 'text-white', 'shadow-md');
                    btnScanner.classList.add('text-slate-400');

                    if (html5QrCode) {
                        html5QrCode.stop().catch(() => {});
                    }
                }
            };

            window.changeFilter = function(month) {
                const url = new URL(window.location.href);
                url.searchParams.set('month', month);
                url.searchParams.set('tab', 'logbook');
                window.location.href = url.toString();
            };
        });
    </script>
</body>
</html>
