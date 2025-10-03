<?php
ob_start();
session_start();

$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
$TotalTarimas=0;
$TotalPiking=0;

date_default_timezone_set('America/Guatemala');

$fecha = date("d") . '-' . date("m") . '-' . date("Y");

$fechaFinal = $fecha;
$fechaInicial = date("d-m-Y", strtotime("-8 days"));



$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case "btnConsultar":
        $Fecha1 = (isset($_POST['txtFechaInicial'])) ? $_POST['txtFechaInicial'] : "";
        $Fecha2 = (isset($_POST['txtFechaFinal'])) ? $_POST['txtFechaFinal'] : "";
       
        $fechaInicial = $Fecha1;
        $fechaFinal = $Fecha2;
       

        break;

    default:

        break;
}




include '../Innet_ADM/Innet_AMD.php';

// Variables de resumen Grafica 1
// Limpia las ubicaciones de produccion que viene en Null
GraphEstatusBodegas();
Limpiar_Nulls();
//AgregarValorAsignaciones();
LimpiarExesoPiking();
// Bloquear carriles piking en bodebas
BloquearCarrilesPiking();
//Recalcular Pallets Completos
$CapacidadTotal = CapacidadTotalFIFO();
$UbicacionesLibres = UnidadesLibresFIFO();
$Exactitud = "99%";
$UnidadesOcupadas = UnidadesOcupadasFIFO();
$PorOcupacionGR = PorcentajeOcupacion();

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
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <![endif]-->

    <style>
        Select {
            height: 10px !important;
        }

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
            border-color: #ed3737;
        }

        .page-item.active .page-link {
            z-index: 1;
            color: #fff;
            background-color: #ed3131;
            border-color: #ed3131;
        }

        .bg-light {
            background-color: #e8eaec00 !important;
        }

        .tab-content {
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
  
        .zmdi-upload{
            padding: 0px 15px 0px 0px;
        }
        .zmdi-upload:hover{
            color: black;
            transition: color 0.2s linear 0.2s;
        }

        

        .file-input__label {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            font-size: 14px;
            padding: 10px 12px;
            background-color: #ff0000;
            box-shadow: 0px 0px 2px rgb(0, 0, 0);
        }

        .btn-enviar{
            color: #fff;
            font-weight: 600;
            padding: 10px 45px;
            background-color: #767676;
            border: none;
            border-radius: 2px;
        }
        .btn-enviar:hover{
            color: #b3b3b3;
        }

        .skeleton-text,
        .chart-skeleton {
            position: relative;
            overflow: hidden;
        }

        .skeleton-text::after,
        .chart-skeleton::after {
            content: '';
            position: absolute;
            top: 0;
            left: -150px;
            width: 150px;
            height: 100%;
            background: linear-gradient(90deg, rgba(233,238,245,0) 0%, rgba(255,255,255,0.8) 50%, rgba(233,238,245,0) 100%);
            animation: shimmer 1.6s infinite;
        }

        .skeleton-text {
            display: inline-block;
            min-width: 60px;
            min-height: 1em;
            background-color: #e9eef5;
            border-radius: 4px;
            color: transparent !important;
        }

        .chart-skeleton {
            background: #f4f6fa;
            border-radius: 8px;
        }

        .skeleton-loaded {
            color: inherit !important;
        }

        .skeleton-loaded::after {
            display: none;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(300%);
            }
        }

    </style>


</head>

