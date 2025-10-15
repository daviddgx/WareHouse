<?php
ob_start();
session_start();

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$Num_Despachos= '';
$Num_Reubicaciones= '';
$Num_Piking= '';
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {
}

include '../LQS_EUQ/Connect.php';

$IDH = $_GET["IDH"];

include '../LQS_EUQ/ListarAsignaciones.php';
include "../Innet_MTC/Innet_MTC.php";
// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';

//Variables para Resumen
$TotalProducciones = "";
$IDHs = "";
$ListaColocadas = "";
$ListaPendientes = "" ;

// Dar valor a las variabes de Resumen

$TotalProducciones = DarValorTotalProducciones($_SESSION['Usuario'],$fechaConsulta);
$IDHs = DarValorIDHsIngresos($_SESSION['Usuario'],$fechaConsulta);
$ListaColocadas = DarValorListaColocadas($_SESSION['Usuario'],$fechaConsulta);
$ListaPendientes = DarValorListaPendientesIngresos($_SESSION['Usuario'],$fechaConsulta); ;


// Fin de la conexion

$Num_Despachos = darValorDespachos($_SESSION['Usuario']);
$Num_Reubicaciones = darValorReubicaciones($_SESSION['Usuario']);
$Num_Piking = darValorPiking($_SESSION['Usuario']);
// Validar formulario y grabar informacion
$Num_Asignaciones = '';
$Num_Asignaciones = darValorAsignaciones($_SESSION['Usuario']);

// Preparar registros para el visor de detalle
$asignacionesList = [];
if ($lista_AsignacionesPRODUCCION) {
    do {
        $registroActual = $lista_AsignacionesPRODUCCION;
        $registroActual['FechaIngresoFormateada'] = isset($registroActual['FechaIngreso']) && $registroActual['FechaIngreso']
            ? date('d/m/Y', strtotime($registroActual['FechaIngreso']))
            : '';
        $asignacionesList[] = $registroActual;
    } while ($lista_AsignacionesPRODUCCION = $ejecutar_sentencia_Asignaciones->fetch(PDO::FETCH_ASSOC));
}

