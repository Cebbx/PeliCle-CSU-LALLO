@php
    $user = \Filament\Facades\Filament::auth()->user() 
        ?? auth('admin')->user() 
        ?? auth('driver')->user();

    // STRICT GUARD 1: Guest / Unauthenticated check (Never run on login screens or for guests)
    if (! $user) {
        return;
    }

    // STRICT GUARD 2: Explicitly stop if current route is a login or authentication page
    $currentPath = request()->path();
    if (str_contains($currentPath, 'login') || str_contains($currentPath, 'password') || str_contains($currentPath, 'auth')) {
        return;
    }

    // STRICT GUARD 3: Strict Role Filter
    // - Admin: Always allowed
    // - Driver: ONLY if they have an active or pending trip ticket
    // - Employees & other roles: Completely disabled from live alert chimes/popups
    if ($user->role === 'driver') {
        $driver = $user->driver 
            ?? \App\Models\Driver::where('name', $user->name)->first()
            ?? ($user->email ? \App\Models\Driver::where('license_number', $user->email)->first() : null);

        $hasTrip = $driver ? \App\Models\TripTicket::where('driver_id', $driver->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists() : false;

        if (! $hasTrip) {
            return;
        }
    } elseif ($user->role !== 'admin') {
        return;
    }
@endphp
{{-- Real-Time System Audio Alert & Live Notification Engine (Strictly Admin & Active Drivers Only) --}}
<style>
    /* ========================================================
       CRIMSON RED UNREAD NOTIFICATION BADGE & PULSE
       ======================================================== */
    .fi-topbar-database-notifications-btn {
        position: relative !important;
    }

    /* Glowing Crimson Red Unread Badge on Topbar Bell */
    .fi-topbar-database-notifications-btn .fi-badge,
    .fi-topbar-database-notifications-btn .fi-badge-color-primary,
    .fi-topbar-database-notifications-btn .fi-badge-color-danger,
    .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn .fi-badge,
    .pelicle-unread-badge {
        background-color: #ef4444 !important;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 10px !important;
        padding: 2px 6px !important;
        min-width: 18px !important;
        height: 18px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1.5px solid #ffffff !important;
        border-radius: 9999px !important;
        box-shadow: 0 0 6px rgba(239, 68, 68, 0.6), 0 2px 4px rgba(0, 0, 0, 0.4) !important;
    }

    html.dark .fi-topbar-database-notifications-btn .fi-badge,
    html.dark .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn .fi-badge,
    html.dark .pelicle-unread-badge {
        border-color: #0f172a !important;
    }

    /* Option A (Ring & Settle): Bell icon color highlight when unread */
    .fi-topbar-database-notifications-btn.has-unread-alerts svg,
    .fi-topbar-database-notifications-btn:has(.fi-badge) svg {
        color: #f59e0b !important;
        transform-origin: top center;
    }

    /* Option A: Only swings 2-3 times when new notification arrives, then settles completely */
    .fi-topbar-database-notifications-btn.bell-ringing svg {
        animation: bellSwingAlert 1.5s ease-in-out !important;
        transform-origin: top center;
    }

    /* Subtle badge pop when bell rings */
    .fi-topbar-database-notifications-btn.bell-ringing .fi-badge,
    .fi-topbar-database-notifications-btn.bell-ringing .pelicle-unread-badge {
        animation: badgePopOnce 0.6s ease-out !important;
    }

    @keyframes badgePopOnce {
        0% { transform: scale(0.85); }
        50% { transform: scale(1.25); }
        100% { transform: scale(1); }
    }

    @keyframes bellSwingAlert {
        0% { transform: rotate(0deg); }
        15% { transform: rotate(14deg); }
        30% { transform: rotate(-12deg); }
        45% { transform: rotate(9deg); }
        60% { transform: rotate(-7deg); }
        75% { transform: rotate(3deg); }
        90% { transform: rotate(-1deg); }
        100% { transform: rotate(0deg); }
    }

    /* Floating Real-Time Live Alert Toast (Appears anywhere in panel) */
    #pelicle-live-toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 380px;
        pointer-events: none;
    }

    .pelicle-live-toast {
        pointer-events: auto;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(12px);
        border: 1.5px solid #ef4444;
        border-radius: 14px;
        padding: 13px 16px;
        color: #ffffff;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.6), 0 0 16px rgba(239, 68, 68, 0.35);
        display: flex;
        align-items: flex-start;
        gap: 12px;
        animation: toastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .pelicle-live-toast.toast-standard {
        border-color: #f59e0b;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.6), 0 0 16px rgba(245, 158, 11, 0.35);
    }

    @keyframes toastSlideIn {
        from { opacity: 0; transform: translateY(-20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>

<div id="pelicle-live-toast-container"></div>

<script>
    (function() {
        // Immediate exit if on login or auth screens
        if (window.location.pathname.includes('/login') || window.location.pathname.includes('/password') || window.location.pathname.includes('/auth')) {
            return;
        }

        // 1. iOS Safari (iPhone 11) + Android + Desktop WAV Ringtone & WebAudio Engine
        let audioCtx = null;
        let iosAudioUnlocked = false;

        // Build real 16-bit PCM WAV data URI in memory so HTML5 <audio> plays on iPhone Media Speaker
        function buildWavDataUri(notesList, totalDurationSec) {
            try {
                const sampleRate = 22050;
                const numSamples = Math.floor(sampleRate * totalDurationSec);
                const buffer = new ArrayBuffer(44 + numSamples * 2);
                const view = new DataView(buffer);

                function writeStr(offset, str) {
                    for (let i = 0; i < str.length; i++) view.setUint8(offset + i, str.charCodeAt(i));
                }

                writeStr(0, 'RIFF');
                view.setUint32(4, 36 + numSamples * 2, true);
                writeStr(8, 'WAVE');
                writeStr(12, 'fmt ');
                view.setUint32(16, 16, true);
                view.setUint16(20, 1, true); // PCM
                view.setUint16(22, 1, true); // Mono
                view.setUint32(24, sampleRate, true);
                view.setUint32(28, sampleRate * 2, true);
                view.setUint16(32, 2, true);
                view.setUint16(34, 16, true);
                writeStr(36, 'data');
                view.setUint32(40, numSamples * 2, true);

                for (let i = 0; i < numSamples; i++) {
                    const t = i / sampleRate;
                    let sample = 0;
                    for (let n = 0; n < notesList.length; n++) {
                        const note = notesList[n];
                        if (t >= note.t && t <= note.t + note.d) {
                            const localT = t - note.t;
                            const env = Math.exp(-localT * (4.5 / note.d));
                            // Fundamental + harmonic overtone for rich iPhone marimba/bell ringtone quality
                            sample += (Math.sin(2 * Math.PI * note.f * localT) * 0.75 +
                                       Math.sin(2 * Math.PI * (note.f * 2) * localT) * 0.25) * note.g * env;
                        }
                    }
                    sample = Math.max(-1, Math.min(1, sample));
                    view.setInt16(44 + i * 2, sample < 0 ? sample * 0x8000 : sample * 0x7FFF, true);
                }

                const bytes = new Uint8Array(buffer);
                let binary = '';
                const chunkSize = 8192;
                for (let i = 0; i < bytes.length; i += chunkSize) {
                    binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
                }
                return 'data:audio/wav;base64,' + btoa(binary);
            } catch (e) {
                return null;
            }
        }

        // Musical 2-bar Ringtone Chime (C5 - E5 - G5 - C6 | E5 - G5 - C6 - E6)
        const chimeNotes = [
            { f: 523.25, t: 0.00, d: 0.40, g: 0.55 },
            { f: 659.25, t: 0.14, d: 0.40, g: 0.55 },
            { f: 783.99, t: 0.28, d: 0.45, g: 0.60 },
            { f: 1046.50, t: 0.42, d: 0.65, g: 0.70 },
            { f: 659.25, t: 0.90, d: 0.35, g: 0.55 },
            { f: 783.99, t: 1.04, d: 0.35, g: 0.60 },
            { f: 1046.50, t: 1.18, d: 0.45, g: 0.65 },
            { f: 1318.51, t: 1.32, d: 0.80, g: 0.75 }
        ];

        const alarmNotes = [
            { f: 920, t: 0.00, d: 0.22, g: 0.75 },
            { f: 680, t: 0.22, d: 0.22, g: 0.75 },
            { f: 920, t: 0.44, d: 0.22, g: 0.75 },
            { f: 680, t: 0.66, d: 0.22, g: 0.75 },
            { f: 920, t: 0.88, d: 0.22, g: 0.75 },
            { f: 680, t: 1.10, d: 0.30, g: 0.75 }
        ];

        const chimeWavUri = buildWavDataUri(chimeNotes, 2.15);
        const alarmWavUri = buildWavDataUri(alarmNotes, 1.45);
        const chimeAudioEl = chimeWavUri ? new Audio(chimeWavUri) : null;
        const alarmAudioEl = alarmWavUri ? new Audio(alarmWavUri) : null;
        if (chimeAudioEl) { chimeAudioEl.preload = 'auto'; chimeAudioEl.playsInline = true; }
        if (alarmAudioEl) { alarmAudioEl.preload = 'auto'; alarmAudioEl.playsInline = true; }

        function getAudioContext() {
            if (!audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    audioCtx = new AudioContextClass();
                }
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume().catch(() => {});
            }
            return audioCtx;
        }

        function unlockAudio() {
            try {
                const ctx = getAudioContext();
                if (ctx) {
                    if (ctx.state === 'suspended') ctx.resume().catch(() => {});
                    if (!iosAudioUnlocked) {
                        const buf = ctx.createBuffer(1, 1, 22050);
                        const src = ctx.createBufferSource();
                        src.buffer = buf;
                        src.connect(ctx.destination);
                        if (src.start) src.start(0);
                    }
                }
                if (!iosAudioUnlocked) {
                    iosAudioUnlocked = true;
                    [chimeAudioEl, alarmAudioEl].forEach(el => {
                        if (el) {
                            el.muted = true;
                            const p = el.play();
                            if (p && typeof p.then === 'function') {
                                p.then(() => {
                                    el.pause();
                                    el.currentTime = 0;
                                    el.muted = false;
                                }).catch(() => { el.muted = false; });
                            } else {
                                el.pause();
                                el.currentTime = 0;
                                el.muted = false;
                            }
                        }
                    });
                }
            } catch (e) {}
        }
        ['touchend', 'pointerup', 'click', 'keydown'].forEach(evt => {
            window.addEventListener(evt, unlockAudio, { passive: true });
        });

        // 2. High Quality Ringtone Chime & Alarm Sound Generator (Plays HTML5 WAV + WebAudio)
        function playSystemNotificationSound(isEmergency) {
            // Play HTML5 <audio> element first (works on iPhone 11 speaker even when silent switch is on)
            try {
                const targetAudio = isEmergency ? alarmAudioEl : chimeAudioEl;
                if (targetAudio) {
                    targetAudio.muted = false;
                    targetAudio.volume = 1.0;
                    targetAudio.currentTime = 0;
                    targetAudio.play().catch(() => {});
                }
            } catch (e) {}

            // Also layer Web Audio API synth
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                const activeNotes = isEmergency ? alarmNotes : chimeNotes;

                activeNotes.forEach(n => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = isEmergency ? 'sawtooth' : 'sine';
                    osc.frequency.setValueAtTime(n.f, now + n.t);
                    gain.gain.setValueAtTime(n.g * 0.6, now + n.t);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + n.t + n.d);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now + n.t);
                    osc.stop(now + n.t + n.d);
                });
            } catch (e) {
                console.warn('PeliCle audio alert error:', e);
            }
        }

        // Option A (Ring & Settle): Rings 2-3 times on incoming event, then settles completely
        function ringBellIconBriefly() {
            const bellBtn = document.querySelector('.fi-topbar-database-notifications-btn');
            if (!bellBtn) return;
            bellBtn.classList.remove('bell-ringing');
            void bellBtn.offsetWidth; // Force CSS reflow to replay animation cleanly
            bellBtn.classList.add('bell-ringing');
            setTimeout(() => {
                bellBtn.classList.remove('bell-ringing');
            }, 1600);
        }

        // Immediately stop bell ringing if user clicks or opens the notification panel
        document.addEventListener('click', (e) => {
            const bell = e.target.closest('.fi-topbar-database-notifications-btn');
            if (bell) {
                bell.classList.remove('bell-ringing');
            }
        }, { passive: true });

        // Global manual testers
        window.testBellSound = function() {
            unlockAudio();
            playSystemNotificationSound(false);
            ringBellIconBriefly();
        };
        window.testAlarmSound = function() {
            unlockAudio();
            playSystemNotificationSound(true);
            ringBellIconBriefly();
        };
        window.testPeliCleSound = function(emergency = false) {
            unlockAudio();
            playSystemNotificationSound(emergency);
            ringBellIconBriefly();
        };

        // 3. Desktop Browser Notification Permission
        function requestDesktopPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }
        if ('Notification' in window && Notification.permission === 'default') {
            document.addEventListener('click', requestDesktopPermission, { once: true });
        }

        function showDesktopNotification(title, body) {
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    const notif = new Notification(title || '🔔 PeliCle: Bagong Transaksyon', {
                        body: body || 'May bagong aktibidad o request na pumasok sa system.',
                        icon: '/csu-logo-sm.png'
                    });
                    notif.onclick = function() {
                        window.focus();
                        openNotificationsOrList();
                    };
                } catch (e) {}
            }
        }

        function openNotificationsOrList() {
            const bellBtn = document.querySelector('.fi-topbar-database-notifications-btn');
            if (bellBtn) {
                bellBtn.click();
            } else {
                const path = window.location.pathname;
                if (path.startsWith('/driver')) window.location.href = '/driver/trip-tickets';
                else if (path.startsWith('/employee')) window.location.href = '/employee/vehicle-requests';
                else window.location.href = '/admin/vehicle-requests';
            }
        }

        // 4. In-App Floating Toast Banner (Deduplicated & Self-Dismissing)
        function showFloatingToast(notif) {
            if (!notif || !notif.id) return;

            let container = document.getElementById('pelicle-live-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'pelicle-live-toast-container';
                document.body.appendChild(container);
            }

            // Prevent duplicate toasts for the same notification
            const existing = container.querySelector('[data-toast-id="' + notif.id + '"]');
            if (existing) return;

            const toast = document.createElement('div');
            toast.setAttribute('data-toast-id', notif.id);
            const isEmerg = notif && notif.is_emergency;
            toast.className = 'pelicle-live-toast ' + (isEmerg ? 'toast-emergency' : 'toast-standard');
            toast.innerHTML = `
                <div style="font-size: 22px; line-height: 1; flex-shrink: 0;">${isEmerg ? '🚨' : '🔔'}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 13px; font-weight: 800; color: #ffffff; margin-bottom: 2px;">
                        ${notif.title || 'Bagong Notipikasyon sa System'}
                    </div>
                    <div style="font-size: 11.5px; color: #cbd5e1; line-height: 1.35; margin-bottom: 4px;">
                        ${notif.body || 'I-click upang buksan ang buong detalye.'}
                    </div>
                    <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">
                        ${notif.created_at || 'Just now'} &bull; Click to open
                    </div>
                </div>
                <button type="button" style="background: transparent; border: none; color: #94a3b8; font-size: 16px; font-weight: bold; cursor: pointer; padding: 0 4px;" title="Close">&times;</button>
            `;

            toast.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                toast.remove();
            });

            toast.addEventListener('click', () => {
                openNotificationsOrList();
            });

            container.appendChild(toast);
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => toast.remove(), 250);
                }
            }, 4500);
        }

        // 5. Update Topbar Bell Badge Indicator
        function updateTopbarBellBadge(count) {
            const bellBtn = document.querySelector('.fi-topbar-database-notifications-btn');
            if (!bellBtn) return;

            let badgeSpan = bellBtn.querySelector('.fi-badge, .pelicle-unread-badge');
            if (count > 0) {
                bellBtn.classList.add('has-unread-alerts');
                if (!badgeSpan) {
                    let badgeCtn = bellBtn.querySelector('.fi-icon-btn-badge-ctn');
                    if (!badgeCtn) {
                        badgeCtn = document.createElement('div');
                        badgeCtn.className = 'fi-icon-btn-badge-ctn';
                        bellBtn.appendChild(badgeCtn);
                    }
                    badgeSpan = document.createElement('span');
                    badgeSpan.className = 'fi-badge pelicle-unread-badge';
                    badgeCtn.appendChild(badgeSpan);
                }
                badgeSpan.textContent = count;
                badgeSpan.style.display = 'inline-flex';
            } else {
                bellBtn.classList.remove('has-unread-alerts');
                bellBtn.classList.remove('bell-ringing');
                if (badgeSpan) {
                    badgeSpan.style.display = 'none';
                }
            }
        }

        // 6. Polling & Event Loop (Foolproof Single-Alert System)
        const originalTitle = document.title;
        const currentPath = window.location.pathname;
        const activePanel = currentPath.startsWith('/employee') ? 'employee' : (currentPath.startsWith('/driver') ? 'driver' : 'admin');
        const seenStorageKey = 'pelicle_seen_notifs_' + activePanel;

        let seenNotifIds = [];
        try {
            seenNotifIds = JSON.parse(localStorage.getItem(seenStorageKey) || '[]');
            if (!Array.isArray(seenNotifIds)) seenNotifIds = [];
        } catch (e) {
            seenNotifIds = [];
        }

        let isFirstCheck = true;
        let isChecking = false;

        async function checkLiveNotifications() {
            if (isChecking || document.hidden) return;
            isChecking = true;

            try {
                const res = await fetch('/api/admin/live-notifications-check?panel=' + encodeURIComponent(activePanel), {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const data = await res.json();
                const currentCount = parseInt(data.count, 10) || 0;
                const latest = data.latest || null;
                const latestId = latest ? String(latest.id) : null;
                const unreadList = Array.isArray(data.unread) ? data.unread : [];

                // Update tab title
                if (currentCount > 0) {
                    document.title = '(' + currentCount + ') ' + originalTitle.replace(/^\(\d+\)\s*/, '');
                } else {
                    document.title = originalTitle.replace(/^\(\d+\)\s*/, '');
                }

                // Update bell icon badge
                updateTopbarBellBadge(currentCount);

                // On first load: register all currently existing unread notifications as already seen
                // This ensures old pre-existing notifications DO NOT chime or pop up upon login/refresh!
                if (isFirstCheck) {
                    isFirstCheck = false;
                    unreadList.forEach(item => {
                        if (item && item.id && !seenNotifIds.includes(String(item.id))) {
                            seenNotifIds.push(String(item.id));
                        }
                    });
                    try {
                        if (seenNotifIds.length > 60) seenNotifIds = seenNotifIds.slice(-60);
                        localStorage.setItem(seenStorageKey, JSON.stringify(seenNotifIds));
                    } catch (e) {}
                    return;
                }

                // Only alert if a genuinely new, unseen notification arrives in real-time
                const isNewUnseen = latestId && !seenNotifIds.includes(latestId);

                if (isNewUnseen && latest && currentCount > 0) {
                    // Mark as seen immediately in localStorage so it NEVER rings or pops up twice
                    seenNotifIds.push(latestId);
                    try {
                        if (seenNotifIds.length > 60) seenNotifIds = seenNotifIds.slice(-60);
                        localStorage.setItem(seenStorageKey, JSON.stringify(seenNotifIds));
                    } catch (e) {}

                    const isEmerg = latest.is_emergency || false;
                    playSystemNotificationSound(isEmerg);
                    ringBellIconBriefly();

                    if ('vibrate' in navigator) {
                        try { navigator.vibrate([200, 100, 200]); } catch(e) {}
                    }
                    showDesktopNotification(latest.title, latest.body);
                    showFloatingToast(latest);

                    // Instant Live Sync: trigger immediate refresh on tables & dashboards (only if user is not typing)
                    const isTyping = Boolean(document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName));
                    const hasOpenDropdown = document.querySelector('.fi-dropdown-panel') || document.querySelector('[aria-expanded="true"]');
                    if (window.Livewire && !isTyping && !hasOpenDropdown && !window.location.pathname.includes('/login') && !window.location.pathname.endsWith('/create') && !window.location.pathname.endsWith('/edit')) {
                        try {
                            Livewire.dispatch('$refresh');
                        } catch (err) {}
                    }
                }
            } catch (err) {
                // Ignore network errors
            } finally {
                isChecking = false;
            }
        }

        // Smooth live sync polling every 3 seconds
        setTimeout(checkLiveNotifications, 800);
        setInterval(checkLiveNotifications, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) checkLiveNotifications();
        });

        // Also check whenever Livewire commits
        if (window.Livewire) {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(checkLiveNotifications, 600);
                });
            });
        }
    })();
</script>