<body>
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
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <!--               <! <div class="navbar-brand">-->
                <!-- Logo icon -->
                <!-- Logo text -->
                <!--                    <a href="index.php">-->
                <!--                    <span class="logo-text">-->
                <!-- dark Logo text -->
                <!--                        <img src="../assets/images/Sertero/LogoCBP.png" width="auto" height="40" class="d-inline-block align-top"-->
                <!--                             alt="Logo GDX">-->
                <!--                        <span class="theme_color"> Sertero</span> CBP-->
                <!--                    </span>-->
                <!--                    </a>-->
                <!-- Light Logo text -->

                <!--                </div>-->

                <div class="navbar-brand">
                    <!-- Logo icon -->
                    <a href="index.php">
                        <b class="logo-icon">
                            <!-- Dark Logo icon -->
                            <img src="../assets/images/Sertero/LogoCBP.png" width="auto" height="40" class=""-->
                            <!-- Light Logo icon -->
                            <img src="../assets/images/logo-icon.png" alt="homepage"  width="auto" height="10" class="light-logo"  />
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
                <ul class="navbar-nav float-right align-items-center">
                    <li class="nav-item d-flex align-items-center mr-3">
                        <button type="button" class="btn btn-link text-muted p-0 mr-2" id="admin-fullscreen-toggle" aria-label="Pantalla completa">
                            Pantalla Completa
                        </button>
                        <span id="status" class="online mb-0">Online</span>
                    </li>
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
                            <img src="../assets/images/users/<?php echo $_SESSION['pic'] ?> " alt="user" class="rounded-circle"
                                 width="40">
                            <span class="ml-2 d-none d-lg-inline-block"><span>Bienvenido,</span> <span
                                    class="text-dark"> <?php echo $_SESSION['USR']; ?> </span> <i data-feather="chevron-down"
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
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="page-breadcrumb">
            <div class="row">
                <div class="col-7 align-self-center">
                    <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Datos comprendidos del <?php echo date('d-m-Y', strtotime($fechaInicial)) ?> al  <?php echo  date('d-m-Y', strtotime($fechaFinal)) ?></h3>
                    
                </div>
                <div class="col-5 align-self-center">
                    <div class="customize-input float-right">

                    </div>
                </div>
            </div>
        </div>


        <br>
        <div class="toast  animate__animated animate__backInRight" role="alert" data-autohide="false" aria-live="off"
             aria-atomic="true"  style="position: absolute; top: 90px; right: 50px;">
            <div class="toast-header">

                <svg class="bd-placeholder-img rounded mr-2" width="20" height="20"
                     xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice"
                     focusable="false" role="img">
                    <rect fill="#007aff" width="100%" height="100%"></rect>
                </svg>

                <strong class="mr-auto">Liberacion de Cuarentena</strong>
                <small class="text-muted">Ahora Mismo</small>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">

                <?php $Liberados = LiberarCuarentena();
                $LiberadosHoy = LiberarCuarentenaHoy();

                if ($Liberados > 0) {
                    echo "Se Liberaron correctamente " . $Liberados . " Unidades de Cuarentena";

                } else {
                    echo "Existen " . $LiberadosHoy . " Unidades Liberadas de Cuarentena  el dia de Hoy " . $fecha;
                }
                ?>


            </div>
        </div>
        <br>


        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid animate__animated animate__fadeIn">




            <!-- ***********************Primer Grafico**************************************** -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid animate__animated animate__fadeIn">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-body" >
                                <h4 class="card-title">Seleccione las fechas para generar las graficas</h4>
                                
                                <br>

                                <div class="my-content formulario">
                                    <form role="form" action="" method="post" enctype="multipart/form-data">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Fecha Inicial</label>
                                                        <input name="txtFechaInicial"  type="date" class="form-control" value="<?php echo $fechaInicial; ?>" required>

                                                    </div>

                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Fecha Final</label>
                                                        <input name="txtFechaFinal"  type="date" class="form-control" value="<?php echo $fechaFinal; ?>" required>

                                                    </div>

                                                </div>
                
                                            </div>

                                           

                                            <div class="row">
                                                <div class="col-md-12 centrado">
                                                    <div class="form-group">
                                                        <button type="submit" value="btnConsultar" name="accion" class="btn btn-outline-success">Consultar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">1. Pallets despachados vs Pallets Ingresados</h4>
                                <h6 class="card-subtitle">Consulte datos reacionados con Pallets despachados</h6>
                                <br>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Tarimas Ingresadas vs Tarimas Despachadas</h4>
                                                <h5 class="card-subtitle"> Total de tarimas Despachadas: <span id="totaltarimasdespachadas"></span> -- Promedio de tarimas Despachadas <span id="totaltarimasdespachadaspromedio"></span></h5>
                                                <h5 class="card-subtitle"> Total de tarimas ingresadas: <span id="totaltarimasingresadas"></span> -- Promedio de tarimas Ingresadas <span id="totaltarimasingresadaspromedio"></span></h5>
                                                <canvas id="Tarimas-Trazabilidad" height="85"> </canvas>

                                            </div>
                                        </div>
                                    </div>
                                </div>

            </div>
            </div>
            </div>
            </div>

            <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">2. Capacidad de Bodegas Por dia</h4>
                                <h6 class="card-subtitle">Consulte la capacidad de bodebas por dias</h6>
                                
                                <br>
                                <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Capacidad de bodegas TOTAL por Dia.</h4>
                                                <h5 class="card-subtitle"> Promedio de Almacenamiento: <span id="PromedioCapacidadBodegas"></span>%</h5>
                                                <canvas id="Bod_Total_Diario"  height="100"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                

            </div>
            </div>
            </div>
            </div>


            <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body">
                                <h4 class="card-title">3. Toneladas Despachadas vs Toneladas Ingresadas</h4>
                                <h6 class="card-subtitle">Resumen de datos para las toneladas Despachadas</h6>
                                <br>
                                <div class="col-lg-12 col-md-12 animate__animated  animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title"> Toneladas Despachadas vs Toneladas Ingresadas</h4>
                                                <h4 class="card-subtitle"> Total de Toneladas Produccion: <span id="TotalProduccion"></span> Toneladas --- Promedio: <span id="PromTotalProduccion"></span> Toneladas</h4>
                                                <h4 class="card-subtitle"> Total de Toneladas Despacho: <span id="TotalDespacho"></span> Toneladas --- Promedio: <span id="PromTotalDespacho"></span> Toneladas </h4>
                                                <canvas id="Toneladas-Despachadas" height="100"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                    

                                </div>
            </div>
            </div>
            </div>


            <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body">
                                <h4 class="card-title">4. Bultos despachados vs bultos pickeados</h4>
                                <h6 class="card-subtitle">Comparacion entre bultos despachados y pickeados</h6>
                                <br>
                                <div class="col-lg-12 col-md-12 animate__animated  animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title"> bultos despachados vs pickeados</h4>
                                                <h4 class="card-subtitle"> Total de Bultos Despachados: <span id="TotalBultosDespacho"></span>  --- Promedio: <span id="PromBultosDespacho"></span> Bultos</h4>
                                                <h4 class="card-subtitle"> Total de Bultos Piking: <span id="TotalBultosPiking"></span>  --- Promedio: <span id="PromBultosPiking"></span> Bultos </h4>
                                                <h4 class="card-subtitle"> Porcentaje Piking: <span id="PorcentajeTotalBultosPiking"></span>   %:  </h4>

                                                <canvas id="Bultos-DespachadasvsPikeadas" height="100"> </canvas>

                                            </div>
                                        </div>
                                    </div>



                                </div>
            </div>
            </div>
            </div>

            <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body">
                                <h4 class="card-title">5. Ocupacion de almacenes</h4>
                                <h6 class="card-subtitle">Estado de ocupacion de los almacenes</h6>
                                <br>
                                <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Estado - Capacidad por bodegas</h4>
                                                <canvas id="bar-chart2"  height="80"></canvas>


                                            </div>
                                        </div>
                                    </div>
                                
            </div>
            </div>
            </div>
            </div>


            <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body">
                                <h4 class="card-title">6. Conteo Ciego</h4>
                                <h6 class="card-subtitle">detalle de total de guias a las que se le aplica conteo Ciego durante la carga</h6>
                                <h4 class="card-subtitle"> Total de Guias Verdes: <span id="TotalGuiasCC"></span>  </h4>
                                <h4 class="card-subtitle"> Total de Guias Rojas: <span id="TotalGuiasRojasCC"></span> </h4>
                                <h4 class="card-subtitle"> Porcentaje de Conteo Ciego: <span id="PromedioCC"></span> %</h4>
                                                
                                <br>
                                <div class="col-lg-12 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">% Conteo Ciego posterior: </h4>
                                                <canvas id="Porcentaje-Conteociego" height="80"> </canvas>


                                            </div>
                                        </div>
                                    </div>

                                    
                                
                                
            </div>
            </div>
            </div>
            </div>


            <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body">
                                <h4 class="card-title">7. Porcentaje de FIFO</h4>
                                <h6 class="card-subtitle">Detalle de las correcciones y el porcentaje de exactitud del sistema por inventario ciclico</h6>
                                <h4 class="card-subtitle"> Total de Pallets Eliinados: <span id="TotalPalletsAgregadas"></span>  </h4>
                                <h4 class="card-subtitle"> Total de Pallets Agregados: <span id="TotalPalletsEliminadas"></span> </h4>
                                <h4 class="card-subtitle"> Porcentaje de Exactitud: <span id="PromedioFIFO"></span> %</h4>
                                                
                                <br>
                                <div class="col-lg-12 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Correcciones y ajustes: </h4>
                                                <canvas id="Porcentaje-Exactitud" height="80"> </canvas>


                                            </div>
                                        </div>
                                    </div>

                                    
                                
                                
            </div>
            </div>
            </div>
            </div>


            <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Bodegas</h4>
                                <h6 class="card-subtitle">Consulta rapida del estatus de las bodegas y datos relacionados</h6>
                                <br>
                                <!-- Start First Cards -->
                                <div class="row">
                                    <!-- Column -->
                                    <div class="col-md-6 col-lg-3 col-xlg-3 animate__animated animate__backInUp" style="animation-duration: 1.25s;">
                                        <div class="card card-hover">
                                            <div class="p-2 bg-primary text-center">
                                                <h1 id="capacidad-total" class="font-light text-white"><?php echo $CapacidadTotal; ?></h1>
                                                <h6 class="text-white">Capacidad Total</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Column -->
                                    <div class="col-md-6 col-lg-3 col-xlg-3 animate__animated animate__backInUp" style="animation-duration: 1.5s;">
                                        <div class="card card-hover">
                                            <div class="p-2 bg-success text-center">
                                                <h1 id="Ubicaciones-Lbres" class="font-light text-white"><?php echo $UbicacionesLibres; ?></h1>
                                                <h6 class="text-white">Ubicaciones Libres</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Column -->
                                      <!-- Column -->
                                    <div class="col-md-6 col-lg-3 col-xlg-3 animate__animated animate__backInUp" style="animation-duration: 2s;">
                                        <div class="card card-hover">
                                            <div class="p-2 bg-danger text-center">
                                                <h1  id="Unidades-Ocupadas" class="font-light text-white"><?php echo $UnidadesOcupadas; ?></h1>
                                                <h6 class="text-white">Unidades Ocupadas</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Column -->
                                    <div class="col-md-6 col-lg-3 col-xlg-3 animate__animated animate__backInUp" style="animation-duration: 1.75s;">
                                        <div class="card card-hover">
                                            <div class="p-2 bg-cyan text-center">
                                                <h1 id="Porcentaje-Exactitud" class="font-light text-white"><?php echo $PorOcupacionGR; ?></h1>
                                                <h6 class="text-white">% de Ocupacion</h6>
                                            </div>
                                        </div>
                                    </div>
                                   
                                </div>
                                <!-- *************************************************************** -->
                                <!-- End First Cards -->


                                <!-- ***********************Fin primer Grafico************************************ -->
                                <div class="row">

                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">% de Ocupacion de bodegas</h4>
                                                <canvas id="bar-chart"  height="80"></canvas>


                                            </div>
                                        </div>
                                    </div>

                                    

                                    

            </div>
            
            </div>
            </div>

            <div class="row">
                    <div class="col-12">
                        <div class="card">
                           

                                <div class="row">
                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Toneladas Picking - Despacho</h4>
                                                <h5 class="card-subtitle"> Total de Toneladas Pikeadas: <span id="toneladas"></span> Toneladas</h5>
                                                <canvas id="TONS-Pikeadas" height="85"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Toneladas Pallets - Despacho</h4>
                                                <h5 class="card-subtitle"> Total de Toneladas Despachadas: <span id="toneladasDespachadas"></span> Toneladas</h5>
                                                <canvas id="TONS-Despachadas" height="85"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Toneladas Pallets - Despacho vs Toneladas Picking - Despacho</h4>
                                                <canvas id="TONS-DespachadasvsPikeadas" height="85"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                </div>

            </div>
            </div>
            </div>
            </div>
            </div>



      
            


            <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Bultos despachados</h4>
                                <h6 class="card-subtitle">Consulte datos reacionados con bultos despachados</h6>
                                <br>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Bultos Picking - Despacho</h4>
                                                <h5 class="card-subtitle"> Total de bultos pikeados en despacho: <span id="totalbultospik"></span> Bultos/Cajas -- Promedio: <span id="totalbultospikpromedio"></span> Bultos/Cajas</h5>
                                                <canvas id="Bultos-Pikeados" height="85"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                </div>


                                <div class="row">
                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Bultos Pallets - Despacho</h4>
                                                <h5 class="card-subtitle"> Total de bultos despachados en pallets: <span id="totalbultospall"></span> Bultos/Cajas -- Promedio: <span id="totalbultospallpromedio"></span> Bultos/Cajas</h5>
                                                <canvas id="Bultos-Pallets" height="85"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                </div>

            </div>
            </div>
            </div>
            </div>





                <div class="row">
                    <div class="col-12">
                        <div class="card">







                            <div class="card-body">
                                <h4 class="card-title">Datos de utilidad</h4>
                                <h6 class="card-subtitle">Consulte alguons datos de utilidad</h6>
                                




                                    

                                <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">% de Picking Vs Palles despacho: </h4>
                                                <h5 class="card-subtitle"> Total de Toneladas Pikeadas: <span id="totaltarimaspikingsum"></span> </h5>
                                                <h5 class="card-subtitle"> Total de Toneladas Despachadas en pallets: <span id="totaltarimasdespachadassum"></span> </h5>
                                                <h5 class="card-subtitle"> Total de Despacho(Piking + pallets): <span id="totalDespachosSUM"></span> </h5>
                                                 <h5 class="card-subtitle"> Porcentaje de Piking: <span id="porcentajepiking"></span> %</h5>
                                                
                                                <canvas id="Porcentaje-Piking" height="75"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                <br>
                                <div class="row">

                                    <div class="col-lg-12 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Ocupacion por Linea</h4>
                                                <canvas id="OcupacionPorLinea" height="100"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Ocupacion Top 10 IDH</h4>
                                                <canvas id="OcupacionTop10" height="150"> </canvas>


                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-lg-12 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">
                                                    Proximos a vencer
                                                     <button id="downloadCSV" class="btn btn-primary btn-sm" style="margin-left: 10px;">Descargar Reporte</button>
                                                </h4>
                                                <canvas id="Top10AVencer" height="200"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">
                                                    Proximos a vencer Piking
                                                     <button id="downloadCSVPK" class="btn btn-primary btn-sm" style="margin-left: 10px;">Descargar Reporte</button>
                                                </h4>
                                                <canvas id="Top10AVencerPK" height="220"></canvas>
                                            </div>
                                        </div>
                                    </div>




                                    <div class="col-lg-12 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Ocupacion Bultos en Piking</h4>
                                                <canvas id="TopPiking" height="140"> </canvas>


                                            </div>
                                        </div>
                                    </div>




                                    <div class="col-lg-6 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Pallets Ingresados por operador </h4>
                                                <canvas id="Ingresos-Operador" height="250"> </canvas>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Pallets Despachados por operador </h4>
                                                <canvas id="Despachos-Operador" height="250"> </canvas>


                                            </div>
                                        </div>
                                    </div>

                                </div>






                               

                                
                                <!--
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Top 15 de Productos</h4>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Guias y estatus</h4>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                -->


                                <!-- ***********************Fin primer Grafico************************************ -->
                                <!-- ***********************Fin primer Grafico************************************ -->
                                <!-- ***********************Fin primer Grafico************************************ -->
                                <!-- ***********************Fin primer Grafico************************************ -->
                                <!-- ***********************Fin primer Grafico************************************ -->
                                <!-- ***********************Fin primer Grafico************************************ -->
                                <!-- ***********************Fin primer Grafico************************************ -->

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
        <script src="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
        <script src="../assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>
        <script src="../dist/js/pages/dashboards/dashboard1.min.js"></script> 
        <script src="../dist/js/OnLine.js"></script>
        <!-- Chart JS -->
        <script src="../assets/libs/chart.js/dist/Chart.min.js"></script>



        <script>
            const skeletonTextIds = [
                'PromedioCapacidadBodegas',
                'TotalProduccion',
                'TotalDespacho',
                'PromTotalProduccion',
                'PromTotalDespacho',
                'totaltarimasingresadas',
                'totaltarimasdespachadas',
                'totaltarimasingresadaspromedio',
                'totaltarimasdespachadaspromedio',
                'TotalBultosDespacho',
                'TotalBultosPiking',
                'PorcentajeTotalBultosPiking',
                'PromBultosDespacho',
                'PromBultosPiking',
                'totalbultospall',
                'totalbultospallpromedio',
                'totalbultospik',
                'totalbultospikpromedio',
                'toneladas',
                'totaltarimaspikingsum',
                'toneladasDespachadas',
                'totaltarimasdespachadassum',
                'totalDespachosSUM',
                'porcentajepiking',
                'TotalGuiasCC',
                'TotalGuiasRojasCC',
                'PromedioCC',
                'TotalPalletsAgregadas',
                'TotalPalletsEliminadas',
                'PromedioFIFO',
                'capacidad-total',
                'Ubicaciones-Lbres',
                'Unidades-Ocupadas'
            ];

            document.addEventListener('DOMContentLoaded', function () {
                skeletonTextIds.forEach(function (id) {
                    var element = document.getElementById(id);
                    if (element && element.innerText.trim() === '') {
                        element.classList.add('skeleton-text');
                    }
                });

                document.querySelectorAll('canvas').forEach(function (canvas) {
                    canvas.classList.add('chart-skeleton');
                });
            });

            window.addEventListener('load', function () {
                skeletonTextIds.forEach(function (id) {
                    var element = document.getElementById(id);
                    if (element) {
                        element.classList.remove('skeleton-text');
                        element.classList.add('skeleton-loaded');
                    }
                });

                document.querySelectorAll('.chart-skeleton').forEach(function (canvas) {
                    canvas.classList.add('skeleton-loaded');
                    canvas.classList.remove('chart-skeleton');
                });
            });

            function toggleFullScreen() {
                if (!document.fullscreenElement) {
                    var element = document.documentElement;
                    if (element.requestFullscreen) {
                        element.requestFullscreen();
                    } else if (element.webkitRequestFullscreen) {
                        element.webkitRequestFullscreen();
                    } else if (element.mozRequestFullScreen) {
                        element.mozRequestFullScreen();
                    } else if (element.msRequestFullscreen) {
                        element.msRequestFullscreen();
                    }
                } else if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var fullscreenButton = document.getElementById('admin-fullscreen-toggle');
                if (fullscreenButton) {
                    fullscreenButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        toggleFullScreen();
                    });
                }
            });
        </script>

        <!-- Datos de las bodegas -->
        
        
        <?php
        include '../innet_CHARTS/Innet_CHARTS.php';
        $NombreBodegas = GetNombreBodegas();
        $PorOcupacion = GetPorcentajeOcupacion();
        ?>



