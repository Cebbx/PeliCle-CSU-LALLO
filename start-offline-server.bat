@echo off
chcp 65001 >nul
title CSU Lal-lo PeliCle - Offline Evaluation Server
cls
echo ===================================================================
echo     CSU Lal-lo - PeliCle Vehicle Dispatch Management System
echo            OFFLINE HOTSPOT SERVER (NO INTERNET NEEDED)
echo ===================================================================
echo.

:: 1. Navigate to project root folder
cd /d "C:\Users\Acer\Desktop\IMPORT FILES\flowchart\group-3"

:: 2. Ensure MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo [1/3] Starting MySQL Database Server...
    start /B "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    timeout /t 2 /nobreak >nul
) else (
    echo [1/3] MySQL Database Server is already running.
)

:: 3. Detect PHP executable
set PHP_EXE=C:\Users\Acer\.config\herd\bin\php84\php.exe
if not exist "%PHP_EXE%" (
    set PHP_EXE=php
)

:: 4. Detect Laptop IP Address on Hotspot / Wi-Fi
echo [2/3] Detecting Laptop IP Address on Hotspot / Wi-Fi...
set LAPTOP_IP=127.0.0.1
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set LAST_IP=%%a
)
if defined LAST_IP (
    set LAPTOP_IP=%LAST_IP: =%
)

echo.
echo ===================================================================
echo   PAANO IKUNEKTA ANG CELLPHONE (KAHIT WALANG LOAD O DATA):
echo ===================================================================
echo   1. Buksan ang Mobile Hotspot sa Cellphone (Kahit WALANG LOAD).
echo   2. Ikonekta ang Laptop sa Hotspot Wi-Fi ng Cellphone.
echo   3. Sa Cellphone o Laptop, buksan ang Chrome o Safari:
echo.
echo      PARA SA CELLPHONE (GUARD SCANNER):
echo      http://%LAPTOP_IP%:8000/guard/scanner
echo.
echo      PARA SA LAPTOP (ADMIN / EMPLOYEE / DISPATCH):
echo      http://127.0.0.1:8000
echo.
echo      OFFLINE GUARD LOGIN:
echo      Officer: Edward Cabbat
echo      Password: 1001 (No OTP / No email needed - 100%% Offline)
echo ===================================================================
echo.
echo [3/3] Starting Laravel Server on 0.0.0.0:8000...
echo Keep this window OPEN during your evaluation / demo.
echo Press Ctrl + C to stop the server anytime.
echo.
"%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8000
pause
