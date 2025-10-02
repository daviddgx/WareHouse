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

exit($errores ? 1 : 0);
