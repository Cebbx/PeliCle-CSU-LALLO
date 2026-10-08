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

                <!-- Error & Success Alert Boxes -->
                <div id="login-error-alert" class="hidden p-3 bg-red-500/15 border border-red-500/30 text-red-300 text-xs rounded-xl mb-4 text-center font-medium shadow-sm"></div>
                <div id="login-success-alert" class="hidden p-3 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs rounded-xl mb-4 text-center font-medium shadow-sm"></div>

                <!-- STEP 1: Guard Name Selection & Official Email Entry -->
                <div id="step-guard-id">
                    <!-- Duty Guard Profile Selector Cards (No IDs displayed) -->
                    <div class="mb-4">
                        <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-2 text-left">Select Officer on Duty:</span>
                        @php
                            $guardsList = $activeGuards ?? \App\Http\Controllers\QrCodeController::getActiveGuards();
                            $firstGuard = reset($guardsList);
                            $firstGuardName = $firstGuard['name'] ?? 'Edward Cabbat';
                            $firstGuardEmail = $firstGuard['email'] ?? 'edwarddufale3@gmail.com';
                            $guardColors = [
                                ['border' => 'border-emerald-500/50', 'ring' => 'ring-2 ring-emerald-500/30', 'bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-400'],
                                ['border' => 'border-cyan-500/40', 'ring' => 'ring-2 ring-cyan-500/30', 'bg' => 'bg-cyan-500/20', 'text' => 'text-cyan-400'],
                                ['border' => 'border-purple-500/40', 'ring' => 'ring-2 ring-purple-500/30', 'bg' => 'bg-purple-500/20', 'text' => 'text-purple-400'],
                                ['border' => 'border-amber-500/40', 'ring' => 'ring-2 ring-amber-500/30', 'bg' => 'bg-amber-500/20', 'text' => 'text-amber-400'],
                                ['border' => 'border-rose-500/40', 'ring' => 'ring-2 ring-rose-500/30', 'bg' => 'bg-rose-500/20', 'text' => 'text-rose-400'],
                            ];
                            $gridColsClass = count($guardsList) > 3 ? 'grid-cols-2 sm:grid-cols-4' : (count($guardsList) === 2 ? 'grid-cols-2' : (count($guardsList) === 1 ? 'grid-cols-1' : 'grid-cols-3'));
                        @endphp
                        <div class="grid {{ $gridColsClass }} gap-2 text-center" id="guard-cards-container">
                            @foreach($guardsList as $gKey => $gData)
                                @php
                                    $isFirst = $loop->first;
                                    $c = $guardColors[$loop->index % count($guardColors)];
                                @endphp
                                <div onclick="selectGuard('{{ addslashes($gData['name']) }}', '{{ addslashes($gData['email']) }}', this)" 
                                     class="guard-select-card cursor-pointer {{ $isFirst ? 'bg-slate-950/90 border ' . $c['border'] . ' ' . $c['ring'] . ' shadow-md' : 'bg-slate-950/60 border border-slate-800/80 hover:' . $c['border'] }} rounded-2xl py-3 px-1.5 transition-all">
                                    <div class="w-8 h-8 rounded-full {{ $c['bg'] }} {{ $c['text'] }} border border-slate-700/50 flex items-center justify-center mx-auto mb-1.5 text-xs font-bold">
                                        👮
                                    </div>
                                    <span class="block text-xs font-extrabold {{ $isFirst ? 'text-white' : 'text-slate-300' }} truncate" title="{{ $gData['name'] }}">{{ $gData['name'] }}</span>
                                    <span class="block text-[9px] {{ $c['text'] }} font-semibold mt-0.5 truncate">{{ $gData['badge'] ?? 'Gate Officer' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Login Form Step 1: Email Entry -->
                    <form id="guard-login-form" onsubmit="handleGuardLogin(event)" class="space-y-4">
                        <input type="hidden" id="selected_guard_name" name="guard_name" value="{{ $firstGuardName }}">
                        <div>
                            <label for="guard_email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span id="email-field-label">Official Registered Email</span>
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono font-semibold">Verification</span>
                            </label>
                            <input 
                                type="email" 
                                id="guard_email" 
                                name="email" 
                                required 
                                autofocus 
                                autocomplete="email"
                                placeholder="Enter Gmail (e.g. {{ $firstGuardEmail }})" 
                                class="w-full px-4 py-3.5 bg-slate-950/90 border border-slate-700/90 rounded-2xl text-white placeholder-slate-600 text-center text-sm font-medium focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition shadow-inner"
                            />
                        </div>

                        <button 
                            type="submit" 
                            id="login-submit-btn" 
                            class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-950/40 cursor-pointer flex items-center justify-center gap-2 mt-4"
                        >
                            <span id="btn-text">Send Authentication Code &rarr;</span>
                            <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </form>
                </div>

                <!-- STEP 2: Email Authentication Code (OTP) Form -->
                <div id="step-guard-otp" class="hidden">
                    <div class="bg-slate-950/80 border border-emerald-500/30 rounded-2xl p-3.5 mb-4 text-left shadow-lg">
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm font-bold shrink-0">
                                📩
                            </div>
                            <div>
                                <span class="block text-xs font-extrabold text-white" id="otp-guard-name">Edward Cabbat</span>
                                <span class="block text-[10px] text-slate-400 font-mono" id="otp-masked-email">edw***@gmail.com</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-300 leading-snug">
                            A 6-digit authentication code was sent to your registered Gmail. Enter it below to authorize gate clearance access.
                        </p>
                    </div>

                    <form id="guard-otp-form" onsubmit="handleGuardOtp(event)" class="space-y-4">
                        <div>
                            <label for="guard_otp" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Enter 6-Digit Code</span>
                                </span>
                                <span class="text-[10px] text-amber-400 font-mono font-semibold">Expires in 10 mins</span>
                            </label>
                            <input 
                                type="text" 
                                id="guard_otp" 
                                name="guard_otp" 
                                required 
                                inputmode="numeric" 
                                pattern="[0-9]*"
                                maxlength="6"
                                autocomplete="one-time-code"
                                placeholder="000000" 
                                class="w-full px-4 py-3.5 bg-slate-950 border border-emerald-500/50 rounded-2xl text-white placeholder-slate-700 text-center text-2xl font-mono font-extrabold tracking-[0.4em] focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30 transition shadow-inner"
                            />
                        </div>

                        <button 
                            type="submit" 
                            id="otp-submit-btn" 
                            class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-950/40 cursor-pointer flex items-center justify-center gap-2 mt-4"
                        >
                            <span id="otp-btn-text">Verify Code & Enter Terminal</span>
                            <svg id="otp-btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>

                        <div class="flex items-center justify-between text-[11px] pt-2 px-1">
                            <button type="button" onclick="backToGuardId()" class="text-slate-400 hover:text-white transition-colors cursor-pointer font-semibold">
                                &larr; Change Guard ID
                            </button>
                            <button type="button" id="btn-resend-otp" onclick="resendOtpCode()" class="text-emerald-400 hover:underline transition-colors cursor-pointer font-semibold">
                                Resend Email Code
                            </button>
                        </div>
                    </form>
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
                                $todayPayload = $todayTrip->toGuardSummaryArray();
                                $vehName = $todayPayload['vehicle_name'];
                                $inStamp = $todayTrip->display_gate_in ? \Carbon\Carbon::parse($todayTrip->display_gate_in)->timezone('Asia/Manila')->format('g:i A') : '';
                            @endphp
                            <div class="flex items-center justify-between py-2 border-b border-slate-800/40 last:border-0 text-xs gap-2">
                                <div class="text-left min-w-0 flex-1">
                                    <span class="font-bold text-white block truncate">{{ $vehName }}</span>
                                    <span class="text-[10px] text-slate-400 block truncate">{{ $todayTrip->driver?->name ?? 'N/A' }} &bull; {{ $todayPayload['primary_destination'] }}</span>
                                </div>
                                <div class="text-right flex items-center gap-2 shrink-0">
                                    <div class="text-right">
                                        <span class="text-[10px] font-mono text-emerald-400 block font-bold">{{ $inStamp }}</span>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-300 border border-blue-500/20 font-semibold whitespace-nowrap">
                                            👮 {{ $todayTrip->display_scanned_by }}
                                        </span>
                                    </div>
                                    <button type="button" 
                                            onclick='openTripDetails(@json($todayPayload))' 
                                            title="View Trip & Passengers" 
                                            class="p-1.5 rounded-xl bg-slate-900 hover:bg-emerald-600/20 border border-slate-700 hover:border-emerald-500/50 text-slate-300 hover:text-emerald-400 transition cursor-pointer shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
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
                                    <th class="p-2.5 font-bold whitespace-nowrap text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                                @forelse($monthTrips as $trip)
                                    @php
                                        $tripPayload = $trip->toGuardSummaryArray();
                                        $vehName = $tripPayload['vehicle_name'];
                                        $outStamp = $tripPayload['out_time'];
                                        $inStamp = $tripPayload['in_time'];
                                        $guardWhoScanned = $tripPayload['scanned_by'];
                                    @endphp
                                    <tr class="hover:bg-slate-900/50 transition">
                                        <td class="p-2.5 font-mono font-bold text-amber-400 whitespace-nowrap">{{ $trip->formatted_ticket_number }}</td>
                                        <td class="p-2.5 font-bold text-white whitespace-nowrap">{{ $vehName }}</td>
                                        <td class="p-2.5 whitespace-nowrap text-slate-200">{{ $trip->driver?->name ?? 'N/A' }}</td>
                                        <td class="p-2.5 text-[11px] text-slate-400 max-w-[150px] truncate" title="{{ $tripPayload['primary_destination'] }}">{{ $tripPayload['primary_destination'] }}</td>
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
                                        <td class="p-2.5 text-center whitespace-nowrap">
                                            <button type="button" 
                                                    onclick='openTripDetails(@json($tripPayload))' 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-slate-900 hover:bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 hover:border-emerald-500/60 transition shadow-sm cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>View</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="p-8 text-center text-xs text-slate-500 font-semibold">
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

    <!-- ========================================================
         TRIP TRANSACTION & PASSENGER DETAILS MODAL
         ======================================================== -->
    <div id="trip-details-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-sm hidden transition-opacity duration-200">
        <div class="relative w-full max-w-2xl max-h-[90vh] bg-slate-900 border border-slate-700/80 rounded-3xl shadow-2xl flex flex-col overflow-hidden text-slate-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950/70">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg shrink-0">
                        📋
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="modal-ticket-no" class="text-sm sm:text-base font-mono font-bold text-amber-400 leading-tight">TT No. Lal-lo - 2026-001</h3>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                ✓ Cleared
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400">Campus Gate Transaction &bull; Passenger Clearance Record</p>
                    </div>
                </div>
                <button type="button" onclick="closeTripDetails()" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition cursor-pointer text-sm font-bold" title="Close Modal">
                    ✕
                </button>
            </div>

            <!-- Modal Scrollable Body -->
            <div class="p-5 overflow-y-auto space-y-4 text-xs">
                
                <!-- Vehicle & Driver Overview Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Vehicle Card -->
                    <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1">Vehicle Assignment</span>
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">🚗</span>
                            <div class="min-w-0">
                                <span id="modal-vehicle-name" class="font-bold text-white block text-sm truncate">Toyota Hilux</span>
                                <span id="modal-vehicle-plate" class="text-[11px] font-mono text-emerald-400 font-semibold block truncate">Plate: SAA-1234</span>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Card -->
                    <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1">Assigned Driver</span>
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">👤</span>
                            <div class="min-w-0">
                                <span id="modal-driver-name" class="font-bold text-white block text-sm truncate">Juan Driver</span>
                                <span id="modal-driver-meta" class="text-[10px] text-slate-400 block truncate">License: N/A &bull; Contact: N/A</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gate Movement Timestamps & Duty Officer -->
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-2">Gate Movement & Duty Log</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <div class="bg-slate-900/80 rounded-xl p-2.5 border border-cyan-500/20">
                            <span class="text-[9px] uppercase font-bold text-cyan-400 block">Departure (Gate OUT)</span>
                            <span id="modal-out-time" class="font-mono font-bold text-white block text-xs mt-0.5">---</span>
                        </div>
                        <div class="bg-slate-900/80 rounded-xl p-2.5 border border-emerald-500/20">
                            <span class="text-[9px] uppercase font-bold text-emerald-400 block">Arrival (Gate IN)</span>
                            <span id="modal-in-time" class="font-mono font-bold text-emerald-400 block text-xs mt-0.5">---</span>
                        </div>
                        <div class="bg-slate-900/80 rounded-xl p-2.5 border border-blue-500/20">
                            <span class="text-[9px] uppercase font-bold text-blue-400 block">Cleared By (Officer)</span>
                            <span id="modal-scanned-by" class="font-bold text-blue-300 block text-xs mt-0.5 truncate">👮 Guard</span>
                        </div>
                    </div>
                </div>

                <!-- PASSENGERS & REQUISITION BREAKDOWN -->
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5">
                    <div class="flex items-center justify-between mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-300">Authorized Passengers</span>
                            <span id="modal-total-pax-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                0 Persons
                            </span>
                        </div>
                        <span id="modal-carpool-badge" class="hidden text-[10px] px-2 py-0.5 rounded-full font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            👥 Multi-Dept Carpool
                        </span>
                    </div>

                    <!-- Dynamic container for request / passenger cards -->
                    <div id="modal-requests-container" class="space-y-3">
                        <!-- Injected via JavaScript -->
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-800 bg-slate-950/90 gap-2">
                <a id="modal-print-btn" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 hover:border-slate-600 transition cursor-pointer">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    <span>Print Trip Ticket</span>
                </a>

                <button type="button" onclick="closeTripDetails()" class="px-5 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-lg shadow-emerald-950/30 cursor-pointer">
                    Close
                </button>
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

            // Selection handler for Guard Profile Cards
            let currentSelectedGuard = 'Edward Cabbat';

            window.selectGuard = function(name, email, cardElement) {
                currentSelectedGuard = name;
                const hiddenInput = document.getElementById('selected_guard_name');
                if (hiddenInput) hiddenInput.value = name;

                const label = document.getElementById('email-field-label');
                if (label) label.innerText = "Enter Official Email for " + name;

                const emailInput = document.getElementById('guard_email');
                if (emailInput) {
                    emailInput.placeholder = "Enter " + name + "'s Gmail";
                    emailInput.focus();
                }

                // Update visual card selection
                document.querySelectorAll('.guard-select-card').forEach(c => {
                    c.classList.remove('border-emerald-500/50', 'ring-2', 'ring-emerald-500/30', 'bg-slate-950/90');
                    c.classList.add('border-slate-800/80', 'bg-slate-950/60');
                    const text = c.querySelector('span.truncate');
                    if (text) {
                        text.classList.remove('text-white');
                        text.classList.add('text-slate-300');
                    }
                });

                if (cardElement) {
                    cardElement.classList.add('border-emerald-500/50', 'ring-2', 'ring-emerald-500/30', 'bg-slate-950/90');
                    cardElement.classList.remove('border-slate-800/80', 'bg-slate-950/60');
                    const text = cardElement.querySelector('span.truncate');
                    if (text) {
                        text.classList.add('text-white');
                        text.classList.remove('text-slate-300');
                    }
                }
            };


            window.backToGuardId = function() {
                document.getElementById('step-guard-otp').classList.add('hidden');
                document.getElementById('step-guard-id').classList.remove('hidden');
                const err = document.getElementById('login-error-alert');
                const succ = document.getElementById('login-success-alert');
                if (err) err.classList.add('hidden');
                if (succ) succ.classList.add('hidden');
                const emailInput = document.getElementById('guard_email');
                if (emailInput) emailInput.focus();
            };

            // STEP 1: Submit Guard Email -> Generates & Sends OTP
            window.handleGuardLogin = function(e) {
                e.preventDefault();
                const emailInput = document.getElementById('guard_email');
                const selectedGuardName = document.getElementById('selected_guard_name') ? document.getElementById('selected_guard_name').value : 'Edward Cabbat';
                const errorAlert = document.getElementById('login-error-alert');
                const successAlert = document.getElementById('login-success-alert');
                const submitBtn = document.getElementById('login-submit-btn');
                const btnText = document.getElementById('btn-text');
                const btnSpinner = document.getElementById('btn-spinner');

                const email = emailInput ? emailInput.value.trim() : '';

                if (!email) {
                    errorAlert.innerText = "Please enter your registered security guard email address.";
                    errorAlert.classList.remove('hidden');
                    return;
                }

                // UI loading state
                errorAlert.classList.add('hidden');
                if (successAlert) successAlert.classList.add('hidden');
                submitBtn.disabled = true;
                btnText.innerText = "Sending code to email...";
                btnSpinner.classList.remove('hidden');

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("guard.verify-pin") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ 
                        email: email,
                        guard_name: selectedGuardName
                    })
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    btnText.innerText = "Send Authentication Code \u2192";
                    btnSpinner.classList.add('hidden');

                    if (data.success) {
                        if (data.requires_otp) {
                            // Transition to Step 2: OTP Entry
                            document.getElementById('step-guard-id').classList.add('hidden');
                            document.getElementById('step-guard-otp').classList.remove('hidden');

                            document.getElementById('otp-guard-name').innerText = "👮 " + data.guard_name;
                            document.getElementById('otp-masked-email').innerText = data.email || data.masked_email;

                            if (successAlert) {
                                successAlert.innerText = "Verification code dispatched to " + (data.masked_email || data.email) + "! Check your Gmail inbox.";
                                successAlert.classList.remove('hidden');
                            }

                            const otpInput = document.getElementById('guard_otp');
                            if (otpInput) {
                                otpInput.value = '';
                                otpInput.focus();
                            }
                        } else {
                            // Direct bypass (e.g. 1234 master code)
                            completeDutySignIn(data.guard_name);
                        }
                    } else {
                        // Failed authentication
                        errorAlert.innerText = data.message || "Email not recognized. Please check your entered email.";
                        errorAlert.classList.remove('hidden');
                        if (emailInput) {
                            emailInput.select();
                            emailInput.focus();
                        }
                    }
                })
                .catch(() => {
                    errorAlert.innerText = "Network connection error! Please check your connection and try again.";
                    errorAlert.classList.remove('hidden');
                    submitBtn.disabled = false;
                    btnText.innerText = "Send Authentication Code \u2192";
                    btnSpinner.classList.add('hidden');
                });
            };

            // STEP 2: Submit OTP Code -> Verifies identity and enters gate terminal
            window.handleGuardOtp = function(e) {
                e.preventDefault();
                const otpInput = document.getElementById('guard_otp');
                const errorAlert = document.getElementById('login-error-alert');
                const successAlert = document.getElementById('login-success-alert');
                const submitBtn = document.getElementById('otp-submit-btn');
                const btnText = document.getElementById('otp-btn-text');
                const btnSpinner = document.getElementById('otp-btn-spinner');

                const otp = otpInput ? otpInput.value.trim() : '';

                if (!otp || otp.length < 4) {
                    errorAlert.innerText = "Please enter the 6-digit authentication code.";
                    errorAlert.classList.remove('hidden');
                    return;
                }

                errorAlert.classList.add('hidden');
                if (successAlert) successAlert.classList.add('hidden');
                submitBtn.disabled = true;
                btnText.innerText = "Verifying code...";
                btnSpinner.classList.remove('hidden');

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("guard.verify-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ 
                        otp: otp
                    })
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    btnText.innerText = "Verify Code & Enter Terminal";
                    btnSpinner.classList.add('hidden');

                    if (data.success) {
                        completeDutySignIn(data.guard_name);
                    } else {
                        errorAlert.innerText = data.message || "Invalid authentication code. Please try again.";
                        errorAlert.classList.remove('hidden');
                        if (otpInput) {
                            otpInput.select();
                            otpInput.focus();
                        }
                        if (data.restart) {
                            setTimeout(backToGuardId, 2000);
                        }
                    }
                })
                .catch(() => {
                    errorAlert.innerText = "Network connection error! Please try again.";
                    errorAlert.classList.remove('hidden');
                    submitBtn.disabled = false;
                    btnText.innerText = "Verify Code & Enter Terminal";
                    btnSpinner.classList.add('hidden');
                });
            };

            // Resend OTP
            window.resendOtpCode = function() {
                const btnResend = document.getElementById('btn-resend-otp');
                const errorAlert = document.getElementById('login-error-alert');
                const successAlert = document.getElementById('login-success-alert');

                btnResend.innerText = "Resending...";
                btnResend.disabled = true;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("guard.resend-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => res.json())
                .then(data => {
                    btnResend.innerText = "Resend Email Code";
                    btnResend.disabled = false;

                    if (data.success) {
                        if (successAlert) {
                            successAlert.innerText = data.message + " Check your Gmail inbox.";
                            successAlert.classList.remove('hidden');
                        }
                        errorAlert.classList.add('hidden');
                    } else {
                        errorAlert.innerText = data.message;
                        errorAlert.classList.remove('hidden');
                        if (data.restart) backToGuardId();
                    }
                })
                .catch(() => {
                    btnResend.innerText = "Resend Email Code";
                    btnResend.disabled = false;
                    errorAlert.innerText = "Failed to resend code. Please check your connection.";
                    errorAlert.classList.remove('hidden');
                });
            };

            function completeDutySignIn(guardName) {
                const loginScreen = document.getElementById('login-screen');
                if (guardName) {
                    const guardLabel = document.getElementById('active-guard-label');
                    if (guardLabel) guardLabel.innerText = "👮 " + guardName;
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
            }
 
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

            // ========================================================
            // TRIP DETAILS & PASSENGER INSPECTION MODAL LOGIC
            // ========================================================
            window.openTripDetails = function(trip) {
                if (!trip) return;

                const modal = document.getElementById('trip-details-modal');
                if (!modal) return;

                // Basic Info
                document.getElementById('modal-ticket-no').innerText = trip.ticket_number || ('Trip #' + trip.id);
                document.getElementById('modal-vehicle-name').innerText = trip.vehicle_name || 'Vehicle';
                document.getElementById('modal-vehicle-plate').innerText = 'Plate: ' + (trip.vehicle_plate || 'N/A');
                document.getElementById('modal-driver-name').innerText = trip.driver_name || 'N/A';
                
                let driverMeta = [];
                if (trip.driver_license) driverMeta.push('License: ' + trip.driver_license);
                if (trip.driver_contact) driverMeta.push('Contact: ' + trip.driver_contact);
                document.getElementById('modal-driver-meta').innerText = driverMeta.length > 0 ? driverMeta.join(' • ') : 'Official Campus Driver';

                // Gate Movement & Duty Officer
                document.getElementById('modal-out-time').innerText = trip.out_time || '---';
                document.getElementById('modal-in-time').innerText = trip.in_time || '---';
                document.getElementById('modal-scanned-by').innerText = '👮 ' + (trip.scanned_by || 'Duty Guard');

                // Carpool & Total Pax Badges
                const totalPaxBadge = document.getElementById('modal-total-pax-badge');
                if (totalPaxBadge) {
                    totalPaxBadge.innerText = (trip.total_passengers || 1) + ((trip.total_passengers || 1) > 1 ? ' Persons' : ' Person');
                }

                const carpoolBadge = document.getElementById('modal-carpool-badge');
                if (carpoolBadge) {
                    if (trip.is_carpool) {
                        carpoolBadge.classList.remove('hidden');
                    } else {
                        carpoolBadge.classList.add('hidden');
                    }
                }

                // Print Link
                const printBtn = document.getElementById('modal-print-btn');
                if (printBtn) {
                    printBtn.href = trip.print_url || '#';
                }

                // Render Requisitions and Passenger Breakdown
                const container = document.getElementById('modal-requests-container');
                if (container) {
                    container.innerHTML = '';
                    const requests = trip.requests || [];
                    if (requests.length === 0) {
                        container.innerHTML = '<div class="text-center text-slate-500 py-3 text-xs">No passenger records found for this trip.</div>';
                    } else {
                        requests.forEach(req => {
                            const card = document.createElement('div');
                            card.className = 'bg-slate-900/90 border border-slate-800 rounded-2xl p-3.5 space-y-2.5';

                            // Header with Department & Pax Count
                            let headerHtml = `
                                <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            ${escapeHtml(req.department || 'Department')}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">(${escapeHtml(req.request_number || '')})</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-amber-400 font-mono bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                                        ${req.passenger_count || 1} ${(req.passenger_count || 1) > 1 ? 'passengers' : 'passenger'}
                                    </span>
                                </div>
                            `;

                            // Requester, Destination, Purpose
                            let detailsHtml = `
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-300">
                                    <div><span class="text-slate-500">Requester:</span> <strong class="text-white">${escapeHtml(req.requester || 'N/A')}</strong></div>
                                    <div><span class="text-slate-500">Destination:</span> <strong class="text-emerald-300">${escapeHtml(req.destination || 'N/A')}</strong></div>
                                    <div class="sm:col-span-2"><span class="text-slate-500">Purpose:</span> <span class="text-slate-200 italic">${escapeHtml(req.purpose || 'Official Campus Business')}</span></div>
                                </div>
                            `;

                            // Passenger List
                            let paxChipsHtml = '';
                            const names = req.passenger_names || [];
                            if (names.length > 0) {
                                paxChipsHtml = `
                                    <div class="pt-2 border-t border-slate-800/60">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 block mb-1.5">Authorized Passengers:</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            ${names.map(name => `
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-medium bg-slate-950 border border-slate-800 text-slate-200 shadow-sm">
                                                    <span class="text-[10px] text-emerald-400">👤</span> ${escapeHtml(name)}
                                                </span>
                                            `).join('')}
                                        </div>
                                    </div>
                                `;
                            } else {
                                paxChipsHtml = `
                                    <div class="pt-2 border-t border-slate-800/60">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 block mb-1">Primary Passenger:</span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-medium bg-slate-950 border border-slate-800 text-slate-200 shadow-sm">
                                            <span class="text-[10px] text-emerald-400">👤</span> ${escapeHtml(req.requester || 'Requester')}
                                        </span>
                                    </div>
                                `;
                            }

                            // Others / Students
                            let othersHtml = '';
                            if (req.has_other_passengers && req.other_passengers) {
                                othersHtml = `
                                    <div class="mt-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                                        <strong class="uppercase text-[9px] text-amber-400 block font-bold mb-0.5">Others / Students / Co-Passengers:</strong>
                                        <span>${escapeHtml(req.other_passengers)}</span>
                                    </div>
                                `;
                            }

                            card.innerHTML = headerHtml + detailsHtml + paxChipsHtml + othersHtml;
                            container.appendChild(card);
                        });
                    }
                }

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            window.closeTripDetails = function() {
                const modal = document.getElementById('trip-details-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
                document.body.classList.remove('overflow-hidden');
            };

            // Close on backdrop click
            const modalEl = document.getElementById('trip-details-modal');
            if (modalEl) {
                modalEl.addEventListener('click', function(e) {
                    if (e.target === modalEl) {
                        closeTripDetails();
                    }
                });
            }

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTripDetails();
                }
            });

            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }

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
