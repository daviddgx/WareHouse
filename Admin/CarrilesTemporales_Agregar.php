<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

date_default_timezone_set('America/Guatemala');

$tablaCarrilesTemporales = 'posisciones_temporalesCNF';
$mensajeExito = '';
$mensajeError = '';
$ubicacionesDisponibles = [];

try {
    $conexion = lqs_get_connection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? 'manual';

        if ($accion === 'masiva') {
            if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
                $mensajeError = '<div class="alert alert-danger">Seleccione un archivo Excel válido.</div>';
            } else {
                $archivoTemporal = $_FILES['archivo_excel']['tmp_name'];
                $spreadsheet = IOFactory::load($archivoTemporal);
                $hoja = $spreadsheet->getActiveSheet();
                $filas = $hoja->toArray(null, true, true, true);

                $encabezados = array_shift($filas);
                $columnaUbicacion = null;
                $columnaEstado = null;

                foreach ($encabezados as $columna => $valor) {
                    $encabezado = mb_strtolower(trim((string) $valor));
                    if ($encabezado === 'ubicacion' || $encabezado === 'ubicación') {
                        $columnaUbicacion = $columna;
                    }
                    if ($encabezado === 'estado') {
                        $columnaEstado = $columna;
                    }
                }

                if ($columnaUbicacion === null || $columnaEstado === null) {
                    $mensajeError = '<div class="alert alert-danger">El archivo debe contener las columnas Ubicacion y Estado.</div>';
                } else {
                    $fechaConfig = date('Y-m-d H:i:s');
                    $insertadas = 0;
                    $omitidas = 0;
                    $sentenciaConsulta = $conexion->prepare("SELECT Ubicacion FROM {$tablaCarrilesTemporales} WHERE Ubicacion = ?");
                    $sentenciaInsercion = $conexion->prepare("INSERT INTO {$tablaCarrilesTemporales} (Ubicacion, Estado, FechaConfig) VALUES (?, ?, ?)");

                    foreach ($filas as $fila) {
                        $ubicacion = trim((string) ($fila[$columnaUbicacion] ?? ''));
                        $estado = trim((string) ($fila[$columnaEstado] ?? ''));

                        if ($ubicacion === '' || $estado === '') {
                            $omitidas++;
                            continue;
                        }

                        if (!in_array($estado, ['Activo', 'Desactivo'], true)) {
                            $omitidas++;
                            continue;
                        }

                        $sentenciaConsulta->bind_param('s', $ubicacion);
                        $sentenciaConsulta->execute();
                        $resultado = $sentenciaConsulta->get_result();

                        if ($resultado && $resultado->num_rows > 0) {
                            $omitidas++;
                            continue;
                        }

                        $sentenciaInsercion->bind_param('sss', $ubicacion, $estado, $fechaConfig);
                        $sentenciaInsercion->execute();
                        $insertadas++;
                    }

                    $sentenciaConsulta->close();
                    $sentenciaInsercion->close();

                    $mensajeExito = sprintf(
                        '<div class="alert alert-success">Carga masiva completada. Insertadas: %d. Omitidas: %d.</div>',
                        $insertadas,
                        $omitidas
                    );
                }
            }
        } else {
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            $estado = trim($_POST['estado'] ?? '');
            $fechaConfig = date('Y-m-d H:i:s');

            if ($ubicacion === '' || $estado === '') {
                $mensajeError = '<div class="alert alert-danger">Complete todos los campos.</div>';
            } else {
                $sentencia = $conexion->prepare("SELECT Ubicacion FROM {$tablaCarrilesTemporales} WHERE Ubicacion = ?");
                $sentencia->bind_param('s', $ubicacion);
                $sentencia->execute();
                $resultado = $sentencia->get_result();
                $existe = $resultado->num_rows > 0;
                $sentencia->close();

                if ($existe) {
                    $mensajeError = '<div class="alert alert-danger">La ubicación temporal ya está registrada.</div>';
                } else {
                    $sentencia = $conexion->prepare("INSERT INTO {$tablaCarrilesTemporales} (Ubicacion, Estado, FechaConfig) VALUES (?, ?, ?)");
                    $sentencia->bind_param('sss', $ubicacion, $estado, $fechaConfig);
                    $sentencia->execute();
                    $sentencia->close();
                    $mensajeExito = '<div class="alert alert-success">Ubicación temporal registrada correctamente.</div>';
                }
            }
        }
    }

    $consultaUbicaciones = "SELECT Ubicacion FROM posiciones WHERE Ubicacion NOT IN (SELECT Ubicacion FROM {$tablaCarrilesTemporales})";
    $resultadoUbicaciones = $conexion->query($consultaUbicaciones);

    if ($resultadoUbicaciones) {
        while ($fila = $resultadoUbicaciones->fetch_assoc()) {
            $ubicacionesDisponibles[] = $fila['Ubicacion'];
        }
        $resultadoUbicaciones->free();
    }
} catch (Exception $exception) {
    $mensajeError = '<div class="alert alert-danger">No fue posible registrar la ubicación temporal.</div>';
}

ob_end_flush();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/Sertero/LogoCBP.png">
<title>Henkel CBP / AdminFIFO</title>
<link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
<link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
<link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
<link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
<link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
<link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
<link href="../dist/css/style.min.css" rel="stylesheet">
<link href="../dist/css/Custom/ConEst.css" rel="stylesheet">

