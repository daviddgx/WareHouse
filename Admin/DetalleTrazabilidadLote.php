<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}

include '../LQS_EUQ/Connect.php';

$txtFechaInicial = "";
$txtFechaFinal = "";

$txtFechaInicial2 = "";
$txtFechaFinal2 = "";

$txtHoraInicial = "";
$txtHoraFinal = "";

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {

}

date_default_timezone_set('America/Guatemala');
$hora = date('G:i', time());
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$fecha = date("d") . '-' . date("m") . '-' . date("Y");



// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';


// Obtener variables de URL
$IDHConsulta = $_GET['IDH'];
$FechaProduccion = $_GET['Lote'];;

// 1. Detalle de registro de Produccion
include '../LQS_EUQ/RPTTrazabilidadDetalleAsignaciones.php';

// 2. Detalle de registro en Bodegas
include '../LQS_EUQ/RPTTrazabilidadDetalleBodegas.php';

// 3. Detalle de registros Despachados
include '../LQS_EUQ/RPTTrazabilidadDetalleDespachos.php';

// 4. Detalle de registros Piking
include '../LQS_EUQ/RPTTrazabilidadDetallePiking.php';










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
                            <a class="dropdown-item" href="#">Actualizar Pagina</a>


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

        <div class="container-fluid animate__animated animate__fadeIn">
            

        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Datos de produccion</h4>
                            <h6 class="card-subtitle"> Detalle de los registros de produccion</h6>
                            <br>

                      
                            <div class="dataTables_wrapper" style="overflow-x: auto;">
                            <table id="example" class="table table-striped  " cellspacing="0" width="100%" >
                                <thead>
                                <th> Registros  </th>
                                <th> IDH </th>
                                <th> Descripcion </th>
                                <th> Fecha de Produccion</th>
                                <th> Fecha de Registro del ingreso</th>
                                <th> Fecha de Ingreso a bodegas</th>
                                <th> Pallet Completo  </th>
                                <th> Bultos/Cajas  </th>
                                <th> Origen  </th>
                                <th> Estado  </th>
                                <th> Lote  </th>
                                <th> Verificador  </th>
                                <th> Montacarguista  </th>
                                
                                
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < $lista_AsignacionesDetalle; $i++) {
                                    echo "<tr>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['registros'];
                                    echo "</td>";

                                    
                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['IDH'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['Producto'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo date('d-m-Y', strtotime($lista_AsignacionesDetalle['Produccion']));
                                    echo "</td>";

                                    echo "<td>";
                                    echo date('d-m-Y', strtotime($lista_AsignacionesDetalle['Registro']));
                                    echo "</td>";

                                    echo "<td>";
                                    echo date('d-m-Y', strtotime($lista_AsignacionesDetalle['Colocado']));
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['PalletCompleto'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['Cantidades'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['Origen'];
                                    echo "</td>";
                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['Estado'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['LoteProduccion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['Verificador'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_AsignacionesDetalle['Operador'];
                                    echo "</td>";

                                    echo "</tr>";

                                    $lista_AsignacionesDetalle = $ejecutar_sentencia_Asignaciones->fetch(PDO::FETCH_ASSOC);
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
                            <br>


                        </div>
                    </div>
                </div>
            </div>


            <!-- Tabla 2 -->

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Datos de Pallets en bodega con este Lote</h4>
                            <h6 class="card-subtitle"> Detalle de los registros con este lote en las bodegas en este momento</h6>
                            <br>

                      
                            <div class="dataTables_wrapper" style="overflow-x: auto;">
                            <table id="example2" class="table table-striped  " cellspacing="0" width="100%" >
                                <thead>
                                <th> Ubicion  </th>
                                <th> IDH </th>
                                <th> Pallet Completo </th>
                                <th> Unidades En Pallet </th>
                                <th> Origen</th>
                                <th> Lote</th>
                               
                                
                                
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < $lista_BodegaDetalle; $i++) {
                                    echo "<tr>";

                                    echo "<td>";
                                    echo $lista_BodegaDetalle['Ubicacion'];
                                    echo "</td>";

                                    
                                    echo "<td>";
                                    echo $lista_BodegaDetalle['IDH'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_BodegaDetalle['PaletCompleto'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_BodegaDetalle['UnidadesEnPallet'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_BodegaDetalle['Origen'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_BodegaDetalle['LoteProduccion'];
                                    echo "</td>";

                                    



                                    echo "</tr>";

                                    $lista_BodegaDetalle = $ejecutar_sentencia_Bodega->fetch(PDO::FETCH_ASSOC);
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
                            <br>


                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla 3 -->

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Datos De pallets despachados de este lote</h4>
                            <h6 class="card-subtitle"> Detalle de los datos de pallets despachados de este lote</h6>
                            <br>

                      
                            <div class="dataTables_wrapper" style="overflow-x: auto;">
                            <table id="example3" class="table table-striped  " cellspacing="0" width="100%" >
                                <thead>
                                <th> Ubicion desde donde se despacho </th>
                                <th> Estado </th>
                                <th> IDH</th>
                                <th> Descripcion </th>
                                <th> Pallet Completo</th>
                                <th> Unidades en Pallet</th>
                                <th> Origen</th>
                                <th> Lote</th>
                                <th> Transporte</th>
                                <th> Rampa de despacho</th>
                                <th> Fecha del despacho</th>
                                <th> Fecha de carga al furgon</th>
                                <th> Montacarguista</th>
                               
                                
                                
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < $lista_DespachosDetalle; $i++) {
                                    echo "<tr>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Ubicacion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Estado'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['IDH'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Descripcion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['PaletCompleto'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['UnidadesEnPallet'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Origen'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['LoteProduccion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Guia_Carga'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Rampa'];
                                    echo "</td>";   

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Fecha_Hora_Despacho'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['FechaRealizado'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_DespachosDetalle['Operador'];
                                    echo "</td>";
                                    



                                    echo "</tr>";

                                    $lista_DespachosDetalle = $ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
                            <br>


                        </div>
                    </div>
                </div>
            </div>

              <!-- Tabla 4 -->

              <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Datos de bultos en Piking</h4>
                            <h6 class="card-subtitle"> Detalle de los datos de Piking de este Lote</h6>
                            <br>

                      
                            <div class="dataTables_wrapper" style="overflow-x: auto;">
                            <table id="example4" class="table table-striped  " cellspacing="0" width="100%" >
                                <thead>
                                <th> Ubicion en Piking </th>
                                <th> IDH </th>
                                <th> Bultos</th>
                                <th> Origen </th>
                                <th> Lote</th>
                                <th> Estatus</th>
                                <th> Transporte</th>
                                
                               
                                
                                
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < $lista_PikingDetalle; $i++) {
                                    echo "<tr>";

                                    echo "<td>";
                                    echo $lista_PikingDetalle['Ubicacion'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_PikingDetalle['IDH'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_PikingDetalle['Bultos'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_PikingDetalle['Origen'];
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_PikingDetalle['LoteProduccion'];
                                    echo "</td>";

                                    echo "<td>";

                                    if($lista_PikingDetalle['Estatus'] == ''){
                                        echo "Disponible";

                                    }else{
                                        echo $lista_PikingDetalle['Estatus'];
                                    }
                                    
                                    echo "</td>";

                                    echo "<td>";
                                    echo $lista_PikingDetalle['Transporte'];
                                    echo "</td>";

                                    echo "</tr>";

                                    $lista_PikingDetalle = $ejecutar_sentencia_Piking->fetch(PDO::FETCH_ASSOC);
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
                            <br>


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




    <!--  <script>
        function enviarFormulario() {
            var url = "../innet_ADM/ExportarIngresosFIFO.php?fecha1=" +
            window.open(url, "Descargar Maestro", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150");
        }
    </script> -->

<script>
    $(document).ready(function () {
        $('#example').DataTable({
            order: [[4, 'desc']],
            language: {
                url: 'datatables_espanol.json'
            }


        });
    });
</script>



<script>
    $(document).ready(function () {
        $('#example2').DataTable({
            order: [[4, 'desc']],
            language: {
                url: 'datatables_espanol.json'
            }


        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#example3').DataTable({
            order: [[4, 'desc']],
            language: {
                url: 'datatables_espanol.json'
            }


        });
    });
</script>


<script>
    $(document).ready(function () {
        $('#example4').DataTable({
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
    $(document).ready(function () {

        recargarLista();
        $('#ListaBodegas').change(function () {
            recargarLista();

        });
    })
</script>

<script type="text/javascript">
    function recargarLista() {

        console.warn("Entro a Lista Carriles");
        $.ajax({
            type: "POST",
            url: "TraerAreas.php",
            data: "Bodega=" + $('#ListaBodegas').val(),
            success: function (r) {
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