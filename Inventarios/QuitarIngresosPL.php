<?php
ob_start();
require_once __DIR__ . '/_bootstrap.php';
include '../LQS_EUQ/Connect.php';

$metodoSolicitud = isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'GET';
if ($metodoSolicitud !== 'POST') {
    header('Location: Print_CardexMasivo.php', true, 303);
    exit;
}

inventarios_proteger_acciones(array('quitarIngreso'));
$transporte = isset($_POST['Guia']) && !is_array($_POST['Guia'])
    ? (string) $_POST['Guia']
    : '';

if ($transporte !== '' && ctype_digit($transporte)) {
    $conexion = new mysqli($servername, $username, $password, $dbname);
    try {
        $consulta = $conexion->prepare('DELETE FROM dbs9098416.asignacionesPL WHERE Numero = ?');
        $consulta->bind_param('i', $transporte);
        $consulta->execute();
        $consulta->close();
    } catch (Exception $e) {
        error_log('No se pudo quitar el ingreso temporal: ' . $e->getMessage());
    }
}

header('Location: Print_CardexMasivo.php', true, 303);
exit;
