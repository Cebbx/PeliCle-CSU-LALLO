{{-- Real-Time System Audio Alert & Live Notification Engine for Admin Panel --}}
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

        // 2. Audible System Chime (Dual Mode: High-Pitched Emergency Siren or Ascending Triad Chime)
        function playSystemNotificationSound(isEmergency) {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                if (isEmergency) {
                    // Urgent dual-tone alarm (880Hz / 660Hz alternating)
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    
                    osc.frequency.setValueAtTime(880, now);
                    osc.frequency.setValueAtTime(659.25, now + 0.15);
                    osc.frequency.setValueAtTime(880, now + 0.3);
                    osc.frequency.setValueAtTime(659.25, now + 0.45);

                    gain.gain.setValueAtTime(0.35, now);
                    gain.gain.exponentialRampToValueAtTime(0.01, now + 0.65);

                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now);
                    osc.stop(now + 0.65);
                } else {
                    // Crisp, pleasant 4-tone ascending chime (C5 -> E5 -> G5 -> C6)
                    const notes = [
                        { f: 523.25, t: 0, d: 0.25, g: 0.32 },    // C5
                        { f: 659.25, t: 0.10, d: 0.25, g: 0.32 },  // E5
                        { f: 783.99, t: 0.20, d: 0.30, g: 0.38 },  // G5
                        { f: 1046.50, t: 0.30, d: 0.50, g: 0.42 }  // C6
                    ];

                    notes.forEach(n => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(n.f, now + n.t);
                        gain.gain.setValueAtTime(n.g, now + n.t);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + n.t + n.d);
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

        window.testPeliCleSound = function(emergency = false) {
            unlockAudio();
            playSystemNotificationSound(emergency);
        };

        // 3. Desktop Notification Permission
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
                    const notif = new Notification(title || '🚨 PeliCle: Bagong Transaksyon', {
                        body: body || 'May bagong request o aktibidad na pumasok sa system.',
                        icon: '/csu-logo-sm.png'
                    });
                    notif.onclick = function() {
                        window.focus();
                        window.location.href = '/admin/vehicle-requests';
                    };
                } catch (e) {}
            }
        }

        // 4. In-App Floating Toast Banner (Rendered everywhere in admin panel)
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
                <button type="button" style="background: transparent; border: none; color: #94a3b8; font-size: 15px; font-weight: bold; cursor: pointer; padding: 0 4px;" title="Close">&times;</button>
            `;

            toast.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                toast.remove();
            });

            toast.addEventListener('click', () => {
                const bellBtn = document.querySelector('.fi-topbar-database-notifications-btn');
                if (bellBtn) bellBtn.click();
                else window.location.href = '/admin/vehicle-requests';
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
            if (isChecking) return;
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
                }

                lastKnownCount = currentCount;
                sessionStorage.setItem('pelicle_last_notif_count', currentCount);
            } catch (err) {
                // Ignore transient network errors
            } finally {
                isChecking = false;
            }
        }

        // Initial check and regular polling every 2.5 seconds
        setTimeout(checkLiveNotifications, 800);
        setInterval(checkLiveNotifications, 2500);

        // Also check whenever Livewire finishes a commit
        if (window.Livewire) {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(checkLiveNotifications, 250);
                });
            });
        }
    })();
</script>
