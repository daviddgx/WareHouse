<?php
ob_start();
session_start();

$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
$txtUsuario = $_SESSION['Usuario'];

include '../LQS_EUQ/Connect.php';
include '../LQS_EUQ/LST_DespachosProduccion.php';
include "../Innet_INV/Innet_INV.php";

date_default_timezone_set('America/Guatemala');

$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());


$fechaActualizacion = $fechaConsulta." ".$hora;

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {

}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';

$txtUOrigne = "";
$txtUDestino = "";
$txtDescripcion ="";
$txtIDH = "";


function validarCadena($cadena) {
   $patron = '/^\d-[A-Z]\d+-[A-Z]\d+-[A-Z]\d+$/';    return preg_match($patron, $cadena) === 0;
}


// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case 'btnBloquearUbicacion':


    $txtBodegaOrigen = (isset($_POST['ListaBodegasOrigen'])) ? $_POST['ListaBodegasOrigen'] : "";
        $txtCarrilOrigen = (isset($_POST['txtCarrilINPOrigen'])) ? $_POST['txtCarrilINPOrigen'] : "";
        $txtPosicionOrigen = (isset($_POST['txtPosicionINPOrigen'])) ? $_POST['txtPosicionINPOrigen'] : "";
        $txtNivelOrigen = (isset($_POST['txtNivelINPOrigen'])) ? $_POST['txtNivelINPOrigen'] : "";
        $txtUbicacion = $txtBodegaOrigen."-".$txtCarrilOrigen."-".$txtPosicionOrigen."-".$txtNivelOrigen;
       

        $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
        $sqlDatos = "update posiciones set EstatusUbicacion = 'Libre', EstatusProducto ='Libre',Observaciones = 'DesBloqueo individual por: $txtUsuario al: $fechaActualizacion'  where Ubicacion = '$txtUbicacion'"; ;
        $ejecutar_sentencia_Guias = $conn->query($sqlDatos);


        $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-success text-white border-0 fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <strong>Se desbloqueo correctamente la Ubicacion ' . $txtUbicacion . ' Por favor valide en el mantenimiento de bodegas. -- </strong> Puede validar <a href="DetalleUbicaciones.php?Ubicacion='.$txtUbicacion.'">aquí</a>.
            </div>';



        break;


    default:

        break;
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

    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
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



        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">



                        <div class="card-body ">
                            <h4 class="card-title">Liberar Ubicacion Por Cuarentena </h4>
                            <h6 class="card-subtitle">Seleccione la ubicacion a liberar por medio de los controles</h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>


                            <div>
                                <!-- Contenido del Formulario-->

                                <div class="my-content formulario">
                                    <form role="form" action="" method="post" enctype="multipart/form-data">
                                        <div class="form-body">


                                        <div class="row">


                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Bodega</label>

                                                        <select  class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaBodegasOrigen" id="ListaBodegasOrigen" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                            <option style="display:none; height:50px;" value="" class="ng-binding">
                                                                --- Bodega ---
                                                            </option>
                                                            <?php
                                                            $conn = new mysqli($servername, $username, $password, $dbname);
                                                            $cargos = "SELECT PS.Bodega as Nombre_Bodega, WS.Descripcion FROM `posiciones` PS inner join warehauses WS on PS.Bodega = WS.Nombre_Bodega where EstatusUbicacion = 'Cuarentena' group by Bodega;";

                                                            $result = $conn->query($cargos);
                                                            if ($result->num_rows > 0) {
                                                                while ($row = $result->fetch_assoc()) {

                                                                    echo '<option value="'.$row['Nombre_Bodega'].'">'.utf8_encode($row['Descripcion']).'</option>';
                                                                }
                                                            }
                                                            ?>

                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input type="hidden" name="txtCarrilINPOrigen" value="" id="txtCarrilINPOrigen">
                                                        <!-- Ingresando Selects Dinamicos -->
                                                        <div id="Select_AreaOrigen">
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input type="hidden" name="txtPosicionINPOrigen" value="" id="txtPosicionINPOrigen">
                                                        <div id="Select_PosicionOrigen">
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input type="hidden" name="txtNivelINPOrigen" value="" id="txtNivelINPOrigen">
                                                        <div id="Select_NivelesOrigen">
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col-md-12" style="text-align: center">
                                                    <button type="submit" value="btnBloquearUbicacion" name="accion"
                                                            class="btn btn-outline-success">Desbloquear Ubicacion de Cuarentena
                                                    </button>
                                                </div>


                                            </div>
                                            <!-- FIN Row para Elemento de formulario -->

                                            <!--INICIO Row para Elemento de formulario -->




                                            </div>

                                        </div>

                                    <!--  </form> -->

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

<script src="../dist/js/OnLine.js"></script>
<!--Scripts para DataTables-->
<!--This page plugins -->



<script>
    $(document).ready(function() {
        $('#example').DataTable( {
            language: {
                url: 'datatables_espanol.json'
            }

        } );
    } );
</script>
<!-- Ubicaciones Origen-->

<script type="text/javascript">
    $(document).ready(function(){

        recargarListaOrigen();
        $('#ListaBodegasOrigen').change(function(){
            recargarListaOrigen();

        });
    })
</script>

<script type="text/javascript">
    function recargarListaOrigen() {


        $.ajax({
            type: "POST",
            url: "TraerAreasDesbloquear.php",
            data: "Bodega=" + $('#ListaBodegasOrigen').val(),
            success:function(r) {
                $('#Select_AreaOrigen').html(r);
            }
        });
    }
</script>

<script>
    function cambiarValorCarrilOrigen() {
        var select = document.getElementById("ListaCarrilOrigen");
        var input = document.getElementById("txtCarrilINPOrigen");
        input.value = select.value;
    }
</script>

<script>
    function cambiarValorPosicionOrigen() {
        var select = document.getElementById("ListaPosicionOrigen");
        var input = document.getElementById("txtPosicionINPOrigen");
        input.value = select.value;
    }
</script>

<script>
    function cambiarValorNivelOrigen() {
        var select = document.getElementById("ListaNivelOrigen");
        var input = document.getElementById("txtNivelINPOrigen");
        input.value = select.value;
    }
</script>




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