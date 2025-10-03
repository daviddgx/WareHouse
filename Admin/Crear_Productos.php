<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}

include '../LQS_EUQ/Auth.php';
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';
$error = '';

$txtIDH =  "";

$txtDescripcion = "";
$txtMarca = "";
$txtLINEA =  "";
$txtUnidadPorMedida =  "";
$txtUnidadDeMedida =  "";
$txtBase =  "";
$txtAltura =  "";
$txtCajasPorPalet = "";
$txtPesoNetoUnitario =  "";
$txtPesoBrutoUnitario =  "";
$txtPesoNetoCaja = "";
$txtPesoBrutoCaja = "";
$txtFoto =  "";
$txtDiasCuarentena = "";
$txtDiasVencimiento =  "";
$txtEstado =  "";
$txtUnidadesMinimas = "";
$txtUnidadesMaximas =  "";


// Cargar datos a mostrar

// Creamos la conexion


//comprovacion de dadtos
//fin comprovacion de datos
// Fin de la conexion


// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case "btnRegistrar":
        try{
        $txtIDH = (isset($_POST['txtIDH'])) ? $_POST['txtIDH'] : "";
        $txtDescripcion = (isset($_POST['txtDescripcion'])) ? $_POST['txtDescripcion'] : "";
        $txtMarca = (isset($_POST['txtMarca'])) ? $_POST['txtMarca'] : "";
        $txtLINEA = (isset($_POST['txtLINEA'])) ? $_POST['txtLINEA'] : "";
        $txtUnidadPorMedida = (isset($_POST['txtUnidadPorMedida'])) ? $_POST['txtUnidadPorMedida'] : "";
        $txtUnidadDeMedida = (isset($_POST['txtUnidadDeMedida'])) ? $_POST['txtUnidadDeMedida'] : "";
        $txtBase = (isset($_POST['txtBase'])) ? $_POST['txtBase'] : "";
        $txtAltura = (isset($_POST['txtAltura'])) ? $_POST['txtAltura'] : "";
        $txtCajasPorPalet =$txtBase*$txtAltura;
        $txtPesoNetoUnitario = (isset($_POST['txtPesoNetoUnitario'])) ? $_POST['txtPesoNetoUnitario'] : "";
        $txtPesoBrutoUnitario = (isset($_POST['txtPesoBrutoUnitario'])) ? $_POST['txtPesoBrutoUnitario'] : "";
        $txtPesoNetoCaja = (isset($_POST['txtPesoNetoCaja'])) ? $_POST['txtPesoNetoCaja'] : "";
        $txtPesoBrutoCaja = (isset($_POST['txtPesoBrutoCaja'])) ? $_POST['txtPesoBrutoCaja'] : "";
        $txtFoto = $txtIDH . ".JPG" ;
        $txtDiasCuarentena = (isset($_POST['txtDiasCuarentena'])) ? $_POST['txtDiasCuarentena'] : "";
        $txtDiasVencimiento = (isset($_POST['txtDiasVencimiento'])) ? $_POST['txtDiasVencimiento'] : "";
        $txtEstado = (isset($_POST['txtEstado'])) ? $_POST['txtEstado'] : "";
        $txtUnidadesMinimas = (isset($_POST['txtUnidadesMinimas'])) ? $_POST['txtUnidadesMinimas'] : "";
        $txtUnidadesMaximas = (isset($_POST['txtUnidadesMaximas'])) ? $_POST['txtUnidadesMaximas'] : "";



        //Actualizar datos del registro

        $SQL = "insert into dbs9098416.productos (IDH,CodigoDeBarras,Descripcion,Marca,LINEA,UNIDADESXMEDIDA,UMEDIDA,BASE,ALTURA,CAJASXPALET,PESONETOUNIDAD,PESOBRUTOUNIDAD, PESONETOCAJA, PESOBRUTOCAJA, FOTO, DIASCUARENTENA, DIASVENCIMIENTO, ESTADO, MINIMOPICKING,MAXIMOPICKING ) values (".$txtIDH.", ".$txtIDH.", '".$txtDescripcion."',  '".$txtMarca."', '".$txtLINEA."', ".$txtUnidadPorMedida.", '".$txtUnidadDeMedida."', ".$txtBase.", ".$txtAltura.", ".$txtCajasPorPalet.", ".$txtPesoNetoUnitario.", ".$txtPesoBrutoUnitario.", ".$txtPesoNetoCaja.", ".$txtPesoBrutoCaja.", '".$txtFoto."', ".$txtDiasCuarentena.", ".$txtDiasVencimiento.",'".$txtEstado."', ".$txtUnidadesMinimas.", ".$txtUnidadesMaximas.")" ;

        $sentencia = $pdo->prepare($SQL);


            $sentencia->execute();

            $MensajeExito = '<div class="alert alert-secondary" role="alert">
                                    <strong>Excelente   -- </strong> Informcaion del producto Guardada.
                                </div>';
        } catch (Exception $ex) {


            $MensajeExito = '<div class="alert alert-warning" role="alert">
                                                <strong>Se genero un error:  -- </strong> Valide el mensaje de arriba  '.$ex.'
                                            </div>';

        }

         try {
            // Bloque para actualizar la foto
            date_default_timezone_set('America/Guatemala');
            $fecha = new DateTime();
             $nombreArchivo = $txtIDH . ".jpg";

             $tmpFoto = $_FILES["txtFoto"]["tmp_name"];

            if ($tmpFoto != "") {
                move_uploaded_file($tmpFoto, "../assets/images/Productos/" . $nombreArchivo);

                if (isset($Usuario["Foto"])) {
                    if (file_exists("../assets/images/Productos/" . $Usuario["Foto"])) {
                        if ($Usuario["Foto"] != "imagen.jpg") {
                            unlink("../assets/images/Productos/" . $Usuario["Foto"]);
                        }
                    }
                }

                $sentencia = $pdo->prepare("UPDATE dbs9098416.productos SET Foto=:Foto where IDH=:id;");
                $sentencia->bindParam(':Foto', $nombreArchivo);
                $sentencia->bindParam(':id', $txtIDH);
                echo'<br>';

                $sentencia->execute();
                $_SESSION['pic'] = $nombreArchivo ;
                $MensajeExito = '<div class="alert alert-success" role="alert">
                                                <strong>Excelente!   -- </strong> Los datos se guardaron correctamente
                                            </div>';

                

                // Redireccionar a la página después de 7 segundos
                header('Location: Mantenimiento_Productos.php');
                
            }


        }catch (Exception $ex) {


            $MensajeExito = '<div class="alert alert-warning" role="alert">
                                                <strong>Se genero un error:  -- </strong> Valide el mensaje de arriba '.$ex.'
                                            </div>';
          //  header('Location: Mantenimiento_Productos.php');
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
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">

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
<!-- ==============================================================
<div class="preloader">
    <div class="lds-ripple">
        <div class="preloader">
            <br></br>
            <div class="logoPre">
                <img src="../assets/images/Sertero/LogoHenkel.png" width="300px" height="auto">
                 Sertero<span style="color:#e88733">CBP</span>
            </div>
            <div class="loader-frame">
                <div class="loader1" id="loader1"></div>
                <div class="loader2" id="loader2"></div>
            </div>
        </div>
    </div>
</div>  -->
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

                        <div class="card-body">
                            <h4 class="card-title">Mantenimiento de  Producto</h4>
                            <h6 class="card-subtitle">Crear un producto nuevo</h6>

                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>
                            <!-- Formulario de datos -->
                            <!-- Formulario de datos -->
                            <div class="my-content formulario">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-body">


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>IDH</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           pattern="[0-9]{6,7}"
                                                           placeholder="1234567..."
                                                           value="<?php echo $txtIDH; ?>"  required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Descripcion</label>
                                                    <input name="txtDescripcion" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtDescripcion; ?>"  required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Marca</label>

                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtMarca" id="idtxtMarca" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- MARCA ---
                                                        </option>

                                                        <?php
                                                        $conn = new mysqli($servername,$username,$password,$dbname);
                                                        $cargos = "SELECT distinct(Marca) FROM dbs9098416.productos;";

                                                        $result = $conn->query($cargos);
                                                        if ($result->num_rows > 0) {
                                                            while ($row = $result->fetch_assoc()) {

                                                                echo '<option value="'.$row['Marca'].'">'.utf8_encode($row['Marca']).'</option>';

                                                            }
                                                        }
                                                        ?>
                                                        <option style="" value="nueva" >
                                                            --- REGISTRAR NUEVA ---
                                                        </option>

                                                    </select>

                                                </div>
                                            </div>
                                        </div>

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Linea</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtLINEA" id="idtxtLinea" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- LINEA ---
                                                        </option>

                                                        <?php
                                                        $conn = new mysqli($servername,$username,$password,$dbname);
                                                        $cargos = "SELECT distinct(linea) as linea FROM dbs9098416.productos;";

                                                        $result = $conn->query($cargos);
                                                        if ($result->num_rows > 0) {
                                                            while ($row = $result->fetch_assoc()) {

                                                                echo '<option value="'.$row['linea'].'">'.utf8_encode($row['linea']).'</option>';

                                                            }
                                                        }
                                                        ?>
                                                        <option value="nueva" class="ng-binding">
                                                            --- REGISTRAR NUEVA ---
                                                        </option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Unidad de Medida</label>
                                                    <select  class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtUnidadDeMedida" id="idtxtUMedida" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled" required>
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- UNIDAD DE MEDIDA ---
                                                        </option>

                                                        <?php
                                                        $conn = new mysqli($servername,$username,$password,$dbname);
                                                        $cargos = "SELECT distinct(UMEDIDA) as UMEDIDA FROM dbs9098416.productos;";

                                                        $result = $conn->query($cargos);
                                                        if ($result->num_rows > 0) {
                                                            while ($row = $result->fetch_assoc()) {

                                                                echo '<option value="'.$row['UMEDIDA'].'">'.utf8_encode($row['UMEDIDA']).'</option>';

                                                            }
                                                        }
                                                        ?>
                                                        <option  value="nueva" class="ng-binding">
                                                            --- REGISTRAR NUEVA ---
                                                        </option>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Unidades por Medida</label>
                                                    <input name="txtUnidadPorMedida" type="number" id="Unidades"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtUnidadPorMedida; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Base Paletizado</label>
                                                    <input name="txtBase" type="number" id= "base"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtBase; ?>"  required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Altura Paletizado</label>
                                                    <input name="txtAltura" type="number" id= "altura"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtAltura; ?>"  required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Cajas por Pallet</label>
                                                    <input name="txtCajasPorPalet" type="number" id= "paletizado"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtCajasPorPalet;?>" disabled required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Neto Unitario</label>
                                                    <input name="txtPesoNetoUnitario" type="number" step="0.001" id="PnetoUnitario"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoNetoUnitario;?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Bruto Unitario</label>
                                                    <input name="txtPesoBrutoUnitario" type="number" step="0.001" id="PBrutoUnitario"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoBrutoUnitario;?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Neto por Caja</label>
                                                    <input name="txtPesoNetoCaja" type="number" step="0.001" id="PesoNetoUnitarioCaja"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoNetoCaja;?>" readonly required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Bruto Por Caja</label>
                                                    <input name="txtPesoBrutoCaja" type="number" step="0.001" id="PBrutoUnitarioCaja"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoBrutoCaja;?>"  readonly required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Dias de Cuarentena</label>
                                                    <input name="txtDiasCuarentena" type="number" id="txtDiasCuarentena"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtDiasCuarentena; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estado</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtEstado" id="txtEstado" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Estado ---
                                                        </option>


                                                        <option  value="Activo" class="ng-binding">
                                                            Activo
                                                        </option>

                                                        <option  value="Inactivo" class="ng-binding">
                                                            Inactivo
                                                        </option>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Unidades Minimas en Piking</label>
                                                    <input name="txtUnidadesMinimas" type="number"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtUnidadesMinimas; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Unidades Maximas en Piking</label>
                                                    <input name="txtUnidadesMaximas" type="number"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtUnidadesMaximas; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Dias de Vencimiento</label>
                                                    <input name="txtDiasVencimiento" type="number" id="txtDiasVencimiento"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtDiasVencimiento; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->



                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Fotografia</label>
                                                <div class="input-group mb-12">
                                                    <?php if ($txtFoto != "") { ?>
                                                        <br/>
                                                        <img style="border-radius: 45px !important;"
                                                             class="img-thumbnail rounded mx-auto d-block" width="200px"
                                                             src="../assets/images/Productos/<?php echo $txtFoto; ?>">
                                                        <br/>
                                                        <br/>
                                                    <?php } ?>
                                                    <div class="container">
                                                        <input type="file" class="form-control" accept="image/*"
                                                               name="txtFoto" placeholder="" id="txt6" require="">
                                                    </div>


                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-actions">
                                        <div class="text-center">

                                            <a class="btn btn btn-outline-danger" style="margin-left: 2rem" href="Mantenimiento_Productos.php"><span > Regresar </span></a>
                                            <button type="submit" value="btnRegistrar" name="accion"
                                                    class="btn btn-outline-success">Registrar Producto
                                            </button>

                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Marca -->
<!-- Modal -->
<div class="row">
    <div class="col-md-6">

    <div id="myModal" class="modal" style="justify-content: center;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Para oscurecer el fondo */
">
  <div class="modal-content">
    <span class="close">&times;</span>
    <label for="newBrand">Nueva Marca:</label>
    <input type="text" id="newBrand">
    <button id="saveBrand">Guardar</button>
  </div>
</div>

    </div>
</div>

<!-- JavaScript para manejar el evento click y el modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
  $('#idtxtMarca').change(function(){
    if ($(this).val() == 'nueva') {
      $('#myModal').css('display', 'block');
    }
  });

  $('.close').click(function(){
    $('#myModal').css('display', 'none');
  });

  $('#saveBrand').click(function(){
    var newBrand = $('#newBrand').val();
    $('#idtxtMarca').append('<option value="' + newBrand + '">' + newBrand + '</option>');
    $('#myModal').css('display', 'none');
  });
});
</script>
    <!-- FIN Modal -->

    <!-- Modal Linea -->
    <div class="modal fade" id="ModalLinea" tabindex="-1" role="dialog" aria-labelledby="ModalLineaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Registrar Nueva Linea</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Nombre</label>
                            <input type="text" class="form-control" id="txtLineaNuevaNombre" placeholder="Nombre de Linea">

                        </div>
                        

                        <button type="submit" class="btn btn-primary">Registrar Linea nueva</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

                </div>
            </div>
        </div>
    </div>
    <!-- FIN Modal -->

    <!-- Modal Umedida -->
    <div class="modal fade" id="ModalUmedida" tabindex="-1" role="dialog" aria-labelledby="ModalUMedidaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Registrar Nueva Uniad de Medida</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Nombre</label>
                            <input type="text" class="form-control" id="txtUMedidaNuevaNombre" placeholder="Nombre de la Unidad de Medida">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Descripcion</label>
                            <input type="text" class="form-control" id="txtUMedidaNuevaDescripcion" placeholder="Descripcion">
                        </div>

                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

                </div>
            </div>
        </div>
    </div>
    <!-- FIN Modal -->
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

