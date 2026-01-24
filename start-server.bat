@echo off
setlocal
cd /d %~dp0

REM Open site in Edge then start PHP server on port 5500 with router
start "" msedge "http://localhost:5500"

REM Use XAMPP's PHP and global php.ini for consistent extensions (pdo_mysql)
set "XAMPP_PHP=C:\xampp\php\php.exe"
set "XAMPP_INI=C:\xampp\php\php.ini"

if not exist "%XAMPP_PHP%" (
	echo [Error] XAMPP php.exe not found at %XAMPP_PHP%
	echo Please verify your XAMPP installation path.
	goto :end
)

"%XAMPP_PHP%" -S localhost:5500 -c "%XAMPP_INI%" -t . router.php

:end
endlocal
