@echo off
setlocal
cd /d %~dp0

REM Open site in Edge then start PHP server on port 5500 with router
start "" msedge "http://localhost:5500"

REM Use system PHP with custom php.ini
set "PHP_EXE=C:\Program Files\php-8.4.14-Win32-vs17-x64\php.exe"
set "PHP_INI=%~dp0php.ini"

if not exist "%PHP_EXE%" (
	echo [Error] PHP not found at %PHP_EXE%
	echo Please verify your PHP installation path.
	goto :end
)

"%PHP_EXE%" -c "%PHP_INI%" -S localhost:5500 -t . router.php

:end
endlocal