<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<style>
    Select{
        height: 10px !important;}

    .nav-tabs.nav-bordered li a.active {
        border-bottom: 2px solid #ed3131;
    }

    a {
        color: #ed3131;
        background-color: transparent;
    }

    .btn-Sertero {
        color: #fff;
        background-color: #ed3131;
        border-color: #ff0008;
    }

    .page-item.active .page-link {
        z-index: 1;
        color: #fff;
        background-color: #ed3131;
        border-color: #e60000;
    }

    .bg-light {
        background-color: #e8eaec00 !important;
    }

    .tab-content {
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

</style>

</head>

<body>
<div class="preloader">
    <div class="lds-ripple">
        <div class="preloader">
            <br></br>
            <div class="logoPre">
                <img src="../assets/images/Sertero/LogoHenkel.png" width="300px" height="auto">
            </div>
            <div class="loader-frame">
                <div class="loader1" id="loader1"></div>
                <div class="loader2" id="loader2"></div>
            </div>
        </div>
    </div>
</div>
<div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
     data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
    <header class="topbar" data-navbarbg="skin6">
        <nav class="navbar top-navbar navbar-expand-md">
            <div class="navbar-header" data-logobg="skin6">
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>

                <div class="navbar-brand">
                    <a href="index.php">
                        <b class="logo-icon">
                            <img src="../assets/images/Sertero/LogoCBP.png" width="auto" height="40" class="" -->
                            <img src="../assets/images/logo-icon.png" alt="homepage" width="auto" height="10"
                                 class="light-logo"/>
                        </b>
                        <span class="logo-text">
                                <img src="../assets/images/logo-text.png" alt="homepage" class="dark-logo" width="auto"
                                     height="40"/>
                                <img src="../assets/images/logo-light-text.png" class="light-logo" alt="homepage"/>
                            </span>
                    </a>
                </div>
                <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                   data-toggle="collapse" data-target="#navbarSupportedContent"
                   aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                            class="ti-more"></i></a>
            </div>
            <div class="navbar-collapse collapse" id="navbarSupportedContent">
                <ul class="navbar-nav float-left mr-auto ml-3 pl-1">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="settings" class="svg-icon"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="javascript:ReloadPage();">Actualizar Pagina</a>
                        </div>
                    </li>
                </ul>
                <ul class="navbar-nav float-right"> <p id="status" class="online">Online</p>
                </ul>
            </div>
        </nav>
    </header>
    <aside class="left-sidebar" data-sidebarbg="skin6">
        <div class="scroll-sidebar" data-sidebarbg="skin6">
            <?php include 'Menu.php'; ?>
        </div>
    </aside>

    <div class="page-wrapper">
        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Agregar carril temporal</h4>
                            <h6 class="card-subtitle">Registre una ubicación temporal en el sistema.</h6>
                            <br>
                            <?php echo $mensajeError; ?>
                            <?php echo $mensajeExito; ?>
                            <br>

                            <form method="post" action="">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="ubicacion">Ubicación</label>
                                        <select class="form-control" id="ubicacion" name="ubicacion" required>
                                            <option value="">Seleccione</option>
                                            <?php foreach ($ubicacionesDisponibles as $ubicacionDisponible): ?>
                                                <option value="<?php echo htmlspecialchars($ubicacionDisponible); ?>">
                                                    <?php echo htmlspecialchars($ubicacionDisponible); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="estado">Estado</label>
                                        <select class="form-control" id="estado" name="estado" required>
                                            <option value="">Seleccione</option>
                                            <option value="Activo">Activo</option>
                                            <option value="Desactivo">Desactivo</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="accion" value="manual">
                                <button type="submit" class="btn btn-Sertero">Guardar</button>
                                <a class="btn btn-outline-danger" style="margin-left: 1rem" href="CarrilesTemporales_Consultar.php"><span> Regresar </span></a>
                            </form>
                            <hr>
                            <h6 class="card-subtitle">Carga masiva desde Excel (columnas: Ubicacion y Estado).</h6>
                            <form method="post" action="" enctype="multipart/form-data" style="margin-top: 1rem;">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="archivo_excel">Archivo Excel</label>
                                        <input type="file" class="form-control" id="archivo_excel" name="archivo_excel" accept=".xlsx,.xls" required>
                                    </div>
                                </div>
                                <input type="hidden" name="accion" value="masiva">
                                <button type="submit" class="btn btn-Sertero">Cargar archivo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer text-center text-muted">
            2023 ® All Rights Reserved by Sertero. Designed and Developed by <a
                    href="https://qbit-Lab.com">Qbit-Lab</a>.
        </footer>
    </div>
</div>
<script src="../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../dist/js/app-style-switcher.js"></script>
<script src="../dist/js/feather.min.js"></script>
<script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
<script src="../dist/js/sidebarmenu.js"></script>
<script src="../dist/js/custom.min.js"></script>
<script src="../assets/extra-libs/c3/d3.min.js"></script>
<script src="../assets/extra-libs/c3/c3.min.js"></script>
<script src="../assets/libs/chartist/dist/chartist.min.js"></script>
<script src="../assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>
<script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>
<script src="../dist/js/OnLine.js"></script>

<script>
    const tiempoInactividad = 300000;

    function redirigir() {
        window.location.href = 'index.php';
    }

    let temporizadorInactividad;

    function reiniciarTemporizador() {
        clearTimeout(temporizadorInactividad);
        temporizadorInactividad = setTimeout(redirigir, tiempoInactividad);
    }

    document.addEventListener('mousemove', reiniciarTemporizador);
    document.addEventListener('keypress', reiniciarTemporizador);

    reiniciarTemporizador();
</script>
</body>
</html>
