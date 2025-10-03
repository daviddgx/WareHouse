<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
ob_end_flush();
?>

<!-- Sidebar navigation-->

<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <li class="sidebar-item "><a class="sidebar-link sidebar-link" href="index.php"
                                     aria-expanded="false"><i data-feather="home"
                                                              class="feather-icon"></i><span
                    class="hide-menu">Dashboard</span></a></li>
        <li class="list-divider"></li>
        <li class="nav-small-cap"><span class="hide-menu">Tareas</span></li>

        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i data-feather="file-text"
                                                             class="feather-icon"></i><span
                    class="hide-menu">Guias </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">


                <li class="sidebar-item"><a href="Guias_CargarGuia.php" class="sidebar-link"><span
                                class="hide-menu"> Cargar Guia
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="Traking_Guias.php" class="sidebar-link"><span
                                class="hide-menu"> Seguimiento <br> de Guias
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="Guias_AsignarTransportista.php" class="sidebar-link"><span
                                class="hide-menu"> Completar <br> Guia
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="AsignarUbicaciones.php" class="sidebar-link"><span
                                class="hide-menu"> Calcular <br> Ubicaciones
                                        </span></a>
                </li>
              <!--   <li class="sidebar-item"><a href="ConteoCiegoPrevio.php" class="sidebar-link"><span
                                class="hide-menu"> Conteo Ciego
                                        </span></a>
                </li>


                <li class="sidebar-item"><a href="Guias_ConsultaGuas.php" class="sidebar-link"><span
                            class="hide-menu"> Consultar <br> Guias
                                        </span></a>
                </li> -->
            </ul>
        </li>

        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i data-feather="repeat"
                                                             class="feather-icon"></i><span
                        class="hide-menu">Movimientos </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">

                <li class="sidebar-item"><a href="AbastecerPiking.php" class="sidebar-link"><span
                                class="hide-menu"> Abastecer Piking
                                        </span></a>
                </li>


                <li class="sidebar-item"><a href="ReubicarProductoCarril.php" class="sidebar-link"><span
                                class="hide-menu"> Reubicaciones
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="ReubicarProducto.php" class="sidebar-link"><span
                                class="hide-menu"> Reubicar <br> Pallet
                                        </span></a>
                </li>
                <!--

                <li class="sidebar-item"><a href="LiberarUbicacion.php" class="sidebar-link"><span
                            class="hide-menu"> Liberar Ubicacion
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="LiberarCaril.php" class="sidebar-link"><span
                            class="hide-menu"> Liberar Carril
                                        </span></a>
                </li>