$totalAsignacionesPendientes = count($asignacionesList);
$primerRegistroAsignacion = $totalAsignacionesPendientes > 0 ? $asignacionesList[0] : null;
$ingresarUrl = '';
if ($primerRegistroAsignacion) {
    $ingresarUrl = 'UbicarProducto.php?Guia=' . urlencode($primerRegistroAsignacion['Numero']) . '&IDH=' . urlencode($primerRegistroAsignacion['IDH']) . '&Ubicacion=' . urlencode($primerRegistroAsignacion['Posicion']);
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
    <title>Henkel CBP / Operador MTC</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css"/>
    <link rel="stylesheet" href="../dist/css/Custom/interactiveEnhancements.css">

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
        .card-body {
            flex: 1 1 auto;
            padding: 5px;
        }

        .page-breadcrumb {
            padding: 10px 10px 0;
        }

        .assignment-card-body {
            padding: 1.25rem;
        }

        .assignment-view {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-height: calc(100vh - 240px);
        }

        .assignment-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .assignment-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .assignment-navigation {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .assignment-navigation .nav-btn {
            min-width: 120px;
        }

        .assignment-summary {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .assignment-summary .record-indicator {
            color: #6c757d;
            font-weight: 500;
        }

        .assignment-details {
            flex: 1 1 auto;
            background: #f4f6fb;
            border-radius: 1rem;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .assignment-fields {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .assignment-field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .assignment-field .label {
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .75rem;
            color: #6c757d;
        }

        .assignment-field .value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
        }

        .assignment-field--highlight {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(31, 45, 61, 0.08);
        }

        .assignment-field--highlight .value {
            font-size: clamp(1.25rem, 2.5vw, 2.25rem);
            color: #e53935;
        }

        .assignment-empty {
            text-align: center;
            font-size: 1.25rem;
            color: #6c757d;
            margin: auto;
        }

        .assignment-footer {
            margin-top: auto;
            height: 25vh;
            min-height: 160px;
            display: flex;
            align-items: center;
        }

        .btn-ingresar {
            width: 100%;
            height: 100%;
            font-size: clamp(1.35rem, 2.5vw, 2.2rem);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
        }

        .btn-ingresar.disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        @media (max-width: 767.98px) {
            .assignment-card-body {
                padding: 1rem;
            }

            .assignment-details {
                padding: 1.25rem;
            }

            .assignment-footer {
                min-height: 140px;
            }
        }
    </style>
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
        <div class="container-fluid animate__animated animate__fadeIn" data-aos="fade-up">
            <div class="row">
                <div class="col-12">
                    <div class="card skeleton-target" data-aos="fade-up" data-aos-delay="50">

                        <div class="card-body">
                           <!--  <h4 class="card-title">Lista de ingresos a Realizar</h4>
                            <h6 class="card-subtitle">Estas son las ingresos que debe realizar en este turno </h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>

                            <a class="btn btn-outline-danger" style="margin-left: 2rem" href="Lista_AsignacionesIDH.php"><span > Regresar </span></a>
                            <br>


                            <div class="card-group">
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo $TotalProducciones ;?></h2>

                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total de Movimientos</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="settings"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <h2 class="text-dark mb-1 w-100 text-truncate font-weight-medium"><sup
                                                            class="set-doller"></sup><?php echo $IDHs ;?></h2>
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
                                                    <span
                                                            class="badge bg-success font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block"><?php if($TotalProducciones == 0) {} else{ echo  bcdiv((($ListaColocadas / $TotalProducciones) * 100),'1', 2) ;} ?>%</span>
                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Ingresadas</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="check"></i></span>
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
                                                    <span
                                                            class="badge bg-danger font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block"><?php  if($TotalProducciones == 0) {} else{ echo  bcdiv((($ListaPendientes / $TotalProducciones) * 100),'1', 2) ;} ?>%</span>
                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Pendientes</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="inbox"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            -->

                            <div class="card-body skeleton-target assignment-card-body" data-aos="fade-up" data-aos-delay="100">
                                
                                <div class="assignment-view">
                                    <div class="assignment-header">
                                        <a class="btn btn-outline-danger" href="Lista_AsignacionesIDH.php">
                                            <span>Regresar al listado de IDHs 📦</span>
                                        </a>
                                        <div class="assignment-navigation">
                                            <button type="button" class="btn btn-secondary nav-btn" id="prevRecord" disabled>◀ Anterior</button>
                                            <div class="assignment-summary">
                                                <span class="badge badge-info badge-pill">Pendientes: <span id="pendingTotal"><?php echo $totalAsignacionesPendientes; ?></span></span>
                                                <span class="record-indicator" id="recordIndicator"><?php echo $totalAsignacionesPendientes ? '1 / ' . $totalAsignacionesPendientes : '0 / 0'; ?></span>
                                            </div>
                                            <button type="button" class="btn btn-secondary nav-btn" id="nextRecord" <?php echo $totalAsignacionesPendientes > 1 ? '' : 'disabled'; ?>>Siguiente ▶</button>
                                        </div>
                                    </div>
                                    <div class="assignment-details">
                                        <div class="assignment-empty <?php echo $primerRegistroAsignacion ? 'd-none' : ''; ?>" id="assignmentEmpty">
                                            No hay registros pendientes por despachar.
                                        </div>
                                        <div class="assignment-fields <?php echo $primerRegistroAsignacion ? '' : 'd-none'; ?>" id="assignmentFields">
                                            <div class="assignment-field assignment-field--highlight">
                                                <span class="label">IDH</span>
                                                <span class="value" id="detailIdh"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['IDH'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">Guía</span>
                                                <span class="value" id="detailGuia"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['Numero'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">Material</span>
                                                <span class="value" id="detailMaterial"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['Producto'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">A posición</span>
                                                <span class="value" id="detailPosicion"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['Posicion'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">Bultos</span>
                                                <span class="value" id="detailBultos"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['Cantidades'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">Pallet completo</span>
                                                <span class="value" id="detailPallet"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['PalletCompleto'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">Estatus</span>
                                                <span class="value" id="detailEstado"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['Estado'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="assignment-field">
                                                <span class="label">Fecha de ingreso</span>
                                                <span class="value" id="detailFecha"><?php echo $primerRegistroAsignacion ? htmlspecialchars($primerRegistroAsignacion['FechaIngresoFormateada'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="assignment-footer">
                                        <a id="ingresarButton"
                                           class="btn btn-success btn-ingresar<?php echo $primerRegistroAsignacion ? '' : ' disabled'; ?>"
                                           <?php if ($primerRegistroAsignacion) { ?>
                                               href="<?php echo htmlspecialchars($ingresarUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                           <?php } else { ?>
                                               role="button" aria-disabled="true"
                                           <?php } ?>>
                                            Ingresar ✔️
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin de componentes dinamicos -->

                            <br>

                            <br>
                            <!-- Fin Contenido de esta seccion-->

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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const assignments = <?php echo json_encode($asignacionesList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const prevBtn = document.getElementById('prevRecord');
        const nextBtn = document.getElementById('nextRecord');
        const indicator = document.getElementById('recordIndicator');
        const pendingTotal = document.getElementById('pendingTotal');
        const fieldsWrapper = document.getElementById('assignmentFields');
        const emptyState = document.getElementById('assignmentEmpty');
        const ingresarButton = document.getElementById('ingresarButton');
        const detailElements = {
            idh: document.getElementById('detailIdh'),
            guia: document.getElementById('detailGuia'),
            material: document.getElementById('detailMaterial'),
            posicion: document.getElementById('detailPosicion'),
            bultos: document.getElementById('detailBultos'),
            pallet: document.getElementById('detailPallet'),
            estado: document.getElementById('detailEstado'),
            fecha: document.getElementById('detailFecha')
        };
        let currentIndex = assignments.length > 0 ? 0 : -1;

        function updateButtonLink(assignment) {
            if (!ingresarButton) {
                return;
            }
            if (!assignment) {
                ingresarButton.removeAttribute('href');
                ingresarButton.classList.add('disabled');
                ingresarButton.setAttribute('aria-disabled', 'true');
                ingresarButton.textContent = 'Ingresar ✔️';
                ingresarButton.setAttribute('title', 'No hay registros pendientes');
                return;
            }

            const params = new URLSearchParams({
                Guia: assignment.Numero || '',
                IDH: assignment.IDH || '',
                Ubicacion: assignment.Posicion || ''
            });

            ingresarButton.href = 'UbicarProducto.php?' + params.toString();
            ingresarButton.classList.remove('disabled');
            ingresarButton.removeAttribute('aria-disabled');
            ingresarButton.removeAttribute('title');
            ingresarButton.textContent = 'Ingresar ✔️';
        }

        function renderAssignment(index) {
            const hasAssignments = assignments.length > 0 && index >= 0;

            if (pendingTotal) {
                pendingTotal.textContent = assignments.length;
            }

            if (indicator) {
                indicator.textContent = hasAssignments ? (index + 1) + ' / ' + assignments.length : '0 / 0';
            }

            if (!fieldsWrapper || !emptyState) {
                return;
            }

            if (!hasAssignments) {
                fieldsWrapper.classList.add('d-none');
                emptyState.classList.remove('d-none');
                updateButtonLink(null);
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;
                return;
            }

            const assignment = assignments[index];

            fieldsWrapper.classList.remove('d-none');
            emptyState.classList.add('d-none');

            if (detailElements.idh) detailElements.idh.textContent = assignment.IDH || '';
            if (detailElements.guia) detailElements.guia.textContent = assignment.Numero || '';
            if (detailElements.material) detailElements.material.textContent = assignment.Producto || '';
            if (detailElements.posicion) detailElements.posicion.textContent = assignment.Posicion || '';
            if (detailElements.bultos) detailElements.bultos.textContent = assignment.Cantidades || '';
            if (detailElements.pallet) detailElements.pallet.textContent = assignment.PalletCompleto || '';
            if (detailElements.estado) detailElements.estado.textContent = assignment.Estado || '';
            if (detailElements.fecha) detailElements.fecha.textContent = assignment.FechaIngresoFormateada || '';

            updateButtonLink(assignment);

            if (prevBtn) prevBtn.disabled = index === 0;
            if (nextBtn) nextBtn.disabled = index === assignments.length - 1;
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                if (currentIndex > 0) {
                    currentIndex -= 1;
                    renderAssignment(currentIndex);
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (currentIndex < assignments.length - 1) {
                    currentIndex += 1;
                    renderAssignment(currentIndex);
                }
            });
        }

        renderAssignment(currentIndex);
    });
</script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="../dist/js/Custom/pageEnhancements.js"></script>


</body>

</html>