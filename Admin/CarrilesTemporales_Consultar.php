<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}

include '../LQS_EUQ/Connect.php';

date_default_timezone_set('America/Guatemala');

$tablaCarrilesTemporales = 'posisciones_temporalesCNF';
$mensajeExito = '';
$mensajeError = '';
$registroEditar = null;

try {
    $conexion = lqs_get_connection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        $fechaConfig = date('Y-m-d H:i:s');

        if ($accion === 'eliminar') {
            $ubicacion = trim($_POST['ubicacion'] ?? '');

            if ($ubicacion === '') {
                $mensajeError = '<div class="alert alert-danger">Seleccione una ubicación válida.</div>';
            } else {
                $sentencia = $conexion->prepare("DELETE FROM {$tablaCarrilesTemporales} WHERE Ubicacion = ?");
                $sentencia->bind_param('s', $ubicacion);
                $sentencia->execute();
                $sentencia->close();
                $mensajeExito = '<div class="alert alert-success">Ubicación temporal eliminada correctamente.</div>';
            }
        }

        if ($accion === 'editar') {
            $ubicacionOriginal = trim($_POST['ubicacion_original'] ?? '');
            $ubicacionNueva = trim($_POST['ubicacion'] ?? '');
            $estado = trim($_POST['estado'] ?? '');

            if ($ubicacionOriginal === '' || $ubicacionNueva === '' || $estado === '') {
                $mensajeError = '<div class="alert alert-danger">Complete todos los campos para editar.</div>';
            } else {
                $sentencia = $conexion->prepare("UPDATE {$tablaCarrilesTemporales} SET Ubicacion = ?, Estado = ?, FechaConfig = ? WHERE Ubicacion = ?");
                $sentencia->bind_param('ssss', $ubicacionNueva, $estado, $fechaConfig, $ubicacionOriginal);
                $sentencia->execute();
                $sentencia->close();
                $mensajeExito = '<div class="alert alert-success">Ubicación temporal actualizada correctamente.</div>';
            }
        }
    }

    if (isset($_GET['editar'])) {
        $ubicacionEditar = trim($_GET['editar']);
        if ($ubicacionEditar !== '') {
            $sentencia = $conexion->prepare("SELECT Ubicacion, Estado FROM {$tablaCarrilesTemporales} WHERE Ubicacion = ?");
            $sentencia->bind_param('s', $ubicacionEditar);
            $sentencia->execute();
            $resultado = $sentencia->get_result();
            $registroEditar = $resultado->fetch_assoc();
            $sentencia->close();
        }
    }

    $resultadoLista = $conexion->query("SELECT Ubicacion, Estado, FechaConfig FROM {$tablaCarrilesTemporales} ORDER BY Ubicacion ASC");
} catch (Exception $exception) {
    $mensajeError = '<div class="alert alert-danger">No fue posible consultar la base de datos.</div>';
    $resultadoLista = false;
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
                            <h4 class="card-title">Carriles Temporales</h4>
                            <h6 class="card-subtitle">Consulte y edite las ubicaciones temporales registradas.</h6>
                            <br>
                            <?php echo $mensajeError; ?>
                            <?php echo $mensajeExito; ?>
                            <br>

                            <nav class="navbar navbar-expand-lg navbar-light bg-light ">
                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarNav">
                                    <ul class="navbar-nav ">
                                        <li class="nav-item active">
                                            <a class="btn btn-outline-success" style="margin-left: 2rem" href="CarrilesTemporales_Agregar.php"><span> Agregar ubicación temporal </span></a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <br>

                            <?php if ($registroEditar) { ?>
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Editar ubicación temporal</h5>
                                        <form method="post" action="">
                                            <input type="hidden" name="accion" value="editar">
                                            <input type="hidden" name="ubicacion_original" value="<?php echo htmlspecialchars($registroEditar['Ubicacion']); ?>">
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="ubicacion">Ubicación</label>
                                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?php echo htmlspecialchars($registroEditar['Ubicacion']); ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="estado">Estado</label>
                                                    <select class="form-control" id="estado" name="estado" required>
                                                        <option value="Activo" <?php echo $registroEditar['Estado'] === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                                        <option value="Desactivo" <?php echo $registroEditar['Estado'] === 'Desactivo' ? 'selected' : ''; ?>>Desactivo</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-2 d-flex align-items-end">
                                                    <button type="submit" class="btn btn-Sertero">Guardar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>

                            <table id="example" class="table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Fecha configuración</th>
                                    <th>Editar</th>
                                    <th>Eliminar</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($resultadoLista && $resultadoLista->num_rows > 0) { ?>
                                    <?php while ($registro = $resultadoLista->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($registro['Ubicacion']); ?></td>
                                            <td><?php echo htmlspecialchars($registro['Estado']); ?></td>
                                            <td><?php echo htmlspecialchars($registro['FechaConfig']); ?></td>
                                            <td>
                                                <a href="CarrilesTemporales_Consultar.php?editar=<?php echo urlencode($registro['Ubicacion']); ?>" class="fas fa-edit btn btn-Sertero"></a>
                                            </td>
                                            <td>
                                                <form method="post" action="">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="ubicacion" value="<?php echo htmlspecialchars($registro['Ubicacion']); ?>">
                                                    <button type="submit" class="fas fa-trash-alt btn btn-outline-danger"
                                                            onclick="return confirm('¿Está seguro de eliminar esta ubicación temporal?');"></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                                </tbody>
                            </table>
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
<script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            scrollX: true,
            language: {
                url: 'datatables_espanol.json'
            },
            pageLength: 25
        });
    });
</script>

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
