#!/usr/bin/env php
<?php
/**
 * Script para ejecutar los procesos que consolidan el estatus de bodegas.
 * Puede programarse en cron (por ejemplo: cada 5 minutos) ejecutando
 * `php /ruta/al/repositorio/cron/actualizar_estatus_bodegas.php` para
 * mantener la información actualizada sin depender de la carga de la vista.
 */

declare(strict_types=1);

require_once __DIR__ . '/../Innet_ADM/Innet_AMD.php';

// Este proceso puede tardar mas que una peticion web. Debe ejecutarse con PHP
// CLI y nunca deben existir dos instancias modificando las mismas tablas.
if (PHP_SAPI !== 'cli') {
    http_response_code(400);
    exit('Este proceso solo puede ejecutarse desde PHP CLI.' . PHP_EOL);
}
set_time_limit(0);

$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'warehouse_estatus_bodegas.lock';
$lockHandle = fopen($lockPath, 'c');
if ($lockHandle === false) {
    fwrite(STDERR, 'No fue posible crear el archivo de bloqueo del proceso.' . PHP_EOL);
    exit(1);
}
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, 'El proceso ya se encuentra en ejecucion; se omite esta instancia.' . PHP_EOL);
    fclose($lockHandle);
    exit(0);
}

$procesos = [
    'GraphEstatusBodegas' => 'Generar estatus diario de bodegas',
    'Limpiar_Nulls' => 'Limpiar ubicaciones con valores nulos',
    'LimpiarExesoPiking' => 'Normalizar unidades en picking',
    'BloquearCarrilesPiking' => 'Bloquear carriles de picking configurados',
];

$errores = false;

foreach ($procesos as $funcion => $descripcion) {
    if (!function_exists($funcion)) {
        $mensaje = sprintf('El proceso "%s" no está disponible (función %s).', $descripcion, $funcion);
        fwrite(STDERR, $mensaje . PHP_EOL);
        error_log($mensaje);
        $errores = true;
        continue;
    }

    try {
        $funcion();
        echo sprintf('[OK] %s (%s)%s', $descripcion, $funcion, PHP_EOL);
    } catch (Throwable $exception) {
        $mensaje = sprintf('Error al ejecutar %s (%s): %s', $descripcion, $funcion, $exception->getMessage());
        fwrite(STDERR, $mensaje . PHP_EOL);
        error_log($mensaje);
        $errores = true;
    }
}

flock($lockHandle, LOCK_UN);
fclose($lockHandle);
exit($errores ? 1 : 0);
