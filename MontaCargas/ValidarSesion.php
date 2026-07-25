<?php

/*
 * Protección común del módulo MontaCargas.
 * 30 minutos de inactividad + 5 minutos de cortesía.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$limiteSesionMontaCargas = 35 * 60;
$ahoraSesionMontaCargas = time();
$ultimaActividadMontaCargas = isset($_SESSION['MTC_ULTIMA_ACTIVIDAD'])
    ? (int) $_SESSION['MTC_ULTIMA_ACTIVIDAD']
    : $ahoraSesionMontaCargas;

if (
    empty($_SESSION['Usuario'])
    || ($ahoraSesionMontaCargas - $ultimaActividadMontaCargas) >= $limiteSesionMontaCargas
) {
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $parametrosCookie = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametrosCookie['path'],
            $parametrosCookie['domain'],
            $parametrosCookie['secure'],
            $parametrosCookie['httponly']
        );
    }

    session_destroy();
    header('Location: ../Innet/logout.php');
    exit;
}

$_SESSION['MTC_ULTIMA_ACTIVIDAD'] = $ahoraSesionMontaCargas;

