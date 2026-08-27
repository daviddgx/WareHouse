<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Connect.php';
include '../LQS_EUQ/LST_GSCRS.php';
$lista_Guias = isset($lista_Guias) && is_array($lista_Guias) ? $lista_Guias : false;
$hayGuiasPendientes = is_array($lista_Guias) && !empty($lista_Guias);
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';
$alertaValidacion = $_SESSION['guias_alerta_validacion'] ?? null;
unset($_SESSION['guias_alerta_validacion']);

$guiasExistentes = [];
try {
    $sqlGuiasExistentes = "
        SELECT DISTINCT d.Transporte, d.Estatus
        FROM dbs9098416.DetalleGuias AS d
        INNER JOIN dbs9098416.Guia_PreCarga AS p
            ON p.Transporte = d.Transporte
        ORDER BY d.Transporte
    ";
    $consultaGuiasExistentes = $conn->query($sqlGuiasExistentes);
    if ($consultaGuiasExistentes !== false) {
        $guiasExistentes = $consultaGuiasExistentes->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $ex) {
    error_log('Error al validar guias existentes: ' . $ex->getMessage());
}


// Fin de la conexion

// Validar formulario y grabar informacion


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
    </style>
    <style>
        :root {
            --guide-primary: #ed3131;
            --guide-primary-dark: #c51f1f;
            --guide-text: #263238;
            --guide-muted: #6c757d;
            --guide-border: #e6e9ed;
            --guide-surface: #f7f8fa;
        }

        .guide-hero {
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            color: #fff;
            border-radius: 16px;
            background: linear-gradient(125deg, #b71919 0%, var(--guide-primary) 58%, #ff6868 100%);
            box-shadow: 0 12px 28px rgba(237, 49, 49, .18);
        }

        .guide-hero h2 { color: #fff; margin-bottom: .35rem; font-weight: 700; }
        .guide-hero p { margin: 0; color: rgba(255, 255, 255, .88); }
        .guide-step { display: inline-block; margin-bottom: .65rem; padding: .3rem .7rem; border-radius: 999px; background: rgba(255,255,255,.18); font-size: .75rem; font-weight: 700; letter-spacing: .04em; }

        .workflow-card {
            border: 1px solid var(--guide-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(36, 46, 66, .06);
            overflow: hidden;
        }

        .workflow-card .card-body { padding: 1.6rem; }
        .section-heading { display: flex; align-items: flex-start; gap: .85rem; margin-bottom: 1.25rem; }
        .section-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 42px; height: 42px; color: var(--guide-primary); border-radius: 12px; background: rgba(237,49,49,.09); }
        .section-heading h4 { margin: 0 0 .25rem; color: var(--guide-text); font-weight: 700; }
        .section-heading p { margin: 0; color: var(--guide-muted); }

        .upload-zone {
            position: relative;
            display: block;
            padding: 2.2rem 1rem;
            border: 2px dashed #cfd5dc;
            border-radius: 14px;
            background: var(--guide-surface);
            cursor: pointer;
            transition: border-color .2s ease, background .2s ease, transform .2s ease;
        }

        .upload-zone:hover, .upload-zone.is-dragging { border-color: var(--guide-primary); background: #fff7f7; transform: translateY(-1px); }
        .upload-zone.has-file { border-style: solid; border-color: #28a745; background: #f4fcf6; }
        .file-input__input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .upload-icon { width: 50px; height: 50px; margin-bottom: .8rem; color: var(--guide-primary); }
        .upload-title { display: block; color: var(--guide-text); font-size: 1rem; font-weight: 700; }
        .upload-help, .selected-file { display: block; margin-top: .35rem; color: var(--guide-muted); font-size: .85rem; }
        .selected-file { color: #218838; font-weight: 600; }

        .guide-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem; margin-top: 1.25rem; }
        .guide-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-width: 170px; min-height: 44px; border-radius: 10px; font-weight: 700; transition: transform .15s ease, box-shadow .15s ease; }
        .guide-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 7px 16px rgba(36,46,66,.13); }
        .guide-btn:disabled { cursor: not-allowed; opacity: .65; }
        .guide-btn-primary { color: #fff; border: 1px solid var(--guide-primary); background: var(--guide-primary); }
        .guide-btn-primary:hover:not(:disabled) { color: #fff; background: var(--guide-primary-dark); border-color: var(--guide-primary-dark); }

        .guide-table-wrap { overflow-x: auto; border: 1px solid var(--guide-border); border-radius: 12px; }
        .guide-table-wrap .table { margin-bottom: 0; }
        .guide-table-wrap thead th { border: 0; background: #f4f6f8; color: #46505a; font-size: .75rem; text-transform: uppercase; letter-spacing: .035em; white-space: nowrap; }
        .guide-table-wrap tbody td { vertical-align: middle; }
        .processing-spinner { width: 1rem; height: 1rem; border: 2px solid rgba(255,255,255,.45); border-top-color: #fff; border-radius: 50%; animation: guide-spin .7s linear infinite; }
        @keyframes guide-spin { to { transform: rotate(360deg); } }

        @media (max-width: 575.98px) {
            .guide-hero, .workflow-card .card-body { padding: 1.2rem; }
            .guide-btn { width: 100%; }
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
                    <div class="guide-hero">
                        <span class="guide-step">GESTI&Oacute;N DE GU&Iacute;AS</span>
                        <h2>Cargar nuevas gu&iacute;as</h2>
                        <p>Importe el archivo CSV con una o varias gu&iacute;as, revise la informaci&oacute;n detectada y confirme la carga.</p>
                    </div>

                    <div class="card workflow-card">

                        <div class="card-body">
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>



                            <div class="card workflow-card text-center">

                                <div class="card-body">
                                    <div class="section-heading text-left">
                                        <span class="section-icon"><i data-feather="upload-cloud"></i></span>
                                        <div>
                                            <h4>1. Seleccione el archivo</h4>
                                            <p>El archivo puede contener uno o varios n&uacute;meros de transporte.</p>
                                        </div>
                                    </div>
                                    <!-- Start First Cards -->
                                    <div class="row">
                                        <!-- Column -->
                                        <div class="col-md-12 text-center">

                                            <form id="upload-guides-form" class="js-confirm-form" action="SubirGuiadeCarga.php?debug=1" method="POST" enctype="multipart/form-data" data-action="upload">
                                                <label class="upload-zone" id="upload-zone" for="file-input">
                                                    <input type="file" name="dataGuias" id="file-input" class="file-input__input" accept=".csv,text/csv" required>
                                                    <i data-feather="file-plus" class="upload-icon"></i>
                                                    <span class="upload-title">Arrastre el archivo aqu&iacute; o haga clic para buscarlo</span>
                                                    <span class="upload-help">Modo diagn&oacute;stico activo: se mostrar&aacute; cada etapa de la carga</span>
                                                    <span class="selected-file" id="selected-file" aria-live="polite"></span>
                                                </label>
                                                <div class="guide-actions">
                                                    <button type="submit" name="subir" class="btn guide-btn guide-btn-primary">
                                                        <i data-feather="upload"></i><span>Subir archivo CSV</span>
                                                    </button>
                                                </div>
                                            </form>



                                        </div>

                                    </div>




                        </div>
                    </div>

                            <div class="card workflow-card">

                                <div class="card-body">
                                    <div class="section-heading">
                                        <span class="section-icon"><i data-feather="check-square"></i></span>
                                        <div>
                                            <h4>2. Revise y confirme</h4>
                                            <p>Compruebe la informaci&oacute;n antes de guardarla definitivamente.</p>
                                        </div>
                                    </div>
                                    <!-- Start First Cards -->
                                    <form class="js-confirm-form" action="CargarGuias.php" method="POST" data-action="save">
                                        <input type="hidden" name="ContinuarPendiente" value="0">
                                        <div class="form-actions">
                                            <div class="text-center">
                                                <br>

                                                <button type="submit" value="btnModificar" name="Procesar"
                                                        class="btn btn-success guide-btn"><i data-feather="save"></i><span>Guardar carga</span>
                                                </button>

                                            </div>
                                        </div>
                                    </form>
                                    <br>
                                    <div class="guide-table-wrap">
                                        <!-- Column -->
                                        <div >


                                                <table id="example" class="table table-striped  " cellspacing="0" width="100%">
                                                    <thead>


                                                    <th>Guia</th>

                                                    <th>Fecha Pedido</th>
                                                    <th>Fecha Entrega</th>
                                                    <th>Destino</th>
                                                    <th>Lugar</th>
                                                    <th>Canal</th>
                                                    <th>Pais</th>
                                                    <th>Materiales</th>
                                                    <th>Detalles</th>



                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    while (is_array($lista_Guias)) {
                                                        echo "<tr>";

                                                        echo "<td>";
                                                        echo $lista_Guias['Transporte'];
                                                        echo "</td>";



                                                        echo "<td>";
                                                        echo $lista_Guias['FechaPedido'];
                                                        echo "</td>";

                                                        echo "<td>";
                                                        echo $lista_Guias['FechaEngrega'];
                                                        echo "</td>";
                                                        echo "<td>";
                                                        echo $lista_Guias['NombreDestino'];
                                                        echo "</td>";

                                                        echo "<td>";
                                                        echo $lista_Guias['LugarDestino'];
                                                        echo "</td>";

                                                        echo "<td>";
                                                        echo $lista_Guias['canal'];
                                                        echo "</td>";

                                                        echo "<td>";
                                                        echo $lista_Guias['pais'];
                                                        echo "</td>";

                                                        echo "<td>";
                                                        echo $lista_Guias['Materiales'];
                                                        echo "</td>";

                                                        echo "<td>";
                                                        echo '<a href="DetalleGuiasPreCarga.php?Guia=' . $lista_Guias['Transporte'] . '" class="far fa-file-alt  btn btn-Sertero "></a>';
                                                        echo "</td>";



                                                        echo "</tr>";

                                                        $lista_Guias = $ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
                                                    }
                                                    if (!$hayGuiasPendientes) {
                                                        echo '<tr><td colspan="9" class="text-center text-muted py-4">No hay gu&iacute;as pendientes de procesar.</td></tr>';
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>




                                        </div>

                                    </div>
                                    <form class="js-confirm-form" action="EliminarGuiasPre.php" method="POST" data-action="delete">
                                        <div class="form-actions">
                                            <div class="text-center">
                                                <br>

                                                <button type="submit" value="btnModificar" name="Procesar"
                                                        class="btn btn-outline-danger guide-btn"><i data-feather="trash-2"></i><span>Eliminar carga</span>
                                                </button>

                                            </div>
                                        </div>
                                    </form>










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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<script src="../dist/js/OnLine.js"></script>  <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

        <script>
            $(document).ready(function() {
                $('#example').DataTable( {
                    language: {
                        url: 'datatables_espanol.json'
                    }

                } );
            } );
        </script>

        <script>
            (function () {
                'use strict';

                const fileInput = document.getElementById('file-input');
                const uploadZone = document.getElementById('upload-zone');
                const selectedFile = document.getElementById('selected-file');
                const maxFileSize = 10 * 1024 * 1024;
                const hasPendingGuides = <?php echo $hayGuiasPendientes ? 'true' : 'false'; ?>;
                const existingGuides = <?php echo json_encode(
                    $guiasExistentes,
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                ); ?>;
                const serverValidationAlert = <?php echo json_encode(
                    $alertaValidacion,
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                ); ?>;

                const actions = {
                    upload: {
                        title: '¿Subir este archivo?',
                        text: 'Se analizará el CSV antes de guardar las guías.',
                        icon: 'question',
                        confirmText: 'Sí, subir archivo',
                        loadingText: 'Subiendo archivo...'
                    },
                    save: {
                        title: '¿Guardar la carga?',
                        text: 'Las guías revisadas se guardarán definitivamente.',
                        icon: 'question',
                        confirmText: 'Sí, guardar',
                        loadingText: 'Guardando carga...'
                    },
                    delete: {
                        title: '¿Eliminar la carga?',
                        text: 'Se descartarán todas las guías de la precarga actual.',
                        icon: 'warning',
                        confirmText: 'Sí, eliminar',
                        loadingText: 'Eliminando carga...',
                        danger: true
                    }
                };

                function showAlert(options) {
                    if (window.Swal) {
                        return Swal.fire(options);
                    }

                    return Promise.resolve({ isConfirmed: window.confirm(options.text || options.title) });
                }

                function showFileError(message) {
                    showAlert({
                        title: 'Archivo no válido',
                        text: message,
                        icon: 'error',
                        confirmButtonColor: '#ed3131'
                    });
                }

                function validateFile(showError) {
                    const file = fileInput.files[0];
                    if (!file) {
                        if (showError) showFileError('Seleccione un archivo CSV antes de continuar.');
                        return false;
                    }

                    if (!file.name.toLowerCase().endsWith('.csv')) {
                        if (showError) showFileError('El archivo debe tener extensión .csv.');
                        return false;
                    }

                    if (file.size > maxFileSize) {
                        if (showError) showFileError('El archivo supera el tamaño máximo permitido de 10 MB.');
                        return false;
                    }

                    return true;
                }

                function updateFileState() {
                    const file = fileInput.files[0];
                    const valid = validateFile(false);
                    uploadZone.classList.toggle('has-file', Boolean(file && valid));
                    selectedFile.textContent = file
                        ? file.name + ' · ' + (file.size / 1024).toFixed(1) + ' KB'
                        : '';

                    if (file && !valid) validateFile(true);
                }

                function setSubmitting(form, text) {
                    document.querySelectorAll('.js-confirm-form button[type="submit"]').forEach(function (button) {
                        button.disabled = true;
                    });

                    const button = form.querySelector('button[type="submit"]');
                    if (button) {
                        button.innerHTML = '<span class="processing-spinner" aria-hidden="true"></span><span>' + text + '</span>';
                        button.setAttribute('aria-busy', 'true');
                    }
                }

                function existingGuideMessage(guide) {
                    return 'La gu\u00eda ' + guide.Transporte + ' ya existe y est\u00e1 en estatus ' + guide.Estatus + '.';
                }

                function validateExistingGuides(form, action) {
                    const blockedGuides = existingGuides.filter(function (guide) {
                        return String(guide.Estatus).trim().toLowerCase() !== 'pendiente';
                    });

                    if (blockedGuides.length) {
                        const message = blockedGuides.map(existingGuideMessage).join('\n') +
                            '\nNo se puede cargar m\u00e1s informaci\u00f3n de esta gu\u00eda en el estado actual.';

                        return showAlert({
                            title: 'Gu\u00eda existente',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'Cerrar',
                            confirmButtonColor: '#ed3131',
                            allowOutsideClick: false
                        }).then(function () {
                            return false;
                        });
                    }

                    if (existingGuides.length) {
                        const message = existingGuides.map(existingGuideMessage).join('\n') +
                            '\n\u00bfDesea continuar?';

                        return showAlert({
                            title: 'Gu\u00eda pendiente',
                            text: message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '\u00bfDesea continuar?',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            reverseButtons: true
                        }).then(function (result) {
                            if (!result.isConfirmed) return false;
                            form.querySelector('[name="ContinuarPendiente"]').value = '1';
                            setSubmitting(form, action.loadingText);
                            form.submit();
                            return true;
                        });
                    }

                    return Promise.resolve(null);
                }

                if (serverValidationAlert) {
                    showAlert({
                        title: serverValidationAlert.title,
                        text: serverValidationAlert.text,
                        icon: serverValidationAlert.icon || 'error',
                        confirmButtonText: 'Cerrar',
                        confirmButtonColor: '#ed3131'
                    });
                }

                fileInput.addEventListener('change', updateFileState);
                ['dragenter', 'dragover'].forEach(function (eventName) {
                    uploadZone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        uploadZone.classList.add('is-dragging');
                    });
                });
                ['dragleave', 'drop'].forEach(function (eventName) {
                    uploadZone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        uploadZone.classList.remove('is-dragging');
                    });
                });
                uploadZone.addEventListener('drop', function (event) {
                    if (event.dataTransfer.files.length) {
                        fileInput.files = event.dataTransfer.files;
                        updateFileState();
                    }
                });

                document.querySelectorAll('.js-confirm-form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        const actionName = form.dataset.action;
                        const action = actions[actionName];
                        if (!action) return;

                        if (actionName === 'upload' && hasPendingGuides) {
                            showAlert({
                                title: 'Carga pendiente',
                                text: 'Procese las cargas pendientes antes de cargar un nuevo archivo.',
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#ed3131'
                            });
                            return;
                        }

                        if (actionName === 'save' && !hasPendingGuides) {
                            showAlert({
                                title: 'Sin registros',
                                text: 'Ningún registro para cargar.',
                                icon: 'info',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#ed3131'
                            });
                            return;
                        }

                        if (actionName === 'delete' && !hasPendingGuides) {
                            showAlert({
                                title: 'Sin registros',
                                text: 'Sin registros para eliminar.',
                                icon: 'info',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#ed3131'
                            });
                            return;
                        }

                        if (actionName === 'upload' && !validateFile(true)) return;
                        if (form.dataset.pending === 'true') return;

                        const submitButton = form.querySelector('button[type="submit"]');
                        form.dataset.pending = 'true';
                        if (submitButton) submitButton.disabled = true;

                        const validation = actionName === 'save'
                            ? validateExistingGuides(form, action)
                            : Promise.resolve(null);

                        validation.then(function (validationResult) {
                            if (validationResult !== null) {
                                if (!validationResult) {
                                    form.dataset.pending = 'false';
                                    if (submitButton) submitButton.disabled = false;
                                }
                                return;
                            }

                            showAlert({
                                title: action.title,
                                text: action.text,
                                icon: action.icon,
                                showCancelButton: true,
                                confirmButtonText: action.confirmText,
                                cancelButtonText: 'Cancelar',
                                confirmButtonColor: action.danger ? '#dc3545' : '#28a745',
                                cancelButtonColor: '#6c757d',
                                reverseButtons: true,
                                focusCancel: action.danger
                            }).then(function (result) {
                                if (!result.isConfirmed) {
                                    form.dataset.pending = 'false';
                                    if (submitButton) submitButton.disabled = false;
                                    return;
                                }
                                setSubmitting(form, action.loadingText);
                                form.submit();
                            });
                        });
                    });
                });
            }());
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
