<?php
ob_start();
date_default_timezone_set('America/Guatemala');

// La cookie debe estar disponible en /MontaCargas, /Admin y demás módulos.
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
header('Permissions-Policy: geolocation=(self)');
session_start();


//ANCHOR -  Redireccion a HTPPS
//if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
 //   echo "NO DISPONE DE CONEXIÓN HTTPS";
 //   header('Location: https://apps-sertero.com/');
//}



include 'LQS_EUQ/Auth.php';

/**
 * Obtiene la IP visible para la aplicación.
 * HTTP_X_FORWARDED_FOR solo se conserva como dato de auditoría porque puede ser
 * manipulado si el servidor no está detrás de un proxy de confianza.
 */
function obtenerDireccionIP()
{
    return isset($_SERVER['REMOTE_ADDR'])
        ? substr((string) $_SERVER['REMOTE_ADDR'], 0, 45)
        : null;
}

/**
 * Lee texto enviado por el navegador, elimina caracteres de control y limita
 * su longitud antes de guardarlo en la bitácora.
 */
function obtenerTextoNavegador($campo, $longitudMaxima)
{
    if (!isset($_POST[$campo]) || is_array($_POST[$campo])) {
        return null;
    }

    $valor = trim((string) $_POST[$campo]);
    if ($valor === '') {
        return null;
    }

    $valorLimpio = preg_replace('/[\x00-\x1F\x7F]/u', '', $valor);
    if ($valorLimpio === null || $valorLimpio === '') {
        return null;
    }

    return function_exists('mb_substr')
        ? mb_substr($valorLimpio, 0, $longitudMaxima, 'UTF-8')
        : substr($valorLimpio, 0, $longitudMaxima);
}

/**
 * Valida una coordenada proporcionada por la API de geolocalización.
 * Los valores del navegador siguen siendo datos declarados por el cliente.
 */
function obtenerCoordenadaNavegador($campo, $minimo, $maximo)
{
    $valor = obtenerTextoNavegador($campo, 30);
    if ($valor === null || !is_numeric($valor)) {
        return null;
    }

    $coordenada = (float) $valor;
    return $coordenada >= $minimo && $coordenada <= $maximo
        ? $coordenada
        : null;
}

function obtenerDatosNavegadorLogin()
{
    $latitud = obtenerCoordenadaNavegador('GeoLatitud', -90, 90);
    $longitud = obtenerCoordenadaNavegador('GeoLongitud', -180, 180);

    // No se registra una ubicación parcial.
    if ($latitud === null || $longitud === null) {
        $latitud = null;
        $longitud = null;
    }

    $precision = obtenerTextoNavegador('GeoPrecision', 30);
    $precision = $precision !== null && is_numeric($precision) && (float) $precision >= 0
        ? min((float) $precision, 100000000)
        : null;

    $estadosPermitidos = array(
        'OBTENIDA',
        'DENEGADA',
        'NO_DISPONIBLE',
        'TIEMPO_AGOTADO',
        'ERROR',
        'PENDIENTE'
    );
    $estado = obtenerTextoNavegador('GeoEstado', 30);
    if (!in_array($estado, $estadosPermitidos, true)) {
        $estado = 'NO_ENVIADA';
    }

    return array(
        'latitud' => $latitud,
        'longitud' => $longitud,
        'precision_metros' => $precision,
        'estado_ubicacion' => $estado,
        'fecha_ubicacion_cliente' => obtenerTextoNavegador('GeoFecha', 50),
        'nombre_dispositivo' => obtenerTextoNavegador('NombreDispositivoNavegador', 255),
        'plataforma' => obtenerTextoNavegador('PlataformaNavegador', 100),
        'tipo_dispositivo' => obtenerTextoNavegador('TipoDispositivoNavegador', 50)
    );
}

/**
 * Registra un intento de acceso sin interrumpir el login si la bitácora falla.
 * Nunca recibe ni almacena la contraseña.
 */
