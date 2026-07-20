@echo off
:: Run this file as Administrator to register all BallSignals scheduled tasks.
:: One task runs "php artisan schedule:run" every minute, which triggers:
::   06:00 daily  — tips:scrape         (scrape fixtures + odds)
::   08:00 daily  — tips:send-daily     (newsletter to subscribers)
::   00:00 daily  — subscriptions:expire (mark expired VIP accounts)
::   every 3 hrs  — tips:scrape --results-only (update won/lost results)

:: ── Remove old individual tasks if they exist ─────────────────────────────────
schtasks /delete /TN "BallSignalsDailyScrape"    /F >nul 2>&1
schtasks /delete /TN "BallSignalsResultsUpdate"  /F >nul 2>&1
schtasks /delete /TN "BallSignalsScheduler"      /F >nul 2>&1

:: ── Create the single Laravel scheduler task (runs every minute) ──────────────
schtasks /create /TN "BallSignalsScheduler" ^
  /TR "\"C:\xampp\php\php.exe\" \"C:\xampp\htdocs\ballsignals\ballsignals\artisan\" schedule:run" ^
  /SC MINUTE ^
  /MO 1 ^
  /RL HIGHEST ^
  /F

echo.
echo Done! BallSignalsScheduler will now run every minute and handle:
echo   06:00 AM daily  - Scrape fixtures + odds
echo   08:00 AM daily  - Send newsletter to subscribers
echo   12:00 AM daily  - Expire old VIP subscriptions
echo   Every 3 hours   - Update won/lost results
echo.
pause
