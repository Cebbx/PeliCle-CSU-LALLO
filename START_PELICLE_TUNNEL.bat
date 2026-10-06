@echo off
title PeliCle Cloudflare Live Sync Server - Group 3
color 0A

echo ================================================================
echo           GROUP 3 LIVE TUNNEL (PELICLE CAPSTONE)
echo ================================================================
echo.

:: 1. Ensure MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo [1/2] Starting MySQL Database Server...
    start /B "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    timeout /t 2 /nobreak >nul
) else (
    echo [1/2] MySQL Database Server is running.
)

:: 2. Project directory
cd /d "%~dp0"

:: Reset temporary log file
if exist "cf_tunnel.log" del /f /q "cf_tunnel.log"

:: 3. Launch background helper to auto-copy tunnel URL to clipboard & desktop
start /B "" powershell -NoProfile -ExecutionPolicy Bypass -Command "$log = Join-Path '%~dp0' 'cf_tunnel.log'; $desk = [System.Environment]::GetFolderPath('Desktop'); for ($i=0; $i -lt 45; $i++) { Start-Sleep -Seconds 1; if (Test-Path $log) { $c = Get-Content $log -Raw -ErrorAction SilentlyContinue; if ($c -match 'https://([a-zA-Z0-9-]+)\.trycloudflare\.com' -and $matches[1] -ne 'api') { $url = $matches[0]; try { Set-Clipboard -Value $url } catch {}; [System.IO.File]::WriteAllText((Join-Path $desk 'TUNNEL_LINK_GROUP_3.txt'), ('LIVE URL: ' + $url + \"`r`nEMPLOYEE: \" + $url + \"/employee`r`nADMIN: \" + $url + \"/admin`r`nGUARD: \" + $url + \"/guard/scanner\")); [System.IO.File]::WriteAllText((Join-Path $desk 'Group 3 Live Tunnel.url'), ('[InternetShortcut]' + \"`r`nURL=\" + $url)); break } } }"

echo [2/2] Connecting Live Cloudflare Tunnel to Herd...
echo.
echo ================================================================
echo   PAANO KAPAG NAGBAGO ANG CODE SA VS CODE?
echo   - HINDI MO KAILANGANG I-RESTART ANG TUNNEL NA ITO!
echo   - Naka-live sync ito sa Herd local server.
echo   - Kapag may binago at sinave sa VS Code (Ctrl + S),
echo     I-REFRESH (F5) LANG ANG PHONE O BROWSER, LIVE NA AGAD!
echo   - Mananatiling pareho at buhay ang link hangga't bukas ito!
echo.
echo   KOPYAHIN ANG LINK SA BABA (https://xxxx.trycloudflare.com):
echo   * Automatic ding makokopya sa Clipboard (Ctrl+V) kapag lumabas na!
echo ================================================================
echo.

cloudflared.exe tunnel --logfile "cf_tunnel.log" --url https://127.0.0.1:443 --no-tls-verify --http-host-header group-3.test

echo.
echo Tunnel has stopped.
pause
