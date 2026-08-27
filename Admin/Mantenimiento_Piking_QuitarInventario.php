<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Auth.php';
date_default_timezone_set('America/Guatemala');
require_once __DIR__ . '/Mantenimiento_Piking_QuitarInventario_Procesar.php';

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
                            <h4 class="card-title">Quitar unidades del inventario de Piking</h4>
                            <h6 class="card-subtitle">Puede eliminar unidades para ajustar el inventario, todo movimiento queda guardado en la bitacora</h6>

                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>
                            <br>
<!-- Formulario de datos -->
                            <div class="my-content formulario">
                                <form id="formQuitarInventarioPiking" role="form" action="" method="post">
                                    <div class="form-body">
                                    <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>IDH</label>
                                                    <input name="txtIDH" type="text"
                                                           class="form-control"
                                                           placeholder="1234567..."
                                                           value="<?php echo htmlspecialchars((string) $txtIDH, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Lote de produccion</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtLote" id="txtLote" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled" >
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Lote ---
                                                        </option>
                                                        <?php foreach ($lotesDisponibles as $loteDisponible) { ?>
                                                            <?php
                                                            $valorLote = (string) $loteDisponible['LoteProduccion'];
                                                            $loteSeleccionado = ($valorLote === (string) $txtLote)
                                                                ? ' selected'
                                                                : '';
                                                            ?>
                                                            <option value="<?php echo htmlspecialchars($valorLote, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $loteSeleccionado; ?>>
                                                                <?php echo htmlspecialchars($valorLote, ENT_QUOTES, 'UTF-8'); ?>
                                                                -- <?php echo (int) $loteDisponible['Bultos']; ?> Bultos
                                                            </option>
                                                        <?php } ?>


                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Bultos / Cajas a eliminar</label>
                                                    <input name="txtBultos" type="number"
                                                           class="form-control"
                                                           min="1" step="1"
                                                           placeholder="Cantidad de bultos / cajas"
                                                           value="<?php echo $txtBultos > 0 ? (int) $txtBultos : ''; ?>" required>
                                                </div>
                                            </div>
                                            

                                    </div>
                                        



                                        <!-- FIN Row para Elemento de formulario -->

                                        <!--INICIO Row para Elemento de formulario -->

                                        <!-- FIN Row para Elemento de formulario -->





                                    </div>
                                    <br>

                                
                                    <div class="form-actions">
                                        <div class="text-center">
                                                <a class="btn btn-outline-danger" style="margin-left: 2rem" href="DetallePiking.php?Guia=<?php echo rawurlencode($txtIDH); ?>"><span > Regresar </span></a>
                                            <button type="button" id="btnAbrirModalQuitarPiking"
                                                    class="btn btn-outline-success">Eliminar Registros
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo htmlspecialchars($csrfQuitarPiking, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="accion" value="btnAbregarPiking">

                                    <div class="modal fade" id="modalQuitarInventarioPiking" tabindex="-1" role="dialog"
                                         aria-labelledby="tituloModalQuitarPiking" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="tituloModalQuitarPiking">Confirmar eliminación de inventario</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p id="resumenQuitarPiking" class="mb-3"></p>
                                                    <div class="form-group">
                                                        <label for="txtDescripcionEliminacion">Descripción de la eliminación</label>
                                                        <textarea id="txtDescripcionEliminacion"
                                                                  name="txtDescripcionEliminacion"
                                                                  form="formQuitarInventarioPiking"
                                                                  class="form-control" rows="4" maxlength="1000"
                                                                  placeholder="Explique el motivo y cualquier detalle relevante"
                                                                  required><?php echo htmlspecialchars($txtDescripcionEliminacion, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                        <small class="form-text text-muted">
                                                            La descripción y los datos eliminados se guardarán en la bitácora.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <button type="submit" id="btnConfirmarQuitarPiking"
                                                            form="formQuitarInventarioPiking"
                                                            class="btn btn-danger">
                                                        Confirmar eliminación
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
<script src="../dist/js/JsBarcode.all.min.js"></script>
<script>
    $(document).ready(function () {
        var $modal = $('#modalQuitarInventarioPiking');

        // El contenedor principal usa animaciones CSS que crean un contexto de
        // apilamiento. Mover el modal al body evita que el backdrop de Bootstrap
        // quede por encima e impida interactuar con el formulario.
        $modal.appendTo(document.body);

        function abrirModalQuitarPiking() {
            var lote = document.getElementById('txtLote');
            var bultos = document.querySelector('[name="txtBultos"]');

            if (!lote.checkValidity()) {
                lote.reportValidity();
                return;
            }

            if (!bultos.checkValidity()) {
                bultos.reportValidity();
                return;
            }

            $('#resumenQuitarPiking').text(
                'Se eliminarán ' + bultos.value + ' bulto(s) del IDH '
                + <?php echo json_encode((string) $txtIDH); ?>
                + ', lote ' + lote.value + '.'
            );
            $modal.modal('show');
        }

        $('#btnAbrirModalQuitarPiking').on('click', abrirModalQuitarPiking);

        $('#formQuitarInventarioPiking input, #formQuitarInventarioPiking select').on('keydown', function (evento) {
            if (evento.key === 'Enter') {
                evento.preventDefault();
                abrirModalQuitarPiking();
            }
        });

        $modal.on('shown.bs.modal', function () {
            $('#txtDescripcionEliminacion').trigger('focus');
        });

        $('#formQuitarInventarioPiking').on('submit', function (evento) {
            if (!$modal.hasClass('show')) {
                evento.preventDefault();
                abrirModalQuitarPiking();
                return;
            }

            $('#btnConfirmarQuitarPiking')
                .prop('disabled', true)
                .text('Eliminando...');
        });

        <?php if ($accion === 'btnAbregarPiking' && $Mensajeerror !== '') { ?>
        abrirModalQuitarPiking();
        <?php } ?>
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
</body>
<script>

    JsBarcode(".codigo").init();
</script>
</html>