<script>
    // Función para obtener un color interpolado entre verde y rojo
    function getColor(percentage) {
        var green = [0, 200, 66]; // Color verde
        var yellow = [255, 165, 0]; // Color amarillo
        var red = [255, 0, 0];   // Color rojo

        var color = [];

        if (percentage <= 50) {
            // Interpolación entre verde y amarillo para porcentajes menores o iguales a 50
            for (var i = 0; i < 3; i++) {
                color[i] = Math.round(green[i] + (yellow[i] - green[i]) * (percentage / 50));
            }
        } else {
            // Interpolación entre amarillo y rojo para porcentajes mayores a 50
            for (var i = 0; i < 3; i++) {
                color[i] = Math.round(yellow[i] + (red[i] - yellow[i]) * ((percentage - 50) / 50));
            }
        }

        return 'rgb(' + color.join(',') + ')';
    }

    // Capaciodad de bodegas
    var labels = <?php echo json_encode($NombreBodegas); ?>;
    var Datos = <?php echo json_encode($PorOcupacion); ?>;

    // Calcular los colores para cada barra
    var backgroundColors = Datos.map(function (percentage) {
        return getColor(percentage);
    });

    new Chart(document.getElementById("bar-chart").getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: "",
                    backgroundColor: backgroundColors,
                    data: Datos
                }
            ]
        },
        options: {
    legend: { display: true },
    title: {
        display: true,
        text: '% Capacidad por bodegas'
    },
    scales: {
        yAxes: [{
            ticks: {
                beginAtZero: true,
                max: 100 // Establecer el máximo del eje Y en 100
            }
        }]
    },
    plugins: {
        datalabels: {
            anchor: 'end',
            align: 'end',
            formatter: function (value, context) {
                return value + '%';
            },
            font: {
                size: 20 // Tamaño del texto
            }
        }
    },
    animation: {
        onComplete: function () {
            var ctx = this.chart.ctx;
            ctx.textAlign = "center";
            ctx.textBaseline = "bottom";
            ctx.font = "22px Arial";

            this.data.datasets.forEach(function (dataset) {
                for (var i = 0; i < dataset.data.length; i++) {
                    var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                    ctx.fillStyle = '#7c8798'; // Color
                    ctx.fillText(dataset.data[i] + '%', model.x, model.y - 5);
                }
            });
        }
    }
}


    });
</script>


<!-- Grafica de cantidad por bodega-->
<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';

    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

    
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT CONCAT('Bodega ', bodega) AS bodega_concatenada
FROM (SELECT DISTINCT bodega FROM posiciones) AS b WHERE b.bodega <> 9
ORDER BY b.bodega + 0 desc";
    $result = $conn->query($sql);
    

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG3 = [];
    $Ocupadas = [];
    $Totales = [];
    $Libres = [];
    $TotalProducion = 0;
    $TotalDespacho = 0;
    $Registros = 0;
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $labelsG3[] = $row['bodega_concatenada'];
            $ToneladasProduccionG3[] = $row['ToneladasProduccion'];
            $TotalProducion += $row['ToneladasProduccion'];
            $Registros +=1;
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<?php
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';

    $FechaHoy = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

   
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT Bodega,count(*) as Ocupadas FROM `posiciones` where Estado = 'Ocupada' GROUP by Bodega order by Bodega+0 desc
  ";


    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
           
            $Ocupadas[] = $row['Ocupadas'];
            
        }
    }


    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT Bodega,count(*) as Libres FROM `posiciones` where Estado = 'Libre' GROUP by Bodega order by Bodega+0 desc";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $Libres[] = $row['Libres'];
        }
    }


    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT Bodega,count(*) as Totales FROM `posiciones` GROUP by Bodega order by Bodega+0 desc";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $Totales[] = $row['Totales'];
        }
    }






} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("bar-chart2").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG3)); ?>,
            datasets: [
                {
                    label: "Ubicaciones Totales",
                    backgroundColor: "#072CE8",
                    borderColor: "#8CB6F5",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($Totales)); ?>
                },
                {
                    label: "Ubicaciones Ocupadas",
                    backgroundColor: "#e80729",
                    borderColor: "#f58c9b",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($Ocupadas)); ?>
                },
                {
                    label: "Ubicaciones Libres",
                    backgroundColor: "#04d400",
                    borderColor: "#8df58c",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($Libres)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Estado - Capacidad de bodegas'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i] , model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });
</script>



<!-- Fin Grafica de Cantidad por bodega-->




















<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
$PromedioAlmacenes = 0;
$Registros = 0;
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';

    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));


    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT Fecha,Cant_CapacidadTotal,Cant_Ocupadas, (Cant_Ocupadas/Cant_CapacidadTotal)*100 as Porcentaje FROM `gaf_capacidadbodegasdiaria` where NombreBodega = 'Todas' and date(Fecha) BETWEEN '$FechaHace9Dias' and '$FechaActual' order by date(Fecha) desc ";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG2 = [];
    $capacidadTotalDataG2 = [];
    $ocupadasDataG2 = [];
    $porcentajeDataG2 = [];
  

    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {

            $labelsG2[] = date('d/m/Y', strtotime($row['Fecha']));
            $capacidadTotalDataG2[] = $row['Cant_CapacidadTotal'];
            $ocupadasDataG2[] = $row['Cant_Ocupadas'];
            $porcentajeDataG2[] = round($row['Cant_Ocupadas'] / $row['Cant_CapacidadTotal'] * 100);
            $PromedioAlmacenes += round($row['Cant_Ocupadas'] / $row['Cant_CapacidadTotal'] * 100);
            $Registros += 1;
        }
    }

    $PromedioAlmacenes = round($PromedioAlmacenes / $Registros,2);

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}


?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("Bod_Total_Diario").getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_reverse($labelsG2)); ?>,
        datasets: [
            {
                label: "% Ocupacion",
                type: 'line',
                fill: false,
                backgroundColor: "#5c5c5b",
                borderColor: "#5c5c5b",
                borderWidth: 3,
                yAxisID: 'porcentaje-axis',
                data: <?php echo json_encode(array_reverse($porcentajeDataG2)); ?>
            },
            {
                label: "Capacidad Total",
                backgroundColor: "#F4EB95",
                borderColor: "#D4C35E",
                borderWidth: 3,
                data: <?php echo json_encode(array_reverse($capacidadTotalDataG2)); ?>
            },
            {
                label: "Ubicaciones Ocupadas",
                backgroundColor: "#FFB4A1",
                borderColor: "#FF867F",
                borderWidth: 3,
                data: <?php echo json_encode(array_reverse($ocupadasDataG2)); ?>
            }
            
        ]
    },
    options: {
        legend: { display: true },
        title: {
            display: true,
            text: 'Capacidad de bodegas TOTAL por Dia.'
        },
        scales: {
            yAxes: [
                {
                    id: 'cantidad-axis',
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        beginAtZero: false
                    }
                },
                {
                    id: 'porcentaje-axis',
                    type: 'linear',
                    
                    position: 'right',
                    ticks: {
                        beginAtZero: true,
                        max: 100,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            ]
        },
        plugins: {
        datalabels: {
            anchor: 'end',
            align: 'end',
            formatter: function (value, context) {
                return value ;
            },
            font: {
                size: 13 // Tamaño del texto
            }
        }
    },
    animation: {
    onComplete: function () {
        var ctx = this.chart.ctx;
        ctx.textAlign = "center";
        ctx.textBaseline = "bottom";
        ctx.font = "12px Arial";

        this.data.datasets.forEach(function (dataset) {
            for (var i = 0; i < dataset.data.length; i++) {
                var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                ctx.fillStyle = '#3D3D3D'; // Color
                var value = dataset.data[i];
                if (value < 100) {
                    value += "%";
                }
                ctx.fillText(value, model.x + 10 , model.y - 5);
            }
        });
    }
}

    }
});


