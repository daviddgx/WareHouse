<?php
require_once __DIR__ . '/session_guard.php';
require_once __DIR__ . '/../LQS_EUQ/Connect.php';

date_default_timezone_set('America/Guatemala');

function planoEscapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function planoEstadoVisual($posicion)
{
    $estado = strtolower(trim((string) $posicion['Estado']));
    $estatusUbicacion = strtolower(trim((string) $posicion['EstatusUbicacion']));
    $estatusProducto = strtolower(trim((string) $posicion['EstatusProducto']));

    if (strpos($estatusUbicacion, 'cuarentena') !== false || strpos($estatusProducto, 'cuarentena') !== false) {
        return array('cuarentena', 'Cuarentena');
    }

    if (strpos($estatusUbicacion, 'calidad') !== false || strpos($estatusProducto, 'calidad') !== false) {
        return array('calidad', 'Calidad');
    }

    if ($estado === 'libre') {
        return array('libre', 'Libre');
    }

    if ($estado === 'ocupada' || $estado === 'ocupada-pk' || $estado === 'ocp-pk') {
        return array('ocupada', 'Ocupada');
    }

    if ($estado === 'reservado' || $estado === 'reservada') {
        return array('reservada', 'Reservada');
    }

    if ($estado === 'despacho') {
        return array('despacho', 'Despacho');
    }

    if (
        strpos($estado, 'mov') !== false
        || strpos($estado, 'piking') !== false
        || strpos($estado, 'picking') !== false
    ) {
        return array('movimiento', 'En movimiento');
    }

    return array('otro', $posicion['Estado'] !== '' ? $posicion['Estado'] : 'Sin estado');
}

function planoZonaCarril($carril)
{
    if (preg_match('/^[A-Za-z]+/', (string) $carril, $coincidencia)) {
        return strtoupper($coincidencia[0]);
    }

    return 'OTROS';
}

function planoOrdenNatural($a, $b)
{
    return strnatcasecmp((string) $a, (string) $b);
}

$bodega = isset($_GET['Bodega'])
    ? trim((string) $_GET['Bodega'])
    : (isset($_GET['Guia']) ? trim((string) $_GET['Guia']) : '');

$mensajeError = '';
$descripcionBodega = '';
$posiciones = array();
$zonas = array();
$conteos = array(
    'libre' => 0,
    'ocupada' => 0,
    'reservada' => 0,
    'despacho' => 0,
    'movimiento' => 0,
    'cuarentena' => 0,
    'calidad' => 0,
    'otro' => 0,
);

if ($bodega === '') {
    http_response_code(400);
    $mensajeError = 'No se indicó el número de bodega.';
} elseif (strlen($bodega) > 50) {
    http_response_code(400);
    $mensajeError = 'El número de bodega no es válido.';
} else {
    try {
        $pdo = new PDO(
            'mysql:host=' . $servername . ';dbname=' . $dbname . ';charset=utf8',
            $username,
            $password,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            )
        );

        $sentenciaBodega = $pdo->prepare(
            'SELECT Descripcion
               FROM warehauses
              WHERE Nombre_Bodega = ?
              LIMIT 1'
        );
        $sentenciaBodega->execute(array($bodega));
        $registroBodega = $sentenciaBodega->fetch();
        if ($registroBodega) {
            $descripcionBodega = (string) $registroBodega['Descripcion'];
        }

        $sentencia = $pdo->prepare(
            'SELECT Bodega, Carril, Posicion, Nivel, Ubicacion, Estado, IDH,
                    EstatusUbicacion, EstatusProducto
               FROM posiciones
              WHERE Bodega = ?'
        );
        $sentencia->execute(array($bodega));
        $posiciones = $sentencia->fetchAll();

        usort($posiciones, function ($a, $b) {
            foreach (array('Carril', 'Posicion', 'Nivel') as $campo) {
                $comparacion = strnatcasecmp((string) $a[$campo], (string) $b[$campo]);
                if ($comparacion !== 0) {
                    return $comparacion;
                }
            }

            return 0;
        });

        foreach ($posiciones as $indice => $posicion) {
            list($claveEstado, $etiquetaEstado) = planoEstadoVisual($posicion);
            $posiciones[$indice]['EstadoVisual'] = $claveEstado;
            $posiciones[$indice]['EtiquetaEstado'] = $etiquetaEstado;
            $conteos[$claveEstado]++;

            $zona = planoZonaCarril($posicion['Carril']);
            $zonas[$zona][$posicion['Carril']][$posicion['Posicion']][] = $posiciones[$indice];
        }

        uksort($zonas, 'planoOrdenNatural');
        foreach ($zonas as &$carriles) {
            uksort($carriles, 'planoOrdenNatural');
            foreach ($carriles as &$posicionesCarril) {
                uksort($posicionesCarril, 'planoOrdenNatural');
                foreach ($posicionesCarril as &$niveles) {
                    usort($niveles, function ($a, $b) {
                        return strnatcasecmp((string) $b['Nivel'], (string) $a['Nivel']);
                    });
                }
                unset($niveles);
            }
            unset($posicionesCarril);
        }
        unset($carriles);

        if (!$posiciones) {
            $mensajeError = 'No se encontraron posiciones configuradas para la bodega ' . $bodega . '.';
        }
    } catch (Exception $ex) {
        http_response_code(500);
        error_log('[PlanoBodega] ' . $ex->getMessage());
        $mensajeError = 'No fue posible cargar el plano de la bodega en este momento.';
    }
}

