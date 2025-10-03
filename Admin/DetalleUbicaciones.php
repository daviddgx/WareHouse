<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}

include '../LQS_EUQ/Auth.php';
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';

$txtNombre = "";
$txtApellido = "";
$txtNombreUsuario = "";
$txtTipoUsuario = "";
$txtEmail = "";
$txtFoto = "";

$txtPWD = "";

// Cargar datos a mostrar


// Creamos la conexion


try {
    if (isset($_GET['Ubicacion'])) {
        $IDH_SESION = $_GET['Ubicacion'];
        $sql = "SELECT * FROM dbs9098416.posiciones where Ubicacion = '".$IDH_SESION."'";

        $sentencia = $pdo->prepare($sql,
            array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
        $sentencia->execute();

        $Producto = $sentencia->fetch(PDO::FETCH_LAZY);
        $txtBodega = $Producto['Bodega'];
        $txtCarril = $Producto['Carril'];
        $txtposicion = $Producto['Posicion'];
        $txtNivel = $Producto['Nivel'];
        $txtUbicacion = $Producto['Ubicacion'];
        $txtEstado = $Producto['Estado'];
        $txtIDH = $Producto['IDH'];
        $txtPalletCompleto = $Producto['PaletCompleto'];
        $txtUnidadesEnPallet = $Producto['UnidadesEnPallet'];
        $txtOrigen = $Producto['Origen'];
        $txtFechaProduccion =  date('d/m/Y', strtotime($Producto['FechaProduccion']));
        $txtLoteProduccion = $Producto['LoteProduccion'];
        $txtFechaIngreso =  date('d/m/Y', strtotime($Producto['FechaIngreso']));
        $txtFechaVencimiento =  date('d/m/Y', strtotime($Producto['FechaVencimiento']));
        $txtFechaCuarentena =  date('d/m/Y', strtotime($Producto['FechaCuarentena']));
        $txtCantidad = $Producto['Cantidad'];
        $txtEstatusProducto = $Producto['EstatusProducto'];
        $txtVerificador = $Producto['Verificador'];
        $txtUsuarioMontaCargas = $Producto['UsuarioMontaCargas'];
        $txtTurno = $Producto['Turno'];
        $txtEstatusUbicaion = $Producto['EstatusUbicacion'];
        $txtObservaciones = $Producto['Observaciones'];
        $txtFoto = $txtIDH.'.jpg';

    } else {
        // Manejo de error
    }


} catch (Exception $ex) {

    $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
}
//comprovacion de dadtos
//fin comprovacion de datos
// Fin de la conexion

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
    <title>Henkel CBP / AdminFIFO</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <![endif]-->
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
                <img src="../assets/images/Sertero/LogoHenkel.png" width="300px" height="auto">
                <!-- Sertero<span style="color:#e88733">CBP</span> -->
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
<div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
     data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar" data-navbarbg="skin6">
        <nav class="navbar top-navbar navbar-expand-md">
            <div class="navbar-header" data-logobg="skin6">
                <!-- This is for the sidebar toggle which is visible on mobile only -->
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                        class="ti-menu ti-close"></i></a>

                <div class="navbar-brand">
                    <!-- Logo icon -->
                    <a href="index.php">
                        <b class="logo-icon">
                            <!-- Dark Logo icon -->
                            <img src="../assets/images/Sertero/LogoCBP.png" width="auto" height="40" class="" -->
                            <!-- Light Logo icon -->
                            <img src="../assets/images/logo-icon.png" alt="homepage" width="auto" height="10"
                                 class="light-logo"/>
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                                <!-- dark Logo text -->
                                <img src="../assets/images/logo-text.png" alt="homepage" class="dark-logo" width="auto"
                                     height="40"/>
                            <!-- Light Logo text -->
                                <img src="../assets/images/logo-light-text.png" class="light-logo" alt="homepage"/>
                            </span>
                    </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Toggle which is visible on mobile only -->
                <!-- ============================================================== -->
                <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                   data-toggle="collapse" data-target="#navbarSupportedContent"
                   aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                        class="ti-more"></i></a>
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
                    <!-- create new -->
                    <!-- ============================================================== -->
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

                <!-- ============================================================== -->
                <!-- Right side toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav float-right"> <p id="status" class="online">Online</p>
                    <!-- ============================================================== -->
                    <!-- Search -->
                    <!-- ============================================================== -->
                    <!--                    <li class="nav-item d-none d-md-block">-->
                    <!--                        <a class="nav-link" href="javascript:void(0)">-->
                    <!--                            <form>-->
                    <!--                                <div class="customize-input">-->
                    <!--                                    <input class="form-control custom-shadow custom-radius border-0 bg-white"-->
                    <!--                                           type="search" placeholder="Search" aria-label="Search">-->
                    <!--                                    <i class="form-control-icon" data-feather="search"></i>-->
                    <!--                                </div>-->
                    <!--                            </form>-->
                    <!--                        </a>-->
                    <!--                    </li>-->
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/images/users/<?php echo $_SESSION['pic']; ?> " alt="user"
                                 class="rounded-circle"
                                 width="40">
                            <span class="ml-2 d-none d-lg-inline-block"><span>Bienvenido,</span> <span
                                    class="text-dark"> <?php echo $_SESSION['USR']; ?> </span> <i
                                    data-feather="chevron-down"
                                    class="svg-icon"></i></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">

                            <a class="dropdown-item" href="javascript:PerfilAdminFifo()"><i data-feather="settings"
                                                                                            class="svg-icon mr-2 ml-1"></i>
                                Mi Perfil</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:Salir();"><i data-feather="power"
                                                                                   class="svg-icon mr-2 ml-1"></i>
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

       
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Detalle de Ubicaion</h4>
                            <h6 class="card-subtitle">Valide el detalle de la Ubicacion <php echo $IDH_SESION;></h6>

                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>
                            <!-- Formulario de datos -->
                            <div class="my-content formulario">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-body">


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Bodega</label>
                                                    <input name="txtBodega" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtBodega ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Carril</label>
                                                    <input name="txtCarril" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtCarril ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Posicion</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtposicion ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nivel</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtNivel ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Ubicacion</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtUbicacion ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estado</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtEstado ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>IDH / Material</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtIDH ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Es palet Completo</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtPalletCompleto ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Unidades En Pallet</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtUnidadesEnPallet ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Origen del Material</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtOrigen ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de Produccion</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaProduccion . ''?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Lote De Produccion</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtLoteProduccion . ''?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de Ingreso</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaIngreso . ''?> " readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de vencimiento</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaVencimiento . ''?> " readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de Fin de Cuarentena</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaCuarentena . ''?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estado</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtCantidad ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estatus del Material</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtEstatusProducto ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Verificador / En Ingreso</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtVerificador ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Montacarguista</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtUsuarioMontaCargas ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Turno</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtTurno ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estatus de la Ubicaion</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtEstatusUbicaion ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Observaciones</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtObservaciones ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->



                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Fotografia</label>
                                                <div class="input-group mb-12">
                                                    <?php if ($txtFoto != "") { ?>
                                                        <br/>
                                                        <img style="border-radius: 45px !important;"
                                                             class="img-thumbnail rounded mx-auto d-block" width="200px"
                                                             src="../assets/images/Productos/<?php echo $txtFoto; ?>">
                                                        <br/>
                                                        <br/>
                                                    <?php } ?>


                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-actions">
                                        <div class="text-center">

                                            <a class="btn btn-outline-success" style="margin-left: 2rem" href="MantenimientoPosiciones.php?Guia=<?php echo $txtBodega ;?>"><span > Regresar </span></a>

                                        </div>
                                    </div>
                                </form>
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
            2023 ® All Rights Reserved by Sertero. Designed and Developed by <a
                href="https://qbit-Lab.com">Qbit-Lab</a>.
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
<script src="../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
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
<script src="../assets/libs/chartist/dist/chartist.min.js"></script>
<script src="../assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>
<script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>
<script src="../dist/js/OnLine.js"></script>
<script src="../dist/js/JsBarcode.all.min.js"></script>
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
<script>
    JsBarcode(".codigo").init();
</script>
</html>