var toneladasElement = document.getElementById('PromedioCapacidadBodegas');
var valorToneladas = <?php echo $PromedioAlmacenes; ?>;
toneladasElement.textContent = valorToneladas;

</script>

<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';

    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));


    
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT date(FechaRegistro) AS Fecha, round(sum(ASG.Cantidades * PR.PESOBRUTOCAJA /1000),2) AS ToneladasProduccion FROM `asignaciones` ASG
    inner join productos PR on PR.IDH = ASG.IDH
    where ASG.Estado = 'Ingresado' and date(ASG.FechaRegistro) between '$FechaHace9Dias' and '$FechaActual' GROUP by date(FechaRegistro) ORDER BY  date(FechaRegistro) desc ;  ";
    $result = $conn->query($sql);
    

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG3 = [];
    $ToneladasProduccionG3 = [];
    $ToneladasDespachoG3 = [];
    $TotalProducion = 0;
    $TotalDespacho = 0;
    $Registros = 0;
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $ToneladasProduccionG3[] = $row['ToneladasProduccion'];
            $TotalProducion += $row['ToneladasProduccion'];
            $Registros +=1;
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';

    $FechaHoy = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

   
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT DATE(FechaDespacho) AS Fecha, ROUND(SUM(PesoDeDespacho) /1000,2) AS TotalPesoDespacho
    FROM (  SELECT DISTINCT
    D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
    DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
    D.Operador, 'Turno', 'Tapado/Libre',G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
    TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
    IFNULL(TIMESTAMPDIFF(MONTH, date(D.FechaRealizado), date(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
    'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja,PR.CAJASXPALET as Cajas,0 as CajasPK, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet)  as PesoDeDespacho,
    D.FechaRealizado AS FechaDespacho, MONTHNAME(FechaRealizado) AS MES, DATE_FORMAT(FechaRealizado, '%W') AS nombre_dia,
    CONCAT(
        TIMESTAMPDIFF(DAY, D.FechaRealizado, D.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho  
FROM despachos D
INNER JOIN posiciones P ON P.Ubicacion = D.Posicion
INNER JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
INNER JOIN Guias G ON G.Transporte = D.Guia_Carga 
INNER JOIN productos PR ON PR.IDH = D.IDH
WHERE DATE(D.FechaRealizado) BETWEEN '$FechaHace9Dias' and '$FechaHoy'  

union

SELECT DP.Estatus, CF.Ubicacion,'N/A',PR.Descripcion,'Picking',DP.IDH,DP.FechaProduccion,DP.FechaVencimiento, DS.Operador, 'N/A', 'N/A', GS.NombreDestino,GS.Transportista, DP.Transporte, TIME(DS.FechaRealizado),'N/A', IFNULL(TIMESTAMPDIFF(MONTH, date(DS.FechaRealizado), date(DP.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 'N/A', 'N/A','1', PR.LINEA, PR.PESOBRUTOCAJA, 0 as Cajas,
sum(DP.UnidadesEnPallet) as CajasPK, (PR.PESOBRUTOCAJA * sum(DP.UnidadesEnPallet))  , DS.FechaRealizado,  DATE_FORMAT(DS.FechaRealizado, '%M') AS nombre_mes,
        DATE_FORMAT(DS.FechaRealizado, '%W') AS nombre_dia,
        CONCAT(
        TIMESTAMPDIFF(DAY, DS.FechaRealizado, DS.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho
FROM `detalle_piking` DP
INNER join productos PR     on DP.IDH = PR.IDH
INNER join config_piking CF on DP.IDH = CF.IDH
INNER join despachos DS     on DP.Transporte = DS.Guia_Carga and DP.IDH = DS.IDH
INNER join Guias GS			on DP.Transporte = GS.Transporte
where date(DS.Fecha_Hora_Despacho) BETWEEN '$FechaHace9Dias' and '$FechaHoy'  and DS.Operador = 'Piking'  GROUP by DP.Transporte,DP.IDH) AS subquery
    GROUP BY Fecha
    ORDER BY Fecha DESC";


    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $labelsG3[] = date('d/m/Y', strtotime($row['Fecha']));
            $ToneladasDespachoG3[] = $row['TotalPesoDespacho'];
            $TotalDespacho += $row['TotalPesoDespacho'];
            $TotalTarimas += $row['TotalPesoDespacho'];
        }
    }
   // array_unshift($labelsG3, "Promedio");

    $produccionPromedio = round($TotalProducion / $Registros, 2);
    $despachoPromedio = round($TotalDespacho / $Registros, 2);
    

   
   // array_unshift($ToneladasProduccionG3, $produccionPromedio);
   // array_unshift($ToneladasDespachoG3, $despachoPromedio);
    

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("Toneladas-Despachadas").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG3)); ?>,
            datasets: [
                {
                    label: "Toneladas Produccion",
                    backgroundColor: "#f0c46c",
                    borderColor: "#a67108",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($ToneladasProduccionG3)); ?>
                },
                {
                    label: "Toneladas Despacho",
                    backgroundColor: "#49b4de",
                    borderColor: "#08678c",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($ToneladasDespachoG3)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Toneladas Produccion vs Toneladas Despacho'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i] + ' Tn', model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });


    // Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('TotalProduccion');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalProducion; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;



// Obtener el elemento donde queremos actualizar el valor
var toneladasElement2 = document.getElementById('TotalDespacho');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas2 = <?php echo $TotalDespacho; ?>;
// Actualizar el contenido del elemento
toneladasElement2.textContent = valorToneladas2;





    // Obtener el elemento donde queremos actualizar el valor
    var toneladasElement3 = document.getElementById('PromTotalProduccion');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas3 = <?php echo round($TotalProducion / $Registros,2); ?>;
// Actualizar el contenido del elemento
toneladasElement3.textContent = valorToneladas3;



// Obtener el elemento donde queremos actualizar el valor
var toneladasElement4 = document.getElementById('PromTotalDespacho');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas4 = <?php echo round($TotalDespacho / $Registros,2); ?>;
// Actualizar el contenido del elemento
toneladasElement4.textContent = valorToneladas4;

</script>



<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

  
    include '../LQS_EUQ/Connect.php';
   

    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

   
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT fecha,  SUM(total_asignaciones) AS total_asignaciones,
    SUM(total_despachos) AS total_despachos
  FROM (
    SELECT
      DATE(FechaRegistro) AS fecha,
           COUNT(*) AS total_asignaciones,
      0 AS total_despachos
    FROM asignaciones
    WHERE PalletCompleto = 'Si' and date(FechaRegistro) between '$FechaHace9Dias' and '$FechaActual' AND Estado = 'Ingresado' and cantidades > 0  
    GROUP BY fecha
    UNION ALL
    SELECT
      DATE(Fecha_Hora_Despacho) AS fecha,
           0 AS total_asignaciones,
      COUNT(*) AS total_despachos
    FROM despachos
    WHERE Operador <> 'PIKING' and date(Fecha_Hora_Despacho) between '$FechaHace9Dias' and '$FechaActual' AND Estado = 'Despachado'
    GROUP BY fecha
  ) AS subquery
  GROUP BY fecha
  ORDER BY fecha DESC;";


    $result = $conn->query($sql);
    

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG4 = [];
    $TotalAsignacionesG4 = [];
    $TotalDespachosG4 = [];
    $TotalAsignacionesINF = 0;
    $TotalDespachosINF = 0;
    $Registros = 0;
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $labelsG4[] = date('d/m/Y', strtotime($row['fecha'])).' '.$row['nombre_dia'];
            $TotalAsignacionesG4[] = $row['total_asignaciones'];
            $TotalDespachosG4[] = $row['total_despachos'];

            $TotalAsignacionesINF += $row['total_asignaciones'];
            $TotalDespachosINF += $row['total_despachos'];
            $Registros += 1;

        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("Tarimas-Trazabilidad").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG4)); ?>,
            datasets: [
                {
                    label: "Tarimas Produccion",
                    backgroundColor: "#f5b14c",
                    borderColor: "#c97a04",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($TotalAsignacionesG4)); ?>
                },
                {
                    label: "Tarimas Despacho",
                    backgroundColor: "#93b5f5",
                    borderColor: "#3a7bf2",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($TotalDespachosG4)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Tarimas Produccion vs Tarimas Despacho'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });


var toneladasElement = document.getElementById('totaltarimasingresadas');
var valorToneladas = <?php echo $TotalAsignacionesINF; ?>;
toneladasElement.textContent = valorToneladas;

var toneladasElement2 = document.getElementById('totaltarimasdespachadas');
var valorToneladas2 = <?php echo $TotalDespachosINF  ; ?>;
toneladasElement2.textContent = valorToneladas2;


var toneladasElement3 = document.getElementById('totaltarimasingresadaspromedio');
var valorToneladas3 = <?php echo round($TotalAsignacionesINF / $Registros,2); ?>;
toneladasElement3.textContent = valorToneladas3;

var toneladasElement4 = document.getElementById('totaltarimasdespachadaspromedio');
var valorToneladas4 = <?php echo round($TotalDespachosINF / $Registros,2); ?>;
toneladasElement4.textContent = valorToneladas4;





</script>








<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

  
    include '../LQS_EUQ/Connect.php';
   

    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

   
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT DATE(FechaDespacho) AS Fecha, SUM(Cajas)  AS SUMCajas, SUM(CajasPK)  AS SUMCajasPK
    FROM(SELECT DISTINCT
    D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
    DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
    D.Operador, 'Turno', 'Tapado/Libre',G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
    TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
    IFNULL(TIMESTAMPDIFF(MONTH, date(D.FechaRealizado), date(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
    'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja,PR.CAJASXPALET as Cajas,0 as CajasPK, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet) /1000 as PesoDeDespacho,
    D.FechaRealizado AS FechaDespacho, MONTHNAME(FechaRealizado) AS MES, DATE_FORMAT(FechaRealizado, '%W') AS nombre_dia,
    CONCAT(
        TIMESTAMPDIFF(DAY, D.FechaRealizado, D.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho  
FROM despachos D
INNER JOIN posiciones P ON P.Ubicacion = D.Posicion
INNER JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
INNER JOIN Guias G ON G.Transporte = D.Guia_Carga 
INNER JOIN productos PR ON PR.IDH = D.IDH
WHERE DATE(D.FechaRealizado) BETWEEN '$FechaHace9Dias' and '$FechaActual'  

union

SELECT DP.Estatus, CF.Ubicacion,'N/A',PR.Descripcion,'Picking',DP.IDH,DP.FechaProduccion,DP.FechaVencimiento, DS.Operador, 'N/A', 'N/A', GS.NombreDestino,GS.Transportista, DP.Transporte, TIME(DS.FechaRealizado),'N/A', IFNULL(TIMESTAMPDIFF(MONTH, date(DS.FechaRealizado), date(DP.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 'N/A', 'N/A','1', PR.LINEA, PR.PESOBRUTOCAJA, 0 as Cajas,
sum(DP.UnidadesEnPallet) as CajasPK, (PR.PESOBRUTOCAJA * sum(DP.UnidadesEnPallet)) / 1000 , DS.FechaRealizado,  DATE_FORMAT(DS.FechaRealizado, '%M') AS nombre_mes,
        DATE_FORMAT(DS.FechaRealizado, '%W') AS nombre_dia,
        CONCAT(
        TIMESTAMPDIFF(DAY, DS.FechaRealizado, DS.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho
FROM `detalle_piking` DP
INNER join productos PR     on DP.IDH = PR.IDH
INNER join config_piking CF on DP.IDH = CF.IDH
INNER join despachos DS     on DP.Transporte = DS.Guia_Carga and DP.IDH = DS.IDH
INNER join Guias GS			on DP.Transporte = GS.Transporte
where date(DS.Fecha_Hora_Despacho) BETWEEN '$FechaHace9Dias' and '$FechaActual'  and DS.Operador = 'Piking'  GROUP by DP.Transporte,DP.IDH) AS subquery
    GROUP BY Fecha
    ORDER BY Fecha DESC";



    $result = $conn->query($sql);
    

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG42 = [];
    $TotalAsignacionesG42 = [];
    $TotalDespachosG42 = [];
    $TotalAsignacionesINF2 = 0;
    $TotalDespachosINF2 = 0;
    $Registros = 0;
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $labelsG42[] = date('d/m/Y', strtotime($row['Fecha']));
            $TotalAsignacionesG42[] = $row['SUMCajas'];
            $TotalDespachosG42[] = $row['SUMCajasPK'];

            $TotalAsignacionesINF2 += $row['SUMCajas'];
            $TotalDespachosINF2 += $row['SUMCajasPK'];
            $Registros += 1;

        }
    }



    // Sumatoria de datos para TotalesDespacho que ahora sera Pallets + Piking

    for ($i = 0; $i < count($TotalAsignacionesG42); $i++) {
        // Sumar los elementos correspondientes y guardarlos en el vector de resultados
        $TotalAsignacionesG42[$i] = $TotalAsignacionesG42[$i] + $TotalDespachosG42[$i];
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("Bultos-DespachadasvsPikeadas").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG42)); ?>,
            datasets: [
                {
                    label: "Bultos Despachados",
                    backgroundColor: "#f5b14c",
                    borderColor: "#c97a04",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($TotalAsignacionesG42)); ?>
                },
                {
                    label: "Bultos Pikeados",
                    backgroundColor: "#93b5f5",
                    borderColor: "#3a7bf2",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($TotalDespachosG42)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Tarimas Produccion vs Tarimas Despacho'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });


var toneladasElement = document.getElementById('TotalBultosDespacho');
var valorToneladas = <?php echo $TotalAsignacionesINF2 + $TotalDespachosINF2; ?>;
toneladasElement.textContent = valorToneladas;

var toneladasElement2 = document.getElementById('TotalBultosPiking');
var valorToneladas2 = <?php echo $TotalDespachosINF2  ; ?>;
toneladasElement2.textContent = valorToneladas2;

    var toneladasElement5 = document.getElementById('PorcentajeTotalBultosPiking');
    var valorToneladas5 = <?php echo number_format(($TotalDespachosINF2 / ($TotalAsignacionesINF2 + $TotalDespachosINF2)) * 100, 2);?>;
    toneladasElement5.textContent = valorToneladas5;


var toneladasElement3 = document.getElementById('PromBultosDespacho');
var valorToneladas3 = <?php echo round(($TotalAsignacionesINF2 + $TotalDespachosINF2) / $Registros,2); ?>;
toneladasElement3.textContent = valorToneladas3;

var toneladasElement4 = document.getElementById('PromBultosPiking');
var valorToneladas4 = <?php echo round($TotalDespachosINF2 / $Registros,2); ?>;
toneladasElement4.textContent = valorToneladas4;





</script>






<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT PR.Linea, Count(*) as Total FROM dbs9098416.posiciones PS
    inner Join productos PR on PS.IDH = PR.IDH group by PR.Linea";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $LineaG5 = [];
    $TotalG5 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $LineaG5[] = $row['Linea'];
            $TotalG5[] = $row['Total'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    // Ocupacion por Lineas.
   
    var colores = ["#f0f8ff",  "#adcbe3",  "#6595c0", "#416ab0", "#2d4f9f", "#19358f", "#041a7e", "#00076d"];

    new Chart(document.getElementById("OcupacionPorLinea").getContext('2d'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_reverse($LineaG5)); ?>,
            datasets: [
                {
                    label: "Bultos Produccion",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($TotalG5)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Ocupacion por lineas.'
            },
            plugins: {
                datalabels: {
                    color: '#000000',
                    anchor: 'end',
                    align: 'start',
                    formatter: function(value, context) {
                        return context.chart.data.labels[context.dataIndex];
                    }
                }
            }
        }
    });
</script>








<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT PS.IDH, count(*) as Pallets,PR.Descripcion FROM posiciones PS
    Inner Join productos  PR on PS.IDH = PR.IDH
    where PS.Estado = 'Ocupada' and PS.bodega <> 9 group by PS.IDH  order by Count(*) desc limit 10";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $IDHsG6 = [];
    $CantidadesG6 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $IDHsG6[] = $row['IDH']. ' '.$row['Descripcion'];
            $CantidadesG6[] = $row['Pallets'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Ocupacion TOP 10.
    var colores = ["#f5b14c", "#e1bf4d", "#cdba4d", "#b9b54e", "#a5b04e", "#91ab4f", "#7ea64f", "#6aa14f", "#569c50", "#429750"];


    new Chart(document.getElementById("OcupacionTop10").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($IDHsG6)); ?>,
            datasets: [
                {
                    label: "Pallets por IDH",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($CantidadesG6)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Ocupacion Top 10 IDHs.'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });
</script>



<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);

    $sql = "SELECT Top10aVencer.*, PR.Descripcion
FROM `Top10aVencer`
INNER JOIN productos PR ON PR.IDH = Top10aVencer.IDH
WHERE FechaVencimiento <= DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
ORDER BY FechaVencimiento;";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $IDHsG6 = [];
    $CantidadesG6 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) { 
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $IDHsG6[] = $row['IDH']. '-'.$row['Descripcion']  . ' -- '. date('d/m/Y', strtotime($row['FechaVencimiento'])) ;
            $CantidadesG6[] = $row['Pallets'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Top 10 a Vencer
    var colores = ["#c2411d", "#c2411d", "#c2411d", "#7ea64f", "#91ab4f", "#a5b04e", "#b9b54e", "#cdba4d", "#e1bf4d", "#f5b14c"];

    new Chart(document.getElementById("Top10AVencer").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($IDHsG6)); ?>,
            datasets: [
                {
                    label: "Cantidad de Pallets a vencer",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($CantidadesG6)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Proximos IDHs a vencer con menos de 1 año de vida util.'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true // Configuración para comenzar desde 0 en el eje y
                    }
                }]
            },
            
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });
</script>




<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);

    $sql = "SELECT PK.IDH,PR.Descripcion,date(PK.FechaVencimiento) as Fecha, count(*) as Bultos FROM `detalle_piking` PK inner join productos PR on PK.IDH = PR.IDH where PK.Transporte is null  and date(PK.FechaVencimiento) is not null and date(PK.FechaVencimiento) <= DATE_ADD(CURDATE(), INTERVAL 1 YEAR) GROUP by PK.IDH,date(PK.FechaVencimiento) order by date(PK.FechaVencimiento) asc Limit 10;";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $IDHsG6PK = [];
    $CantidadesG6PK = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) { 
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $IDHsG6PK[] = $row['IDH']. '-'.$row['Descripcion']  . ' -- '. date('d/m/Y', strtotime($row['Fecha'])) ;
            $CantidadesG6PK[] = $row['Bultos'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Top 10 a Vencer
    var colores = ["#c2411d", "#c2411d", "#c2411d", "#7ea64f", "#91ab4f", "#a5b04e", "#b9b54e", "#cdba4d", "#e1bf4d", "#f5b14c"];

    new Chart(document.getElementById("Top10AVencerPK").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($IDHsG6PK); ?>,
            datasets: [
                {
                    label: "Cantidad de Bultos a vencer",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode($CantidadesG6PK); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Proximos IDHs a vencer con menos de 1 año de vida util en Piking.'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true // Configuración para comenzar desde 0 en el eje y
                    }
                }]
            },
            
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });
</script>





<script>
    document.getElementById('downloadCSVPK').addEventListener('click', function () {
        // Datos del SELECT simulados (deberías reemplazar esto con datos reales desde el backend)
        const data = [
            ["IDH", "Descripcion", "FechaVencimiento", "Bultos"],
            <?php
            include '../LQS_EUQ/Connect.php';
            $conn = new mysqli($servername, $username, $password, $dbname);
            $sql = "SELECT PK.IDH,PR.Descripcion,date(PK.FechaVencimiento) as Fecha, count(*) as Bultos FROM `detalle_piking` PK
inner join productos PR on PK.IDH = PR.IDH
where PK.Transporte is null  and date(PK.FechaVencimiento) is not null and date(PK.FechaVencimiento) <= DATE_ADD(CURDATE(), INTERVAL 1 YEAR) GROUP by PK.IDH,date(PK.FechaVencimiento) order by date(PK.FechaVencimiento) asc";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '["' . $row['IDH'] . '", "' . $row['Descripcion'] . '", "' . $row['FechaVencimiento'] . '", "' . $row['Bultos'] . '"],';
                }
            }
            ?>
        ];

        // Convertir datos a formato CSV
        let csvContent = "data:text/csv;charset=utf-8," + data.map(e => e.join(",")).join("\n");

        // Crear un enlace de descarga
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "Reporte_Proximos_A_Vencer_PIKING.csv");
        document.body.appendChild(link);

        // Hacer clic en el enlace para iniciar la descarga
        link.click();

        // Eliminar el enlace después de la descarga
        document.body.removeChild(link);
    });
</script>





<script>
    document.getElementById('downloadCSV').addEventListener('click', function () {
        // Datos del SELECT simulados (deberías reemplazar esto con datos reales desde el backend)
        const data = [
            ["IDH", "Descripcion", "FechaVencimiento", "Pallets"],
            <?php
            include '../LQS_EUQ/Connect.php';
            $conn = new mysqli($servername, $username, $password, $dbname);
            $sql = "SELECT Top10aVencer.*, PR.Descripcion FROM `Top10aVencer`
                    INNER JOIN productos PR ON PR.IDH = Top10aVencer.IDH
                    ORDER BY FechaVencimiento ASC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '["' . $row['IDH'] . '", "' . $row['Descripcion'] . '", "' . $row['FechaVencimiento'] . '", "' . $row['Pallets'] . '"],';
                }
            }
            ?>
        ];

        // Convertir datos a formato CSV
        let csvContent = "data:text/csv;charset=utf-8," + data.map(e => e.join(",")).join("\n");

        // Crear un enlace de descarga
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "Reporte_Proximos_A_Vencer.csv");
        document.body.appendChild(link);

        // Hacer clic en el enlace para iniciar la descarga
        link.click();

        // Eliminar el enlace después de la descarga
        document.body.removeChild(link);
    });
</script>


<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT DP.IDH, PR.Descripcion, Count(*) as pallets FROM `detalle_piking` DP
inner join productos PR on PR.IDH =DP.IDH
WHERE Estatus is null GROUP by IDH order by Count(*) desc limit 20";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $LineaG5 = [];
    $TotalG5 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $LineaG5[] = $row['IDH'].' - '.$row['Descripcion'];
            $TotalG5[] = $row['pallets'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Ocupacion por Lineas.
   
    var colores = ["#e5f5e4", "#e9f0e9", "#cafff8", "#afffdb", "#94ffbe", "#79ffa2", "#5eff85", "#44ff69", "#29ff4c", "#0dff30", "#00f813", "#00e41d", "#00cf28", "#00ba32", "#00a53c", "#009047", "#007a52", "#00655c", "#005066", "#003570", "#001e7b", "#00076d", "#041a7e", "#19358f", "#2d4f9f", "#416ab0", "#6595c0", "##7a8187", "#667d91"];


    new Chart(document.getElementById("TopPiking").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($LineaG5)); ?>,
            datasets: [
                {
                    label: "Bultos Produccion",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 1,
                    data: <?php echo json_encode(array_reverse($TotalG5)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Top 20 Ocupacion en Piking'
            }
        }
    });
</script>

<!-- Grafica de Piking-->

<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $fecha_hoy = date('Y-m-d', strtotime($fechaFinal));
    $fecha_hace_9_dias = date("Y-m-d", strtotime($fechaInicial));

    
    $sql = "SELECT DATE(FechaDespacho) AS Fecha, SUM(Cajas)  AS TotalBultos
FROM ( SELECT DISTINCT
D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
D.Operador, 'Turno', 'Tapado/Libre',G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
IFNULL(TIMESTAMPDIFF(MONTH, date(D.FechaRealizado), date(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja,PR.CAJASXPALET as Cajas, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet)  as PesoDeDespacho,
D.FechaRealizado AS FechaDespacho, MONTHNAME(FechaRealizado) AS MES, DATE_FORMAT(FechaRealizado, '%W') AS nombre_dia,
CONCAT(
TIMESTAMPDIFF(DAY, D.FechaRealizado, D.FechaRealizado), ' días, ',
HOUR(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' horas, ',
MINUTE(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' minutos, ',
SECOND(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' segundos'
) AS TiempoDeDespacho  
FROM despachos D
INNER JOIN posiciones P ON P.Ubicacion = D.Posicion
INNER JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
INNER JOIN Guias G ON G.Transporte = D.Guia_Carga 
INNER JOIN productos PR ON PR.IDH = D.IDH
WHERE DATE(D.FechaRealizado) BETWEEN '$FechaHace9Dias' AND '$FechaHoy') AS subquery
GROUP BY Fecha
ORDER BY Fecha DESC";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG7 = [];
    $BultosG7 = [];
    $TotalBultosPik = 0;
    $Registros = 0;
  

    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {

            $labelsG7[] = date('d/m/Y', strtotime($row['Fecha']));
            $BultosG7[] = $row['TotalBultos'];
            $TotalBultosPik += $row['TotalBultos'];
            $Registros += 1;
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("Bultos-Pallets").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG7)); ?>,
            datasets: [
                {
                    label: "Bultos despachados en pallets",
                    backgroundColor: "#f0c46c",
                    borderColor: "#a67108",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($BultosG7)); ?>
                }
                
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Bultos Despachados en pallets'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "12px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color

                            if (dataset.data[i] <= 100) {
                                ctx.fillText(dataset.data[i] + '', model.x, model.y - 5);
                            } else {
                                ctx.fillText(dataset.data[i], model.x, model.y - 5);
                            }


                        }
                    });
                }
            }
        }
    });

// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('totalbultospall');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalBultosPik; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;


var toneladasElement2 = document.getElementById('totalbultospallpromedio');
var valorToneladas1 = <?php echo round($TotalBultosPik / $Registros,2); ?>;
toneladasElement2.textContent = valorToneladas1;

</script>


<!-- Grafica de Piking-->



<!-- Grafica de Bultos pallets-->
<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $fecha_hoy = date('Y-m-d', strtotime($fechaFinal));
    $fecha_hace_9_dias = date("Y-m-d", strtotime($fechaInicial));

    
    $sql = "SELECT DATE(FechaRealizado) AS Fecha, SUM(CajasPK)  AS TotalBultos
    FROM (
      SELECT DP.Estatus, CF.Ubicacion,'N/A' as Nivel,PR.Descripcion,'Picking',DP.IDH,DP.FechaProduccion,DP.FechaVencimiento, DS.Operador, 'N/A' as Turno, 'Tapado/Libre', GS.NombreDestino,GS.Transportista, DP.Transporte, TIME(DS.FechaRealizado),'N/A' as Notas, IFNULL(TIMESTAMPDIFF(MONTH, date(DS.FechaRealizado), date(DP.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 'Tapando/NoTapando' , 'N/A' as ProductoEsta,'1' as CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA, 
sum(DP.UnidadesEnPallet) as CajasPK, (PR.PESOBRUTOCAJA * sum(DP.UnidadesEnPallet))  as PesoDeDespacho, DS.FechaRealizado,  DATE_FORMAT(DS.FechaRealizado, '%M') AS nombre_mes,
        DATE_FORMAT(DS.FechaRealizado, '%W') AS nombre_dia,
        CONCAT(
        TIMESTAMPDIFF(DAY, DS.FechaRealizado, DS.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho
FROM `detalle_piking` DP
INNER join productos PR     on DP.IDH = PR.IDH
INNER join config_piking CF on DP.IDH = CF.IDH
INNER join despachos DS     on DP.Transporte = DS.Guia_Carga and DP.IDH = DS.IDH
INNER join Guias GS			on DP.Transporte = GS.Transporte
where date(DS.Fecha_Hora_Despacho) BETWEEN '$FechaHace9Dias' AND '$FechaHoy' and DS.Operador = 'PIKING' GROUP by DP.Transporte,DP.IDH)
          AS subquery
    GROUP BY Fecha
    ORDER BY Fecha DESC";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG7 = [];
    $BultosG7 = [];
    $TotalBultosPik = 0;
    $Registros = 0;
  

    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {

            $labelsG7[] = date('d/m/Y', strtotime($row['Fecha']));
            $BultosG7[] = $row['TotalBultos'];
            $TotalBultosPik += $row['TotalBultos'];
            $Registros += 1;
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("Bultos-Pikeados").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG7)); ?>,
            datasets: [
                {
                    label: "Bultos Pikeados",
                    backgroundColor: "#5adfe8",
                    borderColor: "#11848c",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($BultosG7)); ?>
                }
                
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Bultos Pikeados Despachados'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "12px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color

                            if (dataset.data[i] <= 100) {
                                ctx.fillText(dataset.data[i] + '', model.x, model.y - 5);
                            } else {
                                ctx.fillText(dataset.data[i], model.x, model.y - 5);
                            }


                        }
                    });
                }
            }
        }
    });

// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('totalbultospik');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalBultosPik; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;


var toneladasElement2 = document.getElementById('totalbultospikpromedio');
var valorToneladas1 = <?php echo round($TotalBultosPik / $Registros,2); ?>;
toneladasElement2.textContent = valorToneladas1;

</script>
<!-- Grafica de Bultos Pallets-->  


<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    
    $fecha_hoy = date('Y-m-d', strtotime($fechaFinal));
    $fecha_hace_9_dias = date("Y-m-d", strtotime($fechaInicial));

    
    $sql = "SELECT DATE(FechaRealizado) AS Fecha, ROUND(SUM(PesoDeDespacho) /1000,2) AS TotalBultos
    FROM (SELECT DP.Estatus, CF.Ubicacion,PR.Descripcion,'Picking',DP.IDH,DP.FechaProduccion,DP.FechaVencimiento, DS.Operador, GS.NombreDestino,GS.Transportista, DP.Transporte, TIME(DS.FechaRealizado), IFNULL(TIMESTAMPDIFF(MONTH, date(DS.FechaRealizado), date(DP.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, PR.LINEA, PR.PESOBRUTOCAJA, 
sum(DP.UnidadesEnPallet) as CajasPK, (PR.PESOBRUTOCAJA * sum(DP.UnidadesEnPallet))  as PesoDeDespacho, DS.FechaRealizado,  DATE_FORMAT(DS.FechaRealizado, '%M') AS nombre_mes,
        DATE_FORMAT(DS.FechaRealizado, '%W') AS nombre_dia,
        CONCAT(
        TIMESTAMPDIFF(DAY, DS.FechaRealizado, DS.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho
FROM `detalle_piking` DP
INNER join productos PR     on DP.IDH = PR.IDH
INNER join config_piking CF on DP.IDH = CF.IDH
INNER join despachos DS     on DP.Transporte = DS.Guia_Carga and DP.IDH = DS.IDH
INNER join Guias GS			on DP.Transporte = GS.Transporte
where date(DS.Fecha_Hora_Despacho) BETWEEN '$FechaHace9Dias' AND '$FechaHoy' and DS.Operador = 'PIKING' GROUP by DP.Transporte,DP.IDH) AS subquery
    GROUP BY Fecha
    ORDER BY Fecha DESC";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG7 = [];
    $BultosG72 = [];
    $Registros = 0;
    $TotalPiking = 0;
  

    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {

            $labelsG7[] = date('d/m/Y', strtotime($row['Fecha']));
            $BultosG72[] = $row['TotalBultos'];
            $TotalPiking += $row['TotalBultos'];
            $Registros += 1;

        }
    }

    array_unshift($labelsG7, "Promedio");

    
    $PikingPromedio = round($TotalPiking / $Registros, 2);
    
    array_unshift($BultosG72, $PikingPromedio);


} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("TONS-Pikeadas").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG7)); ?>,
            datasets: [
                {
                    label: "Toneladas Pickeadas",
                    backgroundColor: "#5adfe8",
                    borderColor: "#11848c",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($BultosG72)); ?>
                }
                
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Toneladas Pikeadas'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "12px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color

                            if (dataset.data[i] <= 100) {
                                ctx.fillText(dataset.data[i] + 'Tn', model.x, model.y - 5);
                            } else {
                                ctx.fillText(dataset.data[i], model.x, model.y - 5);
                            }


                        }
                    });
                }
            }
        }
    });


// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('toneladas');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalPiking; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;

// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('totaltarimaspikingsum');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalPiking; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;


</script>





<!-- Nueva grafica de Toneladas Despachadas -->

<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    
    $fecha_hoy = date('Y-m-d', strtotime($fechaFinal));
    $fecha_hace_9_dias = date("Y-m-d", strtotime($fechaInicial));

    
    $sql = "SELECT DATE(FechaDespacho) AS Fecha, ROUND(SUM(PesoDeDespacho) / 1000,2)  AS TotalBultos
    FROM (SELECT DISTINCT
      D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
      DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
      D.Operador, 'Turno', 'Tapado/Libre',G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
      TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
      IFNULL(TIMESTAMPDIFF(MONTH, date(D.FechaRealizado), date(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
      'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja,PR.CAJASXPALET as Cajas, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet)  as PesoDeDespacho,
      D.FechaRealizado AS FechaDespacho, MONTHNAME(FechaRealizado) AS MES, DATE_FORMAT(FechaRealizado, '%W') AS nombre_dia,
      CONCAT(
          TIMESTAMPDIFF(DAY, D.FechaRealizado, D.FechaRealizado), ' días, ',
          HOUR(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' horas, ',
          MINUTE(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' minutos, ',
          SECOND(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' segundos'
      ) AS TiempoDeDespacho  
  FROM despachos D
  INNER JOIN posiciones P ON P.Ubicacion = D.Posicion
  INNER JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
  INNER JOIN Guias G ON G.Transporte = D.Guia_Carga 
  INNER JOIN productos PR ON PR.IDH = D.IDH
  WHERE DATE(D.FechaRealizado) BETWEEN '$FechaHace9Dias' AND '$FechaHoy') AS subquery
    GROUP BY Fecha
    ORDER BY Fecha DESC";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $labelsG7 = [];
    $BultosG7 = [];
    $Registros = 0;
    $TotalPalletsDes = 0;
  

    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {

            $labelsG7[] = date('d/m/Y', strtotime($row['Fecha']));
            $BultosG7[] = $row['TotalBultos'];
            $TotalPalletsDes += $row['TotalBultos'];
            $Registros += 1;

        }
    }

    array_unshift($labelsG7, "Promedio");

    
    $PikingPromedio = round($TotalPalletsDes / $Registros, 2);
    
    array_unshift($BultosG7, $PikingPromedio);


} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("TONS-Despachadas").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG7)); ?>,
            datasets: [
                {
                    label: "Toneladas Despachadas",
                    backgroundColor: "#E7A447",
                    borderColor: "#AE8F6C",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($BultosG7)); ?>
                }
                
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Toneladas Despachadas Pallets Completos'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color

                            if (dataset.data[i] <= 100) {
                                ctx.fillText(dataset.data[i] + 'Tn', model.x, model.y - 5);
                            } else {
                                ctx.fillText(dataset.data[i], model.x, model.y - 5);
                            }


                        }
                    });
                }
            }
        }
    });


// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('toneladasDespachadas');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalPalletsDes; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;


// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('totaltarimasdespachadassum');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalPalletsDes; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;


// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('totalDespachosSUM');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo $TotalPalletsDes+$TotalPiking; ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;



// Obtener el elemento donde queremos actualizar el valor
var toneladasElement = document.getElementById('porcentajepiking');
// Simular un valor específico (por ejemplo, 100 Toneladas)
var valorToneladas = <?php echo round($TotalPiking / ($TotalPalletsDes + $TotalPiking ) * 100,3); ?>;
// Actualizar el contenido del elemento
toneladasElement.textContent = valorToneladas;



</script>
<!-- Fin Nueva grafica de Toneladas Despachadas -->


<!-- Nueva Toneladas pallets vs Toneladas Piking -->
<script>
    // Capacidad de bodegas TOTAL por Dia.
    new Chart(document.getElementById("TONS-DespachadasvsPikeadas").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($labelsG7)); ?>,
            datasets: [
                {
                    label: "Toneladas Despachadas",
                    backgroundColor: "#E7A447",
                    borderColor: "#AE8F6C",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($BultosG7)); ?>
                },
                {
                    label: "Toneladas Pikeadas",
                    backgroundColor: "#5adfe8",
                    borderColor: "#11848c",
                    borderWidth: 3,
                    data: <?php echo json_encode(array_reverse($BultosG72)); ?>
                }
                
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Toneladas Despachadas Pallets Completos'
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color

                            if (dataset.data[i] <= 100) {
                                ctx.fillText(dataset.data[i] + 'Tn', model.x, model.y - 5);
                            } else {
                                ctx.fillText(dataset.data[i], model.x, model.y - 5);
                            }


                        }
                    });
                }
            }
        }
    });

</script> 
<!-- Fin Nueva Toneladas pallets vs Toneladas Piking -->


<script>
    // % de Piking.
   
    var colores = ["#E7A447", "#5adfe8", "#5e8f42", "#70a14d", "#81b357", "#93c562"];

    new Chart(document.getElementById("Porcentaje-Piking").getContext('2d'), {
        type: 'pie',
        data: {
            labels: ["Tn Pallets", "Tn Piking"],
            datasets: [
                {
                    label: "Bultos Produccion",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: [<?php echo $TotalPalletsDes+$TotalPiking?>,<?php echo $TotalPiking?>]
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Porcentaje de Piking'
            }
        }
    });
</script>

<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
$Rojas = 0;
$Verdes = 0;
$PorcentajeConteoCiego = 0;
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT distinct(ConteoCiegoPost) as Color,count(*) as Guias FROM `Guias` where ConteoCiegoPost <> '' group by ConteoCiegoPost";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $LineaG8 = [];
    $TotalG8 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $LineaG8[] = $row['Color'];
            $TotalG8[] = $row['Guias'];
           
        }
    }
    $Rojas = $TotalG8[0];
    $Verdes = $TotalG8[1];

    $PorcentajeConteoCiego = round($Rojas / $Verdes *100,2);



    

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // % Conteo Ciego.
   
    var colores = ["#6ec232", "#db4d4f", "#5e8f42", "#70a14d", "#81b357", "#93c562"];

    new Chart(document.getElementById("Porcentaje-Conteociego").getContext('2d'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_reverse($LineaG8)); ?>,
            datasets: [
                {
                    label: "Bultos Produccion",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($TotalG8)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Conteo Ciego.'
            }
        }
    });


var toneladasElement = document.getElementById('TotalGuiasCC');
var valorToneladas = <?php echo $Verdes; ?>;
toneladasElement.textContent = valorToneladas;



var toneladasElement = document.getElementById('TotalGuiasRojasCC');
var valorToneladas = <?php echo $Rojas; ?>;
toneladasElement.textContent = valorToneladas;

var toneladasElement = document.getElementById('PromedioCC');
var valorToneladas = <?php echo $PorcentajeConteoCiego; ?>;
toneladasElement.textContent = valorToneladas;

</script>




















<?php
// Establecer la conexión a la base de datos (reemplaza con tus propios datos)
$Rojas = 0;
$Verdes = 0;
$PorcentajeConteoCiego = 0;
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT 'Agregadas' AS Color, IFNULL(A.Guias, 0) AS Guias
FROM (
    SELECT COUNT(*) AS Guias
    FROM `Bitar_ConteoCiego`
    WHERE DATE(Fecha) BETWEEN '$FechaHace9Dias' and '$FechaActual' AND Accion = 'Agregar'
) A
UNION ALL
SELECT 'Eliminadas' AS Color, IFNULL(E.Guias, 0) AS Guias
FROM (
    SELECT COUNT(*) AS Guias
    FROM `Bitar_ConteoCiego`
    WHERE DATE(Fecha) BETWEEN '$FechaHace9Dias' and '$FechaActual' AND Accion = 'Eliminar'
) E";
    
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $LineaG8 = [];
    $TotalG8 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $LineaG8[] = $row['Color'];
            $TotalG8[] = $row['Guias'];
           
        }
    }
    $Rojas = $TotalG8[0];
    $Verdes = $TotalG8[1];

    $PorcentajeConteoCiego = 100 - (($Rojas + $Verdes) / $CapacidadTotal) ;
    



    

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<script>
    // % Conteo Ciego.
   
    var colores = ["#88F1F5", "#EFC85D", "#5e8f42", "#70a14d", "#81b357", "#93c562"];

    new Chart(document.getElementById("Porcentaje-Exactitud").getContext('2d'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_reverse($LineaG8)); ?>,
            datasets: [
                {
                    label: "Bultos Produccion",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($TotalG8)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Movimientos.'
            }
        }
    });


