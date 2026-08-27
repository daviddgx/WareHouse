<?php
session_start();
require_once 'ValidarSesion.php';
include '../LQS_EUQ/Connect.php';
include '../LQS_EUQ/ListarReubicaciones.php';
include "../Innet_MTC/Innet_MTC.php";
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d"); 
$lista_Asignacion = null;
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {
}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';

if (!empty($_SESSION['mensaje_exito_reubicacion'])) {
    $MensajeExito = '<div class="alert alert-success">'
        . htmlspecialchars($_SESSION['mensaje_exito_reubicacion'], ENT_QUOTES, 'UTF-8')
        . '</div>';
    unset($_SESSION['mensaje_exito_reubicacion']);
}

if (!empty($_SESSION['mensaje_error_reubicacion'])) {
    $Mensajeerror = '<div class="alert alert-danger">'
        . htmlspecialchars($_SESSION['mensaje_error_reubicacion'], ENT_QUOTES, 'UTF-8')
        . '</div>';
    unset($_SESSION['mensaje_error_reubicacion']);
}


//Variables para Resumen
$TotalMovimientos = "";
$IDHs = "";
$ListaColocadas = "";
$ListaPendientes = "" ;

// Dar valor a las variabes de Resumen

$TotalMovimientos = DarValorTotalReubicaciones($_SESSION['Usuario'],$fechaConsulta);
$IDHs = DarValorIDHsMover($_SESSION['Usuario'],$fechaConsulta);
$ListaColocadas = DarValorListaColocadasMover($_SESSION['Usuario'],$fechaConsulta);
$ListaPendientes = DarValorListaPendientesMover($_SESSION['Usuario'],$fechaConsulta); ;



// Fin de la conexion
$Num_Despachos= '';
$Num_Reubicaciones= '';
$Num_Piking= '';
$Num_Despachos = darValorDespachos($_SESSION['Usuario']);
$Num_Reubicaciones = darValorReubicaciones($_SESSION['Usuario']);
$Num_Piking = darValorPiking($_SESSION['Usuario']);
// Validar formulario y grabar informacion
// Validar formulario y grabar informacion

$Num_Asignaciones = '';
$Num_Asignaciones = darValorAsignaciones($_SESSION['Usuario']);

$tokenReubicacion = bin2hex(random_bytes(32));
$_SESSION['token_reubicacion'] = $tokenReubicacion;

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
    <title>Henkel CBP / Operador MTC</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="tablet.css" rel="stylesheet">
    <script src="sesion-montacargas.js" defer></script>
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <![endif]-->

    <style>
        .bolded {
            font-weight: bold;
            font-size: large;
        }
    </style>
</head>

