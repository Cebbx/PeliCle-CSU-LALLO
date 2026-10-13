@php
    $currentUser = auth()->user();
@endphp
<div class="sidebar-footer-wrap" style="padding: 10px 12px; border-top: 1px solid rgba(148, 163, 184, 0.15); font-family: 'Outfit', sans-serif;">
    @if($currentUser)
        <!-- Mobile & Sidebar User Profile Card with Quick Logout -->
        <div class="sidebar-user-card" style="background: rgba(148, 163, 184, 0.08); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: #3b82f6; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0; box-shadow: 0 0 6px rgba(59, 130, 246, 0.4);">
                    {{ $currentUser->initials() }}
                </div>
                <div style="min-width: 0;">
                    <div class="sidebar-user-name" style="font-size: 11px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 85px;">
                        {{ $currentUser->name }}
                    </div>
                    <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">
                        {{ ucfirst($currentUser->role ?? 'User') }}
                    </div>
                </div>
            </div>

            <!-- One-Click Logout Form -->
            <form method="POST" action="{{ filament()->getLogoutUrl() }}" style="margin: 0; flex-shrink: 0;">
                @csrf
                <button type="submit" 
                        title="Sign Out / Log Out"
                        style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; padding: 4px 8px; font-size: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;"
                        onmouseover="this.style.background='#ef4444'; this.style.color='#ffffff';"
                        onmouseout="this.style.background='rgba(239, 68, 68, 0.12)'; this.style.color='#ef4444';">
                    <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    @endif

    <div class="sidebar-footer-title" style="font-size: 10px; font-weight: 700; letter-spacing: -0.01em; line-height: 1.1;">CSU Lal-lo Campus</div>
    <div class="sidebar-footer-sub" style="font-size: 9px; margin-top: 1px; line-height: 1.1;">Vehicle &amp; Trip Management System</div>
    <div class="sidebar-footer-live" style="display: flex; align-items: center; gap: 5px; margin-top: 3px; font-size: 8.5px; font-weight: 500;">
        <span style="width: 5px; height: 5px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 5px #10b981;"></span>
        <span>System Live &nbsp;•&nbsp; {{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}</span>
    </div>
</div>

<style>
    html.dark .sidebar-footer-title { color: #ffffff !important; }
    html.dark .sidebar-footer-sub { color: #94a3b8 !important; }
    html.dark .sidebar-footer-live { color: #cbd5e1 !important; }
    html.dark .sidebar-user-name { color: #f8fafc !important; }

    html:not(.dark) .sidebar-footer-title { color: #0f172a !important; }
    html:not(.dark) .sidebar-footer-sub { color: #64748b !important; }
    html:not(.dark) .sidebar-footer-live { color: #475569 !important; }
    html:not(.dark) .sidebar-user-name { color: #0f172a !important; }
</style>
