<?php
ob_start();
session_start();

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
}

$txtUsuario = $_SESSION['Usuario'];

include '../LQS_EUQ/Connect.php';

date_default_timezone_set('America/Guatemala');

$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());
$txtBuscarIDH = '';


$fechaActualizacion = $fechaConsulta . " " . $hora;



function GetDestinio(mixed $txtUOrigne)
{
    include '../LQS_EUQ/Connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    $cargos = "select Ubicacion from config_piking where IDH = (select IDH from posiciones where Ubicacion =  '$txtUOrigne');";
    $Udestino = '';
    $result = $conn->query($cargos);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $Udestino = $row['Ubicacion'];
        }
    }

    return $Udestino;
}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';

$txtUOrigne = "";
$txtUDestino = "";
$txtDescripcion = "";
$txtIDH = "";



function validarCadena($cadena)
{
    $patron = '/^\d-[A-Z]\d+-[A-Z]\d+-[A-Z]\d+$/';
    return preg_match($patron, $cadena) === 0;
}


// Nueva forma de evaluar los datos
if (isset($_GET['Ubicacion'])) {
    $ParMontacarguista = $_GET['Montacarguista'];
    $ParUbicacion = $_GET['Ubicacion'];

    if ($ParMontacarguista == '') {
        $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>No se Realizo el movimiento debido a que no selecciono un Montacarguista -- </strong> La Ubicacion que habia usado es: ' . $ParUbicacion . '. Por favor seleccione el montacarguista y envie de nuevo la ubicacion.
                                </div>';
    } else {

        $txtUOrigne =  $ParUbicacion;
        $txtUDestino = GetDestinio($txtUOrigne);
        $txtMontaargiosta = $ParMontacarguista;

        //echo "Ubicacion Origen: ".$txtUOrigne. " Ubicacion Destinio: " .$txtUDestino. " Montacarguista: " . $txtMontaargiosta;

        //1. Validacion de Ubicaion Origen
        $conn = new mysqli($servername, $username, $password, $dbname);
        $cargos = "SELECT posiciones.IDH, productos.Descripcion as Descripcion FROM dbs9098416.posiciones join productos on posiciones.IDH = productos.idh where Ubicacion = '$txtUOrigne' and posiciones.Estado = 'Ocupada';";

        $result = $conn->query($cargos);
        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

                // echo '<option value="' . $row['Nombre_Usuario'] . '">' . utf8_encode($row['NombreMont']) . '</option>';
                $txtIDH = $row['IDH'];
                $txtDescripcion = $row['Descripcion'];
            }

            // Validacion de Ubicaion Destino
            $conn = new mysqli($servername, $username, $password, $dbname);
            $cargos = "SELECT * FROM dbs9098416.config_piking where Ubicacion = '$txtUDestino' ";

            $result = $conn->query($cargos);
            if ($result->num_rows > 0) {

                // Registrar Ingreso de montacargas

                include '../LQS_EUQ/Auth.php';
                $txtQRY = "insert into dbs9098416.piking values (null,'$txtIDH','$txtDescripcion','$txtUOrigne','$txtUDestino','$fechaActualizacion',null,'Pendiente','$txtUsuario','$txtMontaargiosta',null);";

                // echo $txtQRY;

                $sentencia = $pdo->prepare($txtQRY);
                $sentencia->execute();



                $txtQRY = "update posiciones set Estado = 'Moviendo' where Ubicacion = '$txtUOrigne';";

                // echo $txtQRY;

                $sentencia = $pdo->prepare($txtQRY);
                $sentencia->execute();

                $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-success text-white border-0 fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <strong>Se asignó correctamente el movimiento desde ' . $ParUbicacion . ' hacia ' . $txtUDestino . ' -- </strong> al operador de montacargas ' . $ParMontacarguista . '
                </div>
                
                <script>
                    // Espera 3 segundos (3000 milisegundos) y luego redirecciona
                    setTimeout(function() {
                        
                        window.location.href = "AbastecerPiking.php";
                    }, 3000);
                </script>"';

            } else {


                $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>La Ubicacion de Destino es incorrecta  (' . $txtUDestino . '), o no hay configuracion de Piking para este IDH Libre -- </strong> Por favor Ingresa una ubicacion que si este en las bodegas y asegurece que este Libre.
                                </div>';
            }
        } else {

            $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>La Ubicacion  de Origen es incorrecta o no esta Ocupada para poder Moverla (' . $txtUOrigne . ') -- </strong> Por favor Ingresa una ubicacion valida.
                                </div>';
        }
    }
}


