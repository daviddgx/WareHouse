<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Auth.php';
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';
$error = '';


// Cargar datos a mostrar

// Creamos la conexion


try {

    $IDH_SESION = filter_input(INPUT_GET, 'Guia', FILTER_VALIDATE_INT);
    if ($IDH_SESION === false || $IDH_SESION === null) {
        throw new InvalidArgumentException('El identificador del producto no es válido.');
    }
    $sql = "SELECT * FROM dbs9098416.productos WHERE IDH = :IDH";

    $sentencia = $pdo->prepare(
        $sql,
        array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
    );

    $sentencia->execute([':IDH' => $IDH_SESION]);

    $Producto = $sentencia->fetch(PDO::FETCH_ASSOC);
    if (!$Producto) {
        throw new RuntimeException('El producto solicitado no existe.');
    }
    $txtIDH = $Producto['IDH'];
    $txtCodigoDeBarras = $Producto['CodigoDeBarras'];
    $txtDescripcion = $Producto['Descripcion'];
    $txtMarca = $Producto['Marca'];
    $txtLinea = $Producto['LINEA'];
    $txtUnidadPorMedida = $Producto['UNIDADESXMEDIDA'];
    $txtUnidadDeMedida = $Producto['UMEDIDA'];
    $txtBase = $Producto['BASE'];
    $txtAltura = $Producto['ALTURA'];
    $txtCajasPorPalet = $Producto['CAJASXPALET'];
    $txtPesoNetoUnitario = $Producto['PESONETOUNIDAD'];
    $txtPesoBrutoUnitario = $Producto['PESOBRUTOUNIDAD'];
    $txtPesoNetoCaja = $Producto['PESONETOCAJA'];
    $txtPesoBrutoCaja = $Producto['PESOBRUTOCAJA'];
    $txtPesoPorPallet = $Producto['PESOPORPALLET'];
    $txtFoto = $Producto['FOTO'];
    $txtDiasCuarentena = $Producto['DIASCUARENTENA'];
    $txtDiasVencimiento = $Producto['DIASVENCIMIENTO'];
    $txtEstado = $Producto['ESTADO'];
    $txtUnidadesMinimas = $Producto['MINIMOPICKING'];
    $txtUnidadesMaximas = $Producto['MAXIMOPICKING'];
} catch (Exception $ex) {

    $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
}
//comprovacion de dadtos
//fin comprovacion de datos
// Fin de la conexion


// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {

    case "btnModificar":



        // El ID de la URL, ya validado al cargar la página, es la referencia autoritativa.
        $txtIDH = $IDH_SESION;
        $txtDescripcion = (isset($_POST['txtDescripcion'])) ? $_POST['txtDescripcion'] : "";
        $txtMarca = (isset($_POST['txtMarca'])) ? $_POST['txtMarca'] : "";
        $txtLinea = (isset($_POST['txtLinea'])) ? $_POST['txtLinea'] : "";
        $txtUnidadPorMedida = (isset($_POST['txtUnidadPorMedida'])) ? $_POST['txtUnidadPorMedida'] : "";
        $txtUnidadDeMedida = (isset($_POST['txtUMedida'])) ? $_POST['txtUMedida'] : "";
        $txtBase = (isset($_POST['txtBase'])) ? $_POST['txtBase'] : "";
        $txtAltura = (isset($_POST['txtAltura'])) ? $_POST['txtAltura'] : "";
        $txtCajasPorPalet = (isset($_POST['txtCajasPorPalet'])) ? $_POST['txtCajasPorPalet'] : "";
        $txtPesoNetoUnitario = (isset($_POST['txtPesoNetoUnitario'])) ? $_POST['txtPesoNetoUnitario'] : "";
        $txtPesoBrutoUnitario = (isset($_POST['txtPesoBrutoUnitario'])) ? $_POST['txtPesoBrutoUnitario'] : "";
        $txtPesoNetoCaja = (isset($_POST['txtPesoNetoCaja'])) ? $_POST['txtPesoNetoCaja'] : "";
        $txtPesoBrutoCaja = (isset($_POST['txtPesoBrutoCaja'])) ? $_POST['txtPesoBrutoCaja'] : "";
        $txtPesoPorPallet = (isset($_POST['txtPesoPorPallet'])) ? $_POST['txtPesoPorPallet'] : "";
        $fotoAnterior = $Producto['FOTO'];
        $txtDiasCuarentena = (isset($_POST['txtDiasCuarentena'])) ? $_POST['txtDiasCuarentena'] : "";
        $txtDiasVencimiento = (isset($_POST['txtDiasVencimiento'])) ? $_POST['txtDiasVencimiento'] : "";
        $txtEstado = (isset($_POST['txtEstado'])) ? $_POST['txtEstado'] : "";
        $txtUnidadesMinimas = (isset($_POST['txtUnidadesMinimas'])) ? $_POST['txtUnidadesMinimas'] : "";
        $txtUnidadesMaximas = (isset($_POST['txtUnidadesMaximas'])) ? $_POST['txtUnidadesMaximas'] : "";



        $camposNuevos = [
            'Descripcion' => $txtDescripcion,
            'Marca' => $txtMarca,
            'LINEA' => $txtLinea,
            'UNIDADESXMEDIDA' => $txtUnidadPorMedida,
            'UMEDIDA' => $txtUnidadDeMedida,
            'BASE' => $txtBase,
            'ALTURA' => $txtAltura,
            'CAJASXPALET' => $txtCajasPorPalet,
            'PESONETOUNIDAD' => $txtPesoNetoUnitario,
            'PESOBRUTOUNIDAD' => $txtPesoBrutoUnitario,
            'PESONETOCAJA' => $txtPesoNetoCaja,
            'PESOBRUTOCAJA' => $txtPesoBrutoCaja,
            'PESOPORPALLET' => $txtPesoPorPallet,
            'DIASCUARENTENA' => $txtDiasCuarentena,
            'DIASVENCIMIENTO' => $txtDiasVencimiento,
            'ESTADO' => $txtEstado,
            'MINIMOPICKING' => $txtUnidadesMinimas,
            'MAXIMOPICKING' => $txtUnidadesMaximas
        ];

        $usuarioAuditoria = $_SESSION['Usuario'] ?? 'Usuario no identificado';
        $direccionIP = $_SERVER['REMOTE_ADDR'] ?? null;
        $ipsProxy = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        $agenteUsuario = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $idSesionHash = session_id() !== '' ? hash('sha256', session_id()) : null;
        $idEventoAuditoria = bin2hex(random_bytes(16));

        try {
            $pdo->beginTransaction();

            // El bloqueo evita que dos ediciones simultáneas auditen valores anteriores incorrectos.
            $consultaAnterior = $pdo->prepare(
                'SELECT Descripcion, Marca, LINEA, UNIDADESXMEDIDA, UMEDIDA, BASE, ALTURA,
                        CAJASXPALET, PESONETOUNIDAD, PESOBRUTOUNIDAD, PESONETOCAJA,
                        PESOBRUTOCAJA, PESOPORPALLET, DIASCUARENTENA, DIASVENCIMIENTO,
                        ESTADO, MINIMOPICKING, MAXIMOPICKING
                 FROM dbs9098416.productos
                 WHERE IDH = :IDH
                 FOR UPDATE'
            );
            $consultaAnterior->execute([':IDH' => $txtIDH]);
            $camposAnteriores = $consultaAnterior->fetch(PDO::FETCH_ASSOC);

            if (!$camposAnteriores) {
                throw new RuntimeException('El producto que intenta modificar no existe.');
            }

            $sentencia = $pdo->prepare('UPDATE dbs9098416.productos SET Descripcion=:Descripcion, Marca=:Marca, LINEA=:LINEA, UNIDADESXMEDIDA=:UNIDADESXMEDIDA, UMEDIDA=:UMEDIDA, BASE=:BASE, ALTURA=:ALTURA, CAJASXPALET=:CAJASXPALET, PESONETOUNIDAD=:PESONETOUNIDAD, PESOBRUTOUNIDAD=:PESOBRUTOUNIDAD, PESONETOCAJA=:PESONETOCAJA, PESOBRUTOCAJA=:PESOBRUTOCAJA, PESOPORPALLET=:PESOPORPALLET, DIASCUARENTENA=:DIASCUARENTENA, DIASVENCIMIENTO=:DIASVENCIMIENTO, ESTADO=:ESTADO, MINIMOPICKING=:MINIMOPICKING, MAXIMOPICKING=:MAXIMOPICKING WHERE IDH=:IDH');
            $parametrosActualizacion = [':IDH' => $txtIDH];
            foreach ($camposNuevos as $campo => $valor) {
                $parametrosActualizacion[':' . $campo] = $valor;
            }
            $sentencia->execute($parametrosActualizacion);

            $insertarAuditoria = $pdo->prepare(
                'INSERT INTO dbs9098416.AuditoriaCambiosProductos
                    (IdEvento, IDH, Campo, ValorAnterior, ValorNuevo, Usuario,
                     DireccionIP, IPsProxy, AgenteUsuario, IdSesionHash)
                 VALUES
                    (:IdEvento, :IDH, :Campo, :ValorAnterior, :ValorNuevo, :Usuario,
                     :DireccionIP, :IPsProxy, :AgenteUsuario, :IdSesionHash)'
            );

            foreach ($camposNuevos as $campo => $valorNuevo) {
                $valorAnterior = $camposAnteriores[$campo];
                $sonIguales = is_numeric($valorAnterior) && is_numeric($valorNuevo)
                    ? (float) $valorAnterior === (float) $valorNuevo
                    : (string) $valorAnterior === (string) $valorNuevo;

                if ($sonIguales) {
                    continue;
                }

                $insertarAuditoria->execute([
                    ':IdEvento' => $idEventoAuditoria,
                    ':IDH' => $txtIDH,
                    ':Campo' => $campo,
                    ':ValorAnterior' => $valorAnterior,
                    ':ValorNuevo' => $valorNuevo,
                    ':Usuario' => $usuarioAuditoria,
                    ':DireccionIP' => $direccionIP,
                    ':IPsProxy' => $ipsProxy,
                    ':AgenteUsuario' => $agenteUsuario,
                    ':IdSesionHash' => $idSesionHash
                ]);
            }

            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error al actualizar/auditar producto ' . $txtIDH . ': ' . $ex->getMessage());
            $Mensajeerror = '<div class="alert alert-warning" role="alert">'
                . '<strong>No fue posible actualizar el producto.</strong>'
                . '</div>';
            break;
        }
        // Bloque para actualizar la foto

        $fecha = new DateTime();
        $nombreOriginalFoto = $_FILES['txtFoto']['name'] ?? '';
        $tmpFoto = $_FILES['txtFoto']['tmp_name'] ?? '';
        $nombreArchivo = $tmpFoto !== ''
            ? $fecha->getTimestamp() . '_' . basename($nombreOriginalFoto)
            : $fotoAnterior;

        if ($tmpFoto != "") {
            if (!move_uploaded_file($tmpFoto, "../assets/images/Productos/" . $nombreArchivo)) {
                error_log('No se pudo mover la fotografía cargada para el producto ' . $txtIDH);
                $Mensajeerror = '<div class="alert alert-warning" role="alert">'
                    . '<strong>No fue posible guardar la fotografía del producto.</strong>'
                    . '</div>';
                break;
            }

            try {
                $pdo->beginTransaction();

                $sentencia = $pdo->prepare(
                    "UPDATE dbs9098416.productos SET Foto = :Foto WHERE IDH = :IDH"
                );
                $sentencia->execute([
                    ':Foto' => $nombreArchivo,
                    ':IDH' => $txtIDH
                ]);

                $insertarFotoAuditoria = $pdo->prepare(
                    'INSERT INTO dbs9098416.AuditoriaCambiosProductos
                        (IdEvento, IDH, Campo, ValorAnterior, ValorNuevo, Usuario,
                         DireccionIP, IPsProxy, AgenteUsuario, IdSesionHash)
                     VALUES
                        (:IdEvento, :IDH, :Campo, :ValorAnterior, :ValorNuevo, :Usuario,
                         :DireccionIP, :IPsProxy, :AgenteUsuario, :IdSesionHash)'
                );
                $insertarFotoAuditoria->execute([
                    ':IdEvento' => $idEventoAuditoria,
                    ':IDH' => $txtIDH,
                    ':Campo' => 'FOTO',
                    ':ValorAnterior' => $fotoAnterior,
                    ':ValorNuevo' => $nombreArchivo,
                    ':Usuario' => $usuarioAuditoria,
                    ':DireccionIP' => $direccionIP,
                    ':IPsProxy' => $ipsProxy,
                    ':AgenteUsuario' => $agenteUsuario,
                    ':IdSesionHash' => $idSesionHash
                ]);

                $pdo->commit();

                $rutaFotoAnterior = "../assets/images/Productos/" . $fotoAnterior;
                if ($fotoAnterior !== '' && $fotoAnterior !== 'imagen.jpg'
                    && $fotoAnterior !== $nombreArchivo && file_exists($rutaFotoAnterior)) {
                    unlink($rutaFotoAnterior);
                }
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Error al actualizar/auditar foto del producto ' . $txtIDH . ': ' . $ex->getMessage());
                $Mensajeerror = '<div class="alert alert-warning" role="alert">'
                    . '<strong>No fue posible actualizar la fotografía del producto.</strong>'
                    . '</div>';
                break;
            }
            $_SESSION['pic'] = $nombreArchivo;
            $MensajeExito = '<div class="alert alert-secondary" role="alert">
                                                <strong>Excelente!   -- </strong> Los datos se actualizaron correctamente
                                            </div>';

            header('Location: Mantenimiento_Productos.php?IDH='.$txtIDH);
        }

        header('Location: Mantenimiento_Productos.php?IDH='.$txtIDH);
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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
                            <h4 class="card-title">Edite un Producto</h4>
                            <h6 class="card-subtitle">cambie la informacion de un producto</h6>

                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>

                            <!-- Formulario de datos -->
                            <div class="my-content formulario">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-body">


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>IDH</label>
                                                    <input name="txtIDH" type="text" id="IDH"
                                                           class="form-control"
                                                           value="<?php echo $txtIDH ?>" readonly="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">

                                                    <svg data-value="<?php echo $txtIDH ?>" data-text="<?php echo $txtIDH ?>" class="codigo"/></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Descripcion</label>
                                                    <input name="txtDescripcion" type="text"
                                                           class="form-control"
                                                           value="<?php echo $txtDescripcion ?>"  required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Marca</label>

                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtMarca" id="idtxtMarca" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">


                                                        <?php echo '<option value="' . $txtMarca . '">' . $txtMarca . '</option>'; ?>

                                                        <?php
                                                        $consultaMarcas = $pdo->prepare(
                                                            'SELECT DISTINCT Marca FROM dbs9098416.productos WHERE Marca <> :Marca ORDER BY Marca'
                                                        );
                                                        $consultaMarcas->execute([':Marca' => $txtMarca]);
                                                        while ($row = $consultaMarcas->fetch(PDO::FETCH_ASSOC)) {
                                                            $marca = htmlspecialchars($row['Marca'], ENT_QUOTES, 'UTF-8');
                                                            echo '<option value="' . $marca . '">' . $marca . '</option>';
                                                        }
                                                        ?>
                                                        <option  value="nueva" >
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
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtLinea" id="idtxtLinea" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">

                                                      <?php echo '<option value="' . $txtLinea . '">' . $txtLinea . '</option>'; ?>



                                                        <?php
                                                        $consultaLineas = $pdo->prepare(
                                                            'SELECT DISTINCT LINEA FROM dbs9098416.productos WHERE LINEA <> :Linea ORDER BY LINEA'
                                                        );
                                                        $consultaLineas->execute([':Linea' => $txtLinea]);
                                                        while ($row = $consultaLineas->fetch(PDO::FETCH_ASSOC)) {
                                                            $linea = htmlspecialchars($row['LINEA'], ENT_QUOTES, 'UTF-8');
                                                            echo '<option value="' . $linea . '">' . $linea . '</option>';
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
                                                    <label> Unidad de Medida</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtUMedida" id="idtxtUMedida" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">

                                                        <?php echo '<option value="' . $txtUnidadDeMedida . '">' . $txtUnidadDeMedida . '</option>';?>



                                                        <?php
                                                        $consultaUnidades = $pdo->prepare(
                                                            'SELECT DISTINCT UMEDIDA FROM dbs9098416.productos WHERE UMEDIDA <> :Unidad ORDER BY UMEDIDA'
                                                        );
                                                        $consultaUnidades->execute([':Unidad' => $txtUnidadDeMedida]);
                                                        while ($row = $consultaUnidades->fetch(PDO::FETCH_ASSOC)) {
                                                            $unidad = htmlspecialchars($row['UMEDIDA'], ENT_QUOTES, 'UTF-8');
                                                            echo '<option value="' . $unidad . '">' . $unidad . '</option>';
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
                                                    <input name="txtUnidadPorMedida" type="number"
                                                           class="form-control"  id="unidadespormedida" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtUnidadPorMedida ?>" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Base Paletizado</label>
                                                    <input name="txtBase" type="number" id= "base"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtBase ?>"  required>
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
                                                           value="<?php echo $txtAltura ?>"  required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Cajas por Pallet</label>
                                                    <input name="txtCajasPorPalet" type="number" id= "paletizado"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtCajasPorPalet ?>" readonly required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Neto Unitario</label>
                                                    <input name="txtPesoNetoUnitario" type="number" step="any"
                                                           class="form-control" id = "PesoNetoUnitario" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoNetoUnitario ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Bruto Unitario</label>
                                                    <input name="txtPesoBrutoUnitario" type="number" step="any"
                                                           class="form-control" id="pesobrutounitario" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoBrutoUnitario ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Neto por Caja</label>
                                                    <input name="txtPesoNetoCaja" type="number" step="any"
                                                           class="form-control" id = "PesoNetoUnitarioCaja"onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoNetoCaja ?>" readonly required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso Bruto Por Caja</label>
                                                    <input name="txtPesoBrutoCaja" type="number" step="any"
                                                           class="form-control" id = "PesoBrutoCaja" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtPesoBrutoCaja ?>" readonly required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN Row para Elemento de formulario -->

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Peso por Pallet</label>
                                                    <input name="txtPesoPorPallet" type="number" min="0" step="0.001"
                                                           class="form-control"
                                                           value="<?php echo htmlspecialchars($txtPesoPorPallet, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!--INICIO Row para Elemento de formulario -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Dias de Cuarentena</label>
                                                    <input name="txtDiasCuarentena" type="number"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtDiasCuarentena ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Estado</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtEstado" id="idtxtEstado" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">


                                                        <?php if($txtEstado == 'ACTIVO'){

                                                            echo '<option  value="ACTIVO" class="ng-binding">
                                                            ACTIVO
                                                        </option>
                                                        <option  value="INACTIVO" class="ng-binding">
                                                            INACTIVO
                                                        </option>';
                                                        }else{

                                                            echo '<option  value="INACTIVO" class="ng-binding">
                                                            INACTIVO
                                                        </option>
                                                        <option  value="ACTIVO" class="ng-binding">
                                                            ACTIVO
                                                        </option>';
                                                        }
                                                        ?>




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
                                                           value="<?php echo $txtUnidadesMinimas ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Unidades Maximas en Piking</label>
                                                    <input name="txtUnidadesMaximas" type="number"
                                                           class="form-control" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtUnidadesMaximas ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Dias de Vencimiento</label>
                                                    <input name="txtDiasVencimiento" type="number"
                                                           class="form-control" id="diasvencimiento" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                                           value="<?php echo $txtDiasVencimiento ?>" required>
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
                                            <button type="submit" value="btnModificar" name="accion"
                                                    class="btn btn-outline-success">Actualizar  Producto
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
    <div class="modal fade" id="ModalMarca" tabindex="-1" role="dialog" aria-labelledby="ModalMarcaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Registrar Nueva Marca</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Nombre</label>
                            <input type="text" class="form-control" id="txtMarcaNuevaNombre" placeholder="Nombre de la marca">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Descripcion</label>
                            <input type="text" class="form-control" id="txtMarcaNuevaDescripcion" placeholder="Descripcion">
                        </div>

                        <button type="submit" class="btn btn-primary">Registrar marca nueva</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

                </div>
            </div>
        </div>
    </div>
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
                        <div class="form-group">
                            <label for="exampleInputPassword1">Descripcion</label>
                            <input type="text" class="form-control" id="txtLineaNuevaDescripcion" placeholder="Descripcion">
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

<!- Scripts personalizados para llenado de campos ->

<!- Total de Paletizado ->
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

<!- Peso Neto por Caja ->
<script>
    $(document).ready(function () {
        $("#PesoNetoUnitario").keyup(function () {
            var Unidades = $("#unidadespormedida").val();
            var PesoNetUnit = $(this).val();
            var PesoPorCaja = Unidades * PesoNetUnit;
            $("#PesoNetoUnitarioCaja").val(PesoPorCaja);
        });
    });
</script>

<!- Peso Bruto por Caja ->
<script>
    $(document).ready(function () {
        $("#pesobrutounitario").keyup(function () {
            var Unidades = $("#unidadespormedida").val();
            var PesoBruUnit = $(this).val();
            var PesoPorCaja = Unidades * PesoBruUnit;
            $("#PesoBrutoCaja").val(PesoPorCaja);
        });
    });
</script>

<!- Modal Agregar marca ->
<script>
    $(document).ready(function(){ //Make script DOM ready
        $('#idtxtMarca').change(function() { //jQuery Change Function
            var opval = $(this).val(); //Get value from select element
            if(opval=="nueva"){ //Compare it and if true
                $('#ModalMarca').modal("show"); //Open Modal
            }
        });
    });
</script>

<!- Modal Agregar Linea ->
<script>
    $(document).ready(function(){ //Make script DOM ready
        $('#idtxtLinea').change(function() { //jQuery Change Function
            var opval = $(this).val(); //Get value from select element
            if(opval=="nueva"){ //Compare it and if true
                $('#ModalLinea').modal("show"); //Open Modal
            }
        });
    });
</script>

<!- Modal Agregar Umedida ->
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

<!- Agragar Dias Vencimiento ->
<script>
    $(document).ready(function(){ //Make script DOM ready
        $('#idtxtLinea').change(function() { //jQuery Change Function
            var opval = $(this).val(); //Get value from select element
            if(opval=="DETERGENTE POLVO "){ //Compare it and if true
                $("#diasvencimiento").val(0); //Open Modal
            }else{
                $("#diasvencimiento").val(8);
            }
        });
    });
</script>

<!- Codigo de Barras ->

<script>

    JsBarcode(".codigo").init();
</script>



</html>
