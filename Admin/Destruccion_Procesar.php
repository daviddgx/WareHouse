<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

include '../LQS_EUQ/Connect.php';

$errores = '';
$mensajeExito = '';
$pendientesDestruccion = [];

try {
    $conn = lqs_get_connection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Ubicacion'])) {
        $ubicacion = $_POST['Ubicacion'];
        $observaciones = isset($_POST['Observaciones']) ? trim($_POST['Observaciones']) : '';
        $usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'sistema';

        $stmt = $conn->prepare("UPDATE posiciones SET EstatusUbicacion = 'Destruccion', EstatusProducto = 'Destruccion', Observaciones = CONCAT(IFNULL(Observaciones,''), ?) WHERE Ubicacion = ? AND EstatusUbicacion = 'Calidad'");
        if ($stmt) {
            $nota = " | Destrucción procesada por $usuario el $fecha. " . ($observaciones !== '' ? 'Nota: ' . $observaciones : '');
            $stmt->bind_param('ss', $nota, $ubicacion);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensajeExito = 'La ubicación ' . htmlspecialchars($ubicacion) . ' fue marcada como destruida.';
            } else {
                $errores = 'No se pudo actualizar la ubicación seleccionada. Verifique que siga marcada como Calidad.';
            }
            $stmt->close();
        } else {
            $errores = 'No se pudo preparar la actualización de destrucción.';
        }
    }

    $sqlPendientes = "SELECT Ubicacion, Bodega, Carril, Posicion, Nivel, IDH, EstatusProducto, Observaciones FROM posiciones WHERE EstatusUbicacion = 'Calidad' ORDER BY Bodega, Carril, Posicion, Nivel";
    $resultado = $conn->query($sqlPendientes);
    if ($resultado instanceof mysqli_result) {
        while ($fila = $resultado->fetch_assoc()) {
            $pendientesDestruccion[] = $fila;
        }
    } else {
        $errores = 'No se pudo obtener la lista de ubicaciones para destrucción.';
    }
} catch (Exception $e) {
    $errores = 'Error al conectar con la base de datos: ' . $e->getMessage();
}

ob_end_flush();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/Sertero/LogoCBP.png">
    <title>Sertero CBP / AdminFIFO</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">

    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>




</head>

