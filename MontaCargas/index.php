
<?php
ob_start();
session_start();

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
}


date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

include '../Innet_ADM/Innet_AMD.php';

// Variables de resumen Grafica 1

$CapacidadTotal = 300; //CapacidadTotalFIFO();
$UbicacionesLibres = 20; //UnidadesLibresFIFO();
$Exactitud = "99%";
$UnidadesOcupadas = 30; //UnidadesOcupadasFIFO();

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
                                                <h1 id="Porcentaje-Exactitud" class="font-light text-white"><?php echo $Exactitud; ?></h1>
                                                <h6 class="text-white">% de Exactitud</h6>
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
                                                <h4 class="card-title">% Capacidad por bodegas</h4>
                                                <canvas id="bar-chart"  height="150"></canvas>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 animate__animated  animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title"> Toneladas Despachadas vs Toneladas Ingresadas</h4>
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
                                                <h4 class="card-title">Tarimas Ingresadas vs Tarimas Despachadas</h4>
                                                <canvas id="Tarimas-Movimientos" height="149"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 animate__animated animate__backInRight">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">% Conteo Ciego</h4>
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
                                                <h4 class="card-title">% de Picking</h4>
                                                <canvas id="Picking" height="150"> </canvas>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Capacidad de Carga vs Toneladas Despachadas</h4>
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
            // Capaciodad de bodegas
            new Chart(document.getElementById("bar-chart").getContext('2d'), {

                type: 'bar',
                data: {
                    labels: ["B1", "B2", "B3", "b4", "B5","B6","B7","B8","B9","B10","B11","B12"],
                    datasets: [
                        {
                            label: "Porcentaje de ocupacion",
                            backgroundColor: ["#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037"],
                            data: [72,69,56,74,0,56,97,21,7,100,65,3]
                        }
                    ]
                },
                options: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: '% Capacidad por bodegas'
                    }
                }
            });
        </script>

        <script>
            // Toneladas despachadas
            new Chart(document.getElementById("Toneladas-Despachadas").getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes"],
                    datasets: [
                        {
                            label: "Despachadas",
                            backgroundColor: ["#ff0022", "#ff0022", "#ff0022", "#ff0022", "#ff0022"],
                            data: [50,300,60,100,85]
                        },
                        {
                            label: "Ingresadas",
                            backgroundColor: ["#716b6b", "#716b6b", "#716b6b", "#716b6b", "#716b6b"],
                            data: [100,60,120,45,89]
                        }

                    ]
                },
                options: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Toneladas despachadas vs toneladas ingresadas'
                    }
                }
            });
        </script>

        <script>
            // Movimientos de tarimas
            new Chart(document.getElementById("Tarimas-Movimientos").getContext('2d'), {
                type: 'line',
                data: {
                    labels: ["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"],
                    datasets: [{
                        data: [90,85,100,85,120,90],
                        label: "Tarimas Ingresadas",
                        borderColor: "#7d7979",
                        fill: false
                    }, {
                        data: [50,300,75,60,200,90],
                        label: "Tarimas Despachadas",
                        borderColor: "#fa0000",
                        fill: false
                    }
                    ]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Tarimas Ingresadas vs Tarimas Despachadas'
                    }
                }
            });
        </script>

        <script>
            // Conteo Ciego
            new Chart(document.getElementById("Conteo-Ciego").getContext('2d'), {

                type: 'bar',
                data: {
                    labels: ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes","Sabado"],
                    datasets: [
                        {
                            label: "Guias Despachadas",
                            backgroundColor: ["#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037"],
                            data: [10,13,20,12,10,12]
                        },
                        {
                            label: "Guias con conteo Ciego",
                            backgroundColor: ["#716b6b", "#716b6b", "#716b6b", "#716b6b", "#716b6b", "#716b6b"],
                            data: [2,3,4,3,2,4]
                        }
                    ]
                },
                options: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Conteo Ciego'
                    }

                }
            });
        </script>

        <script>
            // Porcentaje de Picking
            new Chart(document.getElementById("Picking").getContext('2d'), {
                type: 'line',
                data: {
                    labels: ["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"],
                    datasets: [{
                        data: [22,23,20,12,21,15],
                        label: "% Picking",
                        borderColor: "#ff0000",
                        fill: false
                    }
                    ]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Porcemtaje de Picking'
                    }
                }
            });
        </script>


        <script>
            // Capacidad de carga
            new Chart(document.getElementById("CapacidadCarga").getContext('2d'), {

                type: 'bar',
                data: {
                    labels: ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes","Sabado"],
                    datasets: [
                        {
                            label: "Toneladas Despachadas",
                            backgroundColor: ["#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037", "#fa0037"],
                            data: [44,235,65,218,263,60]
                        },
                        {
                            label: "Capacidad de carga",
                            backgroundColor: ["#716b6b", "#716b6b", "#716b6b", "#716b6b", "#716b6b", "#716b6b"],
                            data: [100,100,100,100,100,50]
                        }
                    ]
                },
                options: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Conteo Ciego'
                    }

                }
            });
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