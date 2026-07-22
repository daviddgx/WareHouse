@echo off
setlocal

set "ORIGEN=C:\Proyectos DOG\WareHouse"
set "DESTINO=C:\xampp\htdocs\FIFO"

echo Copiando archivos desde:
echo   %ORIGEN%
echo hacia:
echo   %DESTINO%
echo.

if not exist "%DESTINO%" mkdir "%DESTINO%"

robocopy "%ORIGEN%" "%DESTINO%" /E /COPY:DAT /DCOPY:T /R:2 /W:2
set "RESULTADO=%ERRORLEVEL%"

if %RESULTADO% GEQ 8 (
    echo.
    echo ERROR: La copia no se completo correctamente. Codigo: %RESULTADO%
    pause
    exit /b %RESULTADO%
)

echo.
echo Copia completada correctamente.
pause
exit /b 0