<body>
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <div class="lds-ripple">
        <div class="preloader">
            <br></br>
            <div class="logoPre">
                <img src="../assets/images/Sertero/LogoHenkel.png" width="200x" height="auto">

            </div>
            <div class="loader-frame">
                <div class="loader1" id="loader1"></div>
                <div class="loader2" id="loader2"></div>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar" data-navbarbg="skin6">
        <nav class="navbar top-navbar navbar-expand-md">
            <div class="navbar-header" data-logobg="skin6">
                <!-- This is for the sidebar toggle which is visible on mobile only -->
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>

                <div class="navbar-brand">
                    <!-- Logo icon -->
                    <a href="../Admin/index.php">
                        <img src="../assets/images/Sertero/LogoHenkel.png" width="120x" height="auto">
                        <img src="../assets/images/Sertero/LogoSertero.png" width="140x" height="auto">
                    </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Toggle which is visible on mobile only -->
                <!-- ============================================================== -->
                <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
            </div>
            <!-- ============================================================== -->
            <!-- End Logo -->
            <!-- ============================================================== -->
            <div class="navbar-collapse collapse" id="navbarSupportedContent">
                <!-- ============================================================== -->
                <!-- toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav float-left mr-auto ml-3 pl-1">
                    <!-- ============================================================== -->
                    <!-- Notification -->

                    <!-- End Messages -->
                    <!-- ============================================================== -->
                </ul>
                <!-- ============================================================== -->
                <!-- Right side toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav float-right">
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">&nbsp;&nbsp;<?php echo $_SESSION['Nombre']; ?></span>
                            <img class="img-profile rounded-circle" src="../assets/images/Sertero/avatar.png">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">

                            <a class="dropdown-item" href="javascript:PerfilAdminFifo()"><i data-feather="settings" class="svg-icon mr-2 ml-1"></i>
                                Mi Perfil</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:Salir();"><i data-feather="power" class="svg-icon mr-2 ml-1"></i>
                                Salir</a>

                        </div>
                    </li>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                </ul>
            </div>
        </nav>
    </header>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <aside class="left-sidebar" data-sidebarbg="skin6">
        <!-- Sidebar scroll-->
        <div class="scroll-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar navigation-->
            <?php include 'Menu.php'; ?>
            <!-- End Sidebar navigation -->
        </div>
        <!-- End Sidebar scroll-->
    </aside>

    <div class="page-wrapper">


        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body ">
                            <h4 class="card-title">Procesar destrucción</h4>
                            <h6 class="card-subtitle">Registrar y gestionar las solicitudes de destrucción de producto.</h6>
                            <br>
                            <?php if ($mensajeExito !== ''): ?>
                                <div class="alert alert-success" role="alert"><?php echo $mensajeExito; ?></div>
                            <?php endif; ?>
                            <?php if ($errores !== ''): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $errores; ?></div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Ubicación</th>
                                        <th>Bodega</th>
                                        <th>Carril</th>
                                        <th>Posición</th>
                                        <th>Nivel</th>
                                        <th>IDH</th>
                                        <th>Estatus producto</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (count($pendientesDestruccion) === 0): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No hay ubicaciones marcadas para destrucción.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pendientesDestruccion as $ubicacion): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ubicacion['Ubicacion']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['Bodega']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['Carril']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['Posicion']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['Nivel']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['IDH']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['EstatusProducto']); ?></td>
                                                <td><?php echo htmlspecialchars($ubicacion['Observaciones']); ?></td>
                                                <td>
                                                    <form method="post" class="form-inline">
                                                        <input type="hidden" name="Ubicacion" value="<?php echo htmlspecialchars($ubicacion['Ubicacion']); ?>">
                                                        <input type="text" name="Observaciones" class="form-control mb-2 mr-sm-2" placeholder="Notas" maxlength="150">
                                                        <button type="submit" class="btn btn-danger mb-2" onclick="return confirm('¿Confirmar destrucción de la ubicación <?php echo htmlspecialchars($ubicacion['Ubicacion']); ?>?');">Marcar destrucción</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>







    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    <footer class="footer text-center text-muted">
        2023 ® All Rights Reserved by Sertero. Designed and Developed by <a href="https://qbit-Lab.com">Qbit-Lab</a>.
    </footer>
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Page wrapper  -->
<!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->

<script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>

<!-- apps -->
<!-- apps -->
<script src="../dist/js/app-style-switcher.js"></script>
<script src="../dist/js/feather.min.js"></script>
<script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
<script src="../dist/js/sidebarmenu.js"></script>
<!--Custom JavaScript -->
<script src="../dist/js/custom.min.js"></script>
<!--This page JavaScript -->
<script src="../assets/extra-libs/c3/d3.min.js"></script>
<script src="../assets/extra-libs/c3/c3.min.js"></script>

<script src="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>

<script src="../dist/js/OnLine.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<!--Scripts para DataTables-->
<!--This page plugins -->



    <script>
        // Establece el tiempo de inactividad en milisegundos (5 minutos = 300,000 milisegundos)
        const tiempoInactividad = 300000;

        // Función que redirige al usuario a la página específica
        function redirigir() {
            window.location.href = 'index.php'; // Reemplaza 'pagina-destino.html' con la URL de la página a la que deseas redirigir al usuario.
        }

        let temporizadorInactividad;

        // Función que reinicia el temporizador de inactividad
        function reiniciarTemporizador() {
            clearTimeout(temporizadorInactividad);
            temporizadorInactividad = setTimeout(redirigir, tiempoInactividad);
        }

        // Agrega eventos para rastrear la actividad del usuario
        document.addEventListener('mousemove', reiniciarTemporizador);
        document.addEventListener('keypress', reiniciarTemporizador);

        // Inicia el temporizador de inactividad al cargar la página
        reiniciarTemporizador();
    </script>
</body>

</html>