$totalPosiciones = count($posiciones);
$totalLibres = $conteos['libre'];
$totalOcupadas = $conteos['ocupada'];
$totalOtros = $totalPosiciones - $totalLibres - $totalOcupadas;
$porcentajeOcupado = $totalPosiciones > 0
    ? number_format(($totalOcupadas / $totalPosiciones) * 100, 1)
    : '0.0';
$tituloBaseBodega = 'Bodega ' . $bodega;
$descripcionNormalizada = strtolower(trim($descripcionBodega));
$tituloBodega = (
    $descripcionBodega !== ''
    && $descripcionNormalizada !== strtolower($tituloBaseBodega)
    && $descripcionNormalizada !== strtolower($bodega)
) ? $tituloBaseBodega . ' — ' . $descripcionBodega : $tituloBaseBodega;
?>
<!DOCTYPE html>
<html dir="ltr" lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Plano visual de posiciones de bodega">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/Sertero/LogoCBP.png">
    <title><?php echo planoEscapar($tituloBodega); ?> / AdminFIFO</title>
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">
    <link href="../dist/css/Custom/plano-bodega.css?v=20260727-3" rel="stylesheet">
</head>
<body>
<div class="preloader">
    <div class="lds-ripple">
        <div class="preloader">
            <br>
            <div class="logoPre">
                <img src="../assets/images/Sertero/LogoHenkel.png" width="300" height="auto" alt="Henkel">
            </div>
            <div class="loader-frame">
                <div class="loader1" id="loader1"></div>
                <div class="loader2" id="loader2"></div>
            </div>
        </div>
    </div>
</div>

