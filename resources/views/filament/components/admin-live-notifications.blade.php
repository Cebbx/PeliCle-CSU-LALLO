@php
    $user = \Filament\Facades\Filament::auth()->user() 
        ?? auth('admin')->user() 
        ?? auth('employee')->user()
        ?? auth('driver')->user()
        ?? auth()->user();

    // STRICT GUARD 1: Guest / Unauthenticated check (Never run on login screens or for guests)
    if (! $user) {
        return;
    }

    // STRICT GUARD 2: Explicitly stop if current route is a login or authentication page
    $currentPath = request()->path();
    if (str_contains($currentPath, 'login') || str_contains($currentPath, 'password') || str_contains($currentPath, 'auth')) {
        return;
    }
@endphp
{{-- Real-Time System Audio Alert & Live Notification Engine (Admin, Driver, and Employee) --}}
<style>
    /* ========================================================
       CRIMSON RED UNREAD NOTIFICATION BADGE & PULSE
       ======================================================== */
    .fi-topbar-database-notifications-btn {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: visible !important;
    }

    .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn,
    .fi-topbar-database-notifications-btn > .pelicle-unread-badge {
        position: absolute !important;
        top: 0px !important;
        right: 8px !important;
        left: auto !important;
        bottom: auto !important;
        inset-inline-start: auto !important;
        inset-inline-end: 8px !important;
        transform: none !important;
        --tw-translate-x: 0 !important;
        --tw-translate-y: 0 !important;
        pointer-events: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 30 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* Crisp Crimson Red Unread Badge on Topbar Bell */
    .fi-topbar-database-notifications-btn .fi-badge,
    .fi-topbar-database-notifications-btn .fi-badge-color-primary,
    .fi-topbar-database-notifications-btn .fi-badge-color-danger,
    .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn .fi-badge,
    .pelicle-unread-badge {
        position: relative !important;
        top: 0 !important;
        right: 0 !important;
        left: auto !important;
        inset-inline-start: auto !important;
        transform: none !important;
        background-color: #ef4444 !important;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 9.5px !important;
        padding: 0 3.5px !important;
        min-width: 16px !important;
        height: 16px !important;
        line-height: 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1.5px solid #ffffff !important;
        border-radius: 9999px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25) !important;
        pointer-events: none !important;
        text-shadow: none !important;
    }

    .fi-topbar-database-notifications-btn > .pelicle-unread-badge {
        position: absolute !important;
        top: 0px !important;
        right: 8px !important;
        left: auto !important;
        inset-inline-start: auto !important;
        inset-inline-end: 8px !important;
        transform: none !important;
        z-index: 30 !important;
    }

    html.dark .fi-topbar-database-notifications-btn .fi-badge,
    html.dark .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn .fi-badge,
    html.dark .pelicle-unread-badge {
        border-color: #070a11 !important;
    }

    @media (max-width: 768px) {
        .fi-topbar-end {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 8px !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
            margin-right: 4px !important;
        }

        /* Compact & Responsive Bell Badge on Mobile (Snugly hugging top-right shoulder of bell) */
        .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn,
        .fi-topbar-database-notifications-btn > .pelicle-unread-badge {
            top: 0px !important;
            right: 9px !important;
            left: auto !important;
            bottom: auto !important;
            inset-inline-start: auto !important;
            inset-inline-end: 9px !important;
            transform: none !important;
            --tw-translate-x: 0 !important;
            --tw-translate-y: 0 !important;
        }

        .fi-topbar-database-notifications-btn .fi-badge,
        .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn .fi-badge,
        .pelicle-unread-badge {
            min-width: 14px !important;
            height: 14px !important;
            line-height: 14px !important;
            font-size: 8.5px !important;
            padding: 0 2px !important;
            border-width: 1px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }
    }

    @media (max-width: 420px) {
        .fi-topbar-end {
            gap: 6px !important;
            margin-right: 2px !important;
        }

        .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn,
        .fi-topbar-database-notifications-btn > .pelicle-unread-badge {
            top: 0px !important;
            right: 9px !important;
            left: auto !important;
            bottom: auto !important;
            inset-inline-start: auto !important;
            inset-inline-end: 9px !important;
            transform: none !important;
            --tw-translate-x: 0 !important;
            --tw-translate-y: 0 !important;
        }
    }

    /* Option A (Ring & Settle): Bell icon color highlight when unread (Steady settled icon) */
    .fi-topbar-database-notifications-btn.has-unread-alerts svg,
    .fi-topbar-database-notifications-btn:has(.fi-badge) svg {
        color: #f59e0b !important;
        animation: none !important;
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
        ['touchend', 'pointerup', 'click', 'keydown', 'focus', 'mousemove'].forEach(evt => {
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

        function findBellButton() {
            return document.querySelector('.fi-topbar-database-notifications-btn, button[wire\\:click*="openDatabaseNotifications"], [aria-label*="notification" i], [aria-label*="abiso" i]');
        }

        // Option A (Ring & Settle): Rings 2-3 times on incoming event, then settles completely
        function ringBellIconBriefly() {
            const bellBtn = findBellButton();
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
            const bell = e.target.closest('.fi-topbar-database-notifications-btn, button[wire\\:click*="openDatabaseNotifications"], [aria-label*="notification" i]');
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
            const bellBtn = findBellButton();
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

        // 5. Update Topbar Bell Badge Indicator & Ensure Pixel-Perfect Positioning on Phone & Desktop
        function ensureBadgePosition() {
            const bellBtn = findBellButton();
            if (!bellBtn) return;
            const badgeCtn = bellBtn.querySelector('.fi-icon-btn-badge-ctn');
            if (badgeCtn) {
                const isMobile = window.innerWidth <= 768;
                const offset = isMobile ? '9px' : '8px';
                badgeCtn.style.setProperty('position', 'absolute', 'important');
                badgeCtn.style.setProperty('left', 'auto', 'important');
                badgeCtn.style.setProperty('inset-inline-start', 'auto', 'important');
                badgeCtn.style.setProperty('right', offset, 'important');
                badgeCtn.style.setProperty('inset-inline-end', offset, 'important');
                badgeCtn.style.setProperty('top', '0px', 'important');
                badgeCtn.style.setProperty('bottom', 'auto', 'important');
                badgeCtn.style.setProperty('transform', 'none', 'important');
                badgeCtn.style.setProperty('--tw-translate-x', '0px', 'important');
                badgeCtn.style.setProperty('--tw-translate-y', '0px', 'important');
                badgeCtn.style.setProperty('background', 'transparent', 'important');
                badgeCtn.style.setProperty('border', 'none', 'important');
                badgeCtn.style.setProperty('box-shadow', 'none', 'important');
            }
            const badgeSpan = bellBtn.querySelector('.fi-badge, .pelicle-unread-badge');
            if (badgeSpan) {
                badgeSpan.style.setProperty('position', 'relative', 'important');
                badgeSpan.style.setProperty('top', '0px', 'important');
                badgeSpan.style.setProperty('right', '0px', 'important');
                badgeSpan.style.setProperty('left', 'auto', 'important');
                badgeSpan.style.setProperty('inset-inline-start', 'auto', 'important');
                badgeSpan.style.setProperty('transform', 'none', 'important');
            }
        }

        function updateTopbarBellBadge(count) {
            const bellBtn = findBellButton();
            if (!bellBtn) return;

            let badgeSpan = bellBtn.querySelector('.fi-badge, .pelicle-unread-badge');
            if (count > 0) {
                bellBtn.classList.add('has-unread-alerts');
                let badgeCtn = bellBtn.querySelector('.fi-icon-btn-badge-ctn');
                if (!badgeSpan) {
                    if (!badgeCtn) {
                        badgeCtn = document.createElement('div');
                        badgeCtn.className = 'fi-icon-btn-badge-ctn';
                        bellBtn.appendChild(badgeCtn);
                    }
                    badgeSpan = document.createElement('span');
                    badgeSpan.className = 'fi-badge pelicle-unread-badge';
                    badgeCtn.appendChild(badgeSpan);
                }
                ensureBadgePosition();
                badgeSpan.textContent = count;
                badgeSpan.style.display = 'inline-flex';
                if (badgeSpan.parentElement) {
                    badgeSpan.parentElement.style.display = 'inline-flex';
                }
            } else {
                bellBtn.classList.remove('has-unread-alerts');
                bellBtn.classList.remove('bell-ringing');
                if (badgeSpan) {
                    badgeSpan.style.display = 'none';
                    if (badgeSpan.parentElement && badgeSpan.parentElement.classList.contains('fi-icon-btn-badge-ctn')) {
                        badgeSpan.parentElement.style.display = 'none';
                    }
                }
            }
        }

        // 6. Polling & Event Loop (Foolproof Real-Time Single-Alert System with Storage Sync)
        const originalTitle = document.title;
        const currentPath = window.location.pathname;
        const activePanel = currentPath.startsWith('/employee') ? 'employee' : (currentPath.startsWith('/driver') ? 'driver' : 'admin');
        const seenStorageKey = 'pelicle_seen_notifs_' + activePanel;

        let seenNotifIds = [];
        function loadSeenNotifIds() {
            try {
                const raw = localStorage.getItem(seenStorageKey) || sessionStorage.getItem(seenStorageKey) || '[]';
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    parsed.forEach(id => {
                        const s = String(id);
                        if (!seenNotifIds.includes(s)) seenNotifIds.push(s);
                    });
                }
            } catch (e) {}
        }
        function saveSeenNotifIds() {
            try {
                if (seenNotifIds.length > 100) seenNotifIds = seenNotifIds.slice(-100);
                const str = JSON.stringify(seenNotifIds);
                localStorage.setItem(seenStorageKey, str);
                sessionStorage.setItem(seenStorageKey, str);
            } catch (e) {}
        }

        loadSeenNotifIds();

        let isFirstCheck = true;
        let isChecking = false;
        let lastChimeTime = 0;
        let lastStateHash = null;
        let pendingLivewireRefresh = false;

        function isElementVisible(el) {
            if (!el) return false;
            const style = window.getComputedStyle(el);
            if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0) {
                return false;
            }
            const rect = el.getBoundingClientRect();
            return rect.width > 0 && rect.height > 0;
        }

        function canSafelyRefreshLivewire() {
            if (document.hidden || !window.Livewire) return false;

            const path = window.location.pathname;
            if (path.includes('/login') || path.includes('/create') || path.includes('/edit')) {
                return false;
            }

            const activeEl = document.activeElement;
            const isTyping = Boolean(activeEl && ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeEl.tagName));
            if (isTyping) return false;

            const isBusy = Boolean(
                document.querySelector('form[data-loading], button[data-loading], [aria-busy="true"]')
            );
            if (isBusy) return false;

            const hasOpenModal = Array.from(
                document.querySelectorAll('.fi-modal-window, .fi-modal.fi-modal-open, [role="dialog"]')
            ).some(isElementVisible);
            if (hasOpenModal) return false;

            const hasOpenDropdown = Array.from(
                document.querySelectorAll('.fi-dropdown-panel')
            ).some(isElementVisible);
            if (hasOpenDropdown) return false;

            return true;
        }

        function triggerSafeLivewireRefresh() {
            if (canSafelyRefreshLivewire()) {
                try {
                    if (window.Livewire && typeof window.Livewire.all === 'function') {
                        window.Livewire.all().forEach(comp => {
                            try {
                                const name = String(comp?.name || '');
                                if (name.includes('database-notifications') || name.includes('global-search')) {
                                    return;
                                }
                                if (comp && comp.$wire && typeof comp.$wire.$refresh === 'function') {
                                    comp.$wire.$refresh();
                                } else if (typeof window.Livewire.fireAction === 'function' && comp) {
                                    window.Livewire.fireAction(comp, '$refresh');
                                }
                            } catch (e) {}
                        });
                    }
                    pendingLivewireRefresh = false;
                } catch (err) {}
            } else {
                pendingLivewireRefresh = true;
            }
        }

        function playChimeWithThrottle(isEmergency) {
            const now = Date.now();
            if (now - lastChimeTime < 4000) {
                // Throttle: Never ring audio multiple times within 4 seconds
                return;
            }
            lastChimeTime = now;
            playSystemNotificationSound(isEmergency);
        }

        async function checkLiveNotifications() {
            if (isChecking || document.hidden) return;
            if (document.querySelector('form[data-loading], button[data-loading], [aria-busy="true"]')) return;
            isChecking = true;

            try {
                const res = await fetch('/api/admin/live-notifications-check?panel=' + encodeURIComponent(activePanel), {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const data = await res.json();
                const currentCount = parseInt(data.count, 10) || 0;
                const unreadList = Array.isArray(data.unread) ? data.unread : [];
                const incomingHash = data.state_hash || null;

                // Track database state hash for automatic table & widget refresh
                if (incomingHash) {
                    if (lastStateHash !== null && lastStateHash !== incomingHash) {
                        pendingLivewireRefresh = true;
                    }
                    lastStateHash = incomingHash;
                }

                // Update tab title with unread count
                if (currentCount > 0) {
                    document.title = '(' + currentCount + ') ' + originalTitle.replace(/^\(\d+\)\s*/, '');
                } else {
                    document.title = originalTitle.replace(/^\(\d+\)\s*/, '');
                }

                // Update bell icon badge
                updateTopbarBellBadge(currentCount);

                loadSeenNotifIds();
                const nowSec = Math.floor(Date.now() / 1000);
                const newUnseenList = [];

                if (isFirstCheck) {
                    isFirstCheck = false;
                    unreadList.forEach(item => {
                        if (item && item.id) {
                            const s = String(item.id);
                            const ageSec = nowSec - (item.created_at_ts || 0);
                            // If created within the last 180 seconds and not yet seen, trigger alert!
                            if (!seenNotifIds.includes(s)) {
                                if (ageSec <= 180) {
                                    seenNotifIds.push(s);
                                    newUnseenList.push(item);
                                } else {
                                    // Older historical notifications are quietly marked as seen
                                    seenNotifIds.push(s);
                                }
                            }
                        }
                    });
                } else {
                    unreadList.forEach(item => {
                        if (item && item.id) {
                            const s = String(item.id);
                            if (!seenNotifIds.includes(s)) {
                                seenNotifIds.push(s);
                                newUnseenList.push(item);
                            }
                        }
                    });
                }

                if (newUnseenList.length > 0 && currentCount > 0) {
                    saveSeenNotifIds();

                    const alertItem = newUnseenList[0];
                    const isEmerg = alertItem.is_emergency || false;
                    playChimeWithThrottle(isEmerg);
                    ringBellIconBriefly();

                    if ('vibrate' in navigator) {
                        try { navigator.vibrate([200, 100, 200]); } catch(e) {}
                    }
                    showDesktopNotification(alertItem.title, alertItem.body);
                    showFloatingToast(alertItem);

                    pendingLivewireRefresh = true;
                } else {
                    saveSeenNotifIds();
                }

                if (pendingLivewireRefresh) {
                    triggerSafeLivewireRefresh();
                }
            } catch (err) {
                // Ignore network errors
            } finally {
                isChecking = false;
            }
        }

        // Initial badge positioning & listeners so badge is snugly anchored immediately
        ensureBadgePosition();
        setTimeout(ensureBadgePosition, 300);
        setTimeout(ensureBadgePosition, 1000);
        window.addEventListener('resize', ensureBadgePosition, { passive: true });
        document.addEventListener('livewire:navigated', ensureBadgePosition);

        // Live notification & table state polling every 2.5 seconds (snappy, background-capable, zero UI lag)
        setTimeout(checkLiveNotifications, 800);
        setInterval(checkLiveNotifications, 2500);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                ensureBadgePosition();
                checkLiveNotifications();
            }
        });
    })();
</script>
