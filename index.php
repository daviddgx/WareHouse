<?php
ob_start();
session_start();


//ANCHOR -  Redireccion a HTPPS
//if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
 //   echo "NO DISPONE DE CONEXIÓN HTTPS";
 //   header('Location: https://apps-sertero.com/');
//}



include 'LQS_EUQ/Auth.php';


// FuncionLogin


if (!empty($_POST['Entrar'])) {
    
    $LUser = $_POST['UserLog'];
    $LClave = $_POST['ClaveLog'];


    // Creamos la conexion

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {

        $error =
            '<div class="alert alert-danger" role="alert"><p><strong>Existe un problema con la conexion entre el sistema y la base de datos ☹️! por favor contacte al administrador de la aplicacion e informele de este error.</div>';
        // $row = $result->fetch_assoc();
    } else {
        // Obtencion de datos
        $LClave = md5($LClave);



        $sql = "SELECT * FROM dbs9098416.usuarios_app where Nombre_Usuario = '$LUser' and Clave_Usuario = '$LClave';";
        $result = $conn->query($sql);
        // Fin Obtencion de datos
        try {



            if ($result->num_rows > 0) {
                //Salida de datos del query

                // Cambiamos los IF anidados por Switch/Case para mejorar el rendimiento

                while ($row = $result->fetch_assoc()) {
                    $sessionDate = date('Y-m-d');

                    switch ($row['TipoUsuario']) {
                        case '1':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: Admin/index.php');
                            break;

                        case '2':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: MontaCargas/index.php');
                            break;

                        case '3':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: Inventarios/index.php');
                            break;

                        case '4':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: Picking/index.php');
                            break;
                        case '5':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: DashBoard/index.php');
                            break;
                        case '6':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: InventariosPL/index.php');
                            break;
                        case '7':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: InventariosDTG/index.php');
                            break;

                            case '22':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['UsuarioFecha'] = $sessionDate;
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: MontaCargas2/index.php');
                            break;
                    }
                }
            } else {
                $error =
                    '<div class="alert alert-danger" role="alert"><p><strong> Usuario o Clave incorrecta, intentelo de nuevo o actualice su clave en la seccion de ayuda. </div>';
                // $row = $result->fetch_assoc();
            }
        } catch (Exception $ex) {
            $error = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ☹️! -- </strong> ' . $ex . '
                                </div>';
        }
        //comprovacion de dadtos
        //fin comprovacion de datos
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
            <img src="../assets/images/Sertero/LogoHenkel.png" alt="Sertero" style="max-width: 180px; height: auto;">
            <h1>Pantalla de acceso Sertero</h1>
            <p>
                Bienvenido
                Sistema Logistico

                Control de Posiciones, planificacion y control de despachos, Inventario, Picking.

                Conectividad en tiempo real interoperabilidad con montacargas
            </p>
            <p style="font-size: 0.9rem; color: rgba(244, 247, 251, 0.7);">
                Interfaz optimizada y responsiva. 
            </p>
        </section>

        <section class="login-card">
            <h2>Ingreso al sistema</h2>
            <form role="form" action="" method="post">
                <div>
                    <input type="text" name="UserLog" placeholder="Usuario" class="form-control" id="UserLog" required>
                </div>
                <div>
                    <input type="password" name="ClaveLog" placeholder="Contraseña" class="form-control" id="ClaveLog" required>
                </div>
                <div class="message-container"><?php echo $error . $mensajeExito; ?></div>
                <button type="submit" name="Entrar"  value="Entrar"class="effect-button">Entrar al Sistema</button>
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