<div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed"
     data-boxed-layout="full">
    <header class="topbar" data-navbarbg="skin6">
        <nav class="navbar top-navbar navbar-expand-md">
            <div class="navbar-header" data-logobg="skin6">
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)">
                    <i class="ti-menu ti-close"></i>
                </a>
                <div class="navbar-brand">
                    <a href="index.php">
                        <b class="logo-icon">
                            <img src="../assets/images/Sertero/LogoCBP.png" width="auto" height="40" alt="CBP">
                            <img src="../assets/images/logo-icon.png" alt="Inicio" width="auto" height="10"
                                 class="light-logo">
                        </b>
                        <span class="logo-text">
                            <img src="../assets/images/logo-text.png" alt="Inicio" class="dark-logo"
                                 width="auto" height="40">
                            <img src="../assets/images/logo-light-text.png" class="light-logo" alt="Inicio">
                        </span>
                    </a>
                </div>
                <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                   data-toggle="collapse" data-target="#navbarSupportedContent"
                   aria-controls="navbarSupportedContent" aria-expanded="false"
                   aria-label="Mostrar navegación">
                    <i class="ti-more"></i>
                </a>
            </div>

            <div class="navbar-collapse collapse" id="navbarSupportedContent">
                <ul class="navbar-nav float-left mr-auto ml-3 pl-1">
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:window.location.reload();" title="Actualizar plano">
                            <i data-feather="refresh-cw" class="svg-icon"></i>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav float-right">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/images/users/<?php echo planoEscapar(isset($_SESSION['pic']) ? $_SESSION['pic'] : ''); ?>"
                                 alt="Usuario" class="rounded-circle" width="40">
                            <span class="ml-2 d-none d-lg-inline-block">
                                <span>Bienvenido,</span>
                                <span class="text-dark"><?php echo planoEscapar(isset($_SESSION['USR']) ? $_SESSION['USR'] : ''); ?></span>
                                <i data-feather="chevron-down" class="svg-icon"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                            <a class="dropdown-item" href="javascript:PerfilAdminFifo()">
                                <i data-feather="settings" class="svg-icon mr-2 ml-1"></i> Mi Perfil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:Salir();">
                                <i data-feather="power" class="svg-icon mr-2 ml-1"></i> Salir
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="left-sidebar" data-sidebarbg="skin6">
        <div class="scroll-sidebar" data-sidebarbg="skin6">
            <?php include __DIR__ . '/Menu.php'; ?>
        </div>
    </aside>

    <div class="page-wrapper plano-page">
        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="plano-heading">
                                <div>
                                    <h4 class="card-title">Plano de <?php echo planoEscapar($tituloBodega); ?></h4>
                                    <h6 class="card-subtitle">
                                        Cada bloque representa una posición y sus niveles. Seleccione una ubicación para ver su detalle.
                                    </h6>
                                </div>
                                <div class="plano-actions">
                                    <a class="btn btn-outline-danger"
                                       href="MantenimientoPosiciones.php?Guia=<?php echo rawurlencode($bodega); ?>">
                                        <i data-feather="arrow-left" class="svg-icon mr-1"></i> Regresar
                                    </a>
                                    <button class="btn btn-outline-info" type="button"
                                            onclick="window.location.reload();">
                                        <i data-feather="refresh-cw" class="svg-icon mr-1"></i> Actualizar
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="window.print();">
                                        <i data-feather="printer" class="svg-icon mr-1"></i> Imprimir
                                    </button>
                                </div>
                            </div>

                            <?php if ($mensajeError !== '') { ?>
                                <div class="alert alert-warning mt-4" role="alert">
                                    <?php echo planoEscapar($mensajeError); ?>
                                </div>
                            <?php } ?>

                            <div class="plano-summary">
                                <div class="plano-stat total">
                                    <strong><?php echo number_format($totalPosiciones); ?></strong>
                                    <span>Ubicaciones</span>
                                </div>
                                <div class="plano-stat ocupada">
                                    <strong><?php echo number_format($totalOcupadas); ?></strong>
                                    <span>Ocupadas</span>
                                </div>
                                <div class="plano-stat libre">
                                    <strong><?php echo number_format($totalLibres); ?></strong>
                                    <span>Libres</span>
                                </div>
                                <div class="plano-stat otros">
                                    <strong><?php echo number_format($totalOtros); ?></strong>
                                    <span>Otros estados</span>
                                </div>
                                <div class="plano-stat porcentaje">
                                    <strong><?php echo $porcentajeOcupado; ?>%</strong>
                                    <span>Ocupación</span>
                                </div>
                            </div>

                            <?php if ($totalPosiciones > 0) { ?>
                                <div class="plano-controls">
                                    <div>
                                        <label for="planoBuscar">Buscar ubicación o IDH</label>
                                        <input id="planoBuscar" class="form-control" type="search"
                                               placeholder="Ej. <?php echo planoEscapar($bodega); ?>-A1-P1-N1 o 3067353">
                                    </div>
                                    <div>
                                        <label for="planoEstado">Filtrar por estado</label>
                                        <select id="planoEstado" class="form-control">
                                            <option value="">Todos los estados</option>
                                            <?php
                                            $etiquetasLeyenda = array(
                                                'libre' => 'Libre',
                                                'ocupada' => 'Ocupada',
                                                'reservada' => 'Reservada',
                                                'despacho' => 'Despacho',
                                                'movimiento' => 'En movimiento',
                                                'cuarentena' => 'Cuarentena',
                                                'calidad' => 'Calidad',
                                                'otro' => 'Otro',
                                            );
                                            foreach ($etiquetasLeyenda as $clave => $etiqueta) {
                                                if ($conteos[$clave] > 0) {
                                                    ?>
                                                    <option value="<?php echo planoEscapar($clave); ?>">
                                                        <?php echo planoEscapar($etiqueta); ?> (<?php echo number_format($conteos[$clave]); ?>)
                                                    </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="planoZoom">Tamaño de ubicaciones</label>
                                        <div class="plano-zoom">
                                            <i data-feather="minus" class="svg-icon"></i>
                                            <input id="planoZoom" type="range" min="30" max="62" step="4" value="42">
                                            <i data-feather="plus" class="svg-icon"></i>
                                        </div>
                                    </div>
                                    <div id="planoResultados" class="plano-results" aria-live="polite">
                                        <?php echo number_format($totalPosiciones); ?> visibles
                                    </div>
                                </div>

                                <div class="plano-legend" aria-label="Leyenda de estados">
                                    <?php foreach ($etiquetasLeyenda as $clave => $etiqueta) {
                                        if ($conteos[$clave] === 0) {
                                            continue;
                                        }
                                        ?>
                                        <span class="plano-legend-item">
                                            <span class="plano-legend-swatch estado-<?php echo planoEscapar($clave); ?>"></span>
                                            <?php echo planoEscapar($etiqueta); ?> (<?php echo number_format($conteos[$clave]); ?>)
                                        </span>
                                    <?php } ?>
                                </div>

                                <div id="planoBodega" class="plano-floor">
                                    <?php foreach ($zonas as $nombreZona => $carriles) { ?>
                                        <section class="plano-zone">
                                            <h5 class="plano-zone-title">Zona <?php echo planoEscapar($nombreZona); ?></h5>
                                            <div class="plano-aisles">
                                                <?php foreach ($carriles as $nombreCarril => $posicionesCarril) {
                                                    $bloques = array_chunk($posicionesCarril, 12, true);
                                                    $numeroBloques = count($bloques);

                                                    foreach ($bloques as $indiceBloque => $bloque) {
                                                        $ubicacionesBloque = 0;
                                                        $ocupadasBloque = 0;
                                                        foreach ($bloque as $nivelesBloque) {
                                                            foreach ($nivelesBloque as $nivelBloque) {
                                                                $ubicacionesBloque++;
                                                                if ($nivelBloque['EstadoVisual'] === 'ocupada') {
                                                                    $ocupadasBloque++;
                                                                }
                                                            }
                                                        }

                                                        $sufijoBloque = $numeroBloques > 1
                                                            ? ' · bloque ' . ($indiceBloque + 1) . '/' . $numeroBloques
                                                            : '';
                                                        ?>
                                                        <article class="plano-aisle">
                                                            <div class="plano-aisle-header">
                                                                <strong>Carril <?php echo planoEscapar($nombreCarril . $sufijoBloque); ?></strong>
                                                                <span><?php echo number_format($ocupadasBloque); ?>/<?php echo number_format($ubicacionesBloque); ?> ocupadas</span>
                                                            </div>
                                                            <div class="plano-rack-scroll">
                                                                <div class="plano-rack">
                                                                    <?php foreach ($bloque as $nombrePosicion => $niveles) { ?>
                                                                        <div class="plano-position">
                                                                            <?php foreach ($niveles as $nivel) {
                                                                                $idh = $nivel['IDH'] === null ? '' : (string) $nivel['IDH'];
                                                                                $detalle = array(
                                                                                    'Ubicación: ' . $nivel['Ubicacion'],
                                                                                    'Carril: ' . $nivel['Carril'],
                                                                                    'Posición: ' . $nivel['Posicion'],
                                                                                    'Nivel: ' . $nivel['Nivel'],
                                                                                    'Estado: ' . $nivel['EtiquetaEstado'],
                                                                                );
                                                                                if ($idh !== '') {
                                                                                    $detalle[] = 'IDH: ' . $idh;
                                                                                }
                                                                                ?>
                                                                                <a class="plano-level estado-<?php echo planoEscapar($nivel['EstadoVisual']); ?>"
                                                                                   href="DetalleUbicaciones.php?Ubicacion=<?php echo rawurlencode($nivel['Ubicacion']); ?>"
                                                                                   data-ubicacion="<?php echo planoEscapar(strtolower($nivel['Ubicacion'])); ?>"
                                                                                   data-idh="<?php echo planoEscapar(strtolower($idh)); ?>"
                                                                                   data-estado="<?php echo planoEscapar($nivel['EstadoVisual']); ?>"
                                                                                   title="<?php echo planoEscapar(implode("\n", $detalle)); ?>"
                                                                                   aria-label="<?php echo planoEscapar(implode(', ', $detalle)); ?>">
                                                                                    <?php echo planoEscapar($nivel['Nivel']); ?>
                                                                                </a>
                                                                            <?php } ?>
                                                                            <div class="plano-position-label"
                                                                                 title="<?php echo planoEscapar($nombrePosicion); ?>">
                                                                                <?php echo planoEscapar($nombrePosicion); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </article>
                                                        <?php
                                                    }
                                                } ?>
                                            </div>
                                        </section>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="plano-empty">
                                    No hay ubicaciones disponibles para construir el plano.
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer text-center text-muted">
            2023 © All Rights Reserved by Sertero. Designed and Developed by
            <a href="https://qbit-Lab.com">Qbit-Lab</a>.
        </footer>
    </div>
</div>

<script src="../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../dist/js/app-style-switcher.js"></script>
<script src="../dist/js/feather.min.js"></script>
<script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
<script src="../dist/js/sidebarmenu.js"></script>
<script src="../dist/js/custom.min.js"></script>

<script>
(function () {
    'use strict';

    var buscar = document.getElementById('planoBuscar');
    var estado = document.getElementById('planoEstado');
    var zoom = document.getElementById('planoZoom');
    var resultados = document.getElementById('planoResultados');
    var ubicaciones = Array.prototype.slice.call(document.querySelectorAll('.plano-level'));
    var carriles = Array.prototype.slice.call(document.querySelectorAll('.plano-aisle'));
    var zonas = Array.prototype.slice.call(document.querySelectorAll('.plano-zone'));

    function normalizar(texto) {
        return (texto || '').toString().toLowerCase().trim();
    }

    function aplicarFiltros() {
        var termino = normalizar(buscar ? buscar.value : '');
        var estadoSeleccionado = estado ? estado.value : '';
        var visibles = 0;

        ubicaciones.forEach(function (ubicacion) {
            var coincideTexto = termino === ''
                || ubicacion.getAttribute('data-ubicacion').indexOf(termino) !== -1
                || ubicacion.getAttribute('data-idh').indexOf(termino) !== -1;
            var coincideEstado = estadoSeleccionado === ''
                || ubicacion.getAttribute('data-estado') === estadoSeleccionado;
            var visible = coincideTexto && coincideEstado;

            ubicacion.classList.toggle('is-muted', !visible);
            if (visible) {
                visibles++;
            }
        });

        carriles.forEach(function (carril) {
            var tieneCoincidencias = carril.querySelector('.plano-level:not(.is-muted)') !== null;
            carril.classList.toggle('is-hidden', !tieneCoincidencias);
        });

        zonas.forEach(function (zona) {
            zona.style.display = zona.querySelector('.plano-aisle:not(.is-hidden)') ? '' : 'none';
        });

        if (resultados) {
            resultados.textContent = visibles.toLocaleString('es-GT') + ' visibles';
        }
    }

    if (buscar) {
        buscar.addEventListener('input', aplicarFiltros);
    }

    if (estado) {
        estado.addEventListener('change', aplicarFiltros);
    }

    if (zoom) {
        zoom.addEventListener('input', function () {
            var medida = zoom.value + 'px';
            document.documentElement.style.setProperty('--plano-slot-width', medida);
            document.documentElement.style.setProperty('--plano-slot-height', medida);
        });
    }
}());
</script>
</body>
</html>
