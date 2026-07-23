<?php
require_once __DIR__ . '/session_guard.php';


ob_start();

date_default_timezone_set('America/Guatemala');

include '../LQS_EUQ/LST_GCDS.php';

$fecha = date('d-m-Y');

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';

// Aquí puedes agregar posteriormente la validación
// de formularios o procesamiento de información.

ob_end_flush();

?>
<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>

    <meta charset="utf-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content=""
    >

    <meta
        name="author"
        content=""
    >

    <link
        rel="icon"
        type="image/png"
        sizes="16x16"
        href="../assets/images/Sertero/LogoCBP.png"
    >

    <title>Henkel CBP / AdminFIFO</title>

    <!-- Estilos principales -->
    <link
        href="../assets/extra-libs/c3/c3.min.css"
        rel="stylesheet"
    >

    <link
        href="../assets/libs/chartist/dist/chartist.min.css"
        rel="stylesheet"
    >

    <link
        href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../dist/css/Custom/PreLoaderStyle.css"
    >

    <link
        href="../dist/css/Custom/adminContainer.css"
        rel="stylesheet"
    >

    <link
        href="../dist/css/style.min.css"
        rel="stylesheet"
    >

    <link
        href="../dist/css/Custom/ConEst.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    >

    <style>

        select {
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
            color: #ffffff;
            background-color: #ed3131;
            border-color: #ed3737;
        }

        .page-item.active .page-link {
            z-index: 1;
            color: #ffffff;
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

        /*
         * Mantiene los botones alineados cuando aparece
         * el spinner.
         */
        .btn-accion-tabla,
        .btn-confirmar-accion {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            min-width: 105px;
        }

        /*
         * Apariencia de botón bloqueado.
         */
        .boton-procesando {
            pointer-events: none !important;
            cursor: not-allowed !important;
            opacity: 0.65 !important;
        }

        /*
         * Ajuste del spinner de Bootstrap.
         */
        .spinner-boton {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

    </style>

</head>

<body>

<!-- =============================================================== -->
<!-- Preloader -->
<!-- =============================================================== -->

<div class="preloader">

    <div class="lds-ripple">

        <div class="preloader">

            <br>

            <div class="logoPre">

                <img
                    src="../assets/images/Sertero/LogoHenkel.png"
                    width="300"
                    height="auto"
                    alt="Henkel"
                >

            </div>

            <div class="loader-frame">

                <div
                    class="loader1"
                    id="loader1"
                ></div>

                <div
                    class="loader2"
                    id="loader2"
                ></div>

            </div>

        </div>

    </div>

</div>

<!-- =============================================================== -->
<!-- Contenedor principal -->
<!-- =============================================================== -->

<div
    id="main-wrapper"
    data-theme="light"
    data-layout="vertical"
    data-navbarbg="skin6"
    data-sidebartype="full"
    data-sidebar-position="fixed"
    data-header-position="fixed"
    data-boxed-layout="full"
>

    <!-- =========================================================== -->
    <!-- Encabezado -->
    <!-- =========================================================== -->

    <header
        class="topbar"
        data-navbarbg="skin6"
    >

        <nav class="navbar top-navbar navbar-expand-md">

            <div
                class="navbar-header"
                data-logobg="skin6"
            >

                <a
                    class="nav-toggler waves-effect waves-light d-block d-md-none"
                    href="javascript:void(0)"
                >
                    <i class="ti-menu ti-close"></i>
                </a>

                <div class="navbar-brand">

                    <a href="index.php">

                        <b class="logo-icon">

                            <img
                                src="../assets/images/Sertero/LogoCBP.png"
                                width="auto"
                                height="40"
                                alt="CBP"
                            >

                            <img
                                src="../assets/images/logo-icon.png"
                                alt="Homepage"
                                width="auto"
                                height="10"
                                class="light-logo"
                            >

                        </b>

                        <span class="logo-text">

                            <img
                                src="../assets/images/logo-text.png"
                                alt="Homepage"
                                class="dark-logo"
                                width="auto"
                                height="40"
                            >

                            <img
                                src="../assets/images/logo-light-text.png"
                                class="light-logo"
                                alt="Homepage"
                            >

                        </span>

                    </a>

                </div>

                <a
                    class="topbartoggler d-block d-md-none waves-effect waves-light"
                    href="javascript:void(0)"
                    data-toggle="collapse"
                    data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <i class="ti-more"></i>
                </a>

            </div>

            <div
                class="navbar-collapse collapse"
                id="navbarSupportedContent"
            >

                <ul class="navbar-nav float-left mr-auto ml-3 pl-1">

                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="navbarDropdown"
                            role="button"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <i
                                data-feather="settings"
                                class="svg-icon"
                            ></i>
                        </a>

                        <div
                            class="dropdown-menu"
                            aria-labelledby="navbarDropdown"
                        >

                            <a
                                class="dropdown-item"
                                href="javascript:ReloadPage();"
                            >
                                Actualizar página
                            </a>

                        </div>

                    </li>

                </ul>

                <ul class="navbar-nav float-right">

                    <p
                        id="status"
                        class="online"
                    >
                        Online
                    </p>

                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle"
                            href="javascript:void(0)"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >

                            <img
                                src="../assets/images/users/<?php
                                    echo htmlspecialchars(
                                        $_SESSION['pic'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>"
                                alt="Usuario"
                                class="rounded-circle"
                                width="40"
                            >

                            <span class="ml-2 d-none d-lg-inline-block">

                                <span>Bienvenido,</span>

                                <span class="text-dark">
                                    <?php
                                        echo htmlspecialchars(
                                            $_SESSION['USR'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    ?>
                                </span>

                                <i
                                    data-feather="chevron-down"
                                    class="svg-icon"
                                ></i>

                            </span>

                        </a>

                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">

                            <a
                                class="dropdown-item"
                                href="javascript:PerfilAdminFifo();"
                            >

                                <i
                                    data-feather="settings"
                                    class="svg-icon mr-2 ml-1"
                                ></i>

                                Mi perfil

                            </a>

                            <div class="dropdown-divider"></div>

                            <a
                                class="dropdown-item"
                                href="javascript:Salir();"
                            >

                                <i
                                    data-feather="power"
                                    class="svg-icon mr-2 ml-1"
                                ></i>

                                Salir

                            </a>

                        </div>

                    </li>

                </ul>

            </div>

        </nav>

    </header>

    <!-- =========================================================== -->
    <!-- Menú lateral -->
    <!-- =========================================================== -->

    <aside
        class="left-sidebar"
        data-sidebarbg="skin6"
    >

        <div
            class="scroll-sidebar"
            data-sidebarbg="skin6"
        >

            <?php include 'Menu.php'; ?>

        </div>

    </aside>

    <!-- =========================================================== -->
    <!-- Contenido de la página -->
    <!-- =========================================================== -->

    <div class="page-wrapper">

        <div class="container-fluid animate__animated animate__fadeIn">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            <h4 class="card-title">
                                Tracking de Guías
                            </h4>

                            <h6 class="card-subtitle">
                                Dar seguimiento al proceso de las guías
                            </h6>

                            <br>

                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>

                            <br>

                            <div>

                                <div>

                                    <div
                                        class="dataTables_wrapper"
                                        style="overflow-x: auto;"
                                    >

                                        <table
                                            id="example"
                                            class="table table-striped"
                                            cellspacing="0"
                                            width="100%"
                                        >

                                            <thead>

                                                <tr>

                                                    <th>Guía</th>
                                                    <th>Fecha Pedido</th>
                                                    <th>Fecha Entrega</th>
                                                    <th>Destino</th>
                                                    <th>Lugar</th>
                                                    <th>Transportista</th>
                                                    <th>País</th>
                                                    <th>Estatus</th>
                                                    <th>Piloto</th>
                                                    <th>Semáforo</th>
                                                    <th>Detalle</th>
                                                    <th>Resumen</th>
                                                    <th>Despachar</th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                            <?php

                                            for ($i = 0; $i < $lista_Guias; $i++) {

                                                /*
                                                 * Se guardan los valores para evitar repetir
                                                 * constantemente el acceso al arreglo.
                                                 */
                                                $transporte = $lista_Guias['Transporte'] ?? '';
                                                $fechaPedido = $lista_Guias['FechaPedido'] ?? '';
                                                $fechaEntrega = $lista_Guias['FechaEngrega'] ?? '';
                                                $nombreDestino = $lista_Guias['NombreDestino'] ?? '';
                                                $lugar = $lista_Guias['lugar'] ?? '';
                                                $transportista = $lista_Guias['Transportista'] ?? '';
                                                $pais = $lista_Guias['pais'] ?? '';
                                                $estatus = $lista_Guias['Estatus'] ?? '';
                                                $piloto = $lista_Guias['Piloto'] ?? '';

                                                /*
                                                 * Valores preparados para mostrarse en HTML.
                                                 */
                                                $transporteHtml = htmlspecialchars(
                                                    $transporte,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $fechaPedidoHtml = htmlspecialchars(
                                                    $fechaPedido,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $fechaEntregaHtml = htmlspecialchars(
                                                    $fechaEntrega,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $nombreDestinoHtml = htmlspecialchars(
                                                    $nombreDestino,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $lugarHtml = htmlspecialchars(
                                                    $lugar,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $transportistaHtml = htmlspecialchars(
                                                    $transportista,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $paisHtml = htmlspecialchars(
                                                    $pais,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $estatusHtml = htmlspecialchars(
                                                    $estatus,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                $pilotoHtml = htmlspecialchars(
                                                    $piloto,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );

                                                /*
                                                 * Valor preparado para enviarse por la URL.
                                                 */
                                                $transporteUrl = urlencode($transporte);

                                                echo '<tr>';

                                                echo '<td>' .
                                                    $transporteHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $fechaPedidoHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $fechaEntregaHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $nombreDestinoHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $lugarHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $transportistaHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $paisHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $estatusHtml .
                                                '</td>';

                                                echo '<td>' .
                                                    $pilotoHtml .
                                                '</td>';

                                                /*
                                                 * Columna Semáforo.
                                                 */
                                                echo '<td>';

                                                switch ($estatus) {

                                                    case 'Producido':

                                                        echo '
                                                            <img
                                                                src="../assets/images/Iconos/circuloNaranja.png"
                                                                width="auto"
                                                                height="40"
                                                                alt="Producido"
                                                            >
                                                        ';

                                                        break;

                                                    case 'Despachando':

                                                        echo '
                                                            <img
                                                                src="../assets/images/Iconos/circuloAmarillo.png"
                                                                width="auto"
                                                                height="40"
                                                                alt="Despachando"
                                                            >
                                                        ';

                                                        break;

                                                    case 'Despachado':

                                                        echo '
                                                            <img
                                                                src="../assets/images/Iconos/circuloVerde.png"
                                                                width="auto"
                                                                height="40"
                                                                alt="Despachado"
                                                            >
                                                        ';

                                                        break;

                                                    case 'Corregir':

                                                        echo '
                                                            <img
                                                                src="../assets/images/Iconos/producido.png"
                                                                width="auto"
                                                                height="40"
                                                                alt="Corregir"
                                                            >
                                                        ';

                                                        break;

                                                    default:

                                                        echo '
                                                            <img
                                                                src="../assets/images/Iconos/circuloRojo.png"
                                                                width="auto"
                                                                height="40"
                                                                alt="Pendiente"
                                                            >
                                                        ';

                                                        break;
                                                }

                                                echo '</td>';

                                                /*
                                                 * Columna Detalle.
                                                 */
                                                echo '<td>';

                                                if ($estatus === 'Pendiente') {

                                                    echo '
                                                        <a
                                                            href="DetalleGuias.php?Guia=' .
                                                                $transporteUrl .
                                                            '"
                                                            class="btn btn-outline-secondary btn-accion-tabla"
                                                            data-titulo="Cargando detalle"
                                                            data-mensaje="Se está abriendo el detalle de la guía ' .
                                                                $transporteHtml .
                                                            '."
                                                            data-procesando="Cargando..."
                                                        >
                                                            <span class="far fa-edit mr-2 icono-boton"></span>
                                                            <span class="texto-boton">Detalle</span>
                                                        </a>
                                                    ';

                                                } else {

                                                    echo '
                                                        <a
                                                            href="DetalleGuiasDespachadas.php?Guia=' .
                                                                $transporteUrl .
                                                            '"
                                                            class="btn btn-outline-secondary btn-accion-tabla"
                                                            data-titulo="Cargando detalle"
                                                            data-mensaje="Se está abriendo el detalle de la guía ' .
                                                                $transporteHtml .
                                                            '."
                                                            data-procesando="Cargando..."
                                                        >
                                                            <span class="far fa-edit mr-2 icono-boton"></span>
                                                            <span class="texto-boton">Detalle</span>
                                                        </a>
                                                    ';
                                                }

                                                echo '</td>';

                                                /*
                                                 * Columna Resumen.
                                                 */
                                                echo '<td>';

                                                switch ($estatus) {

                                                    case 'Despachando':
                                                    case 'FiFo Calculado':

                                                        echo '
                                                            <a
                                                                href="ResumenGuia.php?Guia=' .
                                                                    $transporteUrl .
                                                                '"
                                                                class="btn btn-outline-info btn-accion-tabla"
                                                                data-titulo="Cargando resumen"
                                                                data-mensaje="Se está preparando el resumen de la guía ' .
                                                                    $transporteHtml .
                                                                '."
                                                                data-procesando="Cargando..."
                                                            >
                                                                <span class="far fa-file mr-2 icono-boton"></span>
                                                                <span class="texto-boton">Resumen</span>
                                                            </a>
                                                        ';

                                                        break;

                                                    default:

                                                        echo '';

                                                        break;
                                                }

                                                echo '</td>';

                                                /*
                                                 * Columna Despachar / Corregir.
                                                 */
                                                echo '<td>';

                                                switch ($estatus) {

                                                    case 'Pendiente':

                                                        echo '';

                                                        break;

                                                    case 'FiFo Calculado':

                                                        echo '
                                                            <a
                                                                href="DespacharGuia.php?Guia=' .
                                                                    $transporteUrl .
                                                                '"
                                                                class="btn btn-outline-success btn-confirmar-accion"
                                                                data-titulo="¿Desea despachar esta guía?"
                                                                data-mensaje="La guía ' .
                                                                    $transporteHtml .
                                                                ' será enviada al proceso de despacho."
                                                                data-confirmar="Sí, despachar"
                                                                data-procesando="Despachando..."
                                                                data-color="#28a745"
                                                            >
                                                                <span class="far fa-paper-plane mr-2 icono-boton"></span>
                                                                <span class="texto-boton">Despachar</span>
                                                            </a>
                                                        ';

                                                        break;

                                                    case 'Corregir':

                                                        echo '
                                                            <a
                                                                href="CorregirGuia.php?Guia=' .
                                                                    $transporteUrl .
                                                                '"
                                                                class="btn btn-outline-danger btn-confirmar-accion"
                                                                data-titulo="¿Desea corregir esta guía?"
                                                                data-mensaje="Será dirigido a la pantalla para corregir la guía ' .
                                                                    $transporteHtml .
                                                                '."
                                                                data-confirmar="Sí, corregir"
                                                                data-procesando="Cargando..."
                                                                data-color="#dc3545"
                                                            >
                                                                <span class="far fa-hourglass mr-2 icono-boton"></span>
                                                                <span class="texto-boton">Corregir</span>
                                                            </a>
                                                        ';

                                                        break;

                                                    default:

                                                        echo '';

                                                        break;
                                                }

                                                echo '</td>';

                                                echo '</tr>';

                                                /*
                                                 * Carga el siguiente registro del resultado PDO.
                                                 */
                                                $lista_Guias = $ejecutar_sentencia_Guias->fetch(
                                                    PDO::FETCH_ASSOC
                                                );
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

        </div>

        <!-- ======================================================= -->
        <!-- Pie de página -->
        <!-- ======================================================= -->

        <footer class="footer text-center text-muted">

            2023 ® All Rights Reserved by Sertero.

            Designed and Developed by

            <a href="https://qbit-Lab.com">
                Qbit-Lab
            </a>.

        </footer>

    </div>

</div>

<!-- =============================================================== -->
<!-- JavaScript -->
<!-- =============================================================== -->

<!-- jQuery -->
<script src="../assets/libs/jquery/dist/jquery.min.js"></script>

<!-- Bootstrap -->
<script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>

<!-- Scripts del sistema -->
<script src="../dist/js/app-style-switcher.js"></script>
<script src="../dist/js/feather.min.js"></script>
<script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
<script src="../dist/js/sidebarmenu.js"></script>
<script src="../dist/js/custom.min.js"></script>

<!-- Gráficas -->
<script src="../assets/extra-libs/c3/d3.min.js"></script>
<script src="../assets/extra-libs/c3/c3.min.js"></script>
<script src="../assets/libs/chartist/dist/chartist.min.js"></script>
<script src="../assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>
<script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>
<script src="../dist/js/OnLine.js"></script>

<!-- DataTables -->
<script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    $(document).ready(function () {

        /*
         * Inicialización de DataTables.
         *
         * Se evita utilizar también datatable-basic.init.js,
         * porque podría intentar inicializar la misma tabla dos veces.
         */
        const tabla = $('#example').DataTable({
            language: {
                url: 'datatables_espanol.json'
            },
            responsive: false,
            autoWidth: false
        });

        /**
         * Convierte el botón seleccionado en un botón con spinner
         * y bloquea nuevos clics.
         *
         * @param {jQuery} $boton
         * @param {string} textoProcesando
         */
        function activarSpinner($boton, textoProcesando) {

            const $icono = $boton.find('.icono-boton');
            const $texto = $boton.find('.texto-boton');

            /*
             * Evita activar dos veces el mismo botón.
             */
            if ($boton.hasClass('boton-procesando')) {
                return;
            }

            /*
             * Guarda los valores originales por si en algún momento
             * se necesita restaurar el botón.
             */
            $boton.data(
                'clases-icono-originales',
                $icono.attr('class')
            );

            $boton.data(
                'texto-original',
                $texto.text()
            );

            /*
             * Bloquea el botón.
             */
            $boton.addClass('boton-procesando disabled');

            $boton.attr({
                'aria-disabled': 'true',
                'tabindex': '-1'
            });

            /*
             * Cambia el icono por el spinner.
             */
            $icono
                .removeClass()
                .addClass(
                    'spinner-border spinner-border-sm spinner-boton mr-2'
                )
                .attr({
                    'role': 'status',
                    'aria-hidden': 'true'
                });

            /*
             * Cambia el texto.
             */
            $texto.text(textoProcesando);
        }

        /**
         * Restaura el botón cuando ocurre un error o se cancela
         * la navegación.
         *
         * @param {jQuery} $boton
         */
        function restaurarBoton($boton) {

            const $icono = $boton.find('.icono-boton');
            const $texto = $boton.find('.texto-boton');

            const clasesOriginales =
                $boton.data('clases-icono-originales');

            const textoOriginal =
                $boton.data('texto-original');

            $boton.removeClass(
                'boton-procesando disabled'
            );

            $boton.removeAttr(
                'aria-disabled tabindex'
            );

            if (clasesOriginales) {
                $icono.attr(
                    'class',
                    clasesOriginales
                );
            }

            if (textoOriginal) {
                $texto.text(
                    textoOriginal
                );
            }
        }

        /**
         * Botones Detalle y Resumen.
         *
         * Se usa delegación de eventos para que funcione correctamente
         * aunque DataTables cambie, ordene o reconstruya las filas.
         */
        $(document).on(
            'click',
            '.btn-accion-tabla',
            function (event) {

                event.preventDefault();

                const $boton = $(this);

                if ($boton.hasClass('boton-procesando')) {
                    return;
                }

                const url = $boton.attr('href');

                const titulo =
                    $boton.data('titulo') ||
                    'Procesando';

                const mensaje =
                    $boton.data('mensaje') ||
                    'Espere un momento.';

                const textoProcesando =
                    $boton.data('procesando') ||
                    'Cargando...';

                activarSpinner(
                    $boton,
                    textoProcesando
                );

                Swal.fire({
                    icon: 'info',
                    title: titulo,
                    text: mensaje,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    timer: 700,
                    timerProgressBar: true
                }).then(function () {

                    if (url) {
                        window.location.href = url;
                    } else {

                        restaurarBoton($boton);

                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo continuar',
                            text: 'El botón no tiene una dirección válida.',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }
        );

        /**
         * Botones Despachar y Corregir.
         */
        $(document).on(
            'click',
            '.btn-confirmar-accion',
            function (event) {

                event.preventDefault();

                const $boton = $(this);

                if ($boton.hasClass('boton-procesando')) {
                    return;
                }

                const url =
                    $boton.attr('href');

                const titulo =
                    $boton.data('titulo') ||
                    '¿Desea continuar?';

                const mensaje =
                    $boton.data('mensaje') ||
                    '';

                const textoConfirmar =
                    $boton.data('confirmar') ||
                    'Sí, continuar';

                const textoProcesando =
                    $boton.data('procesando') ||
                    'Procesando...';

                const colorConfirmacion =
                    $boton.data('color') ||
                    '#28a745';

                Swal.fire({
                    icon: 'question',
                    title: titulo,
                    text: mensaje,
                    showCancelButton: true,
                    confirmButtonText: textoConfirmar,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: colorConfirmacion,
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    focusCancel: true,
                    allowOutsideClick: false,
                    allowEscapeKey: true
                }).then(function (resultado) {

                    /*
                     * Si el usuario cancela, no se ejecuta ninguna acción.
                     */
                    if (!resultado.isConfirmed) {
                        return;
                    }

                    /*
                     * Activa el spinner únicamente en el botón seleccionado.
                     */
                    activarSpinner(
                        $boton,
                        textoProcesando
                    );

                    Swal.fire({
                        icon: 'success',
                        title: 'Acción confirmada',
                        text: 'La solicitud se está procesando.',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 650,
                        timerProgressBar: true
                    }).then(function () {

                        if (url) {

                            window.location.href = url;

                        } else {

                            restaurarBoton($boton);

                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo continuar',
                                text: 'El botón no tiene una dirección válida.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                });
            }
        );

        /**
         * Cuando el usuario regresa con el botón Atrás del navegador,
         * algunos navegadores recuperan la página desde la memoria.
         * Este evento restaura cualquier botón que haya quedado bloqueado.
         */
        window.addEventListener(
            'pageshow',
            function (event) {

                if (event.persisted) {

                    $('.boton-procesando').each(
                        function () {
                            restaurarBoton($(this));
                        }
                    );
                }
            }
        );

    });

</script>

<script>

    /*
     * Tiempo máximo de inactividad:
     * 5 minutos = 300,000 milisegundos.
     */
    const tiempoInactividad = 300000;

    let temporizadorInactividad;

    /**
     * Redirige al usuario cuando se supera el tiempo
     * máximo de inactividad.
     */
    function redirigir() {
        window.location.href = 'index.php';
    }

    /**
     * Reinicia el temporizador de inactividad.
     */
    function reiniciarTemporizador() {

        clearTimeout(temporizadorInactividad);

        temporizadorInactividad = setTimeout(
            redirigir,
            tiempoInactividad
        );
    }

    /*
     * Eventos que indican actividad del usuario.
     */
    [
        'mousemove',
        'mousedown',
        'keypress',
        'scroll',
        'touchstart'
    ].forEach(function (evento) {

        document.addEventListener(
            evento,
            reiniciarTemporizador,
            {
                passive: true
            }
        );
    });

    /*
     * Inicia el temporizador.
     */
    reiniciarTemporizador();

</script>

</body>

</html>
