<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$Mensajeerror = "";

// Variables de entorno

$NoGuia = isset($_GET["Guia"]) ? $_GET["Guia"] : null;

function determinarColor() {
    // Obtener el total de registros en la tabla

    include '../LQS_EUQ/Auth.php';
    include '../LQS_EUQ/Connect.php';

// Create connection
    $conexion = new mysqli($servername, $username, $password, $dbname);


    $query = "SELECT COUNT(*) AS total FROM Guias";
    $resultado = mysqli_query($conexion, $query);
    $fila = mysqli_fetch_assoc($resultado);
    $totalRegistros = $fila['total'];

    // Calcular el 5% del total
    $cincoPorCiento = ceil($totalRegistros * 0.05);

    // Obtener el total de registros rojos
    $query = "SELECT COUNT(*) AS total_rojos FROM Guias WHERE ConteoCiegoPost = 'rojo'";
    $resultado = mysqli_query($conexion, $query);
    $fila = mysqli_fetch_assoc($resultado);
    $totalRojos = $fila['total_rojos'];

    // Determinar el color del nuevo registro
    if ($totalRojos < $cincoPorCiento) {
        return 'rojo';
    } else {
        return 'verde';
    }
}


// Datos generales

try {

     if (isset($_GET['Guia'])) {
         $sql = "select * from  Guias where Transporte ='" . $NoGuia . "' ;";

         $sentencia = $pdo->prepare($sql,
             array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
         $sentencia->execute();

         $Result = $sentencia->fetch(PDO::FETCH_LAZY);

         $txtFechaPedido = $Result['FechaPedido'];
         $txtFechaEntrega = $Result['FechaEngrega'];
         $txtDestino = $Result['NombreDestino'];
         $Direccion = $Result['Direccion'];
         $txtLugar = $Result['Lugar'];
         $txtTransportista = $Result['Transportista'];
         $txtPiloto = $Result['Piloto'];
         $txtRampa = $Result['Rampa'];
         $txtMontacarguista = $Result['Montacarguista'];
         $txtEstatus = $Result['Estatus'];
         $txtPlaca = $Result['Placa'];
         $txtMarchamo = $Result['Marchamo'];
         $txtFactura = $Result['Factura'];
         $txtVerificador = $Result['Verificador'];
         $txtChequeo = $Result['RespChequeo'];
         $txtPrepara = $Result['RespPrepara'];
         $txtAyudante = $Result['Ayudante'];
         $txtDPIPiloto = $Result['DPIPiloto'];
         $txtDPI = $Result['DPIPiloto'];
         $txtPlacaFurgon = $Result['PlacaFurgon'];
         $txtConteoCiegoPrevio = $Result['ConteoCiegoPre'];
         $txtConteoCiegoPost = $Result['ConteoCiegoPost'];
     }


}catch (Exception $ex){

    $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
}

// Datos del detalle

try {

    if (isset($_GET['Guia'])) {
        $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);

        $sqlDatos = "
    SELECT 
        Transporte, Entrega, Material, productos.Descripcion, DetalleGuias.cajas, DetalleGuias.PesoNeto, PesoBruto, DetalleGuias.Estatus, DetalleGuias.Ubicacion, Tipo, posiciones.FechaProduccion
    FROM
        DetalleGuias 
    JOIN productos
        ON DetalleGuias.Material = productos.IDH
     left join posiciones
        ON DetalleGuias.Ubicacion = posiciones.Ubicacion
    WHERE
        Transporte = '" . $NoGuia . "' 
    ORDER BY
        Tipo,Material
 ";
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



// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case "btnActualizarGuia":

        $txtPiloto = (isset($_POST['txtPiloto'])) ? $_POST['txtPiloto'] : "";
        $Placa = (isset($_POST['txtPlaca'])) ? $_POST['txtPlaca'] : "";
        $Marchamo = (isset($_POST['txtMarchamo'])) ? $_POST['txtMarchamo'] : "";
        $Factura = (isset($_POST['txtFactura'])) ? $_POST['txtFactura'] : "";
        $Verificador= (isset($_POST['txtVerificador'])) ? $_POST['txtVerificador'] : "";
        $Chequeo = (isset($_POST['txtRespChequeo'])) ? $_POST['txtRespChequeo'] : "";
        $Preparar = (isset($_POST['txtPreparar'])) ? $_POST['txtPreparar'] : "";
        $Ayudante = (isset($_POST['txtAyudante'])) ? $_POST['txtAyudante'] : "";
        $Trasportista = (isset($_POST['txtTransportista'])) ? $_POST['txtTransportista'] : "";

        $txtPlacaFurgon = (isset($_POST['txtPlacaFurgon'])) ? $_POST['txtPlacaFurgon'] : "";
        $DPI = (isset($_POST['txtDPI'])) ? $_POST['txtDPI'] : "";



        $sentencia = $pdo->prepare('UPDATE Guias set Transportista=:parTransportista, Piloto =:parPiloto, Placa = :parPlaca, Marchamo = :parMarchamo, Factura = :parFactura, Verificador = :parVerificador, RespChequeo = :parChequeo, RespPrepara = :parPrepara, Ayudante = :parAyudante, PlacaFurgon= :parFurgon, DPIPiloto = :parDPIPiloto where Transporte =:parTransporte ');

        $sentencia->bindParam(':parPiloto', $txtPiloto);
        $sentencia->bindParam(':parPlaca', $Placa);
        $sentencia->bindParam(':parMarchamo', $Marchamo);
        $sentencia->bindParam(':parFactura', $Factura);
        $sentencia->bindParam(':parVerificador', $Verificador);
        $sentencia->bindParam(':parChequeo', $Chequeo);
        $sentencia->bindParam(':parPrepara', $Preparar);
        $sentencia->bindParam(':parAyudante', $Ayudante);
        $sentencia->bindParam(':parTransporte', $NoGuia);
        $sentencia->bindParam(':parFurgon', $txtPlacaFurgon);
        $sentencia->bindParam(':parDPIPiloto', $DPI);
        $sentencia->bindParam(':parTransportista', $Trasportista);


        try {
            $sentencia->execute();

            header("Location: #");




        }catch (Exception $exception){
            echo $exception->getMessage();
        }

        $sql = "select * from  Guias where Transporte ='" . $NoGuia . "' ;";

        $sentencia = $pdo->prepare($sql,
            array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
        $sentencia->execute();

        $Result = $sentencia->fetch(PDO::FETCH_LAZY);

        $txtFechaPedido = $Result['FechaPedido'];
        $txtFechaEntrega = $Result['FechaEngrega'];
        $txtDestino = $Result['NombreDestino'];
        $Direccion = $Result['Direccion'];
        $txtLugar = $Result['Lugar'];
        $txtTransportista = $Result['Transportista'];
        $txtPiloto = $Result['Piloto'];
        $txtRampa = $Result['Rampa'];
        $txtMontacarguista = $Result['Montacarguista'];

        $txtPlaca = $Result['Placa'];
        $txtMarchamo = $Result['Marchamo'];
        $txtFactura = $Result['Factura'];
        $txtVerificador = $Result['Verificador'];
        $txtChequeo = $Result['RespChequeo'];
        $txtPrepara = $Result['RespPrepara'];
        $txtAyudante = $Result['Ayudante'];


        // Abrir nueva pesta;a con parametro de valor de guia de carga


        break;

    case "btnModificar":

        $txtPiloto = (isset($_POST['txtPiloto'])) ? $_POST['txtPiloto'] : "";
        $Placa = (isset($_POST['txtPlaca'])) ? $_POST['txtPlaca'] : "";
        $Marchamo = (isset($_POST['txtMarchamo'])) ? $_POST['txtMarchamo'] : "";
        $Factura = (isset($_POST['txtFactura'])) ? $_POST['txtFactura'] : "";
        $Verificador= (isset($_POST['txtVerificador'])) ? $_POST['txtVerificador'] : "";
        $Chequeo = (isset($_POST['txtRespChequeo'])) ? $_POST['txtRespChequeo'] : "";
        $Preparar = (isset($_POST['txtPreparar'])) ? $_POST['txtPreparar'] : "";
        $Ayudante = (isset($_POST['txtAyudante'])) ? $_POST['txtAyudante'] : "";

        $txtPlacaFurgon = (isset($_POST['txtPlacaFurgon'])) ? $_POST['txtPlacaFurgon'] : "";
        $DPI = (isset($_POST['txtDPI'])) ? $_POST['txtDPI'] : "";



        $sentencia = $pdo->prepare('UPDATE Guias set Piloto =:parPiloto, Placa = :parPlaca, Marchamo = :parMarchamo, Factura = :parFactura, Verificador = :parVerificador, RespChequeo = :parChequeo, RespPrepara = :parPrepara, Ayudante = :parAyudante, PlacaFurgon= :parFurgon, DPIPiloto = :parDPIPiloto   where Transporte =:parTransporte ');

        $sentencia->bindParam(':parPiloto', $txtPiloto);
        $sentencia->bindParam(':parPlaca', $Placa);
        $sentencia->bindParam(':parMarchamo', $Marchamo);
        $sentencia->bindParam(':parFactura', $Factura);
        $sentencia->bindParam(':parVerificador', $Verificador);
        $sentencia->bindParam(':parChequeo', $Chequeo);
        $sentencia->bindParam(':parPrepara', $Preparar);
        $sentencia->bindParam(':parAyudante', $Ayudante);
        $sentencia->bindParam(':parTransporte', $NoGuia);
        $sentencia->bindParam(':parFurgon', $txtPlacaFurgon);
        $sentencia->bindParam(':parDPIPiloto', $DPI);

        try {
            $sentencia->execute();

            header("Location: ../FPDF2/RE-10-09.php?Guia=".$NoGuia);


        }catch (Exception $exception){
            echo $exception->getMessage();
        }

        $sql = "select * from  Guias where Transporte ='" . $NoGuia . "' ;";

        $sentencia = $pdo->prepare($sql,
            array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
        $sentencia->execute();

        $Result = $sentencia->fetch(PDO::FETCH_LAZY);

        $txtFechaPedido = $Result['FechaPedido'];
        $txtFechaEntrega = $Result['FechaEngrega'];
        $txtDestino = $Result['NombreDestino'];
        $Direccion = $Result['Direccion'];
        $txtLugar = $Result['Lugar'];
        $txtTransportista = $Result['Transportista'];
        $txtPiloto = $Result['Piloto'];
        $txtRampa = $Result['Rampa'];
        $txtMontacarguista = $Result['Montacarguista'];

        $txtPlaca = $Result['Placa'];
        $txtMarchamo = $Result['Marchamo'];
        $txtFactura = $Result['Factura'];
        $txtVerificador = $Result['Verificador'];
        $txtChequeo = $Result['RespChequeo'];
        $txtPrepara = $Result['RespPrepara'];
        $txtAyudante = $Result['Ayudante'];


        // Abrir nueva pesta;a con parametro de valor de guia de carga



        break;

    case "btnModificar2":

        $DPIPiloto = (isset($_POST['txtVALDPIPiloto'])) ? $_POST['txtVALDPIPiloto'] : "";
        $PlacaFurgon = (isset($_POST['txtVALPlacaFurgon'])) ? $_POST['txtVALPlacaFurgon'] : "";
        try {
        $sentencia = $pdo->prepare('UPDATE Guias set PlacaFurgon = :parPlacaFurgon, DPIPiloto = :parDPIPiloto where Transporte =:parTransporte ');

        $sentencia->bindParam(':parDPIPiloto', $DPIPiloto);
        $sentencia->bindParam(':parPlacaFurgon', $PlacaFurgon);
        $sentencia->bindParam(':parTransporte', $NoGuia);


            $sentencia->execute();


            header("Location: ../FPDF2/RE-10-10.php?Guia=".$NoGuia);




        }catch (Exception $exception){
            echo $exception->getMessage();
        }

break;
        case  "btnCerrarGuia" ;

        // 0. Validar que la guia no esta comoletada "Cerrada"
            $sql = "SELECT Count(*) as Pendientes FROM DetalleGuias where Transporte = $NoGuia and Estatus = '';;";

            $sentencia = $pdo->prepare($sql,
                array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
            $sentencia->execute();

            $Result = $sentencia->fetch(PDO::FETCH_LAZY);

            $Pendientes = $Result['Pendientes'];

            if($Pendientes > 0){

                $Mensajeerror = '<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Por el momento no se puede  Cerrar la Guia! -- </strong> Antes de cerrar debe despachar todos los productos destinados a esta guia, actualmente tiene '. $Pendientes .'  registros pendientes de despachar
                                </div>';
                break;


            }else {
                // Calcular el valor de  cemaforo 2 y cerrar la guia


                $Color = determinarColor();

                $sql = "update  Guias  set ConteoCiegoPost = '$Color',Estatus = 'Despachado' where Transporte = $NoGuia and Estatus = 'Despachando' ;";
                $sentencia = $pdo->prepare($sql,
                array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
                $sentencia->execute();


                if (isset($_GET['Guia'])) {
                    $sql = "select * from  Guias where Transporte ='" . $NoGuia . "' ;";

                    $sentencia = $pdo->prepare($sql,
                        array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
                    $sentencia->execute();

                    $Result = $sentencia->fetch(PDO::FETCH_LAZY);

                    $txtFechaPedido = $Result['FechaPedido'];
                    $txtFechaEntrega = $Result['FechaEngrega'];
                    $txtDestino = $Result['NombreDestino'];
                    $Direccion = $Result['Direccion'];
                    $txtLugar = $Result['Lugar'];
                    $txtTransportista = $Result['Transportista'];
                    $txtPiloto = $Result['Piloto'];
                    $txtRampa = $Result['Rampa'];
                    $txtMontacarguista = $Result['Montacarguista'];

                    $txtPlaca = $Result['Placa'];
                    $txtMarchamo = $Result['Marchamo'];
                    $txtFactura = $Result['Factura'];
                    $txtVerificador = $Result['Verificador'];
                    $txtChequeo = $Result['RespChequeo'];
                    $txtPrepara = $Result['RespPrepara'];
                    $txtAyudante = $Result['Ayudante'];
                    $txtDPIPiloto = $Result['DPIPiloto'];
                    $txtPlacaFurgon = $Result['PlacaFurgon'];
                    $txtConteoCiegoPrevio = $Result['ConteoCiegoPre'];
                    $txtConteoCiegoPost = $Result['ConteoCiegoPost'];
                }


                 $Mensajeerror = '<div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se Despacho y Cerro correctamente  la Guia️! -- </strong> Se Asigno un color de '.$Color.' Por favor proceder conforme a lo establecido en el procedimiento.
                                </div>';

                }





        break;

    case "cambiarMontacarguista":
        $nuevoMontacarguista = isset($_POST['nuevoMontacarguista']) ? $_POST['nuevoMontacarguista'] : "";

        try {
            $sentencia = $pdo->prepare('UPDATE Guias SET Montacarguista = :nuevoMontacarguista WHERE Transporte = :parTransporte');
            $sentencia->bindParam(':nuevoMontacarguista', $nuevoMontacarguista);
            $sentencia->bindParam(':parTransporte', $NoGuia);
            $sentencia->execute();

            $Mensajeerror = '<div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <strong>Montacarguista actualizado correctamente!</strong>
                            </div>';
        } catch (Exception $exception) {
            $Mensajeerror = '<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <strong>Error al actualizar el montacarguista:</strong> ' . $exception->getMessage() . '
                            </div>';
        }
        break;

    default :
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
    <title>Henkel CBP / AdminFIFO</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
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

        .Oculto {
            display: none;
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

                        <div class="card-body">
                            <h4 class="card-title">Detalles de la Guia: <?php echo $NoGuia; ?></h4>

                            <?php echo $Mensajeerror;?>


                            
                                <h6 class="card-subtitle">Datos generales de la guia </h6>

                                <!--                                    Barra de acciones-->
                                <nav class="navbar navbar-expand-lg text-center">


                                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                        <span class="navbar-toggler-icon"></span>
                                    </button>
                                    <div class="collapse navbar-collapse" id="navbarNav">
                                        <ul class="navbar-nav ">

                                            <li class="nav-item active">
                                                <a class="btn btn-outline-danger" style="margin-left: 2rem" href="Traking_Guias.php"><span > Regresar </span></a>
                                            </li>


                                            <?php
                                                if($txtEstatus == 'Despachado'){

                                                }else{
                                                    echo '<li class="nav-item active">
                                                <a class="btn btn-outline-info" style="margin-left: 2rem" href="#" data-toggle="modal" data-target="#myModal"><span>Generar RE 10-09</span></a>
                                            </li>';
                                                }
                                            ?>


                                            <!-- Botón que muestra el modal -->


                                            <!-- Modal -->
                                            <div id="myModal" class="modal" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="" method="post" enctype="multipart/form-data">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Completar Informacion de Guia para formato RE 10-09</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body" style="text-align: left">

                                                                <div class="form-body">
                                                                    <div style="display: flex;">
                                                                        <div style="flex: 1;">
                                                                            <div class="form-group">
                                                                            <label>Nombre Piloto:</label>
                                                                            <br>
                                                                            <input name="txtPiloto" type="text" value="<?php echo $txtPiloto?>" >
                                                                            </div>

                                                                            <div class="form-group Oculto">
                                                                                <label>DPI Piloto:</label>
                                                                                <br>
                                                                                <input name="txtDPI" type="text" value="<?php echo $txtDPI?>" >
                                                                            </div>


                                                                            <div class="form-group">
                                                                            <label>Transporte:</label>
                                                                            <br>
                                                                            <input name="txtTransportista" type="text" value="<?php echo $txtTransportista?>" >
                                                                            </div>



                                                                            <div class="form-group">
                                                                            <label>No. de Placa Cabezal:</label>
                                                                            <br>
                                                                            <input name="txtPlaca" type="text"  value="<?php echo $txtPlaca?>" >
                                                                            </div>


                                                                            <div class="form-group">
                                                                                <label>No. de Placa Furgon:</label>
                                                                                <br>
                                                                                <input name="txtPlacaFurgon" type="text"  value="<?php echo $txtPlacaFurgon?>" >
                                                                            </div>



                                                                            <div class="form-group">
                                                                            <label>Marchamo:</label>
                                                                            <br>
                                                                            <input name="txtMarchamo" value="<?php echo $txtMarchamo?>" type="text" >
                                                                            </div>



                                                                            <div class="form-group">
                                                                            <label>Destino:</label>
                                                                            <br>
                                                                            <input type="text" value="<?php echo $txtDestino?>" disabled readonly>
                                                                            </div>



                                                                            <div class="form-group Oculto">
                                                                            <label>Factura:</label>
                                                                            <br>
                                                                            <input name="txtFactura" value="<?php echo $txtFactura?>" type="text" >
                                                                            </div>


                                                                        </div>

                                                                        <div style="flex: 1;">
                                                                            <div class="form-group">
                                                                            <label>Verificador:</label>
                                                                            <br>
                                                                            <input name="txtVerificador" value="<?php echo $txtVerificador?>" type="text" value="" >
                                                                            </div>


                                                                            <div class="form-group">
                                                                            <label>Responsable de Chequeo:</label>
                                                                            <br>
                                                                            <input name="txtRespChequeo" value="<?php echo $txtChequeo?>" type="text" value="" >
                                                                            </div>



                                                                            <div class="form-group">
                                                                            <label>Responsable de Preparar:</label>
                                                                            <br>
                                                                            <input name="txtPreparar"  value="<?php echo $txtPrepara?>" type="text" value="" >
                                                                            </div>



                                                                            <div class="form-group">
                                                                            <label>Montacarguista Responsable:</label>
                                                                            <br>
                                                                            <input type="text" value="<?php echo $txtMontacarguista?>" readonly disabled>
                                                                            </div>



                                                                            <div class="form-group">
                                                                            <label>Ayudante Responsable:</label>
                                                                            <br>
                                                                            <input name="txtAyudante"  value="<?php echo $txtAyudante?>" type="text" value="" >
                                                                            </div>



                                                                            <div class="form-group">
                                                                                <label>Fecha de despacho:</label>
                                                                                <br>
                                                                                <input name="txtFechaDespacho" type="date" id="fechaDespacho" value="">
                                                                            </div>

                                                                            <script>
                                                                                // Obtén la fecha actual en formato YYYY-MM-DD
                                                                                function obtenerFechaActual() {
                                                                                    const fecha = new Date();
                                                                                    const dia = fecha.getDate().toString().padStart(2, '0');
                                                                                    const mes = (fecha.getMonth() + 1).toString().padStart(2, '0'); // Se suma 1 porque los meses van de 0 a 11
                                                                                    const anio = fecha.getFullYear();
                                                                                    return `${anio}-${mes}-${dia}`;
                                                                                }

                                                                                // Establece el valor del campo de fecha al día actual
                                                                                document.getElementById('fechaDespacho').value = obtenerFechaActual();
                                                                            </script>



                                                                            <div class="form-group">
                                                                            <label>Guía:</label>

                                                                            <input type="text" value="<?php echo $NoGuia?>" disabled>
                                                                        </div>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
                                                            <button type="submit" value="btnActualizarGuia" name="accion"
                                                                    class="btn btn-outline-info">Guardar
                                                            </button>
                                                            <button type="submit" value="btnModificar" name="accion"
                                                                    class="btn btn-outline-success">Generar
                                                            </button>

                                                        </div>
                                                        </form>
                                                    </div>
                                                    </div>
                                                </div>


                                            <!-- Agregar los archivos JavaScript de Bootstrap -->
                                            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
                                            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
                                            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


                                            <?php
                                            if($txtEstatus == 'Despachado'){

                                            }else{
                                                echo '<li class="nav-item active">
                                                <a class="btn btn-outline-info" style="margin-left: 2rem" href="#" data-toggle="modal" data-target="#myModal2"><span>Generar RE 10-10</span></a>
                                            </li>
                                            
                                            <li class="nav-item active">
                                                <a class="btn btn-outline-warning" style="margin-left: 2rem" href="RegistroPiking.php?Guia='.$NoGuia.' "<span > Registrar Piking </span></a>
                                            </li>

                                            <li class="nav-item active">
                                                <form action="" method="post" enctype="multipart/form-data">
                                                <button type="submit" value="btnCerrarGuia" name="accion" style="margin-left: 2rem"
                                                        class="btn btn-outline-success">Cerrar / Terminar Guia
                                                </button>

                                                </form>
                                            </li>';
                                            }
                                            ?>






                                            <!-- Modal -->
                                            <div id="myModal2" class="modal" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="" method="post" enctype="multipart/form-data">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Completar Informacion de Guia para formato RE 10-10</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body" style="text-align: left">

                                                                <div class="form-body">
                                                                    <div style="display: flex;">
                                                                        <div style="flex: 1;">
                                                                            <div class="form-group Oculto   ">
                                                                                <label>DPI Piloto:</label>
                                                                                <br>
                                                                                <input name="txtVALDPIPiloto" type="text" value="<?php echo $txtDPIPiloto?>" >
                                                                            </div>


                                                                            <div class="form-group">
                                                                                <label>Placa del Furgon:</label>
                                                                                <br>
                                                                                <input name="txtVALPlacaFurgon" type="text" value="<?php echo $txtPlacaFurgon?>">
                                                                            </div>






                                                                            <div class="form-group">
                                                                                <label>Guía:</label>

                                                                                <input type="text" value="<?php echo $NoGuia?>" disabled>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
                                                                <button type="submit" value="btnModificar2" name="accion"
                                                                        class="btn btn-outline-success">Generar
                                                                </button>

                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal para cambiar al montacarguista -->
                                            <div id="modalCambiarMontacarguista" class="modal" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="" method="post">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Cambiar Montacarguista</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Nuevo Montacarguista:</label>
                                                                    <select name="nuevoMontacarguista" class="form-control" required>
                                                                        <?php
                                                                        // Obtener los nombres de los usuarios con TipoUsuario = 2
                                                                        $query = "SELECT nombre_usuario FROM usuarios_app WHERE TipoUsuario = 2";
                                                                        $result = $pdo->query($query);
                                                                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                                            echo '<option value="' . $row['nombre_usuario'] . '">' . $row['nombre_usuario'] . '</option>';
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="accion" value="cambiarMontacarguista" class="btn btn-outline-success">Guardar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        </ul>
                                    </div>
                                </nav>
                                <br>

                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha del Pedido</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaPedido ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de la entrega</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtFechaEntrega ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Destino</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtDestino ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Direccion</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $Direccion ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Lugar</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtLugar ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Transportista</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtTransportista ?>" readonly="" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Piloto</label>
                                                    <input name="txtPiloto" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtPiloto ?>"  readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Rampa</label>
                                                    <input name="txtRampa" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtRampa ?>" readonly=""  required>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Montacarguista</label>
                                                    <div style="display: flex; align-items: center;">
                                                        <input name="txtMontacarguista" type="text" class="form-control" value="<?php echo $txtMontacarguista ?>" readonly required>
                                                        <button type="button" class="btn btn-outline-info ml-2" data-toggle="modal" data-target="#modalCambiarMontacarguista">Cambiar</button>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="col-md-3">
                                                    <div class="form-group" style="text-align: center">
                                                        <label>Conteo Ciego Previo</label>
                                                        <br>
                                                        <?php

                                                        if($txtConteoCiegoPrevio == 'verde'){
                                                            echo '<img src="../assets/images/Iconos/circuloVerde.png" class="" --="" width="auto" height="120">';
                                                        }else if($txtConteoCiegoPrevio == 'rojo'){
                                                            echo '<img src="../assets/images/Iconos/circuloRojo.png" class="" --="" width="auto" height="120">';
                                                        } else {
                                                            echo '<img src="../assets/images/Iconos/Montacargas.png" class="" --="" width="auto" height="120">';
                                                        }
                                                        ?>

                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group" style="text-align: center">
                                                        <label>Conteo Ciego Posterior</label>
                                                        <br>
                                                        <?php

                                                        if($txtConteoCiegoPost == 'verde'){
                                                            echo '<img src="../assets/images/Iconos/circuloVerde.png" class="" --="" width="auto" height="120">';
                                                        }else if($txtConteoCiegoPost =='rojo'){
                                                            echo '<img src="../assets/images/Iconos/circuloRojo.png" class="" --="" width="auto" height="120">';
                                                        } else {
                                                            echo '<img src="../assets/images/Iconos/Montacargas.png" class="" --="" width="auto" height="120">';
                                                        }
                                                        ?>

                                                    </div>
                                                </div>
                                        </div>

                                    </div>
                                </form>

                            
                        </div>

                        <div class="card">

                            <div class="card-body">
                                <h4 class="card-title">Detalles del pedido</h4>
                                <h6 class="card-subtitle">valide los registros relacionados con el pedido  </h6>
                                <br>
                                <!-- Start First Cards -->
                                <div class="row">
                                    <!-- Column -->
                                    <div class="col-md-12">


                                        <table id="example" class="table table-striped  " cellspacing="0" width="100%">
                                            <thead>


                                            <th>Material</th>
                                            <th>Descripcion</th>
                                            <th>Cajas / Unidades</th>
                                            <th>Peso Neto</th>
                                            <th>Peso Bruto</th>
                                            <th>Despachado</th>
                                            <th>Tipo de Despacho</th>
                                            <th>Ubicacion</th>
                                            <th>Fecha FIFO</th>






                                            </thead>
                                            <tbody>
                                            <?php
                                            for ($i = 0; $i < $lista_Guias; $i++) {
                                                echo "<tr>";

                                                echo "<td>";
                                                echo $lista_Guias['Material'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['Descripcion'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['cajas'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['PesoNeto'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['PesoBruto'];
                                                echo "</td>";

                                                echo "<td>";
                                                {
                                                    //Estatus del Producto
                                                    switch ($lista_Guias['Estatus']){
                                                        case 'Pendiente' :
                                                            echo '<img src="../assets/images/Iconos/circuloNaranja.png" class="" --="" width="auto" height="40">';
                                                            break;

                                                        case 'Despachado' :
                                                            echo '<img src="../assets/images/Iconos/circuloVerde.png" class="" --="" width="auto" height="40">';
                                                            break;

                                                        default :
                                                            echo '<img src="../assets/images/Iconos/circuloRojo.png" class="" --="" width="auto" height="40">';
                                                            break;

                                                    }
                                                }
                                                echo "</td>";

                                                {
                                                    // Validacion por Tipo y Ubicacion
                                                    switch ($lista_Guias['Ubicacion']){
                                                        case '' :
                                                            echo "<td>";
                                                            echo $lista_Guias['Tipo'];
                                                            echo "</td>";

                                                            echo "<td>";
                                                            echo "No hay ubicacion despachable para este IDH";
                                                            echo "</td>";

                                                            echo "<td>";
                                                            echo "";
                                                            echo "</td>";

                                                            break;

                                                        default :
                                                            echo "<td>";
                                                            echo $lista_Guias['Tipo'];
                                                            echo "</td>";

                                                            echo "<td>";
                                                            echo $lista_Guias['Ubicacion'];
                                                            echo "</td>";


                                                            {
                                                                //Fecha de validacion
                                                                switch ($lista_Guias['Tipo']){
                                                                    case 'Piking' :
                                                                        echo "<td>";

                                                                        echo '';
                                                                        echo "</td>";
                                                                        break;

                                                                    case 'Pallets' :
                                                                        echo "<td>";

                                                                        echo date('d/m/Y', strtotime($lista_Guias['FechaProduccion']));

                                                                        echo "</td>";
                                                                        break;

                                                                    default :

                                                                        break;

                                                                }
                                                            }
                                                            break;

                                                    }


                                                }





                                                echo "</tr>";

                                                $lista_Guias = $ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
                                            }
                                            ?>
                                            </tbody>
                                        </table>




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
            $('#example').DataTable({
                language: {
                    url: 'datatables_espanol.json'
                }

            });
        });
    </script>

    <script>
        document.querySelector('input[type="submit"]').addEventListener('click', function(event) {
            if (!document.querySelector('input[type="file"]').files.length) {
                event.preventDefault();
                $("#errorModal").modal("show");
            }
        });
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