// Traer los datos de la tabla
// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";
switch ($accion) {

    case 'btnConsultarIDH':

        $txtBuscarIDH = (isset($_POST['txtBuscarIDH'])) ? $_POST['txtBuscarIDH'] : "";
        break;
    default:
        break;
}

// Construccion de consulta de material
try {
    if ($txtBuscarIDH != '') {
        $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
        $sqlDatos = " SELECT date(FechaProduccion) FROM dbs9098416.posiciones where IDH = $txtBuscarIDH group by date(FechaProduccion) order by FechaProduccion asc " ;
        $ejecutar_sentencia_Guias = $conn->query($sqlDatos);
        // Verifica si la consulta retorna resultados
        // Obtiene los datos en forma de un arreglo
        $lista_Guias =$ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
    }
}catch (Exception $ex){
    $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <![endif]-->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>


    <style>
        .monospace-font {
            font-family: 'Courier New', Courier, monospace; /* Puedes cambiar 'Courier New' por la fuente que prefieras */
        }
    </style>

</head>

<body>
<!-- ============================================================== -->

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
                            <h4 class="card-title">Consulta FiFo </h4>
                            <h6 class="card-subtitle">Consulte el estatus FiFo de un IDH</h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>

                            <!-- Se cambia para seleccionar la vista por IDH -->


                            <div class="my-content formulario">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-body">



                                <div class="row">


                                    <div class="col-md-6">
                                        <h4 class="card-title">1. Seleccione IDH a consultar </h4>
                                        <div class="form-group">
                                        <label>IDH</label>
                                        <select  required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched  monospace-font" name="txtBuscarIDH" id="txtBuscarIDH" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                            <option style="display:none; height:50px;" value="" class="ng-binding">
                                                --- IDH ---
                                            </option>

                                            <?php
                                            $conn = new mysqli($servername, $username, $password, $dbname);
                                            $cargos = "select  posiciones.IDH,productos.Descripcion, count(*) as Pallets from posiciones Inner Join productos  on posiciones.IDH = productos.IDH  group by IDH order by IDH Asc;";

                                            $result = $conn->query($cargos);
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {

                                                    echo '<option value="' . $row['IDH'] . '">' . utf8_encode($row['IDH']) . ' --- ' . utf8_encode($row['Descripcion']) . ' --- ' . utf8_encode($row['Pallets']) . ' Pallets</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        </div>

                                    </div>


                                    <div class="form-actions">
                                        <div class="text-center" style="padding-top: 65px;">
                                            <button type="submit" value="btnConsultarIDH" name="accion"
                                                    class="btn btn-outline-success">Consultar
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                                <br>



                                <!-- Contenido del Formulario-->


<div class="row">




                            </div>


                            <!--  </form> -->




                            <br>
                            <h4 class="card-title"> Detalle de FiFo</h4>
                            <br>



                            <div class="row">
                                <!-- Column -->
                                <div class="col-md-12">

                                    <?php
                                    // Nueva línea con la imagen
                                    echo '<div class="col-md-12 text-center mb-3">';
                                    echo '<img src="../assets/images/Productos/' . $txtBuscarIDH . '.jpg" alt="' . $txtBuscarIDH . '" width="200" height="200">';
                                    echo '<div class="col-md-2 mb-2 text-center"><label>IDH: </label><input type="text" class="form-control form-control-sm text-center" value="' . $txtBuscarIDH . '" readonly></div>';

                                    echo '</div>';




                                    for ($i = 0; $i < $lista_Guias; $i++) {
                                        // Línea actual


                                        $FechaProduccion = $lista_Guias['date(FechaProduccion)'];
                                        $FechaProduccion2 = date('d-m-Y', strtotime($lista_Guias['date(FechaProduccion)']));
                                        echo '<div class="col-md-2 mb-2 text-center"><label>Fechas de Produccion: </label><input type="text" class="form-control form-control-sm text-center" value="' . $FechaProduccion2 . '" readonly></div>';


                                        // Listado de Carriles por cada fecha
                                        try {
                                            $conn2  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
                                            $sqlDatos2 = " select concat(Bodega,'-',Carril) as Carril,productos.descripcion ,Count(*) as Pallets from  posiciones inner join productos on posiciones.IDH = productos.IDH where posiciones.IDH = $txtBuscarIDH and date(fechaProduccion) = '$FechaProduccion' group by bodega,Carril,productos.descripcion" ;

                                            $ejecutar_sentencia_Guias2 = $conn2->query($sqlDatos2);
                                            // Verifica si la consulta retorna resultados
                                            // Obtiene los datos en forma de un arreglo
                                            $lista_Guias2 =$ejecutar_sentencia_Guias2->fetch(PDO::FETCH_ASSOC);

                                        } catch (Exception $ex){
                                            $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                        </div>';
                                            echo $Mensajeerror;
                                        }



                                        for ($j = 0; $j < $lista_Guias2; $j++) {

                                            echo '<div class="row linea">';
                                            //echo '<div class="col-md-2 mb-2 text-center"><label> </label><input type="text" class="form-control form-control-sm text-center" value="" readonly></div>';
                                            echo '<div class="col-md-2 mb-2 text-center"><label> </label></div>';
                                            echo '<div class="col-md-2 mb-2 text-center"><label>Carril: </label><input type="text" class="form-control form-control-sm text-center" value="' . $lista_Guias2['Carril'] . '" readonly></div>';
                                            echo '<div class="col-md-4 mb-4 text-center" data-toggle="tooltip" data-placement="top" title="' . $lista_Guias2['descripcion'] . '"><label>Descripción: </label><input type="text" class="form-control form-control-sm text-center" value="' . $lista_Guias2['descripcion'] . '" readonly></div>';
                                            echo '<div class="col-md-2 mb-2 text-center"><label>Pallets: </label><input type="text" class="form-control form-control-sm text-center" value="' . $lista_Guias2['Pallets'] . '" readonly></div>';
                                            echo '</div>';

                                            $lista_Guias2 = $ejecutar_sentencia_Guias2->fetch(PDO::FETCH_ASSOC);

                                        }

                                            $lista_Guias = $ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
                                    }


                                    ?>
                                </div>
                            </div>





                        </div>
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

<script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const abastecerLinks = document.querySelectorAll(".abastecerLink");

        abastecerLinks.forEach(function(link) {
            link.addEventListener("click", function() {
                const ubicacion = link.getAttribute("data-ubicacion");
                const selectMontacarguista = document.getElementById("txtMontacarguiasta");
                const montacarguista = selectMontacarguista.value;
                const url = `AbastecerPiking.php?Ubicacion=${ubicacion}&Montacarguista=${montacarguista}`;
                window.location.href = url;
            });
        });
    });
</script>

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

<script>
    // Establece el tiempo de inactividad en milisegundos (5 minutos = 300,000 milisegundos)
    const tiempoInactividad = 300000;

    // Función que redirige al usuario a la página específica
   // function redirigir() {
   //     window.location.href = 'index.php'; // Reemplaza 'pagina-destino.html' con la URL de la página a la que deseas redirigir al usuario.
   // }

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


<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            searching: true,
            language: {
                url: 'datatables_espanol.json'
            },
            order: [[4, 'asc']], // Ordenar por la columna 5 (índice 4) de forma ascendente
        });

        $('#filterButton').on('click', function() {
            var valueColumn1 = $('#inputColumn1').val();
            var valueColumn2 = $('#inputColumn2').val();

            table.column(0).search(valueColumn1).draw();
            table.column(2).search(valueColumn2).draw();
        });
    });
</script>


<!-- Componentes para mejorar navegacion -->

<script>
    document.onkeydown = function (e) {
        if (e.key === "F5") {
            e.preventDefault();
            alert("La recarga de página está deshabilitada. Tampoco regrese desde el Navegador, use los botones de la APP.");
        }
    };
</script>




</body>

</html>