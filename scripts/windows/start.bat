@echo off
chcp 65001 >nul
title برنامه شهریه
cd /d "%~dp0..\.."

echo.
echo  برنامه روی این آدرس باز می‌شود:  http://127.0.0.1:8000
echo  برای بستن برنامه این پنجره را ببندید.
echo.

start "" http://127.0.0.1:8000
php artisan serve
pause
