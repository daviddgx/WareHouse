<?php
ob_start();
session_start(); 
include '../LQS_EUQ/Connect.php';
include '../LQS_EUQ/LST_DespachosProduccion.php';
include "../Innet_INV/Innet_INV.php";

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {

}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';
$Turno1 = "06:00";
$Turno2 = "18:00";

$txtIDH =  "";
$txtAlto =  "";
$txtDescripcion = "";
$txtBultos = "";
$txtBase =  "";
$txtFoto =  "";
$Produto = "Estoy limpio";
$Linea = "";
$txtTurno = 0;


if(strtotime($hora) < strtotime($Turno2)){
    $HoraTrabajo = "Turno No. 1";
}else{
    $HoraTrabajo = "Turno No. 2";
}

if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
    $txtTurno = 1;
    $HoraTrabajo = "Turno No. 1";

}else{

    if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")){
        $HoraTrabajo = "Turno No. 2";
        $txtTurno = 2;

    }else{
        $txtTurno = 2;
        $HoraTrabajo = "Turno No. 2";
    }
}


// Fin de la conexion
//Variables para Resumen
$TotalProducciones = "";
$IDHs = "";
$ListaColocadas = "";
$ListaPendientes = "" ;

// Dar valor a las variabes de Resumen
$TotalProducciones = DarValorProducciones();
$Lineas = DarValorLineas();
$ListaColocadas = DarValorListaColocadas();
$ListaPendientes = DarValorListaPendientes();
// Variables para fechas de vencimiento y cuarentena
$txtDiasCuarentena = 0;
$txtDiasVencimiento = 0;

function validarCadena($cadena) {

    return false;
}

function GetUbicacion($IDH){
    include '../LQS_EUQ/Connect.php';
    $Ubicacion = "";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $cargos = "SELECT ubicacion FROM dbs9098416.config_piking where IDH = $IDH ;";

    $result = $conn->query($cargos);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $Ubicacion = $row['ubicacion'];

                    }
    }



    Return $Ubicacion;
}


// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case "btnBuscar":

        $MensajeError = '<div class="alert alert-danger" role="alert">
        <strong>Se genero un error   -- </strong> Por favor validar con Sertero Error al buscar el producto <br>
        </div>';

        $txtIDH = (isset($_POST['txtIDH'])) ? $_POST['txtIDH'] : "";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {

            $error =
                '<div class="alert alert-danger" role="alert"><p><strong>Existe un problema con la conexion entre el sistema y la base de datos ️! por favor contacte al administrador de la aplicacion e informele de este error.</div>';
            // $row = $result->fetch_assoc();
        } else {

            $sql = "SELECT IDH,Descripcion,Base,Altura,CAJASXPALET,LINEA,Foto,DIASCUARENTENA,DIASVENCIMIENTO FROM dbs9098416.productos where IDH = $txtIDH;";
            $result = $conn->query($sql);
            // Fin Obtencion de datos
            try {

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                        $txtIDH = $row['IDH'];
                        $txtAlto = $row['Altura'];
                        $txtDescripcion = $row['Descripcion'];
                        $txtBase = $row['Base'];
                        $txtFoto = $row['Foto'];
                        $txtBultos = $row['CAJASXPALET'];
                        $txtLinea = $row['LINEA'];
                        $txtDiasCuarentena = $row['DIASCUARENTENA'];
                        $txtDiasVencimiento = $row['DIASVENCIMIENTO'];

                    }
                } else {
                    $Mensajeerror =
                        '<div class="alert alert-danger" role="alert"><br><p><strong> El Producto IDH: ' . $txtIDH . ' no existe, Notifique al responsable para registrarlo  </div>';
                }
            } catch (Exception $ex) {
                $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
            }
        }

        break;

    case "btnModificar":


// nevo flujo para  piking
        $txtBodega = (isset($_POST['ListaBodegas'])) ? $_POST['ListaBodegas'] : "";
        // metodo Nuevo
        // Conexión a la base de datos

        $conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }



