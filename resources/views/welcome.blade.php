<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        <title>PeliCle - CSU Lal-lo Vehicle & Trip Management</title>
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Outfit', 'sans-serif'],
                            display: ['Space Grotesk', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#f0f9ff',
                                100: '#e0f2fe',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                            }
                        }
                    }
                }
            }
        </script>
        
        <style>
            body {
                background: radial-gradient(circle at top left, #0b0f19, #02040a 90%);
            }
            .grid-overlay {
                background-image: radial-gradient(rgba(14, 165, 233, 0.08) 1.5px, transparent 1.5px);
                background-size: 28px 28px;
            }
            .glass {
                background: rgba(15, 23, 42, 0.65);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.05);
                position: relative;
                overflow: hidden;
            }
            .glass::before {
                content: '';
                position: absolute;
                top: 0;
                left: -150%;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.04),
                    transparent
                );
                transition: 0.6s;
            }
            .glass:hover::before {
                left: 150%;
            }
            .glass-hover:hover {
                background: rgba(15, 23, 42, 0.85);
                border-color: rgba(14, 165, 233, 0.3);
                box-shadow: 0 15px 35px -10px rgba(14, 165, 233, 0.25);
                transform: translateY(-4px);
            }
            .glass-hover:active {
                transform: scale(0.98);
            }
            .text-glow {
                text-shadow: 0 0 25px rgba(14, 165, 233, 0.5);
            }
            @keyframes float-orb-1 {
                0% { transform: translate(0px, 0px) scale(1); }
                50% { transform: translate(30px, -40px) scale(1.1); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            @keyframes float-orb-2 {
                0% { transform: translate(0px, 0px) scale(1); }
                50% { transform: translate(-30px, 30px) scale(1.15); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-orb-1 { animation: float-orb-1 18s infinite alternate ease-in-out; }
            .animate-orb-2 { animation: float-orb-2 22s infinite alternate ease-in-out; }
        </style>
    </head>
    <body class="text-stone-100 min-h-screen flex flex-col justify-between antialiased selection:bg-brand-500 selection:text-white overflow-x-hidden relative">
        
        <!-- Background Grid & Glowing Orbs -->
        <div class="absolute inset-0 grid-overlay -z-20 pointer-events-none opacity-70"></div>
        <div class="absolute top-[-10%] left-[5%] w-[350px] sm:w-[500px] h-[350px] sm:h-[500px] bg-brand-500/15 rounded-full blur-[100px] -z-10 pointer-events-none animate-orb-1"></div>
        <div class="absolute bottom-[5%] right-[5%] w-[400px] sm:w-[600px] h-[400px] sm:h-[600px] bg-indigo-500/10 rounded-full blur-[120px] -z-10 pointer-events-none animate-orb-2"></div>

        <!-- Top Header -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-3.5 sm:py-5 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shadow-md border border-brand-500/30 bg-slate-900/80 p-1 flex-shrink-0">
                    <img src="/csu-logo.png" alt="CSU Logo" class="w-full h-full object-contain block m-auto" />
                </div>
                <div class="flex flex-col justify-center">
                    <span class="font-display font-bold text-base sm:text-lg tracking-wider uppercase leading-tight bg-gradient-to-r from-stone-100 to-stone-300 bg-clip-text text-transparent">PeliCle</span>
                    <span class="text-[9px] sm:text-[10px] block text-sky-400/90 tracking-widest uppercase font-semibold leading-tight mt-0.5">CSU LAL-LO</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[11px] sm:text-xs font-semibold border border-emerald-500/20 shadow-sm flex-shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>System Online</span>
            </div>
        </header>

        <!-- Main Hero & Portals Section -->
        <main class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-2 sm:py-6 flex-grow flex flex-col justify-center items-center z-10 gap-5 sm:gap-8">
            
            <!-- Hero Title & Centered Logo -->
            <div class="text-center max-w-2xl flex flex-col items-center justify-center mx-auto">
                <!-- Logo with glowing effect perfectly centered -->
                <div class="relative mb-3 sm:mb-4 flex items-center justify-center mx-auto">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-brand-500 to-indigo-500 blur-md opacity-50 transition-opacity duration-300"></div>
                    <img src="/csu-lallo-clean.png" alt="CSU Lal-lo Campus Logo" class="w-20 h-20 sm:w-28 sm:h-28 object-contain rounded-full border border-sky-400/40 shadow-2xl relative z-10 bg-slate-950/90 p-1 block mx-auto" />
                </div>
                
                <h1 class="font-display text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mb-2 leading-tight">
                    <span class="bg-gradient-to-r from-sky-400 via-blue-400 to-indigo-400 bg-clip-text text-transparent text-glow">PeliCle Portal Access</span>
                </h1>
                <p class="text-stone-300 text-xs sm:text-base max-w-xl mx-auto leading-relaxed font-light px-2">
                    Vehicle Reservation, Trip Dispatch & Gate Clearance System for CSU Lal-lo Campus. Select your portal to continue.
                </p>
            </div>

            <!-- Portal Grid (2 columns on mobile, 4 columns on desktop) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 w-full max-w-6xl">
                
                <!-- 1. Admin Control Panel -->
                <a href="/admin" class="glass glass-hover p-4 sm:p-6 rounded-2xl sm:rounded-[1.5rem] transition-all duration-300 flex flex-col justify-between group border-t-2 border-t-indigo-500/80">
                    <div>
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-all duration-300 border border-indigo-500/30 shadow-inner">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <h2 class="text-sm sm:text-lg font-bold font-display text-stone-100 group-hover:text-indigo-400 transition-colors mb-1 sm:mb-2">Admin Panel</h2>
                        <p class="text-[11px] sm:text-xs text-stone-400 leading-snug sm:leading-relaxed mb-3 sm:mb-4 hidden sm:block">
                            Approve schedules, dispatch trip tickets, and track fleet analytics.
                        </p>
                        <p class="text-[10px] text-stone-400 leading-snug sm:hidden mb-2">
                            Approval & Fleet Analytics
                        </p>
                    </div>
                    <div class="flex items-center text-[11px] sm:text-xs font-semibold text-indigo-400 gap-1 group-hover:translate-x-1.5 transition-transform duration-300">
                        <span>Access Panel</span> 
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                <!-- 2. Employee Portal -->
                <a href="/employee" class="glass glass-hover p-4 sm:p-6 rounded-2xl sm:rounded-[1.5rem] transition-all duration-300 flex flex-col justify-between group border-t-2 border-t-teal-500/80">
                    <div>
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-all duration-300 border border-teal-500/30 shadow-inner">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-sm sm:text-lg font-bold font-display text-stone-100 group-hover:text-teal-400 transition-colors mb-1 sm:mb-2">Employee Portal</h2>
                        <p class="text-[11px] sm:text-xs text-stone-400 leading-snug sm:leading-relaxed mb-3 sm:mb-4 hidden sm:block">
                            Request vehicles, manage passengers, and track reservation status.
                        </p>
                        <p class="text-[10px] text-stone-400 leading-snug sm:hidden mb-2">
                            Vehicle Request & Status
                        </p>
                    </div>
                    <div class="flex items-center text-[11px] sm:text-xs font-semibold text-teal-400 gap-1 group-hover:translate-x-1.5 transition-transform duration-300">
                        <span>Request Vehicle</span> 
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                <!-- 3. Driver Portal -->
                <a href="/driver" class="glass glass-hover p-4 sm:p-6 rounded-2xl sm:rounded-[1.5rem] transition-all duration-300 flex flex-col justify-between group border-t-2 border-t-sky-500/80">
                    <div>
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-sky-500/15 text-sky-400 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-all duration-300 border border-sky-500/30 shadow-inner">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-sm sm:text-lg font-bold font-display text-stone-100 group-hover:text-sky-400 transition-colors mb-1 sm:mb-2">Driver Portal</h2>
                        <p class="text-[11px] sm:text-xs text-stone-400 leading-snug sm:leading-relaxed mb-3 sm:mb-4 hidden sm:block">
                            View assigned trips, request fuel slips, and show QR code at gate.
                        </p>
                        <p class="text-[10px] text-stone-400 leading-snug sm:hidden mb-2">
                            Assigned Trips & Gate QR
                        </p>
                    </div>
                    <div class="flex items-center text-[11px] sm:text-xs font-semibold text-sky-400 gap-1 group-hover:translate-x-1.5 transition-transform duration-300">
                        <span>Driver Login</span> 
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                <!-- 4. Security Guard Scanner Portal -->
                <a href="/guard/scanner" class="glass glass-hover p-4 sm:p-6 rounded-2xl sm:rounded-[1.5rem] transition-all duration-300 flex flex-col justify-between group border-t-2 border-t-emerald-500/80">
                    <div>
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-all duration-300 border border-emerald-500/30 shadow-inner">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-sm sm:text-lg font-bold font-display text-stone-100 group-hover:text-emerald-400 transition-colors mb-1 sm:mb-2">Guard Scanner</h2>
                        <p class="text-[11px] sm:text-xs text-stone-400 leading-snug sm:leading-relaxed mb-3 sm:mb-4 hidden sm:block">
                            Scan driver QR codes at gate to verify departure and arrival clearance.
                        </p>
                        <p class="text-[10px] text-stone-400 leading-snug sm:hidden mb-2">
                            Gate Camera QR Scanner
                        </p>
                    </div>
                    <div class="flex items-center text-[11px] sm:text-xs font-semibold text-emerald-400 gap-1 group-hover:translate-x-1.5 transition-transform duration-300">
                        <span>Open Scanner</span> 
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

            </div>

            <!-- Stats Overview Banner (Clean & Responsive) -->
            <div class="glass w-full max-w-6xl rounded-2xl p-4 sm:p-5 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6 text-center border border-white/5">
                <div class="p-1">
                    <span class="text-lg sm:text-2xl font-extrabold font-display bg-gradient-to-r from-brand-400 to-blue-400 bg-clip-text text-transparent">4</span>
                    <span class="block text-[9px] sm:text-[10px] text-stone-400 uppercase tracking-wider font-semibold mt-0.5">Active Vehicles</span>
                </div>
                <div class="border-l border-stone-800/80 p-1">
                    <span class="text-lg sm:text-2xl font-extrabold font-display bg-gradient-to-r from-brand-400 to-blue-400 bg-clip-text text-transparent">5</span>
                    <span class="block text-[9px] sm:text-[10px] text-stone-400 uppercase tracking-wider font-semibold mt-0.5">Real Drivers</span>
                </div>
                <div class="border-t sm:border-t-0 sm:border-l border-stone-800/80 p-1">
                    <span class="text-lg sm:text-2xl font-extrabold font-display bg-gradient-to-r from-brand-400 to-blue-400 bg-clip-text text-transparent">100%</span>
                    <span class="block text-[9px] sm:text-[10px] text-stone-400 uppercase tracking-wider font-semibold mt-0.5">SMS Alerts</span>
                </div>
                <div class="border-t sm:border-t-0 border-l border-stone-800/80 p-1">
                    <span class="text-lg sm:text-2xl font-extrabold font-display bg-gradient-to-r from-brand-400 to-blue-400 bg-clip-text text-transparent">QR</span>
                    <span class="block text-[9px] sm:text-[10px] text-stone-400 uppercase tracking-wider font-semibold mt-0.5">Gate Clearance</span>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 border-t border-stone-900/60 text-center text-[10px] sm:text-xs text-stone-400 flex flex-col gap-1 justify-center items-center z-10">
            <p>&copy; {{ date('Y') }} Cagayan State University Lal-lo Campus. All rights reserved.</p>
            <p class="font-display opacity-40">PeliCle Fleet Management System</p>
        </footer>

    </body>
</html>
