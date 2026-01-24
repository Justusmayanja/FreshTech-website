@echo off
setlocal
cd /d %~dp0

REM Open site in Edge then start PHP server on port 5500 with router
start "" msedge "http://localhost:5500"
php -S localhost:5500 -c php.ini -t . router.php

endlocal