<!-- Scripts personalizados para llenado de campos -->

<!-- Total de Paletizado -->
<script>
    $(document).ready(function () {
        $("#altura").keyup(function () {
            var base = $("#base").val();
            var altura = $(this).val();
            var paletizado = base * altura;
            $("#paletizado").val(paletizado);
        });
    });
</script>

<script>
    $(document).ready(function () {
        $("#base").keyup(function () {
            var base = $("#base").val();
            var altura = $("#altura").val();
            var paletizado =altura * base  ;
            $("#paletizado").val(paletizado);
        });
    });
</script>

<!-- Peso Neto por Caja -->
<script>
    $(document).ready(function () {
        $("#PnetoUnitario").keyup(function () {
            var Unidades = $("#Unidades").val();
            var PnetoUnitario = $("#PnetoUnitario").val();
            var PesoNetoCaja = Unidades * PnetoUnitario;
            $("#PesoNetoUnitarioCaja").val(PesoNetoCaja);
        });
    });
</script>


<!-- Peso Bruto Por caja -->
<script>
    $(document).ready(function () {
        $("#PBrutoUnitario").keyup(function () {
            var Unidades = $("#Unidades").val();
            var PbrutoUnitario = $("#PBrutoUnitario").val();
            var PesoBrutoCaja = Unidades * PbrutoUnitario;
            $("#PBrutoUnitarioCaja").val(PesoBrutoCaja);
        });
    });