// Preparar la consulta SQL
        $sql = "INSERT INTO `asignaciones` (`Numero`, `IDH`, `Producto`, `Posicion`, `FechaRegistro`, `FechaColocado`, `Estado`, `Operador`, `PalletCompleto`, `Cantidades`, `Origen`, `FechaProduccion`, `LoteProduccion`, `FechaIngreso`, `FechaVencimiento`, `FechaCuarentena`, `Verificador`, `EstatusProducto`, `Observaciones`)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar los datos
        $numero = null;

        $idh = (isset($_POST['txtIDH'])) ? $_POST['txtIDH'] : "";
        $producto = (isset($_POST['txtDescripcion'])) ? $_POST['txtDescripcion'] : "";
        $txtBodega = (isset($_POST['ListaBodegas'])) ? $_POST['ListaBodegas'] : "";



        $Ubicacion = '';

        if ($txtBodega == '10'){

            $Ubicacion = GetUbicacion($idh);

        }else {
            $txtCarril = (isset($_POST['txtCarrilINP'])) ? $_POST['txtCarrilINP'] : "";
            $txtPosicion = (isset($_POST['txtPosicionINP'])) ? $_POST['txtPosicionINP'] : "";
            $txtNivel = (isset($_POST['txtNivelINP'])) ? $_POST['txtNivelINP'] : "nada";

            $Ubicacion = $txtBodega."-".$txtCarril."-".$txtPosicion."-".$txtNivel;
        }



        $posicion = $Ubicacion;

        $fechaRegistro = $fechaConsulta." ".$hora;
        $fechaColocado = null;
        $estado = "Pendiente";
        $operador =  (isset($_POST['txtMontacarguista'])) ? $_POST['txtMontacarguista'] : "";
        $txtBase = (isset($_POST['txtBase'])) ? $_POST['txtBase'] : "";
        $txtAlto = (isset($_POST['txtAlto'])) ? $_POST['txtAlto'] : "";
        $Paletizado = $txtBase * $txtAlto;
        $cantidades = (isset($_POST['txtTotalBultos'])) ? $_POST['txtTotalBultos'] : "";
        $Unidades = $cantidades;

        if($Unidades == $Paletizado ){
            $palletCompleto = "Si";
        }else{
            $palletCompleto = "No";
        }

       

        $origen = (isset($_POST['txtOrigen'])) ? $_POST['txtOrigen'] : "";

       
        $fechaProduccion = (isset($_POST['txtFechaRec'])) ? $_POST['txtFechaRec'] : ""." ".$hora;

        $loteProduccion = (isset($_POST['txtLoteProd'])) ? $_POST['txtLoteProd'] : "";

        $fechaIngreso = $fechaConsulta." ".$hora;

        $txtDiasVencimiento =(isset($_POST['txtDiasVencimiento'])) ? $_POST['txtDiasVencimiento'] : "";
        $txtDiasCuarentena = (isset($_POST['txtDiasCuarentena'])) ? $_POST['txtDiasCuarentena'] : "";

        $fechaVencimiento = date('Y-m-d', strtotime($fechaConsulta . ' + ' . $txtDiasVencimiento . ' days'));

        $fechaCuarentena = date('Y-m-d', strtotime($fechaConsulta . ' + ' . $txtDiasCuarentena . ' days'));

        $verificador = (isset($_POST['txtClaveReceptor'])) ? $_POST['txtClaveReceptor'] : "";;

        $estatusProducto = null;

        $observaciones = (isset($_POST['txtIDIngreso'])) ? $_POST['txtIDIngreso'] : "";


// Preparar la sentencia

        if($operador == 'vacio'){
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Seleccione el operador de montacargas. No se guardaron los datos  </div>';


        }else if($posicion == ''){
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Ingrese la Ubicaion. Si es Piking consulte si este IDH esta configurado en Piking. No se guardaron los datos </div>';

        } else if(validarCadena($posicion)) {
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Ingrese la Ubicaion de forma correcta. La ubicacion ingresada fue '.$posicion.' No se guardaron los datos </div>';

        }else if($producto == '') {
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Ingreso el IDH pero no dio clic en buscar. No se guardaron los datos registrelo de nuevo. </div>';

        } else{

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssss", $numero, $idh, $producto, $posicion, $fechaRegistro, $fechaColocado, $estado, $operador, $palletCompleto, $cantidades, $origen, $fechaProduccion, $loteProduccion, $fechaIngreso, $fechaVencimiento, $fechaCuarentena, $verificador, $estatusProducto, $observaciones);

// Ejecutar la sentencia y verificar si fue exitosa
        if ($stmt->execute() === TRUE) {
            $Mensajeerror =
                '<div class="alert alert-success" role="alert"><br><p><strong> Se registro correctamente el ingreso en la Ubicacion '.$Ubicacion.'  </div>';
             ReservarUbicacion($Ubicacion);
            ImprimirCardex($Ubicacion);
            include '../LQS_EUQ/LST_DespachosProduccion.php';

            // Dar valor a las variabes de Resumen
            $TotalProducciones = DarValorProducciones();
            $Lineas = DarValorLineas();
            $ListaColocadas = DarValorListaColocadas();
            $ListaPendientes = DarValorListaPendientes();
// Variables para fechas de vencimiento y cuarentena
            $txtDiasCuarentena = 0;
            $txtDiasVencimiento = 0;

        } else {
            $Mensajeerror =
                '<div class="alert alert-danger" role="alert"><br><p><strong> se genero un error al registrar el ingreso, Contacte a SERTERO '.$conn->error.' </div>';

        }


        }

