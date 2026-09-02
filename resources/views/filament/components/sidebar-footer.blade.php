<div class="sidebar-footer-wrap" style="padding: 6px 12px; border-top: 1px solid rgba(148, 163, 184, 0.15); font-family: 'Outfit', sans-serif;">
    <div class="sidebar-footer-title" style="font-size: 10.5px; font-weight: 700; letter-spacing: -0.01em; line-height: 1.1;">CSU Lal-lo Campus</div>
    <div class="sidebar-footer-sub" style="font-size: 9.5px; margin-top: 1px; line-height: 1.1;">Vehicle &amp; Trip Management System</div>
    <div class="sidebar-footer-live" style="display: flex; align-items: center; gap: 5px; margin-top: 3px; font-size: 9px; font-weight: 500;">
        <span style="width: 5px; height: 5px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 5px #10b981;"></span>
        <span>System Live &nbsp;•&nbsp; {{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}</span>
    </div>
</div>
<style>
    html.dark .sidebar-footer-title { color: #ffffff !important; }
    html.dark .sidebar-footer-sub { color: #94a3b8 !important; }
    html.dark .sidebar-footer-live { color: #cbd5e1 !important; }

    html:not(.dark) .sidebar-footer-title { color: #0f172a !important; }
    html:not(.dark) .sidebar-footer-sub { color: #64748b !important; }
    html:not(.dark) .sidebar-footer-live { color: #475569 !important; }
</style>
