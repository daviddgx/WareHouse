<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Connect.php';
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");



// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';
$lista_Guias = array();
$txtBodega = '';
$txtCarril = '';

if (!isset($_SESSION['csrf_invent_eliminar'])) {
    $_SESSION['csrf_invent_eliminar'] = bin2hex(random_bytes(32));
}
$csrfInventEliminar = $_SESSION['csrf_invent_eliminar'];

if (isset($_GET['MSG']) && $_GET['MSG'] === 'SCS') {
    $cantidadEliminada = isset($_GET['eliminadas']) ? (int) $_GET['eliminadas'] : 0;
    $cantidadOmitida = isset($_GET['omitidas']) ? (int) $_GET['omitidas'] : 0;
    $detalleOmitidas = $cantidadOmitida > 0
        ? ' ' . $cantidadOmitida . ' ubicación(es) ya no estaban ocupadas y se omitieron.'
        : '';
    $MensajeExito = '<div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Operación completada.</strong> Se eliminaron ' . $cantidadEliminada . ' ubicación(es).' . $detalleOmitidas . '
    </div>';
}

$erroresEliminacion = array(
    'METODO' => 'La eliminación debe confirmarse desde esta página.',
    'SESION' => 'La sesión del formulario venció. Intente nuevamente.',
    'SIN_SELECCION' => 'Seleccione al menos una ubicación para eliminar.',
    'SIN_MOTIVO' => 'Debe indicar el motivo de la eliminación.',
    'MOTIVO_LARGO' => 'El motivo no puede exceder 500 caracteres.',
    'SIN_CAMBIOS' => 'Las ubicaciones seleccionadas ya no estaban ocupadas. No se realizó ningún cambio.',
    'ERROR' => 'No fue posible completar la eliminación. No se aplicó ningún cambio.'
);

if (isset($_GET['ERR']) && isset($erroresEliminacion[$_GET['ERR']])) {
    $Mensajeerror = '<div class="alert alert-danger" role="alert">'
        . htmlspecialchars($erroresEliminacion[$_GET['ERR']], ENT_QUOTES, 'UTF-8')
        . '</div>';
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion === 'btnBuscar') {
    $txtBodega = isset($_POST['ListaBodegasDestino']) && is_scalar($_POST['ListaBodegasDestino'])
        ? trim((string) $_POST['ListaBodegasDestino'])
        : '';
    $txtCarril = isset($_POST['txtCarrilOrigen']) && is_scalar($_POST['txtCarrilOrigen'])
        ? trim((string) $_POST['txtCarrilOrigen'])
        : '';
} elseif (isset($_GET['Bodega'], $_GET['Carril'])) {
    $txtBodega = is_scalar($_GET['Bodega']) ? trim((string) $_GET['Bodega']) : '';
    $txtCarril = is_scalar($_GET['Carril']) ? trim((string) $_GET['Carril']) : '';
}

