<?php
session_start();
include '../LQS_EUQ/Connect.php';
include '../Innet_INV/Innet_INV.php';


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

$txtIDH ="";
$txtProducto ="";
$txtFechaRegistro ="";
$txtEstado ="";
$txtOperador ="";
$txtVerificador ="";
$txtCantidades ="";
$txtOrigen ="";
$txtFehaVencimiento ="";
$txtFechaCuarentena ="";
$txtPosicion ="";
$TxtID = $_GET['Ubicacion'];

// Coneccion y asignacion de variables a mostrar

try {
    if (isset($TxtID)) {
        include '../LQS_EUQ/Auth.php';
        $sql = "SELECT IDH,Producto,FechaRegistro,Estado, Operador,Verificador,Cantidades,Origen, FechaVencimiento, FechaCuarentena, Posicion FROM dbs9098416.asignacionesPL where Numero = '" . $TxtID . "'";

        $sentencia = $pdo->prepare($sql,
            array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
        $sentencia->execute();
        $Producto = $sentencia->fetch(PDO::FETCH_LAZY);

        $txtIDH = $Producto['IDH'];
        $txtCodigoDeBarras = $Producto['CodigoDeBarras'];
        $txtProducto = $Producto['Producto'];
        $txtFechaRegistro = $Producto['FechaRegistro'];
        $txtEstado = $Producto['Estado'];
        $txtOperador = $Producto['Operador'];
        $txtVerificador = $Producto['Verificador'];
        $txtCantidades = $Producto['Cantidades'];
        $txtOrigen = $Producto['Origen'];
        $txtFehaVencimiento =  date('d/m/Y', strtotime($Producto['FechaVencimiento']));
        $txtFechaCuarentena =  date('d/m/Y', strtotime($Producto['FechaCuarentena']));
        $txtPosicion = $Producto['Posicion'];

    } else {
        header( 'Location: Print_Cardex.php' ) ;
    }


} catch (Exception $ex) {

    $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
}




// Dar valor a las variabes de Resumen

$txtDiasCuarentena = 0;
$txtDiasVencimiento = 0;

function validarCadena($cadena) {
   $patron = '/^\d-[A-Z]\d+-[A-Z]\d+-[A-Z]\d+$/';    return preg_match($patron, $cadena) === 0;
}


// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {
    case "btnActualizar":
        $txtBodega = (isset($_POST['ListaBodegas'])) ? $_POST['ListaBodegas'] : "";
        $txtCarril = (isset($_POST['txtCarrilINP'])) ? $_POST['txtCarrilINP'] : "";
        $txtPosicionSLC = (isset($_POST['txtPosicionINP'])) ? $_POST['txtPosicionINP'] : "";
        $txtNivel = (isset($_POST['txtNivelINP'])) ? $_POST['txtNivelINP'] : "nada";
        $Ubicacion = $txtBodega."-".$txtCarril."-".$txtPosicionSLC."-".$txtNivel;
        $posicion = $Ubicacion;

        if(validarCadena($posicion)) {
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Ingrese la Ubicaion de forma correcta. No se guardaron los datos </div>';

        } else {
            $txtPosicion = $posicion;

            CorregirUbicacionPL($posicion,$TxtID);
            $Mensajeerror = '<div class="alert alert-success" role="alert"><br><p><strong> Los datos se guardaron correctamente, puede regresar </div>';

        }



        break;

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
        $txtCarril = (isset($_POST['txtCarrilINP'])) ? $_POST['txtCarrilINP'] : "";
        $txtPosicion = (isset($_POST['txtPosicionINP'])) ? $_POST['txtPosicionINP'] : "";
        $txtNivel = (isset($_POST['txtNivelINP'])) ? $_POST['txtNivelINP'] : "nada";
        $Ubicacion = $txtBodega."-".$txtCarril."-".$txtPosicion."-".$txtNivel;
        $posicion = $Ubicacion;

        $fechaRegistro = $fechaConsulta." ".$hora;

        $fechaColocado = null;

        $estado = "Pendiente";

        $operador =  (isset($_POST['txtMontacarguista'])) ? $_POST['txtMontacarguista'] : "";

        $txtBase = (isset($_POST['txtBase'])) ? $_POST['txtBase'] : "";
        $txtAlto = (isset($_POST['txtAlto'])) ? $_POST['txtAlto'] : "";
        $Paletizado = $txtBase * $txtAlto;
        $Unidades = $txtBultos;
        if($Unidades < $Paletizado ){
            $palletCompleto = "No";
        }else{
            $palletCompleto = "Si";
        }

        $cantidades = (isset($_POST['txtTotalBultos'])) ? $_POST['txtTotalBultos'] : "";

        $origen = (isset($_POST['txtOrigen'])) ? $_POST['txtOrigen'] : "";

        $fechaProduccion = $fechaConsulta." ".$hora;

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
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Ingrese la Ubicaion. No se guardaron los datos </div>';


        } else if(validarCadena($posicion)) {
            $Mensajeerror = '<div class="alert alert-danger" role="alert"><br><p><strong> Ingrese la Ubicaion de forma correcta. No se guardaron los datos </div>';

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
        <nav class="navbar navbar-expand-lg navbar-light  ">


            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ">

                    <li class="nav-item active">
                        <a class="btn btn-outline-danger" style="margin-left: 2rem" href="Print_CardexMasivo.php"><span > Regresar </span></a>
                    </li>




                </ul>
            </div>
        </nav>
        <br>

        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">



                        <div class="card-body ">
                            <h4 class="card-title">Editar la Ubicacion de un ingreso</h4>
                            <h6 class="card-subtitle">Detalles del ingreso</h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>
                            <!-- Formulario de datos -->
                            <div class="my-content formulario">

                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>IDH</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtIDH ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">

                                                    <svg  data-value="<?php echo $txtIDH ?>" data-text="<?php echo $txtIDH ?>" class="codigo"/></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Material</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtProducto ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha Registro</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaRegistro ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estado</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtEstado ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Operador</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtOperador ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Verificador</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtVerificador ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Cantidades</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtCantidades ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Origen</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtOrigen ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de Vencimiento</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFehaVencimiento ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>FechaCuarentena</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaCuarentena ?>" readonly="" required>
                                                </div>
                                            </div>

                                        </div><!--INICIO Row para Elemento de formulario -->
                                        <div class="row" style="text-align: center; font-size: 25px">

                                            <div class="col-md-12" style="display: flex;
  justify-content: center;
  align-items: center;; font-size: 25px ">
                                                <div class="form-group">
                                                    <label>Posicion asignada actualmente</label>
                                                    <input type="text"
                                                           class="form-control h3" style="text-align: center; font-size: 25px"
                                                           value="<?php echo $txtPosicion ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group" style="text-align: center">
                                                <label>Fotografia</label>
                                                <div class="input-group mb-12">
                                                    <?php if ($txtIDH != "") { ?>
                                                        <br/>
                                                        <img style="border-radius: 45px !important;"
                                                             class="img-thumbnail rounded mx-auto d-block" width="200px"
                                                             src="../assets/images/Productos/<?php echo $txtIDH.".jpg"; ?>">

                                                        <br/>
                                                        <br/>
                                                    <?php } ?>


                                                </div>
                                            </div>
                                        </div>

                                    </div>



                            </div>
                            <!-- Fin Formulario de datos -->
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
                            <h4 class="card-title">Nueva Ubicacion Para el producto </h4>
                            <h6 class="card-subtitle">Seleccione la ubicacion donde se colocara el producto</h6>
                            <form role="form" action="" method="post" enctype="multipart/form-data">
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
                                                <button type="submit" value="btnActualizar" name="accion" class="btn btn-outline-success">Actualizar Ubicacion
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



</body>

</html>