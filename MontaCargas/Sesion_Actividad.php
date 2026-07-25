<?php

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$ahora = time();
$limite = 35 * 60;
$ultimaActividad = isset($_SESSION['MTC_ULTIMA_ACTIVIDAD'])
    ? (int) $_SESSION['MTC_ULTIMA_ACTIVIDAD']
    : $ahora;

if (empty($_SESSION['Usuario']) || ($ahora - $ultimaActividad) >= $limite) {
    $_SESSION = array();
    session_destroy();
    http_response_code(401);
    echo json_encode(array('activa' => false));
    exit;
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : 'estado';

if ($accion === 'actividad') {
    $_SESSION['MTC_ULTIMA_ACTIVIDAD'] = $ahora;
}

echo json_encode(array(
    'activa' => true,
    'ultimaActividad' => isset($_SESSION['MTC_ULTIMA_ACTIVIDAD'])
        ? (int) $_SESSION['MTC_ULTIMA_ACTIVIDAD']
        : $ahora
));

