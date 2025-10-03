<?php
ob_start();
session_start();

// ANCHOR -  Redireccion a HTPPS
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') && php_sapi_name() !== 'cli-server') {
    echo "NO DISPONE DE CONEXIÓN HTTPS";
    header('Location: https://apps-sertero.com/');
}

include 'LQS_EUQ/Auth.php';

$errorMessage = $errorMessage ?? '';
$mensajeExito = $mensajeExito ?? '';

if (!empty($_POST['Entrar'])) {
    $LUser = trim($_POST['UserLog'] ?? '');
    $LClave = $_POST['ClaveLog'] ?? '';

    if ($LUser === '' || $LClave === '') {
        $errorMessage = 'Debe ingresar su usuario y contraseña.';
    } else {
        $conn = @new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_errno) {
            $errorMessage = 'Existe un problema con la conexión entre el sistema y la base de datos ☹️. Por favor contacte al administrador de la aplicación e infórmele de este error.';
        } else {
            $hashedPassword = md5($LClave);
            $sql = "SELECT * FROM dbs9098416.usuarios_app WHERE Nombre_Usuario = ? AND Clave_Usuario = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $errorMessage = 'No fue posible validar sus credenciales en este momento. Intente de nuevo más tarde.';
            } else {
                $stmt->bind_param('ss', $LUser, $hashedPassword);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $sessionDate = date('Y-m-d');

                    while ($row = $result->fetch_assoc()) {
                        $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                        $_SESSION['UsuarioFecha'] = $sessionDate;
                        $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                        $_SESSION['pic'] = $row['Foto'];

                        switch ($row['TipoUsuario']) {
                            case '1':
                                header('Location: Admin/index.php');
                                break;
                            case '2':
                                header('Location: MontaCargas/index.php');
                                break;
                            case '3':
                                header('Location: Inventarios/index.php');
                                break;
                            case '4':
                                header('Location: Picking/index.php');
                                break;
                            case '5':
                                header('Location: DashBoard/index.php');
                                break;
                            case '6':
                                header('Location: InventariosPL/index.php');
                                break;
                            case '7':
                                header('Location: InventariosDTG/index.php');
                                break;
                            default:
                                header('Location: index.php');
                                break;
                        }

                        exit;
                    }
                } else {
                    $errorMessage = 'Usuario o clave incorrecta. Inténtelo de nuevo o actualice su clave en la sección de ayuda.';
                }

                $stmt->close();
            }

            $conn->close();
        }
    }
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertero CBP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/images/sertero/LogoCBP.png" width="auto" height="auto">
    <style>
        :root {
            color-scheme: light dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            color: #f4f7fb;
            background: linear-gradient(135deg, rgba(5, 31, 64, 0.92), rgba(3, 111, 171, 0.9)), url('../assets/images/Sertero/Wallpaler.jpg') no-repeat center/cover fixed;
            padding: 2rem 1.5rem;
        }

        .page-wrapper {
            width: min(920px, 100%);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            align-items: stretch;
        }

        .brand-card,
        .login-card {
            border-radius: 24px;
            padding: clamp(1.75rem, 2.5vw, 2.75rem);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(5, 25, 60, 0.6);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.35);
        }

        .brand-card {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .brand-card img {
            width: min(220px, 70%);
            height: auto;
        }

        .brand-card h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.8vw, 2.6rem);
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-card p {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(244, 247, 251, 0.82);
        }

        .login-card {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            background: rgba(255, 255, 255, 0.08);
        }

        .login-card h2 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 600;
            text-align: center;
        }

        form {
            display: grid;
            gap: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1.1rem;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(3, 19, 41, 0.7);
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

        .floating-alert {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            max-width: min(360px, calc(100% - 3rem));
            padding: 1rem 1.25rem;
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            background: rgba(220, 53, 69, 0.92);
            color: #fff;
            z-index: 20;
            backdrop-filter: blur(12px);
            transition: opacity 0.35s ease, transform 0.35s ease;
        }

        .floating-alert.success {
            background: rgba(40, 167, 69, 0.92);
        }

        .floating-alert.hidden {
            opacity: 0;
            transform: translateY(-12px);
            pointer-events: none;
        }

        .floating-alert .alert-text {
            flex: 1;
            line-height: 1.45;
        }

        .floating-alert .alert-close {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .floating-alert .alert-close:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 50%;
        }

        footer {
            margin-top: 2.5rem;
            font-size: 0.85rem;
            color: rgba(244, 247, 251, 0.65);
            text-align: center;
        }

        @media (max-width: 520px) {
            body {
                padding: 1.5rem 1rem;
            }

            .page-wrapper {
                gap: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php if (!empty($errorMessage) || !empty($mensajeExito)) : ?>
        <div class="floating-alert <?php echo empty($errorMessage) ? 'success' : 'error'; ?>" role="alert" aria-live="assertive">
            <span class="alert-text">
                <?php echo htmlspecialchars(empty($errorMessage) ? $mensajeExito : $errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <button type="button" class="alert-close" aria-label="Cerrar notificación">×</button>
        </div>
    <?php endif; ?>

    <div class="page-wrapper">
        <section class="brand-card">
            <img src="../assets/images/Sertero/LogoHenkel.png" alt="Sertero">
            <h1>Bienvenido al ecosistema logístico de Sertero</h1>
            <p>Centraliza tus operaciones y gestiona la información crítica de manera segura desde cualquier dispositivo.</p>
            <p style="font-size: 0.9rem; color: rgba(244, 247, 251, 0.7);">Optimizado para tabletas y móviles. Mantente productivo estés donde estés.</p>
        </section>
        <section class="login-card">
            <h2>Ingreso al sistema</h2>
            <form role="form" action="" method="post">
                <input type="text" name="UserLog" placeholder="Usuario" class="form-control" id="form-username" required>
                <input type="password" name="ClaveLog" placeholder="Contraseña" class="form-control" id="form-password" required>
                <button type="submit" name="Entrar" class="effect-button">Entrar</button>
            </form>
        </section>
    </div>

    <footer>
        © <?php echo date('Y'); ?> Sertero CBP. Todos los derechos reservados.
    </footer>

    <script>
        const floatingAlert = document.querySelector('.floating-alert');

        if (floatingAlert) {
            const closeButton = floatingAlert.querySelector('.alert-close');

            const hideAlert = () => {
                if (!floatingAlert.classList.contains('hidden')) {
                    floatingAlert.classList.add('hidden');
                    floatingAlert.addEventListener('transitionend', () => floatingAlert.remove(), { once: true });
                }
            };

            closeButton?.addEventListener('click', hideAlert);
            setTimeout(hideAlert, 7000);
        }
    </script>
</body>

</html>
