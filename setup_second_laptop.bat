@echo off
title Capstone Project Setup Tool
color 0b
echo ===============================================================
echo   AUTOMATED CAPSTONE PROJECT SETUP (SECOND LAPTOP)
echo ===============================================================
echo.
echo Please wait, configuring the project to run on this laptop...
echo.

:: Go to the directory where this batch file is located
cd /d "%~dp0"
echo Current project directory: %cd%
echo.

:: 1. Copy .env.example to .env if .env doesn't exist
if not exist .env (
    if exist .env.example (
        echo [1/4] Creating .env file from .env.example...
        copy .env.example .env > nul
    ) else (
        echo [1/4] Creating new .env file...
        echo APP_NAME=Laravel > .env
        echo APP_ENV=local >> .env
        echo APP_KEY=base64:zd6MLoCxMNGtH4d6Rb8iGia/nhZRrvDVYciQfaVLzN0= >> .env
        echo APP_DEBUG=true >> .env
        echo APP_URL=http://group-3.test >> .env
    )
) else (
    echo [1/4] .env file already exists.
)

:: 2. Set database to SQLite
echo [2/4] Setting database connection to SQLite...
powershell -Command "(gc .env) -replace 'DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite' -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' | Out-File -encoding ASCII .env"
echo Database configuration updated.

:: 3. Clear Laravel config cache
echo.
echo [3/4] Clearing Laravel config cache...
if exist artisan (
    php artisan config:clear
    php artisan optimize:clear
) else (
    echo.
    echo ERROR: artisan file not found! Make sure this script is inside the project folder.
    pause
    exit /b
)

:: 4. Link project to Herd
echo.
echo [4/4] Linking site to Laravel Herd...
call herd link
echo.

echo ===============================================================
echo   SUCCESS! The project is fully set up on this laptop.
echo   You can now open your browser and visit:
echo   👉 http://group-3.test
echo ===============================================================
echo.
pause