if ($txtBodega !== '' && $txtCarril !== '') {
    include '../LQS_EUQ/UbicacionesOcupadas.php';
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
                            <h4 class="card-title">Elimine la imformacion de una ubicacion</h4>
                            <h6 class="card-subtitle"> ⚠ Los datos se borraran definitivamente tenga cuidado! ⚠ </h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>

                            <h4 class="card-title">  Seleccione la bodega y carril para mostrar las ubicaciones que se pueden corregir </h4>
                                <div class="my-content formulario">
                                    <form role="form" action="" method="post" enctype="multipart/form-data">
                                        <div class="form-body">
                                        <div class="row">
                                        <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Bodega</label>
                                                            <select  required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaBodegasDestino" id="ListaBodegasOrigen" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled" >
                                                                <option style="display:none; height:50px;" value="" class="ng-binding">
                                                                    --- Bodega ---
                                                                </option>
                                                                <?php
                                                                $conn = new mysqli($servername, $username, $password, $dbname);
                                                                $cargos = "SELECT Nombre_Bodega,Descripcion FROM dbs9098416.warehauses;";

                                                                $result = $conn->query($cargos);
                                                                if ($result->num_rows > 0) {
                                                                    while ($row = $result->fetch_assoc()) {

                                                                        $bodegaSeleccionada = ((string) $row['Nombre_Bodega'] === (string) $txtBodega)
                                                                            ? ' selected'
                                                                            : '';
                                                                        echo '<option value="'
                                                                            . htmlspecialchars($row['Nombre_Bodega'], ENT_QUOTES, 'UTF-8')
                                                                            . '"' . $bodegaSeleccionada . '>'
                                                                            . htmlspecialchars($row['Descripcion'], ENT_QUOTES, 'UTF-8')
                                                                            . '</option>';
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                    <div class="form-group">
                                                            <label>Carril</label>
                                                            <input type="hidden" name="txtCarrilOrigen" value="" id="txtCarrilOrigen">
                                                            <div id="AreaOrigen">
                                                        </div>
                                                    </div>
                                                </div>
                                                      
                                      
                                                      <div class="col-md-4">
                                                      
                                                        <br>
                                                        <button type="submit" value="btnBuscar" name="accion"
                                                                class="btn btn-outline-info">Buscar Ubicaciones
                                                        </button>
                                                        </div>
                                                        
                                        </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Tabla de resultado con las ubicaciones  -->
                                <br>
                                <br>
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                    <div>
                                        <h4 class="card-title mb-1">Detalle de ubicaciones a corregir</h4>
                                        <span id="contadorSeleccionadas" class="text-muted">0 ubicaciones seleccionadas</span>
                                    </div>
                                    <button type="button" id="btnAbrirModalEliminar"
                                            class="btn btn-outline-danger" data-toggle="modal"
                                            data-target="#modalEliminarSeleccionadas" disabled>
                                        Borrar seleccionadas
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table id="example" class="table table-striped" cellspacing="0" width="100%" style="text-align: center;">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="seleccionarTodas"
                                                           aria-label="Seleccionar todas las ubicaciones filtradas"
                                                           title="Seleccionar todas las ubicaciones filtradas">
                                                </th>
                                                <th>Ubicación</th>
                                                <th>IDH</th>
                                                <th>Unidades en Pallet</th>
                                                <th>Origen</th>
                                                <th>Fecha de Ingreso</th>
                                                <th>Fecha de Producción</th>
                                                <th>Lote</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lista_Guias as $guia) { ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="seleccionar-ubicacion"
                                                               value="<?php echo htmlspecialchars((string) $guia['Ubicacion'], ENT_QUOTES, 'UTF-8'); ?>"
                                                               aria-label="Seleccionar ubicación <?php echo htmlspecialchars((string) $guia['Ubicacion'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    </td>
                                                    <td><?php echo htmlspecialchars((string) $guia['Ubicacion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $guia['IDH'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $guia['UnidadesEnPallet'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $guia['Origen'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $guia['FechaIngreso'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $guia['FechaProduccion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $guia['LoteProduccion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="modal fade" id="modalEliminarSeleccionadas" tabindex="-1" role="dialog"
                                     aria-labelledby="tituloModalEliminar" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form id="formEliminarSeleccionadas" action="Invent_BorrarDetalle.php" method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="tituloModalEliminar">Confirmar eliminación</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p id="resumenEliminacion" class="mb-3"></p>
                                                    <div class="form-group">
                                                        <label for="motivoEliminacion">Motivo de eliminación</label>
                                                        <textarea id="motivoEliminacion" name="motivo" class="form-control"
                                                                  rows="4" maxlength="500" required
                                                                  placeholder="Explique por qué se eliminarán estas líneas"></textarea>
                                                        <small class="form-text text-muted">Este motivo se guardará en la bitácora de cada ubicación.</small>
                                                    </div>
                                                    <input type="hidden" name="csrf_token"
                                                           value="<?php echo htmlspecialchars($csrfInventEliminar, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="bodega"
                                                           value="<?php echo htmlspecialchars($txtBodega, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="carril"
                                                           value="<?php echo htmlspecialchars($txtCarril, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div id="posicionesSeleccionadasFormulario"></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <button type="submit" id="btnConfirmarEliminacion" class="btn btn-danger">
                                                        Eliminar ubicaciones
                                                    </button>
                                                </div>
                                            </form>
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
    $(document).ready(function () {
        var ubicacionesSeleccionadas = new Set();
        // El backdrop de Bootstrap se agrega directamente a <body>. Si el modal
        // permanece dentro de page-wrapper/animate__animated, queda atrapado en
        // un contexto de apilamiento inferior y el backdrop bloquea sus campos.
        var $modalEliminar = $('#modalEliminarSeleccionadas').appendTo(document.body);
        var tabla = $('#example').DataTable({
            columnDefs: [
                { targets: 0, orderable: false, searchable: false }
            ],
            language: {
                url: 'datatables_espanol.json'
            }
        });

        function cajasFiltradas() {
            return $(tabla.rows({ search: 'applied' }).nodes()).find('.seleccionar-ubicacion');
        }

        function actualizarControles() {
            var cantidad = ubicacionesSeleccionadas.size;
            $('#contadorSeleccionadas').text(
                cantidad + (cantidad === 1 ? ' ubicación seleccionada' : ' ubicaciones seleccionadas')
            );
            $('#btnAbrirModalEliminar').prop('disabled', cantidad === 0);

            var $cajas = cajasFiltradas();
            var seleccionadasEnFiltro = 0;

            $cajas.each(function () {
                var seleccionada = ubicacionesSeleccionadas.has(this.value);
                this.checked = seleccionada;
                if (seleccionada) {
                    seleccionadasEnFiltro++;
                }
            });

            var todasSeleccionadas = $cajas.length > 0 && seleccionadasEnFiltro === $cajas.length;
            $('#seleccionarTodas')
                .prop('checked', todasSeleccionadas)
                .prop('indeterminate', seleccionadasEnFiltro > 0 && !todasSeleccionadas);
        }

        $('#example').on('change', '.seleccionar-ubicacion', function () {
            if (this.checked) {
                ubicacionesSeleccionadas.add(this.value);
            } else {
                ubicacionesSeleccionadas.delete(this.value);
            }
            actualizarControles();
        });

        $('#seleccionarTodas').on('change', function () {
            var seleccionar = this.checked;
            cajasFiltradas().each(function () {
                if (seleccionar) {
                    ubicacionesSeleccionadas.add(this.value);
                } else {
                    ubicacionesSeleccionadas.delete(this.value);
                }
                this.checked = seleccionar;
            });
            actualizarControles();
        });

        tabla.on('draw', actualizarControles);

        $modalEliminar.on('show.bs.modal', function (evento) {
            if (ubicacionesSeleccionadas.size === 0) {
                evento.preventDefault();
                return;
            }

            var cantidad = ubicacionesSeleccionadas.size;
            $('#resumenEliminacion').text(
                'Se eliminarán definitivamente ' + cantidad
                + (cantidad === 1 ? ' ubicación seleccionada.' : ' ubicaciones seleccionadas.')
            );

            var $contenedor = $('#posicionesSeleccionadasFormulario').empty();
            ubicacionesSeleccionadas.forEach(function (posicion) {
                $('<input>', {
                    type: 'hidden',
                    name: 'posiciones[]',
                    value: posicion
                }).appendTo($contenedor);
            });
        }).on('shown.bs.modal', function () {
            $('#motivoEliminacion').trigger('focus');
        });

        $('#formEliminarSeleccionadas').on('submit', function (evento) {
            var motivo = $('#motivoEliminacion').val().trim();
            if (ubicacionesSeleccionadas.size === 0 || motivo === '') {
                evento.preventDefault();
                return;
            }

            $('#btnConfirmarEliminacion')
                .prop('disabled', true)
                .text('Eliminando...');
        });

        actualizarControles();
    });
</script>



<!--Scripts para recargar Carriles Origen Ocupados -->
<script type="text/javascript">
    var carrilSeleccionado = <?php echo json_encode($txtCarril); ?>;

    $(document).ready(function(){

        recargarLista();
        $('#ListaBodegasOrigen').change(function(){
            carrilSeleccionado = '';
            $('#txtCarrilOrigen').val('');
            recargarLista();

        });
    })
</script>

<script type="text/javascript">
    function recargarLista() {


        $.ajax({
            type: "POST",
            url: "CarrilesOriginales.php",
            data: {
                Bodega: $('#ListaBodegasOrigen').val(),
                CarrilSeleccionado: carrilSeleccionado
            },
            success:function(r) {
                $('#AreaOrigen').html(r);
                if (carrilSeleccionado !== '') {
                    $('#txtCarrilOrigen').val(carrilSeleccionado);
                }
            }
        });
    }
</script>

<script>
    function cambiarValorCarril() {
        var select = document.getElementById("ListaCarril");
        var input = document.getElementById("txtCarrilOrigen");
        input.value = select.value;
        carrilSeleccionado = select.value;
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
