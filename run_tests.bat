@echo off
REM Script para ejecutar los tests de APIEmpresas
set PHP_PATH=D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe

if not exist "%PHP_PATH%" (
    echo [ERROR] No se encontro PHP en %PHP_PATH%
    echo Por favor, verifica tu carpeta de Laragon.
    pause
    exit /b
)

echo [OK] Ejecutando tests de salud profesionales (Modo Nativo CI4)...
"%PHP_PATH%" vendor/bin/phpunit tests/feature/PublicRoutesTest.php --colors=always
pause
