<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}

include '../LQS_EUQ/Connect.php';

$txtFechaInicial="";
$txtFechaFinal="";

$txtFechaInicial2="";
$txtFechaFinal2="";

$txtHoraInicial="";
$txtHoraFinal="";

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {

}

date_default_timezone_set('America/Guatemala');
$hora = date('G:i', time());
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$Turno1 = "06:00";
$Turno2 = "18:00";
$FechaTrabajoAnterior="";
$HoraTrabajoInicio="";
$HoraTrabajoFinal="";

if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
    $txtTurno = "1";
    $txtFechaInicial = $fechaConsulta;
    $txtHoraInicial = $Turno1 ;

    $txtFechaFinal  = $fechaConsulta;
    $txtHoraFinal  = $Turno2;

}else{

    if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")){
        $txtTurno = "2";
        $txtFechaInicial = $fechaConsulta;
        $txtHoraInicial = $Turno2 ;

        $txtFechaFinal  = $fechaConsulta;
        $txtHoraFinal  = "23:59";


    }else{
        $txtTurno = "2";
        $FechaTrabajoAnterior= date('Y-m-d', strtotime($fechaConsulta . ' -1 day')); // Resta un día a la fecha actual

        $txtFechaInicial = $FechaTrabajoAnterior;
        $txtHoraInicial = $Turno2 ;

        $txtFechaFinal  = $fechaConsulta;
        $txtHoraFinal  =  $Turno1;

    }
}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';




// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case "btnConsultar":
        $Fecha1 = (isset($_POST['txtFechaInicial'])) ? $_POST['txtFechaInicial'] : "";
        $Hora1 = (isset($_POST['txtHoraInicial'])) ? $_POST['txtHoraInicial'] : "";

        $Fecha2 = (isset($_POST['txtFechaFinal'])) ? $_POST['txtFechaFinal'] : "";
        $Hora2 = (isset($_POST['txtHoraFinal'])) ? $_POST['txtHoraFinal'] : "";

        $txtFechaInicial = $Fecha1;
        $txtHoraInicial = $Hora1;
        $txtFechaFinal = $Fecha2;
        $txtHoraFinal = $Hora2;

        $HoraTrabajoInicio = $Fecha1." ".$Hora1;
        $HoraTrabajoFinal  = $Fecha2." ".$Hora2;

        include '../LQS_EUQ/RPTIngresosFIFO.php';

        $txtFechaInicial2 = $Fecha1."%20".$Hora1;
        $txtFechaFinal2  = $Fecha2."%20".$Hora2;

        break;

    default:

        break;
}



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
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
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
                            <a class="dropdown-item" href="Print_Cardex.php">Actualizar Pagina</a>


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


        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->

    <br>

        <div class="container-fluid animate__animated animate__fadeIn">
            

        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body" >
                            <h4 class="card-title">Seleccione la fecha y hora para generar el reporte</h4>
                            <h6 class="card-subtitle">Resumen de ingresos a Bodegas.</h6>
                            <br>

                            <div class="my-content formulario">
                                    <form role="form" action="" method="post" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fecha Inicial</label>
                                                <input name="txtFechaInicial"  type="date" class="form-control" value="<?php echo $txtFechaInicial ?>" required>

                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Hora Inicial</label>
                                                <input name="txtHoraInicial" type="time" class="form-control" value="<?php echo $txtHoraInicial ?>" required>

                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fecha Final</label>
                                                <input name="txtFechaFinal"  type="date" class="form-control" value="<?php echo $txtFechaFinal ?>" required>

                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Hora Final</label>
                                                <input name="txtHoraFinal" type="time" class="form-control" value="<?php echo $txtHoraFinal ?>" required>

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


        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Reporte Personalizado</h4>
                            <h6 class="card-subtitle"> Relice consultas personalizadas a la informacion del sistema</h6>
                            <br>

                            <div class="text-center">
                                <a href="../FPDF2/ReporteIngresos.php?FInicio=<?php echo $txtFechaInicial2?>&FFinal=<?php echo $txtFechaFinal2?>" target= "_blank" class="btn btn-success"> <i class="fas fa-file-excel"></i> Generar Reporte</a>
                            </div>
                            <br>
                            <div class="dataTables_wrapper" style="overflow-x: auto;">
                            <table id="example" class="table table-striped  " cellspacing="0" width="100%" >
                                <thead>



                                <th>IDH</th>
                                <th>Descripcion</th>
                                <th>Estado</th>
                                <th>Operador</th>
                                <th>Verificador</th>
                                <th>Pallets</th>
                                <th>Bultos</th>
                                <th>Unidades</th>


                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < $lista_DespachoPRODUCCION; $i++) {
                                    echo "<tr>";
                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['IDH'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Producto'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Estado'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Operador'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Verificador'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Cantidad'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Bultos'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Unidades'];
                                    echo "</td>";


                                    $lista_DespachoPRODUCCION = $ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
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
<!--Scripts para DataTables-->
<!--This page plugins -->
<script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            order: [[4, 'desc']],
            language: {
                url: 'datatables_espanol.json'
            }


        });
    });
</script>

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

<!-- Script para listas dinamicas -->

<script type="text/javascript">
    $(document).ready(function(){

        recargarLista();
        $('#ListaBodegas').change(function(){
            recargarLista();

        });
    })
</script>

<script type="text/javascript">
    function recargarLista() {

        console.warn( "Entro a Lista Carriles" );
        $.ajax({
            type: "POST",
            url: "TraerAreas.php",
            data: "Bodega=" + $('#ListaBodegas').val(),
            success:function(r) {
                $('#Select_Area').html(r);
            }
        });
    }
</script>

<script>
    function cambiarValorCarril() {
        var select = document.getElementById("ListaCarril");
        var input = document.getElementById("txtCarrilINP");
        input.value = select.value;
    }
</script>

<script>
    function cambiarValorPosicion() {
        var select = document.getElementById("ListaPosicion");
        var input = document.getElementById("txtPosicionINP");
        input.value = select.value;
    }
</script>

<script>
    function cambiarValorNivel() {
        var select = document.getElementById("ListaNivel");
        var input = document.getElementById("txtNivelINP");
        input.value = select.value;
    }
</script>
<!-- Cargar Area-->



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