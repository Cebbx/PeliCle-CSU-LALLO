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
        // 1. Shared Web Audio Context & Autoplay Unlock
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    audioCtx = new AudioContextClass();
                }
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        function unlockAudio() {
            const ctx = getAudioContext();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume();
            }
        }
        window.addEventListener('click', unlockAudio, { passive: true });
        window.addEventListener('keydown', unlockAudio, { passive: true });
        window.addEventListener('touchstart', unlockAudio, { passive: true });

        // 2. High Quality Bell Chime & Alarm Sound Generator
        function playSystemNotificationSound(isEmergency) {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                if (isEmergency) {
                    // Urgent dual-tone siren alarm (880Hz / 660Hz alternating)
                    for (let i = 0; i < 4; i++) {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sawtooth';
                        const startTime = now + (i * 0.16);
                        const freq = (i % 2 === 0) ? 920 : 680;

                        osc.frequency.setValueAtTime(freq, startTime);
                        gain.gain.setValueAtTime(0.35, startTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, startTime + 0.15);

                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(startTime);
                        osc.stop(startTime + 0.15);
                    }
                } else {
                    // Crisp 4-tone ascending bell chime (C5 -> E5 -> G5 -> C6) with natural decay
                    const notes = [
                        { f: 523.25, t: 0, d: 0.35, g: 0.38 },    // C5
                        { f: 659.25, t: 0.10, d: 0.35, g: 0.40 },  // E5
                        { f: 783.99, t: 0.20, d: 0.45, g: 0.45 },  // G5
                        { f: 1046.50, t: 0.32, d: 0.70, g: 0.50 }  // C6 (Resonant Bell)
                    ];

                    notes.forEach(n => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(n.f, now + n.t);
                        gain.gain.setValueAtTime(n.g, now + n.t);
                        gain.gain.exponentialRampToValueAtTime(0.0001, now + n.t + n.d);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(now + n.t);
                        osc.stop(now + n.t + n.d);
                    });
                }
            } catch (e) {
                console.warn('PeliCle audio alert error:', e);
            }
        }

        // Global manual testers for instant verification in browser console
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

                // Update tab title
                if (currentCount > 0) {
                    document.title = '(' + currentCount + ') ' + originalTitle.replace(/^\(\d+\)\s*/, '');
                } else {
                    document.title = originalTitle.replace(/^\(\d+\)\s*/, '');
                }

                // Update bell icon badge
                updateTopbarBellBadge(currentCount);

                // If new notifications arrived (count increased)
                if (lastKnownCount !== null && currentCount > lastKnownCount) {
                    const latest = data.latest || {};
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