// Cerrar la conexión a la base de datos



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

       <br>
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
                            <h4 class="card-title">Datos para Imprimir Ingresos, Esta trabajando en el <?php echo $HoraTrabajo;?></h4>
                            <h6 class="card-subtitle">Cree las etiquetas de identificación de productos</h6>
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
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Codigo del Producto</label>
                                                        <input name="txtIDH" type="text" class="form-control" value="<?php echo $txtIDH ?>" required>

                                                    </div>

                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <br>
                                                        <button type="submit" value="btnBuscar" name="accion" class=" col-md-8 btn btn-outline-success  justify-content: center; align-items: center; ">Buscar
                                                        </button>
                                                    </div>

                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Descripcion</label>
                                                        <input type="hidden" name="txtDiasVencimiento" value="<?php echo $txtDiasVencimiento?>" id="txtDiasVencimiento">
                                                        <input type="hidden" name="txtDiasCuarentena" value="<?php echo $txtDiasCuarentena?>" id="txtDiasCuarentena">
                                                        <input name="txtDescripcion" type="text" class="form-control" value="<?php echo $txtDescripcion; ?>" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>No. Documento de Ingreso</label>
                                                        <input name="txtIDIngreso" type="text" class="form-control" value="">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                    <label>Fecha de Produccion</label>
                                                    <input name="txtFechaRec" type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Origen</label>

                                                        <select class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtOrigen" id="txtOrigen" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">


                                                            <option value="Produccion">
                                                                Produccion
                                                            </option>

                                                            <option value="Importacion">
                                                                Importacion
                                                            </option>

                                                            <option value="Devolucion">
                                                                Devolucion
                                                            </option>
                                                            <option value="Maquilador">
                                                                Maquilador
                                                            </option>

                                                            <option value="Rechazo">
                                                                Rechazo
                                                            </option>

                                                            <option value="Boega Externa">
                                                                Boega Externa
                                                            </option>




                                                        </select>
                                                    </div>
                                                </div>



                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Fotografia</label>
                                                        <div class="input-group mb-12">
                                                            <?php if ($txtFoto != "") { ?>
                                                                <br />
                                                                <img style="border-radius: 30px !important;" class="img-thumbnail rounded mx-auto d-block" width="200px" src="../assets/images/Productos/<?php echo $txtFoto; ?>">
                                                                <br />
                                                                <br />
                                                            <?php } ?>


                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <!--INICIO Row para Elemento de formulario -->
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Hora</label>
                                                        <input name="txtHora" type="text" class="form-control" value="<?php echo $hora; ?>" required>
                                                    </div> -
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Turno</label>
                                                        <input name="txtTurno" type="text" class="form-control" value="<?php echo $txtTurno; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group hide">
                                                        <label>Pallet</label>
                                                        <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtUMedida" id="idtxtUMedida" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">


                                                            <option value="Naranja">
                                                                Naranja
                                                            </option>

                                                            <option value="Blanco">
                                                                Blanco
                                                            </option>

                                                            <option value="Jaula">
                                                                Jaula
                                                            </option>




                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Clave Receptor</label>
                                                        <input name="txtClaveReceptor" type="text" class="form-control" value="<?php echo $_SESSION['Usuario']; ?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Montacarguista</label>
                                                        <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtMontacarguista" id="txtMontacarguista" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled" >
                                                            <option style="display:none; height:50px;" value="vacio" class="ng-binding">
                                                                --- Montacarguista ---
                                                            </option>

                                                            <?php
                                                            $conn = new mysqli($servername, $username, $password, $dbname);
                                                            $cargos = "SELECT concat(Nombre,' ',Apellido)as NombreMont, Nombre_Usuario FROM dbs9098416.usuarios_app where TipoUsuario = 2;";

                                                            $result = $conn->query($cargos);
                                                            if ($result->num_rows > 0) {
                                                                while ($row = $result->fetch_assoc()) {

                                                                    echo '<option value="' . $row['Nombre_Usuario'] . '">' . utf8_encode($row['NombreMont']) . '</option>';
                                                                }
                                                            }
                                                            ?>


                                                        </select>
                                                    </div>


                                                </div>

                                                <div class="col-md-2"><label># Lote</label>
                                                <input name="txtLoteProd" id="txtLoteProd" type="text" class="form-control" value="G.M <?php echo date('d-m-Y'); ?>"><label># Lote</label>
                                                    </div>
                                                </div>

                                                <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fechaInput = document.querySelector('input[name="txtFechaRec"]');
        const loteInput = document.getElementById('txtLoteProd');

        fechaInput.addEventListener('change', function() {
            const fechaSeleccionada = fechaInput.value;
            
            
            
            loteInput.value = 'G.M ' + fechaSeleccionada;
        });
    });
