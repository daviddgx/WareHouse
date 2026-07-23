<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Usuario']) || $_SESSION['Usuario'] === '') {
    if (defined('ADMIN_SESSION_JSON_RESPONSE') && ADMIN_SESSION_JSON_RESPONSE) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Sesion invalida',
        ]);
    } else {
        header('Location: ../Innet/505.html');
    }
    exit;
}