function registrarIntentoLogin($conn, $usuario, $resultado, $motivo = null)
{
    try {
        $datosNavegador = obtenerDatosNavegadorLogin();
        $ip = obtenerDireccionIP();
        $ipsProxy = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? substr((string) $_SERVER['HTTP_X_FORWARDED_FOR'], 0, 500)
            : null;
        $agente = isset($_SERVER['HTTP_USER_AGENT'])
            ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 1000)
            : null;
        $plataforma = isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM'])
            ? trim(substr((string) $_SERVER['HTTP_SEC_CH_UA_PLATFORM'], 0, 100), '"')
            : null;
        if ($datosNavegador['plataforma'] !== null) {
            $plataforma = $datosNavegador['plataforma'];
        }
        $idioma = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            ? substr((string) $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 100)
            : null;
        $metodo = isset($_SERVER['REQUEST_METHOD'])
            ? substr((string) $_SERVER['REQUEST_METHOD'], 0, 10)
            : null;
        $uri = isset($_SERVER['REQUEST_URI'])
            ? substr((string) $_SERVER['REQUEST_URI'], 0, 500)
            : null;
        $referente = isset($_SERVER['HTTP_REFERER'])
            ? substr((string) $_SERVER['HTTP_REFERER'], 0, 1000)
            : null;

        // La ubicación procede exclusivamente de la API del navegador, no de la
        // IP del proxy. La API no proporciona país, región ni ciudad.
        $pais = null;
        $region = null;
        $ciudad = null;
        $latitud = $datosNavegador['latitud'];
        $longitud = $datosNavegador['longitud'];

        $nombreDispositivo = $datosNavegador['nombre_dispositivo'];
        if ($nombreDispositivo === null) {
            // Respaldo para clientes sin JavaScript; puede ser NULL detrás de un proxy.
            $nombreDispositivo = $ip ? @gethostbyaddr($ip) : null;
            if ($nombreDispositivo === $ip) {
                $nombreDispositivo = null;
            }
        }

        $idSesionHash = session_id() !== ''
            ? hash('sha256', session_id())
            : null;
        $datosAdicionales = json_encode(array(
            'host_solicitado' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
            'es_https' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'accept' => isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null,
            'ubicacion_navegador' => array(
                'fuente' => $latitud !== null && $longitud !== null
                    ? 'navigator.geolocation'
                    : null,
                'estado' => $datosNavegador['estado_ubicacion'],
                'precision_metros' => $datosNavegador['precision_metros'],
                'fecha_cliente' => $datosNavegador['fecha_ubicacion_cliente']
            ),
            'dispositivo_navegador' => array(
                'nombre_generado' => $datosNavegador['nombre_dispositivo'],
                'plataforma' => $datosNavegador['plataforma'],
                'tipo' => $datosNavegador['tipo_dispositivo']
            )
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fechaHoraGuatemala = (new DateTimeImmutable(
            'now',
            new DateTimeZone('America/Guatemala')
        ))->format('Y-m-d H:i:s.u');

        $sqlAuditoria = "INSERT INTO dbs9098416.AuditoriaIntentosLogin
            (Usuario, Resultado, Motivo, FechaHora, DireccionIP, IPsProxy, NombreDispositivo,
             AgenteUsuario, Plataforma, Idioma, MetodoHTTP, URI, Referente,
             PaisCodigo, Region, Ciudad, Latitud, Longitud, IdSesionHash, DatosAdicionales)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtAuditoria = $conn->prepare($sqlAuditoria);
        if (!$stmtAuditoria) {
            error_log('No se pudo preparar la auditoría de login: ' . $conn->error);
            return;
        }

        $stmtAuditoria->bind_param(
            'ssssssssssssssssddss',
            $usuario,
            $resultado,
            $motivo,
            $fechaHoraGuatemala,
            $ip,
            $ipsProxy,
            $nombreDispositivo,
            $agente,
            $plataforma,
            $idioma,
            $metodo,
            $uri,
            $referente,
            $pais,
            $region,
            $ciudad,
            $latitud,
            $longitud,
            $idSesionHash,
            $datosAdicionales
        );
        if (!$stmtAuditoria->execute()) {
            error_log('No se pudo registrar la auditoría de login: ' . $stmtAuditoria->error);
        }
        $stmtAuditoria->close();
    } catch (Throwable $ex) {
        error_log('Error al registrar la auditoría de login: ' . $ex->getMessage());
    }
}


// FuncionLogin


if (!empty($_POST['Entrar'])) {
    
    $LUser = trim((string) $_POST['UserLog']);
    $LClave = (string) $_POST['ClaveLog'];


    // Creamos la conexion

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {

        $error =
            '<div class="alert alert-danger" role="alert"><p><strong>Existe un problema con la conexion entre el sistema y la base de datos ☹️! por favor contacte al administrador de la aplicacion e informele de este error.</div>';
        // $row = $result->fetch_assoc();
    } else {
        // Obtencion de datos
        $LClave = md5($LClave);



        try {
            $sql = "SELECT Nombre_Usuario, Nombre, Apellido, Foto, TipoUsuario
                    FROM dbs9098416.usuarios_app
                    WHERE Nombre_Usuario = ? AND Clave_Usuario = ?
                    LIMIT 1";
            $stmtLogin = $conn->prepare($sql);
            if (!$stmtLogin) {
                throw new RuntimeException('No se pudo preparar la consulta de autenticación.');
            }

            $stmtLogin->bind_param('ss', $LUser, $LClave);
            if (!$stmtLogin->execute()) {
                throw new RuntimeException('No se pudo ejecutar la consulta de autenticación.');
            }

            $stmtLogin->store_result();

            if ($stmtLogin->num_rows > 0) {
                $stmtLogin->bind_result(
                    $usuarioEncontrado,
                    $nombreEncontrado,
                    $apellidoEncontrado,
                    $fotoEncontrada,
                    $tipoUsuarioEncontrado
                );
                $stmtLogin->fetch();

                $row = array(
                    'Nombre_Usuario' => $usuarioEncontrado,
                    'Nombre' => $nombreEncontrado,
                    'Apellido' => $apellidoEncontrado,
                    'Foto' => $fotoEncontrada,
                    'TipoUsuario' => $tipoUsuarioEncontrado
                );
                $stmtLogin->free_result();

                registrarIntentoLogin($conn, $LUser, 'SATISFACTORIO', 'CREDENCIALES_VALIDAS');
                session_regenerate_id(true);
                //Salida de datos del query

                // Cambiamos los IF anidados por Switch/Case para mejorar el rendimiento

                    $sessionDate = date('Y-m-d');

                    switch ($row['TipoUsuario']) {
                        case '1':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /Admin/index.php', true, 303);
                            exit;

                        case '2':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            $_SESSION['MTC_ULTIMA_ACTIVIDAD'] = time();
                            header('Location: /MontaCargas/index.php', true, 303);
                            exit;

                        case '3':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['INV_ULTIMA_ACTIVIDAD'] = time();
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /Inventarios/index.php', true, 303);
                            exit;

                        case '4':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /Picking/index.php', true, 303);
                            exit;
                        case '5':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /DashBoard/index.php', true, 303);
                            exit;
                        case '6':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /InventariosPL/index.php', true, 303);
                            exit;
                        case '7':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /InventariosDTG/index.php', true, 303);
                            exit;

                            case '22':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: /MontaCargas2/index.php', true, 303);
                            exit;
                    }
            } else {
                registrarIntentoLogin($conn, $LUser, 'FALLIDO', 'CREDENCIALES_INVALIDAS');
                $error =
                    '<div class="alert alert-danger" role="alert"><p><strong> Usuario o Clave incorrecta, intentelo de nuevo o actualice su clave en la seccion de ayuda. </div>';
                // $row = $result->fetch_assoc();
            }
        } catch (Throwable $ex) {
            registrarIntentoLogin($conn, $LUser, 'FALLIDO', 'ERROR_AUTENTICACION');
            $error = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ☹️! -- </strong> ' . $ex . '
                                </div>';
        }
        //comprovacion de dadtos
        //fin comprovacion de datos
        if (isset($stmtLogin) && $stmtLogin instanceof mysqli_stmt) {
            $stmtLogin->close();
        }
        $conn->close();
    }

    // Fin de la conexion
}

// FinFuncionLogIN
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertero CBP</title>
    <link rel="icon" href="assets/images/favicon.png">
    <style>
        :root {
            color-scheme: light dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, rgba(5, 31, 64, 0.92), rgba(3, 111, 171, 0.9)), url('../assets/images/Sertero/Wallpaler.jpg') no-repeat center/cover fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #f4f7fb;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            backdrop-filter: blur(12px);
            background: rgba(0, 0, 0, 0.35);
            position: sticky;
            top: 0;
            z-index: 5;
            gap: 1.5rem;
        }

        .top-info,
        .weather-info {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
        }

        .top-info span,
        .weather-info span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .main-content {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 4vh 6vw;
            align-items: center;
        }

        .brand-card {
            padding: 2.5rem;
            background: rgba(5, 25, 60, 0.55);
            border-radius: 24px;
            backdrop-filter: blur(16px);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: #f4f7fb;
        }

        .brand-card h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.8vw, 2.8rem);
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-card p {
            margin: 0;
            font-size: 1rem;
            color: rgba(244, 247, 251, 0.85);
            line-height: 1.6;
        }

        .login-card {
            padding: clamp(1.75rem, 2.5vw, 2.5rem);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            backdrop-filter: blur(14px);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.16);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .login-card h2 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 600;
            text-align: center;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1.1rem;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(3, 19, 41, 0.65);
            color: #f4f7fb;
            font-size: 1rem;
            transition: border-color 0.3s ease, background 0.3s ease;
        }

        .form-control::placeholder {
            color: rgba(244, 247, 251, 0.55);
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(18, 194, 233, 0.9);
            background: rgba(3, 19, 41, 0.85);
        }

        .effect-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 0.95rem 1.1rem;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #12c2e9, #c471ed, #f64f59);
            color: #fff;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(18, 194, 233, 0.35);
            transition: transform 0.3s ease, box-shadow 0.3s ease, filter 0.3s ease;
        }

        .effect-button:hover,
        .effect-button:focus {
            transform: translateY(-2px);
            box-shadow: 0 22px 40px rgba(244, 79, 89, 0.32);
            filter: brightness(1.05);
        }

        .message-container {
            min-height: 1.2rem;
            font-size: 0.95rem;
        }

        .footer-note {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.85rem;
            color: rgba(244, 247, 251, 0.6);
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem 1.5rem;
            }

            .main-content {
                padding: 3vh 6vw;
                gap: 1.5rem;
            }
        }

        @media (max-width: 520px) {
            .main-content {
                grid-template-columns: 1fr;
                padding: 2.5vh 1.5rem;
            }

            .top-bar {
                border-radius: 0 0 18px 18px;
            }

            body {
                background-attachment: scroll;
            }
        }
        /* Identidad visual compartida con la página 505 */
        :root {
            color-scheme: dark;
            --bg: #07111f;
            --surface: rgba(15, 30, 49, .76);
            --surface-border: rgba(255, 255, 255, .12);
            --text: #f7fafc;
            --muted: #a9b8ca;
            --accent: #ff912b;
        }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            padding: 24px;
            display: grid;
            place-items: center;
            overflow-x: hidden;
            color: var(--text);
            background:
                radial-gradient(circle at 16% 18%, rgba(255, 145, 43, .15), transparent 28rem),
                radial-gradient(circle at 90% 85%, rgba(58, 105, 255, .14), transparent 30rem),
                var(--bg);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .2;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(to bottom, #000, transparent 85%);
        }

        .top-bar {
            position: fixed;
            top: 26px;
            right: 32px;
            z-index: 5;
            padding: 0;
            color: var(--muted);
            background: none;
            backdrop-filter: none;
            font-size: .8rem;
        }

        .weather-info { display: none; }

        .main-content {
            width: min(1080px, 100%);
            min-height: min(680px, calc(100dvh - 48px));
            grid-template-columns: minmax(0, 1.08fr) minmax(330px, .92fr);
            gap: 0;
            padding: 0;
            overflow: hidden;
            border: 1px solid var(--surface-border);
            border-radius: 30px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(0, 0, 0, .4);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            animation: loginEnter .6s cubic-bezier(.2, .8, .2, 1) both;
        }

        .brand-card, .login-card {
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .brand-card {
            position: relative;
            justify-content: center;
            padding: clamp(36px, 6vw, 70px);
            background:
                linear-gradient(110deg, rgba(5, 16, 29, .94), rgba(5, 16, 29, .66)),
                url("assets/images/Sertero/WMS.jpeg") center/cover no-repeat;
            overflow: hidden;
        }

        .brand-card::after {
            content: "";
            position: absolute;
            width: 340px;
            aspect-ratio: 1;
            right: -110px;
            bottom: -120px;
            border: 1px solid rgba(255, 145, 43, .24);
            border-radius: 50%;
            box-shadow:
                0 0 0 38px rgba(255, 145, 43, .035),
                0 0 0 80px rgba(255, 145, 43, .018);
            animation: loginBreathe 4s ease-in-out infinite;
        }

        .brand-identity {
            position: absolute;
            top: clamp(32px, 5vw, 54px);
            left: clamp(36px, 6vw, 70px);
            display: inline-flex;
            align-items: center;
            gap: 11px;
            font-weight: 780;
        }

        .sertero-logo {
            width: min(205px, 48vw);
            height: auto;
            display: block;
            filter: drop-shadow(0 10px 26px rgba(0, 0, 0, .35));
        }

        .eyebrow {
            margin: 0 0 14px;
            color: var(--accent);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .brand-card h1 {
            max-width: 560px;
            font-size: clamp(2.3rem, 4.8vw, 4.4rem);
            line-height: .99;
            letter-spacing: -.055em;
        }

        .brand-card p {
            max-width: 540px;
            color: var(--muted);
            font-size: 1.04rem;
            line-height: 1.7;
        }

        .brand-card > * { position: relative; z-index: 1; }

        .logistics-modules {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 8px;
        }

        .module {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 11px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 9px;
            color: #dce7f1;
            background: rgba(4, 14, 25, .52);
            backdrop-filter: blur(8px);
            font-size: .75rem;
            font-weight: 680;
        }

        .module-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 145, 43, .13);
        }

        .login-card {
            justify-content: center;
            padding: clamp(36px, 6vw, 64px);
            border-left: 1px solid var(--surface-border);
            background: rgba(5, 15, 27, .36);
        }

        .login-card h2 {
            font-size: clamp(1.65rem, 3vw, 2.1rem);
            text-align: left;
            letter-spacing: -.03em;
        }

        .login-intro {
            margin: -10px 0 4px;
            color: var(--muted);
            line-height: 1.55;
        }

        .login-card form { display: grid; gap: 20px; }
        .field { display: grid; gap: 8px; }

        .field label {
            color: #dbe5ef;
            font-size: .85rem;
            font-weight: 680;
        }

        .password-wrap { position: relative; }

        .form-control {
            min-height: 52px;
            padding: 0 46px 0 16px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 13px;
            color: var(--text);
            background: rgba(4, 14, 25, .55);
            font: inherit;
        }

        .form-control:focus {
            border-color: var(--accent);
            background: rgba(4, 14, 25, .82);
            box-shadow: 0 0 0 4px rgba(255, 145, 43, .1);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 8px;
            min-width: 38px;
            min-height: 38px;
            border: 0;
            border-radius: 9px;
            color: var(--muted);
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .toggle-password:hover { color: var(--accent); background: rgba(255,255,255,.05); }
        .message-container:empty { display: none; }

        .message-container .alert {
            margin: 0;
            padding: 12px 14px;
            border: 1px solid rgba(255, 125, 137, .22);
            border-radius: 11px;
            color: #ffd9dc;
            background: rgba(255, 125, 137, .08);
            line-height: 1.5;
        }

        .message-container p { margin: 0; }
        .message-container .close { display: none; }

        .effect-button {
            min-height: 52px;
            gap: 9px;
            padding: 0 19px;
            border-radius: 13px;
            color: #032b27;
            background: var(--accent);
            box-shadow: 0 10px 30px rgba(255, 145, 43, .2);
            font-weight: 760;
        }

        .effect-button:hover, .effect-button:focus {
            color: #032b27;
            background: #ffa44f;
            box-shadow: 0 14px 34px rgba(255, 145, 43, .24);
        }

        .effect-button:disabled { cursor: wait; opacity: .7; transform: none; }

        .privacy {
            margin: 2px 0 0;
            color: #718499;
            font-size: .76rem;
            line-height: 1.55;
            text-align: center;
        }

        .footer-note { display: none; }

        @keyframes loginEnter {
            from { opacity: 0; transform: translateY(18px) scale(.985); }
        }

        @keyframes loginBreathe {
            50% { transform: scale(1.04); opacity: .72; }
        }

        @media (max-width: 780px) {
            body { padding: 16px; }
            .top-bar { position: absolute; top: 30px; right: 22px; }
            .top-info span:first-child { display: none; }
            .main-content { grid-template-columns: 1fr; border-radius: 24px; }
            .brand-card { min-height: 430px; padding-top: 120px; }
            .login-card { border-top: 1px solid var(--surface-border); border-left: 0; }
        }

        @media (max-width: 430px) {
            body { padding: 0; }
            .main-content { min-height: 100dvh; border: 0; border-radius: 0; }
            .brand-card, .login-card { padding: 30px 22px; }
            .brand-card { min-height: 410px; padding-top: 110px; }
            .brand-identity { left: 22px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <div class="top-info">
            <span>📅 <span id="current-date">--</span></span>
            <span>🕒 <span id="current-time">--</span></span>
        </div>
        <div class="weather-info" id="weather-info">
            <span>🌤️ <span id="weather-status">Cargando clima...</span></span>
        </div>
    </div>

    <main class="main-content">
        <section class="brand-card">
            <div class="brand-identity">
                <img class="sertero-logo" src="assets/images/Sertero/LogoHenkel.png"
                     alt="Sertero">
            </div>
            <br>
            <br>
            <p class="eyebrow">Warehouse Management System</p>
            <h1>Logística conectada. Operación bajo control.</h1>
            <p>
                Una plataforma central para coordinar inventario, ubicaciones, picking,
                despachos y movimientos de montacargas en tiempo real.
            </p>
            <div class="logistics-modules" aria-label="Módulos del sistema">
                <span class="module"><span class="module-dot"></span>Inventario</span>
                <span class="module"><span class="module-dot"></span>Picking</span>
                <span class="module"><span class="module-dot"></span>Despachos</span>
                <span class="module"><span class="module-dot"></span>Montacargas</span>
            </div>
        </section>

        <section class="login-card">
            <h2>Bienvenido de nuevo</h2>
            <p class="login-intro">Ingresa tus credenciales para continuar.</p>
            <form id="loginForm" role="form" action="/index.php" method="post">
                <input type="hidden" name="Entrar" value="Entrar">
                <input type="hidden" name="GeoLatitud" id="GeoLatitud">
                <input type="hidden" name="GeoLongitud" id="GeoLongitud">
                <input type="hidden" name="GeoPrecision" id="GeoPrecision">
                <input type="hidden" name="GeoFecha" id="GeoFecha">
                <input type="hidden" name="GeoEstado" id="GeoEstado" value="PENDIENTE">
                <input type="hidden" name="NombreDispositivoNavegador" id="NombreDispositivoNavegador">
                <input type="hidden" name="PlataformaNavegador" id="PlataformaNavegador">
                <input type="hidden" name="TipoDispositivoNavegador" id="TipoDispositivoNavegador">
                <div class="field">
                    <label for="UserLog">Usuario</label>
                    <input type="text" name="UserLog" placeholder="Escribe tu usuario"
                           class="form-control" id="UserLog" autocomplete="username"
                           maxlength="100" autocapitalize="none" spellcheck="false" required autofocus>
                </div>
                <div>
                    <input type="password" name="ClaveLog" placeholder="Contraseña" class="form-control" id="ClaveLog" required>
                </div>
                <div class="message-container" role="alert" aria-live="polite"><?php echo $error . $mensajeExito; ?></div>
                <p class="privacy" id="locationStatus" aria-live="polite">La ubicación se solicitará al ingresar.</p>
                <button type="submit" class="effect-button" id="submitButton">
                    <span id="submitText">Entrar al sistema</span>
                    <span aria-hidden="true">→</span>
                </button>
                <p class="privacy">Acceso exclusivo para personal autorizado. Los intentos, la ubicación compartida y los datos del dispositivo son auditados.</p>
            </form>
        </section>
    </main>

    <footer class="footer-note">
        © <?php echo date('Y'); ?> Sertero CBP. Todos los derechos reservados.
    </footer>

    <script>
        const dateElement = document.getElementById('current-date');
        const timeElement = document.getElementById('current-time');

        const updateDateTime = () => {
            const now = new Date();
            const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'America/Guatemala' };
            const optionsTime = { hour: 'numeric', minute: '2-digit', second: '2-digit', timeZone: 'America/Guatemala' };

            dateElement.textContent = now.toLocaleDateString('es-GT', optionsDate);
            timeElement.textContent = now.toLocaleTimeString('es-GT', optionsTime);
        };

        updateDateTime();
        setInterval(updateDateTime, 1000);

        const loginForm = document.getElementById('loginForm');
        const passwordInput = document.getElementById('ClaveLog');
        const passwordContainer = passwordInput.parentElement;
        const togglePassword = document.createElement('button');
        const submitButton = document.getElementById('submitButton');
        const submitText = document.getElementById('submitText');
        const locationStatus = document.getElementById('locationStatus');
        const geoLatitud = document.getElementById('GeoLatitud');
        const geoLongitud = document.getElementById('GeoLongitud');
        const geoPrecision = document.getElementById('GeoPrecision');
        const geoFecha = document.getElementById('GeoFecha');
        const geoEstado = document.getElementById('GeoEstado');
        const nombreDispositivo = document.getElementById('NombreDispositivoNavegador');
        const plataformaNavegador = document.getElementById('PlataformaNavegador');
        const tipoDispositivoNavegador = document.getElementById('TipoDispositivoNavegador');
        let enviandoFormulario = false;

        const obtenerNombreNavegador = () => {
            const ua = navigator.userAgent;
            if (/Edg\/|EdgA\/|EdgiOS\//.test(ua)) return 'Microsoft Edge';
            if (/OPR\/|OPiOS\//.test(ua)) return 'Opera';
            if (/Firefox\/|FxiOS\//.test(ua)) return 'Firefox';
            if (/Chrome\/|CriOS\//.test(ua)) return 'Chrome';
            if (/Safari\//.test(ua)) return 'Safari';
            return 'Navegador';
        };

        const obtenerTipoDispositivo = () => {
            const ua = navigator.userAgent;
            if (/iPad|Tablet|PlayBook|Silk/i.test(ua)
                || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
                return 'Tableta';
            }
            if (/Mobi|Android|iPhone|iPod/i.test(ua)) return 'Móvil';
            return 'Escritorio';
        };

        const normalizarPlataforma = (plataformaDetectada) => {
            const referencia = `${plataformaDetectada || ''} ${navigator.userAgent}`;
            if (/Android/i.test(referencia)) return 'Android';
            if (/iPhone|iPad|iPod/i.test(referencia)) return 'iOS/iPadOS';
            if (/CrOS/i.test(referencia)) return 'ChromeOS';
            if (/Windows|Win32|Win64/i.test(referencia)) return 'Windows';
            if (/Mac/i.test(referencia)) return 'macOS';
            if (/Linux/i.test(referencia)) return 'Linux';
            return plataformaDetectada || 'Plataforma desconocida';
        };

        const actualizarDatosDispositivo = (datosAvanzados = {}) => {
            const plataforma = normalizarPlataforma(datosAvanzados.platform
                || (navigator.userAgentData && navigator.userAgentData.platform)
                || navigator.platform
                || '');
            const modelo = datosAvanzados.model ? datosAvanzados.model.trim() : '';
            const tipo = obtenerTipoDispositivo();
            const partesNombre = [
                modelo || plataforma,
                obtenerNombreNavegador(),
                tipo
            ];

            nombreDispositivo.value = partesNombre.join(' · ').slice(0, 255);
            plataformaNavegador.value = plataforma.slice(0, 100);
            tipoDispositivoNavegador.value = tipo.slice(0, 50);
        };

        actualizarDatosDispositivo();

        // En navegadores Chromium, el modelo está disponible principalmente en
        // dispositivos móviles. Si no existe, se conserva sistema + navegador + tipo.
        if (navigator.userAgentData && navigator.userAgentData.getHighEntropyValues) {
            navigator.userAgentData
                .getHighEntropyValues(['model', 'platform', 'platformVersion'])
                .then(actualizarDatosDispositivo)
                .catch(() => {});
        }

        passwordContainer.classList.add('field', 'password-wrap');
        passwordInput.setAttribute('autocomplete', 'current-password');
        togglePassword.type = 'button';
        togglePassword.className = 'toggle-password';
        togglePassword.textContent = 'Ver';
        togglePassword.setAttribute('aria-label', 'Mostrar contraseña');
        togglePassword.setAttribute('aria-pressed', 'false');
        passwordContainer.appendChild(togglePassword);

        togglePassword.addEventListener('click', () => {
            const showing = passwordInput.type === 'text';
            passwordInput.type = showing ? 'password' : 'text';
            togglePassword.textContent = showing ? 'Ver' : 'Ocultar';
            togglePassword.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
            togglePassword.setAttribute('aria-pressed', String(!showing));
            passwordInput.focus();
        });

        const enviarLogin = () => {
            enviandoFormulario = true;
            submitText.textContent = 'Validando acceso…';
            HTMLFormElement.prototype.submit.call(loginForm);
        };

        loginForm.addEventListener('submit', (event) => {
            if (enviandoFormulario || !loginForm.checkValidity()) return;

            event.preventDefault();
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.disabled = true;
            submitText.textContent = 'Solicitando ubicación…';
            locationStatus.textContent = 'Acepta el permiso del navegador para compartir tu ubicación.';

            if (!navigator.geolocation) {
                geoEstado.value = 'NO_DISPONIBLE';
                locationStatus.textContent = 'Este navegador no permite obtener la ubicación. Se registrará como no disponible.';
                enviarLogin();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (posicion) => {
                    geoLatitud.value = String(posicion.coords.latitude);
                    geoLongitud.value = String(posicion.coords.longitude);
                    geoPrecision.value = String(posicion.coords.accuracy);
                    geoFecha.value = new Date(posicion.timestamp).toISOString();
                    geoEstado.value = 'OBTENIDA';
                    locationStatus.textContent = 'Ubicación compartida correctamente.';
                    enviarLogin();
                },
                (errorUbicacion) => {
                    const estadosError = {
                        1: 'DENEGADA',
                        2: 'NO_DISPONIBLE',
                        3: 'TIEMPO_AGOTADO'
                    };
                    geoEstado.value = estadosError[errorUbicacion.code] || 'ERROR';
                    locationStatus.textContent = errorUbicacion.code === 1
                        ? 'No se compartió la ubicación. El acceso continuará y se registrará el permiso denegado.'
                        : 'No fue posible obtener la ubicación. El acceso continuará y se registrará el estado.';
                    enviarLogin();
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 60000
                }
            );
        });

        const weatherStatus = document.getElementById('weather-status');

        fetch('https://api.open-meteo.com/v1/forecast?latitude=14.6331&longitude=-90.6070&current_weather=true&timezone=America%2FGuatemala')
            .then(response => response.ok ? response.json() : Promise.reject(response.statusText))
            .then(data => {
                if (data?.current_weather) {
                    const { temperature, windspeed, weathercode } = data.current_weather;
                    const descriptions = {
                        0: 'Despejado',
                        1: 'Mayormente despejado',
                        2: 'Parcialmente nublado',
                        3: 'Nublado',
                        45: 'Niebla',
                        48: 'Niebla helada',
                        51: 'Llovizna ligera',
                        53: 'Llovizna moderada',
                        55: 'Llovizna intensa',
                        61: 'Lluvia ligera',
                        63: 'Lluvia moderada',
                        65: 'Lluvia intensa',
                        80: 'Chubascos ligeros',
                        81: 'Chubascos moderados',
                        82: 'Chubascos fuertes',
                        95: 'Tormenta eléctrica'
                    };
                    const description = descriptions[weathercode] || 'Condición variable';
                    weatherStatus.textContent = `${description} · ${temperature.toFixed(0)}°C · Viento ${windspeed.toFixed(0)} km/h`;
                } else {
                    weatherStatus.textContent = 'No se pudo obtener el clima.';
                }
            })
            .catch(() => {
                weatherStatus.textContent = 'Clima no disponible en este momento.';
            });
    </script>
</body>

</html>
