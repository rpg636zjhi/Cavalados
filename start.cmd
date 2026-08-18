@echo off
setlocal EnableExtensions
cd /d "%~dp0"

set "PHP_BINARY=%CD%\bin\php7\php.exe"
set "PHP_INI=%CD%\bin\php7\php.ini"

if not exist "%PHP_BINARY%" (
    echo [Cavalados] PHP 7 para Windows nao encontrado.
    echo Use: .\install.ps1 -Archive CAMINHO_DO_ZIP -Sha256 HASH_SHA256
    exit /b 1
)

if exist "%CD%\PocketMine-MP.phar" (
    set "SERVER_FILE=%CD%\PocketMine-MP.phar"
) else if exist "%CD%\src\pocketmine\PocketMine.php" (
    set "SERVER_FILE=%CD%\src\pocketmine\PocketMine.php"
) else (
    echo [Cavalados] Arquivo principal do servidor nao encontrado.
    exit /b 1
)

echo [Cavalados] Iniciando o codinome Auriverde...
if exist "%PHP_INI%" (
    "%PHP_BINARY%" -c "%PHP_INI%" "%SERVER_FILE%" %*
) else (
    "%PHP_BINARY%" "%SERVER_FILE%" %*
)
set "EXIT_CODE=%ERRORLEVEL%"

if not "%EXIT_CODE%"=="0" (
    echo.
    echo O servidor encerrou com o codigo %EXIT_CODE%.
    pause
)
exit /b %EXIT_CODE%
