@echo off
chcp 65001 >nul
title ارتقاء برنامه شهریه
cd /d "%~dp0..\.."

echo.
echo  ============================================
echo   ارتقاء برنامه شهریه به نسخه جدید
echo  ============================================
echo.

where php >nul 2>nul
if errorlevel 1 (
    echo  PHP پیدا نشد. برنامه قدیمی را با همان روش همیشگی باز کنید
    echo  و از پشتیبان بخواهید کمک کند.
    echo.
    pause
    exit /b 1
)

php artisan upgrade:from-v1

echo.
echo  ----- پایان -----
pause
