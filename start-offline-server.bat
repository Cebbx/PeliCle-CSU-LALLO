@echo off
chcp 65001 >nul
title CSU Lal-lo PeliCle - Offline Evaluation Server
cls
echo ===================================================================
echo     CSU Lal-lo - PeliCle Vehicle Dispatch Management System
echo            OFFLINE HOTSPOT SERVER (NO INTERNET NEEDED)
echo ===================================================================
echo.

set PHP_EXE=C:\Users\Acer\.config\herd\bin\php84\php.exe
if not exist "%PHP_EXE%" (
    set PHP_EXE=php
)

echo [1/3] Detecting Laptop IP Address on Hotspot / Wi-Fi...
set LAPTOP_IP=127.0.0.1
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set LAST_IP=%%a
)
if defined LAST_IP (
    set LAPTOP_IP=%LAST_IP: =%
)

echo [2/3] Laptop Local IP detected: %LAPTOP_IP%
echo.
echo ===================================================================
echo   PAANO IKUNEKTA ANG CELLPHONE (KAHIT WALANG LOAD O DATA):
echo ===================================================================
echo   1. Buksan ang Mobile Hotspot sa Cellphone (Kahit WALANG LOAD).
echo   2. Ikonekta ang Laptop sa Hotspot Wi-Fi ng Cellphone.
echo   3. Sa Cellphone, buksan ang Chrome o Safari at pumunta sa:
echo.
echo      http://%LAPTOP_IP%:8000/guard/scanner
echo.
echo      (O kaya sa Admin: http://%LAPTOP_IP%:8000/admin)
echo.
echo   Guard Officer: Edward Cabbat
echo   Guard Password: 1001 (Zero email/OTP required - 100% Offline)
echo ===================================================================
echo.
echo [3/3] Starting Laravel Server on 0.0.0.0:8000...
echo Keep this window OPEN during your evaluation / demo.
echo Press Ctrl + C to stop the server anytime.
echo.
"%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8000
pause
