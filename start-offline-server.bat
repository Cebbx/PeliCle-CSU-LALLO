@echo off
title CSU Vehicle System - OFFLINE HOTSPOT SERVER (Port 8000)
color 0A

echo ============================================================================
echo        CSU LAL-LO VEHICLE SYSTEM - OFFLINE / HOTSPOT DEFENSE MODE
echo ============================================================================
echo.
echo   PAANO GAMITIN SA DEFENSE (PHONE HOTSPOT / WI-FI):
echo   1. Buksan ang Mobile Hotspot ng Phone (kahit WALANG load / WALANG data).
echo   2. I-connect itong Laptop at ang mga Cellphone sa iisang Hotspot/Wi-Fi.
echo   3. Hintaying lumabas ang mga LINKS sa ibaba at buksan sa browser!
echo.

:: Locate PHP Executable
set "PHP_EXE=C:\Users\Acer\.config\herd\bin\php84\php.exe"
if not exist "%PHP_EXE%" (
    for /f "delims=" %%F in ('dir /b /s "C:\Users\Acer\.config\herd\bin\php*.exe" 2^>nul') do (
        set "PHP_EXE=%%F"
        goto :found_php
    )
    set "PHP_EXE=php"
)
:found_php

:: 1. Start MySQL Automatically if not running
echo [1/5] Chine-check ang Database (MySQL)...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo       - OK! Running na ang MySQL Database.
) else (
    echo       - Binubuksan ang MySQL Database...
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 3 /nobreak >nul
        echo       - OK! Naka-start na ang MySQL Database.
    ) else (
        echo       - WARNING: Hindi nahanap ang C:\xampp\mysql\bin\mysqld.exe.
        echo         Siguraduhing naka-start ang MySQL sa XAMPP o Herd.
    )
)

:: 2. Ensure Windows Firewall allows Port 8000 on Public/Hotspot & Private Networks
echo [2/5] Inaayos ang Windows Firewall para sa Phone Hotspot (Port 8000)...
netsh advfirewall firewall show rule name="PeliCle Offline Hotspot Port 8000" >nul 2>&1
if not "%ERRORLEVEL%"=="0" (
    netsh advfirewall firewall add rule name="PeliCle Offline Hotspot Port 8000" dir=in action=allow protocol=TCP localport=8000 profile=any >nul 2>&1
    netsh advfirewall firewall add rule name="PeliCle Offline PHP Server" dir=in action=allow program="%PHP_EXE%" profile=any >nul 2>&1
)
echo       - OK! Ready ang Port 8000 para sa mga naka-connect sa Hotspot.

:: 3. Free Port 8000 if an old PHP server is stuck on it
echo [3/5] Chine-check kung libre ang Port 8000...
for /f "tokens=5" %%P in ('netstat -ano ^| findstr ":8000 " ^| findstr "LISTENING" 2^>nul') do (
    if not "%%P"=="0" (
        echo       - May lumang server sa Port 8000 ^(PID %%P^), nire-restart...
        taskkill /F /PID %%P >nul 2>&1
    )
)
echo       - OK! Libre ang Port 8000.

:: 4. Detect Laptop IPv4 Address on Wi-Fi / Hotspot
echo [4/5] Kinukuha ang IP Address ng Laptop sa Hotspot/Wi-Fi...
set "LAPTOP_IP="
for /f "usebackq delims=" %%I in (`powershell -NoProfile -Command "$ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' -and ($_.InterfaceAlias -match 'Wi-Fi|WLAN|Wireless|Ethernet') } | Sort-Object -Property @{Expression={if ($_.InterfaceAlias -match 'Wi-Fi|WLAN|Wireless') {0} else {1}}} | Select-Object -First 1 -ExpandProperty IPAddress); if (-not $ip) { $ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' } | Select-Object -First 1 -ExpandProperty IPAddress) }; Write-Output $ip" 2^>nul`) do (
    if not defined LAPTOP_IP set "LAPTOP_IP=%%I"
)

if not defined LAPTOP_IP (
    for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
        set "TEMP_IP=%%a"
        setlocal enabledelayedexpansion
        set "TEMP_IP=!TEMP_IP:~1!"
        endlocal & set "LAPTOP_IP=%TEMP_IP%"
    )
)
if not defined LAPTOP_IP set "LAPTOP_IP=127.0.0.1"
for /f "tokens=* delims= " %%A in ("%LAPTOP_IP%") do set "LAPTOP_IP=%%A"

echo       - OK! Laptop Hotspot IP: %LAPTOP_IP%

:: 5. Clear Laravel cache so dynamic Hotspot IP works on all portals
echo [5/5] Nililinis ang system cache para gumana sa lahat ng Cellphone...
cd /d "C:\Users\Acer\Desktop\IMPORT FILES\flowchart\group-3"
"%PHP_EXE%" artisan optimize:clear >nul 2>&1
echo       - OK! System cache cleared.

