
<?php
ob_start();
session_start();
require_once 'ValidarSesion.php';

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
}


date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

include '../Innet_ADM/Innet_AMD.php';
include '../LQS_EUQ/Auth.php';

/*
 * Datos reales del dashboard. Las consultas se mantienen en este archivo para
 * que las graficas reflejen exactamente las mismas tablas que opera MontaCargas.
 */
function dashboardRows($pdo, $sql, $params = array())
{
    try {
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        error_log('MontaCargas dashboard: ' . $exception->getMessage());
        return array();
    }
}

$resumenPosiciones = dashboardRows($pdo, "
    SELECT COUNT(*) AS total,
           SUM(LOWER(Estado) = 'libre') AS libres,
           SUM(LOWER(Estado) IN ('ocupada', 'ocupada-pk', 'ocp-pk')) AS ocupadas
    FROM dbs9098416.posiciones
");
$resumenPosiciones = isset($resumenPosiciones[0]) ? $resumenPosiciones[0] : array();
$CapacidadTotal = isset($resumenPosiciones['total']) ? (int) $resumenPosiciones['total'] : 0;
$UbicacionesLibres = isset($resumenPosiciones['libres']) ? (int) $resumenPosiciones['libres'] : 0;
$UnidadesOcupadas = isset($resumenPosiciones['ocupadas']) ? (int) $resumenPosiciones['ocupadas'] : 0;
$Ocupacion = $CapacidadTotal > 0 ? round(($UnidadesOcupadas / $CapacidadTotal) * 100, 1) : 0;

$capacidadBodegas = dashboardRows($pdo, "
    SELECT Bodega,
           SUM(LOWER(Estado) IN ('ocupada', 'ocupada-pk', 'ocp-pk')) AS ocupadas,
           SUM(LOWER(Estado) = 'libre') AS libres,
           SUM(LOWER(Estado) NOT IN ('ocupada', 'ocupada-pk', 'ocp-pk', 'libre')) AS otras
    FROM dbs9098416.posiciones
    GROUP BY Bodega
    ORDER BY CAST(Bodega AS UNSIGNED), Bodega
");

$estadosPosiciones = dashboardRows($pdo, "
    SELECT COALESCE(NULLIF(Estado, ''), 'Sin estado') AS estado, COUNT(*) AS total
    FROM dbs9098416.posiciones
    GROUP BY COALESCE(NULLIF(Estado, ''), 'Sin estado')
    ORDER BY total DESC
");

$productosOcupados = dashboardRows($pdo, "
    SELECT CAST(IDH AS CHAR) AS idh, COUNT(*) AS total
    FROM dbs9098416.posiciones
    WHERE IDH NOT IN (0) AND LOWER(Estado) IN ('ocupada', 'ocupada-pk', 'ocp-pk')
    GROUP BY IDH
    ORDER BY total DESC
    LIMIT 10
");

$actividadSemanal = dashboardRows($pdo, "
    SELECT DATE_FORMAT(d.fecha, '%d/%m') AS etiqueta,
           COALESCE(i.total, 0) AS ingresos,
           COALESCE(ds.total, 0) AS despachos,
           COALESCE(r.total, 0) AS reubicaciones,
           COALESCE(p.total, 0) AS picking
    FROM (
        SELECT CURDATE() - INTERVAL n DAY AS fecha
        FROM (
            SELECT 6 AS n UNION ALL SELECT 5 UNION ALL SELECT 4 UNION ALL
            SELECT 3 UNION ALL SELECT 2 UNION ALL SELECT 1 UNION ALL SELECT 0
        ) dias
    ) d
    LEFT JOIN (
        SELECT DATE(FechaColocado) AS fecha, COUNT(*) AS total
        FROM dbs9098416.asignaciones
        WHERE FechaColocado >= CURDATE() - INTERVAL 6 DAY AND Estado = 'Ingresado'
        GROUP BY DATE(FechaColocado)
    ) i ON i.fecha = d.fecha
    LEFT JOIN (
        SELECT DATE(FechaRealizado) AS fecha, COUNT(*) AS total
        FROM dbs9098416.despachos
        WHERE FechaRealizado >= CURDATE() - INTERVAL 6 DAY AND Estado = 'Despachado'
        GROUP BY DATE(FechaRealizado)
    ) ds ON ds.fecha = d.fecha
    LEFT JOIN (
        SELECT DATE(Fecha_Movimiento) AS fecha, COUNT(*) AS total
        FROM dbs9098416.Reubicaciones
        WHERE Fecha_Movimiento >= CURDATE() - INTERVAL 6 DAY AND Estado = 'Reubicada'
        GROUP BY DATE(Fecha_Movimiento)
    ) r ON r.fecha = d.fecha
    LEFT JOIN (
        SELECT DATE(Fecha_Movimiento) AS fecha, COUNT(*) AS total
        FROM dbs9098416.piking
        WHERE Fecha_Movimiento >= CURDATE() - INTERVAL 6 DAY AND Estado = 'Reubicada'
        GROUP BY DATE(Fecha_Movimiento)
    ) p ON p.fecha = d.fecha
    ORDER BY d.fecha
");

$usuarioDashboard = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : '';
$tareasUsuario = dashboardRows($pdo, "
    SELECT tipo,
           SUM(estado = 'Pendiente') AS pendientes,
           SUM(estado <> 'Pendiente') AS completadas
    FROM (
        SELECT 'Ingresos' AS tipo, Estado AS estado FROM dbs9098416.asignaciones WHERE Operador = :usuario1
        UNION ALL
        SELECT 'Despachos', Estado FROM dbs9098416.despachos WHERE Operador = :usuario2
        UNION ALL
        SELECT 'Reubicaciones', Estado FROM dbs9098416.Reubicaciones WHERE Montacarguista = :usuario3
        UNION ALL
        SELECT 'Picking', Estado FROM dbs9098416.piking WHERE Montacarguista = :usuario4
    ) movimientos
    GROUP BY tipo
    ORDER BY FIELD(tipo, 'Ingresos', 'Despachos', 'Reubicaciones', 'Picking')
", array(
    ':usuario1' => $usuarioDashboard,
    ':usuario2' => $usuarioDashboard,
    ':usuario3' => $usuarioDashboard,
    ':usuario4' => $usuarioDashboard
));

// Contadores requeridos por Menu.php. Se inicializan siempre para evitar
// warnings cuando el usuario no tiene tareas pendientes.
$Num_Despachos = '';
$Num_Asignaciones = '';
$Num_Reubicaciones = '';
$Num_Piking = '';
$variableMenuPorTipo = array(
    'Ingresos' => 'Num_Asignaciones',
    'Despachos' => 'Num_Despachos',
    'Reubicaciones' => 'Num_Reubicaciones',
    'Picking' => 'Num_Piking'
);

foreach ($tareasUsuario as $tareaUsuario) {
    $tipoTarea = isset($tareaUsuario['tipo']) ? $tareaUsuario['tipo'] : '';
    $pendientesTarea = isset($tareaUsuario['pendientes']) ? (int) $tareaUsuario['pendientes'] : 0;

    if ($pendientesTarea > 0 && isset($variableMenuPorTipo[$tipoTarea])) {
        $nombreVariableMenu = $variableMenuPorTipo[$tipoTarea];
        $$nombreVariableMenu = '<span class="badge badge-primary notify-no"> ' . $pendientesTarea . ' </span>';
    }
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
    <title>Henkel CBP / AdminFIFO</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
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
    </style>
    <style>
        .zmdi-upload{
            padding: 0px 15px 0px 0px;
        }
        .zmdi-upload:hover{
            color: black;
            transition: color 0.2s linear 0.2s;
        }

        .file-input__input {

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

    </style>


</head>

<body>
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ==============================================================
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
</div> -->
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
                            <img src="../assets/images/users/<?php echo $_SESSION['pic']?> " alt="user" class="rounded-circle"
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
                    <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Bienvenido <?php echo $_SESSION['USR'];?>!!</h3>
                    <div class="d-flex align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0">
                                <li class="breadcrumb-item"> <?php echo '<div class="user_admin dropdown"><span class="user_adminname">Fecha: ' . $fecha . '</span></div>';?>
                                </li>
                            </ol>
                        </nav>
                    </div>
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

                <?php $Liberados = 10;//LiberarCuarentena();
                $LiberadosHoy = 10;//LiberarCuarentenaHoy();

                if($Liberados > 0){
                    echo "Se Liberaron correctamente ".$Liberados." Unidades de Cuarentena";

                }else{
                    echo "Existen ".$LiberadosHoy." Unidades Liberadas de Cuarentena  el dia de Hoy ". $fecha;
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




            <div class="container-fluid ">
                <div class="row">
                    <div class="col-12">
                        <div class="card">






                            <div class="card-body">
                                <h4 class="card-title">DashBoard</h4>
                                <h6 class="card-subtitle">Consulta rapida del estatus de las bodegas</h6>
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
                                            <div class="p-2 bg-cyan text-center">
                                                <h1 id="Ubicaciones-Lbres" class="font-light text-white"><?php echo $UbicacionesLibres; ?></h1>
                                                <h6 class="text-white">Ubicaciones Libres</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Column -->
                                    <div class="col-md-6 col-lg-3 col-xlg-3 animate__animated animate__backInUp" style="animation-duration: 1.75s;">
                                        <div class="card card-hover">
                                            <div class="p-2 bg-success text-center">
                                                <h1 id="Porcentaje-Exactitud" class="font-light text-white"><?php echo $Ocupacion; ?>%</h1>
                                                <h6 class="text-white">% de Ocupación</h6>
                                            </div>
                                        </div>
                                    </div>
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
                                </div>
                                <!-- *************************************************************** -->
                                <!-- End First Cards -->


                                <!-- ***********************Fin primer Grafico************************************ -->
                                <div class="row">

                                    <div class="col-lg-6 col-md-12 animate__animated  animate__backInLeft">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Ocupación por bodega</h4>
                                                <canvas id="bar-chart"  height="150"></canvas>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 animate__animated  animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Actividad de ingresos y despachos (7 días)</h4>
                                                <canvas id="Toneladas-Despachadas" height="150"> </canvas>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class="row">
                                    <div class="col-lg-6 col-md-12 animate__animated  animate__backInLeft ">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Reubicaciones y picking (7 días)</h4>
                                                <canvas id="Tarimas-Movimientos" height="149"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Estado actual de las ubicaciones</h4>
                                                <canvas id="Conteo-Ciego" height="150"> </canvas>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Mis tareas por operación</h4>
                                                <canvas id="Picking" height="150"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Top 10 productos almacenados (IDH)</h4>
                                                <canvas id="CapacidadCarga" height="150"> </canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
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

        <!-- Datos de las bodegas -->

        <script>
            (function () {
                var capacidadBodegas = <?php echo json_encode($capacidadBodegas, JSON_UNESCAPED_UNICODE); ?>;
                var actividadSemanal = <?php echo json_encode($actividadSemanal, JSON_UNESCAPED_UNICODE); ?>;
                var estadosPosiciones = <?php echo json_encode($estadosPosiciones, JSON_UNESCAPED_UNICODE); ?>;
                var tareasUsuario = <?php echo json_encode($tareasUsuario, JSON_UNESCAPED_UNICODE); ?>;
                var productosOcupados = <?php echo json_encode($productosOcupados, JSON_UNESCAPED_UNICODE); ?>;
                var rojo = '#ed3131';
                var gris = '#767676';
                var cyan = '#27a9e3';
                var verde = '#28b779';
                var paleta = [rojo, cyan, verde, '#ffb848', '#7460ee', '#5f76e8', '#ff8c42', '#8d6e63'];

                function valores(filas, campo) {
                    return filas.map(function (fila) { return Number(fila[campo]) || 0; });
                }

                function etiquetas(filas, campo) {
                    return filas.map(function (fila) { return String(fila[campo]); });
                }

                function ejeEnteros() {
                    return {
                        ticks: { beginAtZero: true, precision: 0 },
                        gridLines: { color: 'rgba(0,0,0,.05)' }
                    };
                }

                new Chart(document.getElementById('bar-chart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: capacidadBodegas.map(function (fila) { return 'B' + fila.Bodega; }),
                        datasets: [
                            { label: 'Ocupadas', data: valores(capacidadBodegas, 'ocupadas'), backgroundColor: rojo },
                            { label: 'Libres', data: valores(capacidadBodegas, 'libres'), backgroundColor: verde },
                            { label: 'Otros estados', data: valores(capacidadBodegas, 'otras'), backgroundColor: gris }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: { xAxes: [{ stacked: true }], yAxes: [Object.assign(ejeEnteros(), { stacked: true })] },
                        tooltips: { mode: 'index', intersect: false }
                    }
                });

                new Chart(document.getElementById('Toneladas-Despachadas').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: etiquetas(actividadSemanal, 'etiqueta'),
                        datasets: [
                            { label: 'Ingresos', data: valores(actividadSemanal, 'ingresos'), borderColor: gris, backgroundColor: 'rgba(118,118,118,.12)', fill: false, lineTension: 0 },
                            { label: 'Despachos', data: valores(actividadSemanal, 'despachos'), borderColor: rojo, backgroundColor: 'rgba(237,49,49,.12)', fill: false, lineTension: 0 }
                        ]
                    },
                    options: { responsive: true, scales: { yAxes: [ejeEnteros()] }, tooltips: { mode: 'index', intersect: false } }
                });

                new Chart(document.getElementById('Tarimas-Movimientos').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: etiquetas(actividadSemanal, 'etiqueta'),
                        datasets: [
                            { label: 'Reubicaciones', data: valores(actividadSemanal, 'reubicaciones'), backgroundColor: cyan },
                            { label: 'Picking', data: valores(actividadSemanal, 'picking'), backgroundColor: rojo }
                        ]
                    },
                    options: { responsive: true, scales: { yAxes: [ejeEnteros()] }, tooltips: { mode: 'index', intersect: false } }
                });

                new Chart(document.getElementById('Conteo-Ciego').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: etiquetas(estadosPosiciones, 'estado'),
                        datasets: [{ data: valores(estadosPosiciones, 'total'), backgroundColor: estadosPosiciones.map(function (_, i) { return paleta[i % paleta.length]; }) }]
                    },
                    options: { responsive: true, cutoutPercentage: 58, legend: { position: 'right' } }
                });

                new Chart(document.getElementById('Picking').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: etiquetas(tareasUsuario, 'tipo'),
                        datasets: [
                            { label: 'Pendientes', data: valores(tareasUsuario, 'pendientes'), backgroundColor: '#ffb848' },
                            { label: 'Completadas', data: valores(tareasUsuario, 'completadas'), backgroundColor: verde }
                        ]
                    },
                    options: { responsive: true, scales: { xAxes: [{ stacked: true }], yAxes: [Object.assign(ejeEnteros(), { stacked: true })] } }
                });

                new Chart(document.getElementById('CapacidadCarga').getContext('2d'), {
                    type: 'horizontalBar',
                    data: {
                        labels: productosOcupados.map(function (fila) { return 'IDH ' + fila.idh; }),
                        datasets: [{ label: 'Ubicaciones ocupadas', data: valores(productosOcupados, 'total'), backgroundColor: rojo }]
                    },
                    options: { responsive: true, legend: { display: false }, scales: { xAxes: [ejeEnteros()] } }
                });
            }());
        </script>

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
                    UbicacionesExactitud.innerText = Math.floor(numeroActual3) + '%';

                    // Incrementar el número actual
                    numeroActual3 += incremento3;

                    // Esperar el intervalo de tiempo antes de la siguiente actualización
                    setTimeout(animarNumero3, intervalo);
                } else {
                    // Establecer el número objetivo como el contenido final del elemento
                    UbicacionesExactitud.innerText = porcentajeExactitud + '%';
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
        const tiempoInactividad = 300000;

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