-->

            </ul>
        </li>

        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i data-feather="award"
                                                             class="feather-icon"></i><span
                    class="hide-menu">Calidad </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">

                <li class="sidebar-item"><a href="ConsultarCalidad.php" class="sidebar-link"><span
                            class="hide-menu"> Consultar Calidad
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="BloquearLoteCalidad.php" class="sidebar-link"><span
                            class="hide-menu"> Bloquear Lote <br> por Calidad
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="LiberarLoteCalidad.php" class="sidebar-link"><span
                                class="hide-menu"> Liberar Lote <br> de Calidad
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="BloquearUbicacionCalidad.php" class="sidebar-link"><span
                            class="hide-menu"> Bloquear Ubicacion <br> por Calidad
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="LiberarUbicacionCalidad.php" class="sidebar-link"><span
                                class="hide-menu"> Liberar Ubicacion <br> de Calidad
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="BloquearCarrilCalidad.php" class="sidebar-link"><span
                            class="hide-menu"> Bloquear Carril <br> por Calidad
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="LiberarCarrilCalidad.php" class="sidebar-link"><span
                            class="hide-menu"> Liberar Carril <br> por Calidad e IDH
                                        </span></a>
                </li>

            </ul>
        </li>

        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i data-feather="activity"
                                                             class="feather-icon"></i><span
                    class="hide-menu">Cuarentena </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">


                <li class="sidebar-item"><a href="ConsultarCuarentena.php" class="sidebar-link"><span
                            class="hide-menu"> Consultar Cuarentena
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="BloquearLoteCuarentena.php" class="sidebar-link"><span
                            class="hide-menu"> Bloquear Lote <br> por Cuarentena
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="LiberarLoteCuarentena.php" class="sidebar-link"><span
                                class="hide-menu"> Liberar Lote <br> por Cuarentena
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="BloquearUbicacion.php" class="sidebar-link"><span
                            class="hide-menu"> Bloquear Ubicacion  <br> por Cuarentena
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="LiberarUbicacionCuarentena.php" class="sidebar-link"><span
                                class="hide-menu"> Liberar Ubicacion  <br> por Cuarentena
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="BloquearCarril.php" class="sidebar-link"><span
                            class="hide-menu"> Bloquear Carril  <br> por Cuarentena
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="LiberarCarrilCuarentena.php" class="sidebar-link"><span
                                class="hide-menu"> Liberar Carril  <br> por Cuarentena
                                        </span></a>
                </li>

            </ul>
        </li>

        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i
                    class="fas fa-tags"></i><span
                    class="hide-menu">Inventario Ciclico </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                <li class="sidebar-item"><a href="Invent_CrearRevision.php" class="sidebar-link"><span
                            class="hide-menu"> Generar Listado
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="Invent_Agregar.php" class="sidebar-link"><span
                            class="hide-menu"> Corregir Inventario <br> Agregar Pallet
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="Invent_Eliminar.php" class="sidebar-link"><span
                            class="hide-menu"> Corregir Inventario <br> Borrar Pallet
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="Invent_Resultados.php" class="sidebar-link"><span
                            class="hide-menu"> Consultar <br>Movimientos
                                        </span></a>
                </li>
                

            </ul>
        </li>



        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i
                    class="fas fa-search"></i><span
                    class="hide-menu">Consultas </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">


                <li class="sidebar-item"><a href="Traking_Guias_his.php" class="sidebar-link"><span
                                class="hide-menu"> Guias <br> Terminadas
                                        </span></a>
                </li>


                <li class="list-divider"></li>

                <li class="sidebar-item"><a href="ConsultaFi-Fo.php" class="sidebar-link"><span
                                class="hide-menu">  Consulta <br>Fi-Fo
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="EstadoUbicaciones.php" class="sidebar-link"><span
                                class="hide-menu">  Estado de <br>Ubicaciones
                                        </span></a>
                </li>
                <li class="sidebar-item"><a href="SuperConsulta.php" class="sidebar-link"><span
                            class="hide-menu">  Consulta <br>Personalizada
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="ConsltaTrazabilidad.php" class="sidebar-link"><span
                                class="hide-menu"> Trazabilidad <br> Despachos
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="ConsltaTrazabilidadLote.php" class="sidebar-link"><span
                                class="hide-menu"> Trazabilidad <br> Por Lote
                                        </span></a>
                </li>



                <li class="sidebar-item"><a href="ConsultaIngresos.php" class="sidebar-link"><span
                                class="hide-menu"> Ingresos
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="ConsultaDespachos.php" class="sidebar-link"><span
                                class="hide-menu"> Despachos
                                        </span></a>
                </li>

                <li class="sidebar-item"><a href="ConsultaReubicaciones.php" class="sidebar-link"><span
                                class="hide-menu"> Reubicaciones
                                        </span></a>
                </li>




            </ul>
        </li>

        <li class="list-divider"></li>

        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i
                    class="fas fa-list"></i><span
                    class="hide-menu">Formatos </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                <li class="sidebar-item">

                    <a href="..\assets\Formatos\RE-1054.pdf" class="sidebar-link" target="_blank">
                        <span class="hide-menu">RE-10-54</span>
                    </a>



            </ul>
        </li>


        <li class="list-divider"></li>
        <li class="nav-small-cap"><span class="hide-menu">Mantenimiemtos</span></li>

        <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Mantenimiento_Productos.php"
                                    aria-expanded="false"><i
                    class="fas fa-dolly"></i><span
                    class="hide-menu">Productos
                                </span></a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Mantenimiento_Bodegas.php"
                                    aria-expanded="false"><i
                    class="fas fa-warehouse"></i><span
                    class="hide-menu">Bodegas
                                </span></a>
        </li>

        </li>
        <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Mantenimiento_Piking.php"
                                    aria-expanded="false"><i
                    class="fas fa-pallet"></i><span
                    class="hide-menu">Picking
                                </span></a>
        </li>

        <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="DespachosEspeciales.php"
                                    aria-expanded="false"><i
                        class="fas fa-bus"></i><span
                        class="hide-menu">Despachos Especiales
                                </span></a>
        </li>

        <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Unificaciondeentregas.php"
                                    aria-expanded="false"><i
                        class="fas fa-archive"></i><span
                        class="hide-menu">Unificacion de <br> entregas
                                </span></a>
        </li>


        <li class="sidebar-item"><a class="sidebar-link has-arrow" href="javascript:void(0)"
                                    aria-expanded="false"><i
                    class="fas fa-user"></i><span
                    class="hide-menu">Usuarios </span></a>
            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                <li class="sidebar-item"><a href="Usuarios_Admin.php" class="sidebar-link"><span
                            class="hide-menu"> Mantenimiento <br> Usuarios
                                        </span></a>
                </li>

            </ul>
        </li>

    </ul>
</nav>
<!-- End Sidebar navigation -->


