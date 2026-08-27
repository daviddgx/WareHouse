<?php

/**
 * Protección común para todas las páginas del módulo de inventarios.
 *
 * - La sesión vence al cambiar de día o tras 35 minutos sin actividad.
 * - Las respuestas autenticadas no se guardan en la caché del navegador.
 * - Las operaciones de escritura pueden usar tokens de un solo uso y PRG.
 */
date_default_timezone_set('America/Guatemala');

if (session_id() === '') {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    $parametrosSesion = session_get_cookie_params();
    $sesionSegura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(
        $parametrosSesion['lifetime'],
        '/',
        $parametrosSesion['domain'],
        $sesionSegura,
        true
    );
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

function inventarios_cerrar_sesion()
{
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
}

$ahoraInventarios = time();
$fechaSesionInventarios = isset($_SESSION['UsuarioFecha'])
    ? (string) $_SESSION['UsuarioFecha']
    : '';
$ultimaActividadInventarios = isset($_SESSION['INV_ULTIMA_ACTIVIDAD'])
    ? (int) $_SESSION['INV_ULTIMA_ACTIVIDAD']
    : $ahoraInventarios;
$limiteInactividadInventarios = 35 * 60;

if (
    empty($_SESSION['Usuario'])
    || $fechaSesionInventarios !== date('Y-m-d')
    || ($ahoraInventarios - $ultimaActividadInventarios) >= $limiteInactividadInventarios
) {
    inventarios_cerrar_sesion();
    header('Location: /index.php?sesion=expirada', true, 303);
    exit;
}

$_SESSION['INV_ULTIMA_ACTIVIDAD'] = $ahoraInventarios;

function inventarios_ruta_actual()
{
    if (!empty($_SERVER['REQUEST_URI'])) {
        return (string) $_SERVER['REQUEST_URI'];
    }

    return !empty($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '/Inventarios/index.php';
}

function inventarios_ambito_token()
{
    return !empty($_SERVER['SCRIPT_NAME'])
        ? (string) $_SERVER['SCRIPT_NAME']
        : inventarios_ruta_actual();
}

function inventarios_podar_tokens()
{
    if (empty($_SESSION['_INV_TOKENS']) || !is_array($_SESSION['_INV_TOKENS'])) {
        $_SESSION['_INV_TOKENS'] = array();
        return;
    }

    $limite = time() - 7200;
    foreach ($_SESSION['_INV_TOKENS'] as $hash => $datos) {
        if (!is_array($datos) || empty($datos['creado']) || (int) $datos['creado'] < $limite) {
            unset($_SESSION['_INV_TOKENS'][$hash]);
        }
    }

    if (count($_SESSION['_INV_TOKENS']) > 500) {
        uasort($_SESSION['_INV_TOKENS'], function ($a, $b) {
            return (int) $a['creado'] - (int) $b['creado'];
        });
        $_SESSION['_INV_TOKENS'] = array_slice($_SESSION['_INV_TOKENS'], -500, null, true);
    }
}

function inventarios_campo_token($ambito = null)
{
    inventarios_podar_tokens();
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $_SESSION['_INV_TOKENS'][$hash] = array(
        'ambito' => $ambito !== null ? (string) $ambito : inventarios_ambito_token(),
        'creado' => time()
    );

    return '<input type="hidden" name="_inv_token" value="'
        . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function inventarios_guardar_flash($mensaje)
{
    $_SESSION['_INV_FLASH'][inventarios_ruta_actual()] = (string) $mensaje;
}

function inventarios_proteger_acciones($acciones)
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $accion = isset($_POST['accion']) && !is_array($_POST['accion'])
        ? (string) $_POST['accion']
        : '';
    if (!in_array($accion, $acciones, true)) {
        return;
    }

    inventarios_podar_tokens();
    $token = isset($_POST['_inv_token']) && !is_array($_POST['_inv_token'])
        ? (string) $_POST['_inv_token']
        : '';
    $hash = $token !== '' ? hash('sha256', $token) : '';
    $registro = $hash !== '' && isset($_SESSION['_INV_TOKENS'][$hash])
        ? $_SESSION['_INV_TOKENS'][$hash]
        : null;

    if (!is_array($registro) || $registro['ambito'] !== inventarios_ambito_token()) {
        inventarios_guardar_flash(
            '<div class="alert alert-warning" role="alert"><strong>Operación no repetida.</strong> '
            . 'La solicitud ya fue procesada, venció o provino de una página anterior.</div>'
        );
        header('Location: ' . inventarios_ruta_actual(), true, 303);
        exit;
    }

    // Se consume antes de escribir. La serialización de la sesión hace que dos
    // solicitudes simultáneas no puedan utilizar el mismo token.
    unset($_SESSION['_INV_TOKENS'][$hash]);
    $GLOBALS['_INV_POST_PROTEGIDO'] = true;
}

function inventarios_finalizar_post($mensaje = '')
{
    if (empty($GLOBALS['_INV_POST_PROTEGIDO'])) {
        return;
    }

    if ($mensaje === '') {
        $mensaje = '<div class="alert alert-success" role="alert">La operación fue procesada correctamente.</div>';
    }
    inventarios_guardar_flash($mensaje);
    header('Location: ' . inventarios_ruta_actual(), true, 303);
    exit;
}

function inventarios_restaurar_flash(&$mensajeExito, &$mensajeError)
{
    $ruta = inventarios_ruta_actual();
    if (empty($_SESSION['_INV_FLASH'][$ruta])) {
        return;
    }

    $mensajeError = (string) $_SESSION['_INV_FLASH'][$ruta];
    $mensajeExito = '';
    unset($_SESSION['_INV_FLASH'][$ruta]);
}