</script>

<!-- Dias de Vencimiento segun Linea -->
<script type="text/javascript">
    $(document).ready(function(){

        DiasCuarentena();
        $('#idtxtLinea').change(function(){
            DiasCuarentena();
        });
    })
</script>

<script type="text/javascript">
    function DiasCuarentena() {


        console.warn( "Entro a validar los registros" );
        var Linea = $("#idtxtLinea").val();
        var Linea2 = "DETERGENTE LIQUIDO Y QUITAGRASA";

        console.warn( Linea );

        if(Linea == Linea2){
            console.warn( "Deberia dar valor de cuarentena a 8" );
            $("#txtDiasCuarentena").val("8");
            $("#txtDiasVencimiento").val("730");
        }
        else{
            $("#txtDiasCuarentena").val("0");
            $("#txtDiasVencimiento").val("1095");

        }

    }
</script>

<!-- Modal Agregar marca -->


<!-- Modal Agregar Linea -->
<script>
    $(document).ready(function(){ //Make script DOM ready
        $('#idtxtLinea').change(function() { //jQuery Change Function
            var opval = $(this).val(); //Get value from select element
            if(opval=="nueva"){ //Compare it and if true
                $('#ModalLinea').modal("show"); //Open Modal
            }
        });
    });1
</script>

<!-- Modal Agregar Umedida -->
<script>
    $(document).ready(function(){ //Make script DOM ready
        $('#idtxtUMedida').change(function() { //jQuery Change Function
            var opval = $(this).val(); //Get value from select element
            if(opval=="nueva"){ //Compare it and if true
                $('#ModalUmedida').modal("show"); //Open Modal
            }
        });
    });
</script>

</html>