:: Automatically update MGA_LINKS.txt with the current Hotspot IP
set "LINKS_FILE_1=%~dp0MGA_LINKS.txt"
(
echo ============================================================================
echo        CSU LAL-LO VEHICLE SYSTEM - MGA LINKS SA DEFENSE ^(OFFLINE MODE^)
echo ============================================================================
echo.
echo PAANO GAMITIN SA PHONE HOTSPOT / WI-FI ^(KAHit WALANG INTERNET / DATA^):
echo   1. Buksan ang Hotspot ng Phone at i-connect ang Laptop at mga Cellphone.
echo   2. I-double click ang "start-offline-server.bat" ^(Huwag isasara ang black window^).
echo   3. Kusang maa-update ang IP Address sa baba. I-copy at buksan sa browser!
echo.
echo ----------------------------------------------------------------------------
echo   PARA SA MGA CELLPHONE O IBANG LAPTOP ^(Connected sa Hotspot / Wi-Fi^)
echo   ^(Current Hotspot IP: %LAPTOP_IP%^)
echo ----------------------------------------------------------------------------
echo   Landing Page ^(All Portals^) : http://%LAPTOP_IP%:8000
echo   Admin Portal               : http://%LAPTOP_IP%:8000/admin
echo   Employee Portal            : http://%LAPTOP_IP%:8000/employee
echo   Driver Portal              : http://%LAPTOP_IP%:8000/driver
echo   Guard QR Scanner           : http://%LAPTOP_IP%:8000/guard/scanner
echo.
echo ----------------------------------------------------------------------------
echo   PARA DITO MISMO SA LAPTOP NA ITO ^(Localhost Links^)
echo ----------------------------------------------------------------------------
echo   Landing Page ^(All Portals^) : http://localhost:8000
echo   Admin Portal               : http://localhost:8000/admin
echo   Employee Portal            : http://localhost:8000/employee
echo   Driver Portal              : http://localhost:8000/driver
echo   Guard QR Scanner           : http://localhost:8000/guard/scanner
echo.
echo ----------------------------------------------------------------------------
echo   MGA LOGIN CREDENTIALS ^(ACCOUNTS PARA SA DEMO / DEFENSE^)
echo ----------------------------------------------------------------------------
echo   [ADMIN PORTAL]
echo     Email    : admin@csu.edu.ph
echo     Password : password
echo.
echo   [EMPLOYEE PORTAL]
echo     Email    : csu.employee1@gmail.com
echo     Password : password
echo.
echo   [DRIVER PORTAL]
echo     Email    : driver1@csu.edu.ph
echo     Password : password
echo.
echo   [GUARD QR SCANNER]
echo     Officer  : Edward Cabbat ^(Offline Direct Login^)
echo     Password : 1001
echo ============================================================================
) > "%LINKS_FILE_1%" 2>nul

if exist "C:\Users\Acer\Desktop\offline  mode\" (
    copy /Y "%LINKS_FILE_1%" "C:\Users\Acer\Desktop\offline  mode\MGA_LINKS.txt" >nul 2>&1
)

:: Copy main hotspot URL to clipboard
echo http://%LAPTOP_IP%:8000| clip

echo.
echo ============================================================================
echo   READY NA ANG OFFLINE HOTSPOT SERVER! (Naka-copy na sa Clipboard ang Link)
echo   (Na-update na rin ang MGA_LINKS.txt gamit ang IP: %LAPTOP_IP%)
echo ============================================================================
echo.
echo   [LINKS PARA SA CELLPHONE / IBANG DEVICE SA HOTSPOT]
echo   Landing Page     :  http://%LAPTOP_IP%:8000
echo   Admin Portal     :  http://%LAPTOP_IP%:8000/admin
echo   Employee Portal  :  http://%LAPTOP_IP%:8000/employee
echo   Driver Portal    :  http://%LAPTOP_IP%:8000/driver
echo   Guard Scanner    :  http://%LAPTOP_IP%:8000/guard/scanner
echo.
echo   [LINKS PARA DITO MISMO SA LAPTOP]
echo   Landing Page     :  http://localhost:8000
echo   Admin Portal     :  http://localhost:8000/admin
echo   Employee Portal  :  http://localhost:8000/employee
echo   Driver Portal    :  http://localhost:8000/driver
echo   Guard Scanner    :  http://localhost:8000/guard/scanner
echo.
echo   [DEMO ACCOUNTS]
echo   Admin    : admin@csu.edu.ph        ^| Pass: password
echo   Employee : csu.employee1@gmail.com ^| Pass: password
echo   Driver   : driver1@csu.edu.ph      ^| Pass: password
echo   Guard    : Edward Cabbat           ^| Pass: 1001 ^(No Email Code Needed^)
echo ============================================================================
echo   PAALALA: HUWAG ISASARA ANG WINDOW NA ITO HABANG NAG-DEDEFENSE!
echo ============================================================================
echo.

"%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8000
pause
