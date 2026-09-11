<?php
ob_start();
require_once __DIR__ . '/_bootstrap.php';
include "../Innet_MTC/Innet_MTC.php";

$metodoSolicitud = isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'GET';
if ($metodoSolicitud !== 'POST') {
    header('Location: Print_Cardex.php', true, 303);
    exit;
}

inventarios_proteger_acciones(array('anularIngreso'));
$ubicacion = isset($_POST['Ubicacion']) && !is_array($_POST['Ubicacion'])
    ? (string) $_POST['Ubicacion']
    : '';

if ($ubicacion !== '' && ctype_digit($ubicacion)) {
    AnularIngreso($ubicacion);
    LiberarUbicacionAnulada($ubicacion);
}

header('Location: Print_Cardex.php', true, 303);
exit;
