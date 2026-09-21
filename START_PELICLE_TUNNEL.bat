@echo off
title PeliCle Cloudflare Live Sync Server
color 0A
cd /d "C:\Users\MARCO LOS BANOS\.gemini\antigravity\scratch\group-3"

echo ================================================================
echo           PELICLE CLOUDFLARE LIVE SYNC SERVER
echo ================================================================
echo.
echo [1/2] Connecting to Herd Local Database Engine...
echo [2/2] Starting Cloudflare Secure HTTPS Tunnel...
echo.
echo ----------------------------------------------------------------
echo   KOPYAHIN ANG LINK NA LILITAW SA BABA (https://...trycloudflare.com)
echo   I-open sa PHONE:  https://xxxx.trycloudflare.com/employee
echo   I-open sa LAPTOP: http://group-3.test/admin
echo ----------------------------------------------------------------
echo.
cloudflared.exe tunnel --url http://127.0.0.1:80 --http-host-header group-3.test
pause
