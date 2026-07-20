@echo off
cd /d C:\xampp\htdocs\ballsignals\ballsignals
C:\xampp\php\php.exe artisan schedule:run >> C:\xampp\htdocs\ballsignals\ballsignals\storage\logs\scheduler.log 2>&1