<body>
echo "<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
r
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
                    <a href="index.php">
                        <b class="logo-icon">
                            <!-- Dark Logo icon -->
                            <img src="../assets/images/Sertero/LogoCBP.png" width="auto" height="40" class="" -->
                            <!-- Light Logo icon -->
                            <img src="../assets/images/logo-icon.png" alt="homepage" width="auto" height="10" class="light-logo" />
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                <!-- dark Logo text -->
                <img src="../assets/images/logo-text.png" alt="homepage" class="dark-logo" width="auto" height="40" />
                            <!-- Light Logo text -->
                <img src="../assets/images/logo-light-text.png" class="light-logo" alt="homepage" />
              </span>
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
                    <!-- create new -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                <ul class="navbar-nav float-right">
                    <p id="status" class="online">Online</p>
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
                        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/images/users/<?php echo $_SESSION['pic']; ?> " alt="user" class="rounded-circle" width="40">
                            <span class="ml-2 d-none d-lg-inline-block"><span>Bienvenido,</span> <span class="text-dark"> <?php echo $_SESSION['USR']; ?> </span> <i data-feather="chevron-down" class="svg-icon"></i></span>
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

        <div class="page-breadcrumb">
            <div class="row">

                <div class="col-5 align-self-center">
                    <div class="customize-input float-right">

                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Lista de Reubicaciones a Realizar</h4>
                            <h6 class="card-subtitle">Estos son las Reubicaciones que debe realizar en este turno </h6>
                            <br>
                            <!-- Start First Cards -->
                            <!-- *************************************************************** -->
                            <div class="card-group">
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo $TotalMovimientos ;?></h2>

                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total de Movimientos</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="activity"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <h2 class="text-dark mb-1 w-100 text-truncate font-weight-medium"><sup class="set-doller"></sup><?php echo $IDHs ;?></h2>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">IDHs
                                                </h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="box"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo $ListaColocadas ;?></h2>
                                                    <span class="badge bg-success font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block"><?php if($TotalMovimientos == 0) {} else{ echo  bcdiv((($ListaColocadas / $TotalMovimientos) * 100),'1', 2); }?>%</span>
                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Reubicadas</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="flag"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo $ListaPendientes ;?></h2>
                                                    <span class="badge bg-danger font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block"><?php if($TotalMovimientos == 0) {} else{ echo  bcdiv((($ListaPendientes / $TotalMovimientos) * 100),'1', 2) ;} ?>%</span>
                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Pendientes</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="compass"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- *************************************************************** -->
                            <!-- End First Cards -->
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>

                            <!-- Contenido de esta seccion-->


                            <!-- Start First Cards -->
                            <!-- *************************************************************** -->

                            <!-- *************************************************************** -->
                            <!-- End First Cards -->



                            <table id="example" class="table table-striped  " cellspacing="0" width="100%">
                                <thead>


                                <th>IDH</th>
                                <th>Descripcion</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Estado</th>
                                <th>Mover</th>

                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < $lista_Movimientos; $i++) {


                                    $IDGUIA = $lista_Movimientos['id'];
                                    $Origen = $lista_Movimientos['Origen'];
                                    $Destino = $lista_Movimientos['Destino'];
                                    $IDH = $lista_Movimientos['IDH'];

                                    echo "<td>";
                                    echo $lista_Movimientos['IDH'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_Movimientos['Descripcion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_Movimientos['Origen'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_Movimientos['Destino'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_Movimientos['Estado'];
                                    echo "</td>";


                                    echo "<td>";
                                    echo '<form method="post" action="MoverProducto.php" class="form-mover mb-0">';
                                    echo '<input type="hidden" name="token" value="' . htmlspecialchars($tokenReubicacion, ENT_QUOTES, 'UTF-8') . '">';
                                    echo '<input type="hidden" name="Guia" value="' . htmlspecialchars($IDGUIA, ENT_QUOTES, 'UTF-8') . '">';
                                    echo '<button type="submit" class="btn btn-primary btn-mover">Mover</button>';
                                    echo '</form>';
                                    echo "</td>";

                                    echo "<script>";
                                    echo "
    $(document).ready(function() {
        $('#btn-mover-$IDH').on('click', function() {
            var btn = $(this);
            btn.html('<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> ').prop('disabled', true);
            btn.hide(); // Oculta el botón después de deshabilitarlo y cambiar su contenido
        });
    });
";
                                    echo "</script>";



                                    echo "</tr>";



                                    $lista_Movimientos = $ejecutar_sentencia_Movimientos->fetch(PDO::FETCH_ASSOC);
                                }
                                ?>
                                </tbody>
                            </table>
                            <br>
                            <!-- Fin Contenido de esta seccion-->



                            <br>
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
<script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
 scrollX: true,

            language: {
                url: 'datatables_espanol.json'
            }
        });


        $(table.column(2).nodes()).addClass('bolded');
        $(table.column(3).nodes()).addClass('bolded');
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function () {
        var movimientoEnProceso = false;
        var mensajes = [
            'Buscando ubicación...', 'Identificando IDH...',
            'Revisando producción...', 'Moviendo datos...',
            'Actualizando ubicaciones...', 'Registrando en bitácora...',
            'Ya casi está listo...', 'Asegurando la transacción...'
        ];

        $('.form-mover').on('submit', function (event) {
            event.preventDefault();
            if (movimientoEnProceso) return;
            movimientoEnProceso = true;

            var formulario = this;
            var indice = 0;
            $('.btn-mover').prop('disabled', true).attr('aria-disabled', 'true');

            if (!window.Swal) {
                formulario.submit();
                return;
            }

            Swal.fire({
                title: 'Procesando reubicación',
                html: '<p id="mensaje-reubicacion" class="mb-0"></p>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    var mensaje = document.getElementById('mensaje-reubicacion');
                    Swal.showLoading();
                    mensaje.textContent = mensajes[indice];
                    window.setInterval(function () {
                        indice = (indice + 1) % mensajes.length;
                        mensaje.textContent = mensajes[indice];
                    }, 1800);
                    formulario.submit();
                }
            });
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) window.location.reload();
        });
    }());
</script>
</body>

</html>
