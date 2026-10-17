@php
    $isAuthenticated = \Filament\Facades\Filament::auth()->check()
        || auth('admin')->check()
        || auth('employee')->check()
        || auth('driver')->check();
@endphp

@if (! $isAuthenticated)
    {{-- 1. Floating Theme Switcher (Top Right Corner) --}}
    <div id="login-theme-switcher-container" style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;">
        <button 
            type="button" 
            id="login-theme-toggle-btn"
            onclick="toggleLoginTheme()"
            title="Toggle Light / Dark Mode"
            style="
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 0.85rem;
                border-radius: 9999px;
                font-size: 0.8125rem;
                font-weight: 600;
                cursor: pointer;
                border: 1px solid rgba(148, 163, 184, 0.3);
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                color: #334155;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            "
            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0, 0, 0, 0.12)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.08)';"
        >
            {{-- Moon Icon (Shows in Light Mode) --}}
            <span id="login-theme-moon-icon" style="display: inline-flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6366f1;">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                </svg>
            </span>
            {{-- Sun Icon (Shows in Dark Mode) --}}
            <span id="login-theme-sun-icon" style="display: none; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f59e0b;">
                    <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                </svg>
            </span>
            <span id="login-theme-text" style="font-family: 'Outfit', sans-serif;">Dark Mode</span>
        </button>
    </div>

    {{-- 2. "Bumalik sa Main Portal" Link (Below the Login Card) --}}
    <div style="margin-top: 1.5rem; text-align: center; width: 100%;">
        <a 
            href="/" 
            style="
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                border-radius: 0.75rem;
                font-size: 0.875rem;
                font-weight: 600;
                font-family: 'Outfit', sans-serif;
                color: #475569;
                text-decoration: none;
                background: rgba(241, 245, 249, 0.85);
                border: 1px solid rgba(203, 213, 225, 0.6);
                backdrop-filter: blur(8px);
                transition: all 0.2s ease;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            "
            class="fi-login-back-btn"
            onmouseover="this.style.color='#0284c7'; this.style.borderColor='#38bdf8'; this.style.background='#ffffff'; this.style.transform='translateY(-1px)';"
            onmouseout="this.style.color='#475569'; this.style.borderColor='rgba(203, 213, 225, 0.6)'; this.style.background='rgba(241, 245, 249, 0.85)'; this.style.transform='translateY(0)';"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Bumalik sa Main Portal</span>
        </a>
    </div>

    {{-- Dark Mode Specific Styles & Toggle Script --}}
    <style>
        html.dark #login-theme-toggle-btn {
            background: rgba(15, 23, 42, 0.85) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f1f5f9 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
        }
        html.dark .fi-login-back-btn {
            background: rgba(30, 41, 59, 0.75) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #94a3b8 !important;
        }
        html.dark .fi-login-back-btn:hover {
            background: rgba(30, 41, 59, 1) !important;
            color: #38bdf8 !important;
            border-color: #38bdf8 !important;
        }
    </style>

    <script>
        function updateLoginThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const moonIcon = document.getElementById('login-theme-moon-icon');
            const sunIcon = document.getElementById('login-theme-sun-icon');
            const textEl = document.getElementById('login-theme-text');

            if (moonIcon && sunIcon && textEl) {
                if (isDark) {
                    moonIcon.style.display = 'none';
                    sunIcon.style.display = 'inline-flex';
                    textEl.textContent = 'Light Mode';
                } else {
                    moonIcon.style.display = 'inline-flex';
                    sunIcon.style.display = 'none';
                    textEl.textContent = 'Dark Mode';
                }
            }
        }

        function toggleLoginTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateLoginThemeUI();
            try {
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: isDark ? 'light' : 'dark' }));
            } catch (e) {}
        }

        document.addEventListener('DOMContentLoaded', updateLoginThemeUI);
        setTimeout(updateLoginThemeUI, 50);
    </script>
@endif