var toneladasElement = document.getElementById('TotalPalletsAgregadas');
var valorToneladas = <?php echo $Verdes; ?>;
toneladasElement.textContent = valorToneladas;



var toneladasElement = document.getElementById('TotalPalletsEliminadas');
var valorToneladas = <?php echo $Rojas; ?>;
toneladasElement.textContent = valorToneladas;

var toneladasElement = document.getElementById('PromedioFIFO');
var valorToneladas = <?php echo $PorcentajeConteoCiego; ?>;
toneladasElement.textContent = valorToneladas;

</script>


<?php
// Ingresos por operador en la semana
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    include '../LQS_EUQ/Connect.php';
   
    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

   
  
    $sql = "SELECT  Operador, count(*) as Pallets FROM `asignaciones` where date(FechaColocado) BETWEEN '$FechaHace9Dias' and '$FechaActual' and Operador is not null GROUP by Operador order by count(*) Desc";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $IDHsG9 = [];
    $CantidadesG9 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) { 
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $IDHsG9[] = $row['Operador'] ;
            $CantidadesG9[] = $row['Pallets'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Top 10 a Vencer
    var colores = ["#429750", "#569c50", "#6aa14f", "#7ea64f", "#91ab4f", "#a5b04e", "#b9b54e", "#cdba4d", "#e1bf4d", "#f5b14c"];

    new Chart(document.getElementById("Ingresos-Operador").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($IDHsG9)); ?>,
            datasets: [
                {
                    label: "Pallets Que ingresaron a bodega",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($CantidadesG9)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Pallets movidos a bodegas por operador'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true // Configuración para comenzar desde 0 en el eje y
                    }
                }]
            },
            
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });
</script>

<?php
// Despachos por operador en la semana
try {

    // Metodo tradicional
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    include '../LQS_EUQ/Connect.php';
    
    
    $FechaActual = date('Y-m-d', strtotime($fechaFinal));
    $FechaHace9Dias = date("Y-m-d", strtotime($fechaInicial));

    
    $sql = "SELECT  Operador, count(*) as Pallets FROM `despachos` where date(Fecha_hora_despacho) BETWEEN '$FechaHace9Dias' and '$FechaActual'  and Operador is not null and Operador <> 'PIKING' GROUP by Operador order by count(*) asc";
    $result = $conn->query($sql);

    // Inicializar arrays para las etiquetas y los conjuntos de datos
    $IDHsG9 = [];
    $CantidadesG9 = [];
   
   
    // Procesar los resultados
    if ($result->num_rows > 0) { 
        // Almacena los nombres de las bodegas en un array

        while ($row = $result->fetch_assoc()) {
            $IDHsG9[] = $row['Operador'] ;
            $CantidadesG9[] = $row['Pallets'];
           
        }
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>


<script>
    // Top 10 a Vencer
    var colores = ["#a5b04e", "#b9b54e", "#cdba4d", "#e1bf4d", "#f5b14c"];

    new Chart(document.getElementById("Despachos-Operador").getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($IDHsG9)); ?>,
            datasets: [
                {
                    label: "Pallets Que se despacharon",
                    backgroundColor: colores,
                    borderColor: "#ffffff",
                    borderWidth: 4,
                    data: <?php echo json_encode(array_reverse($CantidadesG9)); ?>
                }
            ]
        },
        options: {
            legend: { display: true },
            title: {
                display: true,
                text: 'Pallets movidos para despacho'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true // Configuración para comenzar desde 0 en el eje y
                    }
                }]
            },
            
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function (value, context) {
                        return value;
                    },
                    font: {
                        size: 20 // Tama;o del texto
                    }
                }
            },
            animation: {
                onComplete: function () {
                    var ctx = this.chart.ctx;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "TOP";
                    ctx.font = "10px Arial";

                    this.data.datasets.forEach(function (dataset) {
                        for (var i = 0; i < dataset.data.length; i++) {
                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                            ctx.fillStyle = '#7c8798'; // Color
                                ctx.fillText(dataset.data[i], model.x, model.y - 7);
                          
                            }
                    });
                }
            }
        }
    });
</script>







<!-- FIN GRAFICAS -->

       




        


       

        <!-- Animacion de Numeros Capacidad Total -->
        <script>
            // Obtener el elemento del número a animar
            var capacidadTotalElement = document.getElementById('capacidad-total');

            // Obtener el número objetivo
            var capacidadTotal = parseInt(capacidadTotalElement.innerText);

            // Configurar el intervalo de tiempo para la animación (en milisegundos)
            var intervalo = 10;

            // Calcular la cantidad de incremento en cada intervalo
            var incremento = capacidadTotal / (1500 / intervalo);

            // Variable para llevar el seguimiento del número actual
            var numeroActual = 0;

            // Función para animar el número
            function animarNumero() {
                // Verificar si el número actual es menor o igual al número objetivo
                if (numeroActual <= capacidadTotal) {
                    // Actualizar el contenido del elemento con el número actual
                    capacidadTotalElement.innerText = Math.floor(numeroActual);

                    // Incrementar el número actual
                    numeroActual += incremento;

                    // Esperar el intervalo de tiempo antes de la siguiente actualización
                    setTimeout(animarNumero, intervalo);
                } else {
                    // Establecer el número objetivo como el contenido final del elemento
                    capacidadTotalElement.innerText = capacidadTotal;
                }
            }

            // Iniciar la animación cuando se cargue la página
            window.addEventListener('load', animarNumero);
        </script>


        <!-- Animacion de Numeros Ubicaciones Libres-->
        <script>
            // Obtener el elemento del número a animar
            var UbicacionesLibres = document.getElementById('Ubicaciones-Lbres');

            // Obtener el número objetivo
            var capacidadLibre = parseInt(UbicacionesLibres.innerText);

            // Configurar el intervalo de tiempo para la animación (en milisegundos)
            var intervalo = 10;

            // Calcular la cantidad de incremento en cada intervalo
            var incremento2 = capacidadLibre / (1600 / intervalo);

            // Variable para llevar el seguimiento del número actual
            var numeroActual2 = 0;

            // Función para animar el número
            function animarNumero2() {
                // Verificar si el número actual es menor o igual al número objetivo
                if (numeroActual2 <= capacidadLibre) {
                    // Actualizar el contenido del elemento con el número actual
                    UbicacionesLibres.innerText = Math.floor(numeroActual2);

                    // Incrementar el número actual
                    numeroActual2 += incremento2;

                    // Esperar el intervalo de tiempo antes de la siguiente actualización
                    setTimeout(animarNumero2, intervalo);
                } else {
                    // Establecer el número objetivo como el contenido final del elemento
                    UbicacionesLibres.innerText = capacidadLibre;
                }
            }

            // Iniciar la animación cuando se cargue la página
            window.addEventListener('load', animarNumero2);
        </script>



        <!-- Animacion de Numeros Porcentaje-->
        <script>
            // Obtener el elemento del número a animar
            var UbicacionesExactitud = document.getElementById('Porcentaje-Exactitud');

            // Obtener el número objetivo
            var porcentajeExactitud = parseInt(UbicacionesExactitud.innerText);

            // Configurar el intervalo de tiempo para la animación (en milisegundos)
            var intervalo = 10;

            // Calcular la cantidad de incremento en cada intervalo
            var incremento3 = porcentajeExactitud / (1700 / intervalo);

            // Variable para llevar el seguimiento del número actual
            var numeroActual3 = 0;

            // Función para animar el número
            function animarNumero3() {
                // Verificar si el número actual es menor o igual al número objetivo
                if (numeroActual3 <= porcentajeExactitud) {
                    // Actualizar el contenido del elemento con el número actual
                    UbicacionesExactitud.innerText = Math.floor(numeroActual3);

                    // Incrementar el número actual
                    numeroActual3 += incremento3;

                    // Esperar el intervalo de tiempo antes de la siguiente actualización
                    setTimeout(animarNumero3, intervalo);
                } else {
                    // Establecer el número objetivo como el contenido final del elemento
                    UbicacionesExactitud.innerText = porcentajeExactitud;
                }
            }

            // Iniciar la animación cuando se cargue la página
            window.addEventListener('load', animarNumero3);
        </script>

        <!-- Animacion de Unidades Ocupadas-->
        <script>
            // Obtener el elemento del número a animar
            var UbicacionesOcupadas = document.getElementById('Unidades-Ocupadas');

            // Obtener el número objetivo
            var porcentajeOcupadas = parseInt(UbicacionesOcupadas.innerText);

            // Configurar el intervalo de tiempo para la animación (en milisegundos)
            var intervalo = 10;

            // Calcular la cantidad de incremento en cada intervalo
            var incremento4 = porcentajeOcupadas / (1800 / intervalo);

            // Variable para llevar el seguimiento del número actual
            var numeroActual4 = 0;

            // Función para animar el número
            function animarNumero4() {
                // Verificar si el número actual es menor o igual al número objetivo
                if (numeroActual4 <= porcentajeOcupadas) {
                    // Actualizar el contenido del elemento con el número actual
                    UbicacionesOcupadas.innerText = Math.floor(numeroActual4);

                    // Incrementar el número actual
                    numeroActual4 += incremento4;

                    // Esperar el intervalo de tiempo antes de la siguiente actualización
                    setTimeout(animarNumero4, intervalo);
                } else {
                    // Establecer el número objetivo como el contenido final del elemento
                    UbicacionesOcupadas.innerText = porcentajeOcupadas;
                }
            }

            // Iniciar la animación cuando se cargue la página
            window.addEventListener('load', animarNumero4);
        </script>


        <!--Custom JavaScript -->

        <script type="text/javascript">
            $('.toast').toast('show');
        </script>
    <script>
        // Establece el tiempo de inactividad en milisegundos (5 minutos = 300,000 milisegundos)
        const tiempoInactividad = 1800000;

        // Función que redirige al usuario a la página específica
        function redirigir() {
            window.location.href = '../innet/logout.php'; // Reemplaza 'pagina-destino.html' con la URL de la página a la que deseas redirigir al usuario.
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