</script>
                                            </div>
                                            <!-- FIN Row para Elemento de formulario -->

                                            <!--INICIO Row para Elemento de formulario -->
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Base</label>
                                                        <input name="txtBase" type="number" class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" value="<?php echo $txtBase; ?>" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Alto</label>
                                                        <input name="txtAlto" type="number"  class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" value="<?php echo $txtAlto; ?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Tot. Bultos</label>
                                                        <input name="txtTotalBultos" type="number" class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" value="<?php echo $txtBultos; ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Codigo de Barras</label>
                                                        <svg data-value="<?php echo $txtIDH ?>" data-text="<?php echo $txtIDH ?>" class="codigo" /></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>


                                            </div>

                                        </div>

                                    <!--  </form> -->

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
                            <h4 class="card-title">Ubicacion del Producto</h4>
                            <h6 class="card-subtitle">Seleccione la ubicacion donde se colocara el producto</h6>

                            <div class="my-content formulario">
                                <!--    <form role="form" action="" method="post" enctype="multipart/form-data"> -->
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Bodega</label>

                                                    <select  class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaBodegas" id="ListaBodegas" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Bodega ---
                                                        </option>
                                                        <?php
                                                        $conn = new mysqli($servername, $username, $password, $dbname);
                                                        $cargos = "SELECT Nombre_Bodega,Descripcion FROM dbs9098416.warehauses;";

                                                        $result = $conn->query($cargos);
                                                        if ($result->num_rows > 0) {
                                                            while ($row = $result->fetch_assoc()) {

                                                                echo '<option value="'.$row['Nombre_Bodega'].'">'.utf8_encode($row['Descripcion']).'</option>';
                                                            }
                                                            echo '<option value="10">Picking</option>';
                                                        }
                                                        ?>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <input type="hidden" name="txtCarrilINP" value="" id="txtCarrilINP">
                                                    <!-- Ingresando Selects Dinamicos -->
                                                    <div id="Select_Area">

                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <input type="hidden" name="txtPosicionINP" value="" id="txtPosicionINP">
                                                    <div id="Select_Posicion">
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <input type="hidden" name="txtNivelINP" value="" id="txtNivelINP">
                                                    <div id="Select_Niveles">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>




                                    <div class="row">
                                        <div class="col-md-12 centrado">
                                            <div class="form-group text-center" >
                                                <button type="submit" value="btnModificar" name="accion" class="btn btn-outline-success">Registrar e imprimir
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
                            <h4 class="card-title">Lista de Produccion</h4>
                            <h6 class="card-subtitle"> Valide los registros del dia</h6>
                            <br>
                            <!-- Start First Cards -->
                            <!-- *************************************************************** -->
                            <div class="card-group">
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo $TotalProducciones?></h2>

                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Ingresos</h6>
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
                                                            class="set-doller"></sup><?php echo $Lineas;?></h2>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Operadores
                                                </h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="users"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border-right">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo$ListaColocadas ?></h2>
                                                    <span
                                                            class="badge bg-success font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block"><?php if($TotalProducciones == 0){echo 0;} else{ echo  bcdiv((($ListaColocadas / $TotalProducciones) * 100),'1', 2) ;} ?>%</span>
                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Colocadas</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="file-plus"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                                            <div>
                                                <div class="d-inline-flex align-items-center">
                                                    <h2 class="text-dark mb-1 font-weight-medium"><?php echo $ListaPendientes?></h2>
                                                    <span
                                                            class="badge bg-danger font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block"><?php if($TotalProducciones == 0){echo 0;} else{ echo  bcdiv((($ListaPendientes / $TotalProducciones) * 100),'1', 2) ;} ?>%</span>
                                                </div>
                                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Pendientes</h6>
                                            </div>
                                            <div class="ml-auto mt-md-3 mt-lg-0">
                                                <span class="opacity-7 text-muted"><i data-feather="file-plus"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- *************************************************************** -->
                            <!-- End First Cards -->


                            <br>
                            <div class="dataTables_wrapper" style="overflow-x: auto;">
                            <table id="example" class="table table-striped  " cellspacing="0" width="100%">
                                <thead>



                                <th>IDH</th>
                                <th>Descripcion</th>
                                <th>Posicion</th>
                                <th>Operador</th>
                                <th>Bultos</th>
                                <th>Hora</th>
                                <th>Estatus</th>
                                <th>Icono</th>
                                <th>Avance</th>
                                <th>Ubicacion</th>
                                <th>ReImprimir</th>
                                <th>ANULAR</th>

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
                                    echo $lista_DespachoPRODUCCION['Posicion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['nombreOperador'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Cantidades'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['FechaRegistro'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachoPRODUCCION['Estado'];
                                    echo "</td>";

                                    echo "<td>";
                                    {
                                        //Estatus del Producto
                                        switch ($lista_DespachoPRODUCCION['Estado']){
                                            case 'Producido' :
                                                 echo '<img src="../assets/images/Iconos/producido.png" class="" --="" width="auto" height="40">';
                                                break;

                                            case 'Ingresado' :
                                                echo '<img src="../assets/images/Iconos/Montacargas.png" class="" --="" width="auto" height="40">';
                                                break;

                                            case 'Colocado' :
                                                echo '<img src="../assets/images/Iconos/pallet.png" class="" --="" width="auto" height="40">';
                                                break;

                                                case 'Anulado' :
                                                echo '<img src="../assets/images/Iconos/Anulado.png" class="" --="" width="auto" height="40">';
                                                break;

                                            default :
                                                echo '<img src="../assets/images/Sertero/LogoCBP.png" class="" --="" width="auto" height="40">';
                                                break;

                                        }

                                    }

                                    echo "</td>";

                                    // Iconos
                                    echo "<td>";
                                    {
                                        //Estatus del Producto
                                        switch ($lista_DespachoPRODUCCION['Estado']){
                                            case 'Producido' :
                                                echo '<img src="../assets/images/Iconos/circuloNaranja.png" class="" --="" width="auto" height="40">';
                                                break;

                                            case 'Ingresado' :
                                                echo '<img src="../assets/images/Iconos/circuloVerde.png" class="" --="" width="auto" height="40">';
                                                break;

                                            case 'Colocado' :
                                                echo '<img src="../assets/images/Iconos/circuloVerde.png" class="" --="" width="auto" height="40">';
                                                break;

                                                case 'Anulado' :
                                                echo '<img src="../assets/images/Iconos/Anulado.png" class="" --="" width="auto" height="40">';
                                                break;

                                            default :
                                                echo '<img src="../assets/images/Iconos/circuloRojo.png" class="" --="" width="auto" height="40">';
                                                break;

                                        }

                                    }

                                    echo "</td>";


                                    //Ubicacion cambio
                                    switch ($lista_DespachoPRODUCCION['Estado']){
                                        case 'Pendiente' :

                                            echo "<td>";
                                            echo '<a href="EditarUbicacion.php?Ubicacion='.$lista_DespachoPRODUCCION['Numero'].'" class="btn btn-outline-info "><i class="fas fa-pen-square"></i> Cambiar</a>';
                                            echo "</td>";

                                            break;

                                        default :
                                            echo "<td>";
                                            echo "</td>";
                                            break;

                                    }

                                    //Reimprimir
                                    switch ($lista_DespachoPRODUCCION['Estado']){
                                        case 'Pendiente' :
                                            echo "<td>";
                                            echo '<a href="Cardex.php?Ubicacion='.$lista_DespachoPRODUCCION['Posicion'].'" target="_blank" class="btn btn-outline-dark "> <i class="fas fa-print"></i> Re-Imprimir</a>';
                                            echo "</td>";

                                            break;

                                        default :
                                            echo "<td>";
                                            echo "</td>";
                                            break;

                                    }


                                    //Anular
                                    switch ($lista_DespachoPRODUCCION['Estado']){
                                        case 'Pendiente' :
                                            echo "<td>";
                                            echo '<a href="AnularIngreso.php?Ubicacion='.$lista_DespachoPRODUCCION['Numero'].'" class="btn btn-outline-danger "><i"></i>  Anular ⚠️</a>';
                                            echo "</td>";

                                            break;

                                        default :
                                            echo "<td>";
                                            echo "</td>";
                                            break;

                                    }

                                    
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



</body>

</html>