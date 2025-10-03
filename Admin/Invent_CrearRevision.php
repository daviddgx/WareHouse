<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}

include '../LQS_EUQ/Connect.php';
date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {
}

// Variables de entorno
$MensajeExito = '';
$Mensajeerror = '';
$lista_Guias;
$TotalPallets = 0;



// Validar formulario y grabar informacion
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";
switch ($accion) {

    case 'btnGenerarReporte':

        include '../LQS_EUQ/Connect.php';

        
        $Bodega = (isset($_POST['txtBodega'])) ? $_POST['txtBodega'] : "";
        $Linea = (isset($_POST['txtLinea'])) ? $_POST['txtLinea'] : "";


        try {
            $conn = new PDO('mysql:host=' . $servername . ';dbname=' . $dbname, $username, $password);

            $sqlDatos = "SELECT * FROM `InventarioPorLinea_Estatus` where Bodega = '$Bodega' and Linea = '$Linea'";
           
            $ejecutar_sentencia_Guias = $conn->query($sqlDatos);

            // Verifica si la consulta retorna resultados

            // Obtiene los datos en forma de un arreglo
            $lista_Guias = $ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $ex) {
            // Captura la excepción y procesala de alguna manera
            // (por ejemplo, registrando el error en un archivo de log)
            echo $ex->getMessage();
            error_log("Error: " . $ex->getMessage());
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
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

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alasql/4.2.3/alasql.min.js"></script>
   
    
    

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    


    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #exampleTab, #exampleTab * {
                visibility: visible;
            }
            #exampleTab {
                position: absolute;
                left: 0;
                top: 0;
            }
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 10vh;
        }

        .result-box {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Crear Revisión</h4>
                            <h6 class="card-subtitle">Cree y administre revisiones de inventario ciclico </h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>
                            <h4 class="card-title">Seleccione los datos de bodega y Liea a consultar </h4>
                            <div class="my-content formulario">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div class="form-body">
                            <div class="row">
                            
                                    <div class="col-md-6">
                                            
                                            <div class="form-group">
                                                <label>Bodega</label>
                                                <select  required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched  monospace-font" name="txtBodega" id="txtBodega" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                    <option style="display:none; height:50px;" value="" class="ng-binding">
                                                        --- Bodega ---
                                                    </option>
                                                    <?php
                                                    $conn = new mysqli($servername, $username, $password, $dbname);
                                                    $cargos = "SELECT DISTINCT(Bodega) FROM `posiciones` order by Bodega+'';";
                                                    $result = $conn->query($cargos);
                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo '<option value="' . $row['Bodega'] . '">Bodega ' . utf8_encode($row['Bodega']) . ' </option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            </div>
                                            
                                            <div class="col-md-6">        
                                            <div class="form-group">
                                                <label>Linea</label>
                                                <select  required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched  monospace-font" name="txtLinea" id="txtLinea" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                    <option style="display:none; height:50px;" value="" class="ng-binding">
                                                        --- Linea ---
                                                    </option>
                                                    <?php
                                                    $conn = new mysqli($servername, $username, $password, $dbname);
                                                    $cargos = "SELECT DISTINCT(LINEA) as Linea FROM `productos`;";
                                                    $result = $conn->query($cargos);
                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo '<option value="' . $row['Linea'] . '"> ' . utf8_encode($row['Linea']) . ' </option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            </div>
                                                
                            </div>
                            

                                    <div class="form-actions">
                                        <div class="text-center" style="padding-top: 65px;">
                                            <button type="submit" value="btnGenerarReporte" name="accion"
                                                    class="btn btn-outline-success">Generar Listado
                                            </button>
                                        </div>
                                    </div>

                                    </form>
                                    
                                    
                        </div>

                        <div class="card-body">
                            <h4 class="card-title">Detalle de Inventario Ciclico</h4>
                            <h6 class="card-subtitle"></h6>
                            <br>
                            <?php echo $Mensajeerror; ?>
                            <?php echo $MensajeExito; ?>
                            <br>
                            <div >
                                <!-- Column -->
                                <div >
                                    <div class="dataTables_wrapper" style="overflow-x: auto; " id="ExampleTab">
                                    <table id="example" class="table table-striped" cellspacing="0" width="100%">
                                        <thead>

                                        <th>Carril</th>
                                        <th>IDH</th>
                                        <th>Linea</th>
                                        <th>Estado</th>
                                        <th>Descripcion</th>
                                        <th>Total</th>
                                        <th>Comentarios</th>
                                        
                                        </thead>

                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < $lista_Guias; $i++) {
                                            echo "<tr>";

                                            echo "<td>";
                                            echo $lista_Guias['Carril'];
                                            echo "</td>";

                                            echo "<td>";
                                            echo $lista_Guias['IDH'];
                                            echo "</td>";

                                            echo "<td>";
                                            echo $lista_Guias['LINEA'];
                                            echo "</td>";

                                            echo "<td>";
                                            echo $lista_Guias['Estado'];
                                            echo "</td>";

                                            echo "<td>";
                                            echo $lista_Guias['Descripcion'];
                                            echo "</td>";

                                            echo "<td>";
                                            echo $lista_Guias['Total'];
                                            $TotalPallets += $lista_Guias['Total'];
                                            echo "</td>";

                                            echo "<td>";
                                            echo "             ";
                                            echo "</td>";
                                            $lista_Guias = $ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
                                        }
                                        ?>

<!-- Linea de los totales -->
<?php 



echo "<tr>";

echo "<td>";
echo "Total de Pallets: ";
echo "</td>";

echo "<td>";
echo "";
echo "</td>";

echo "<td>";
echo "";
echo "</td>";

echo "<td>";
echo "";
echo "</td>";

echo "<td>";
echo "";
echo "</td>";

echo "<td>";
echo $TotalPallets;

echo "</td>";

echo "<td>";
echo "             ";
echo "</td>";


?>

                                        </tbody>
                                    </table>


    <div class="container">
        <div class="result-box">
           
        <h2 style="text-align: right: ;">El total de pallets es: <?php echo $TotalPallets; ?></h2>
        </div>
    </div>
       
    <div class="container">
    <button  type="button" class="btn btn-success" onclick="exportarExcel()">EXPORTAR A EXCEL</button>
    </div>


<script>
    $(document).ready(function() {
        $('#example').DataTable();
    });

    function imprimirTabla() {
        window.print();
    }

    function exportarExcel() {
        var tabla = document.getElementById('example');
        var ws = XLSX.utils.table_to_sheet(tabla);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
        XLSX.writeFile(wb, 'BoletaInventario.xlsx');
    }
</script>




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



</body>

</html>