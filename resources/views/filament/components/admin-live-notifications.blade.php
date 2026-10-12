{{-- Real-Time System Audio Alert & Live Notification Engine for All Panels (Admin, Employee, Driver) --}}
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
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.8), 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        animation: notifRedPulse 2s infinite ease-in-out !important;
    }

    html.dark .fi-topbar-database-notifications-btn .fi-badge,
    html.dark .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn .fi-badge,
    html.dark .pelicle-unread-badge {
        border-color: #0f172a !important;
    }

    @keyframes notifRedPulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.7), 0 0 0 0 rgba(239, 68, 68, 0.5);
        }
        50% {
            transform: scale(1.15);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.95), 0 0 0 5px rgba(239, 68, 68, 0);
        }
    }

    /* Bell icon subtle animation and highlight when unread */
    .fi-topbar-database-notifications-btn.has-unread-alerts svg,
    .fi-topbar-database-notifications-btn:has(.fi-badge) svg {
        color: #f59e0b !important;
        animation: bellSwingAlert 3s infinite ease-in-out !important;
        transform-origin: top center;
    }

    @keyframes bellSwingAlert {
        0%, 65%, 100% { transform: rotate(0deg); }
        70% { transform: rotate(14deg); }
        74% { transform: rotate(-12deg); }
        78% { transform: rotate(8deg); }
        82% { transform: rotate(-6deg); }
        86% { transform: rotate(2deg); }
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

        // Global manual testers
        window.testBellSound = function() {
            unlockAudio();
            playSystemNotificationSound(false);
        };
        window.testAlarmSound = function() {
            unlockAudio();
            playSystemNotificationSound(true);
        };
        window.testPeliCleSound = function(emergency = false) {
            unlockAudio();
            playSystemNotificationSound(emergency);
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

        // 4. In-App Floating Toast Banner
        function showFloatingToast(notif) {
            let container = document.getElementById('pelicle-live-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'pelicle-live-toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const isEmerg = notif && notif.is_emergency;
            toast.className = 'pelicle-live-toast ' + (isEmerg ? 'toast-emergency' : 'toast-standard');
            toast.innerHTML = `
                <div style="font-size: 24px; line-height: 1; flex-shrink: 0;">${isEmerg ? '🚨' : '🔔'}</div>
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
                    setTimeout(() => toast.remove(), 300);
                }
            }, 6500);
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
                if (badgeSpan) {
                    badgeSpan.style.display = 'none';
                }
            }
        }

        // 6. Polling & Event Loop
        const originalTitle = document.title;
        let lastKnownCount = sessionStorage.getItem('pelicle_last_notif_count') !== null 
            ? parseInt(sessionStorage.getItem('pelicle_last_notif_count'), 10) 
            : null;
        let lastKnownId = sessionStorage.getItem('pelicle_last_notif_id') || null;
        let isChecking = false;

        async function checkLiveNotifications() {
            if (isChecking || document.hidden) return;
            isChecking = true;

            try {
                const res = await fetch('/api/admin/live-notifications-check', {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const data = await res.json();
                const currentCount = parseInt(data.count, 10) || 0;
                const latest = data.latest || null;
                const latestId = latest ? String(latest.id) : null;

                // Update tab title
                if (currentCount > 0) {
                    document.title = '(' + currentCount + ') ' + originalTitle.replace(/^\(\d+\)\s*/, '');
                } else {
                    document.title = originalTitle.replace(/^\(\d+\)\s*/, '');
                }

                // Update bell icon badge
                updateTopbarBellBadge(currentCount);

                // Trigger ringtone & toast if count increased OR a new notification ID arrived
                const hasNewNotification = (lastKnownCount !== null && currentCount > lastKnownCount) ||
                                           (lastKnownId !== null && latestId && latestId !== lastKnownId);

                if (hasNewNotification && latest) {
                    const isEmerg = latest.is_emergency || false;
                    
                    playSystemNotificationSound(isEmerg);
                    if ('vibrate' in navigator) {
                        try { navigator.vibrate([200, 100, 200]); } catch(e) {}
                    }
                    showDesktopNotification(latest.title, latest.body);
                    showFloatingToast(latest);

                    // Instant Live Sync: trigger immediate refresh on tables & dashboards (only if user is not typing in a form)
                    const activeEl = document.activeElement;
                    const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT' || activeEl.isContentEditable);
                    if (window.Livewire && !isTyping && !window.location.pathname.endsWith('/create') && !window.location.pathname.endsWith('/edit')) {
                        try {
                            Livewire.dispatch('$refresh');
                        } catch (err) {}
                    }
                }

                lastKnownCount = currentCount;
                sessionStorage.setItem('pelicle_last_notif_count', currentCount);
                if (latestId) {
                    lastKnownId = latestId;
                    sessionStorage.setItem('pelicle_last_notif_id', latestId);
                }
            } catch (err) {
                // Ignore transient network errors
            } finally {
                isChecking = false;
            }
        }

        // Smooth, zero-lag live sync polling every 2.5 seconds
        setTimeout(checkLiveNotifications, 600);
        setInterval(checkLiveNotifications, 2500);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) checkLiveNotifications();
        });

        // Also check whenever Livewire commits
        if (window.Livewire) {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(checkLiveNotifications, 400);
                });
            });
        }
    })();
</script>
