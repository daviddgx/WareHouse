<?php
require_once __DIR__ . '/session_guard.php';

ob_start();

include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$Mensajeerror = "";

// Variables de entorno

$NoGuia = isset($_GET["Guia"]) ? $_GET["Guia"] : null;

function DisponibleparaPikear($IDH){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("select count(*) as Bultos from  detalle_piking where IDH = $IDH and Estatus is null and EstatusProducto != 'Bloqueado'");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Bultos'] != 0){
        return  true;
    }else {
        return false;
    }
}

// Datos del detalle

try {

    if (isset($_GET['Guia'])) {
        $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);

        $sqlDatos = " SELECT  IDRegistro, Material,B.Descripcion,Cajas,PesoNeto,PesoBruto,Estatus,tipo,C.Ubicacion  FROM
        dbs9098416.DetalleGuias A
        inner join productos B on A.Material = B.IDH 
         inner join config_piking C on A.Material = C.IDH 
   
    WHERE
       Transporte = $NoGuia  and tipo = 'Piking' AND Estatus <> 'Despachado' " ;

        $ejecutar_sentencia_Guias = $conn->query($sqlDatos);

        // Verifica si la consulta retorna resultados

        // Obtiene los datos en forma de un arreglo
        $lista_Guias =$ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
    }

}catch (Exception $ex){

    $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ️! -- </strong> ' . $ex . '
                                </div>';
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


    <![endif]-->

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

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fondo oscuro semi-transparente */
            z-index: 9999; /* Asegura que esté por encima de todo */
            display: none; /* Inicialmente oculto */
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
<div class="overlay" id="overlay"></div>
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
                            <a class="dropdown-item" href="javascript:ReloadPage();">Actualizar Pagina</a>


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
        <div class="container-fluid animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h4 class="card-title">Detalles de piking en la Guia: <?php echo $NoGuia; ?></h4>

                            <?php echo $Mensajeerror;?>


                            <div class="card">
                                <h6 class="card-subtitle">Datos generales de la guia </h6>

                                <!--                                    Barra de acciones-->
                                <nav class="navbar navbar-expand-lg text-center">


                                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                        <span class="navbar-toggler-icon"></span>
                                    </button>
                                    <div class="collapse navbar-collapse" id="navbarNav">
                                        <ul class="navbar-nav ">

                                            <li class="nav-item active">
                                                <a class="btn btn-outline-danger" style="margin-left: 2rem" href="DetalleGuiasDespachadas.php?Guia=<?php echo $NoGuia ?>"><span > Regresar </span></a>
                                            </li>
                                        </ul>
                                    </div>
                                </nav>
                            </div>



                        <div class="card">

                            <div class="card-body">
                                <h4 class="card-title">Detalles del pedido</h4>
                                <h6 class="card-subtitle">valide los registros relacionados con el pedido  </h6>

                                <li class="nav-item active">
    <a id="btnDespachar" class="btn btn-outline-info" style="margin-left: 2rem" href="DespacharPickingMasivo.php?Guia=<?php echo $NoGuia ?>" onclick="desaparecerBoton(this);"> Despachar Pickeo Masivo </a>
</li>

<script>
    function desaparecerBoton(boton) {
        // Deshabilitar el botón y cambiar su texto
        boton.disabled = true;
        boton.innerHTML = 'Procesando, espere...';

        // Desaparecer el botón después de 2 segundos (2000 milisegundos)
        setTimeout(function() {
            boton.style.display = 'none';
        }, 500);

        // O bien, si deseas que el botón desaparezca inmediatamente después de hacer clic, puedes eliminar el setTimeout y solo dejar la siguiente línea:
        // boton.style.display = 'none';
    }
</script>

                                <br>
                                <!-- Start First Cards -->
                                <div class="row">
                                    <!-- Column -->
                                    <div class="col-md-12">


                                        <table id="example" class="table table-striped  " cellspacing="0" width="100%">
                                            <thead>


                                            <th>Material</th>
                                            <th>Descripcion</th>
                                            <th>Unidades de piking</th>
                                            <th>Peso Neto</th>
                                            <th>Peso Bruto</th>
                                            <th>Despachado</th>
                                            <th>Tipo de Despacho</th>
                                            <th>Ubicacion</th>
                                            <th>Lote</th>
                                            <th>Despachar</th>

                                            </thead>
                                            <tbody>
                                            <?php
                                            for ($i = 0; $i < $lista_Guias; $i++) {
                                                echo "<tr>";

                                                echo "<td>";
                                                echo $lista_Guias['Material'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['Descripcion'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['Cajas'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['PesoNeto'];
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['PesoBruto'];
                                                echo "</td>";

                                                echo "<td>";
                                                {
                                                    //Estatus del Producto
                                                    switch ($lista_Guias['Estatus']){
                                                        case 'Pendiente' :
                                                            echo '<img src="../assets/images/Iconos/circuloNaranja.png" class="" --="" width="auto" height="40">';
                                                            break;

                                                        case 'Despachado' :
                                                            echo '<img src="../assets/images/Iconos/circuloVerde.png" class="" --="" width="auto" height="40">';
                                                            break;

                                                        default :
                                                            echo '<img src="../assets/images/Iconos/circuloRojo.png" class="" --="" width="auto" height="40">';
                                                            break;

                                                    }
                                                }
                                                echo "</td>";

                                                echo "<td>";
                                                echo $lista_Guias['Ubicacion'];
                                                echo "</td>";



                                                // Ubicacion
                                                echo "<td>";
                                                echo $lista_Guias['tipo'];
                                                echo "</td>";


                                                //lote
                                                echo "<td>";

                                                echo '<select id="txtLotes" name="txtLotes" style="color: black" >';
                                                $conn = new mysqli($servername, $username, $password, $dbname);
                                                $IDH = $lista_Guias['Material'];
                                               // $cargos = "SELECT distinct(LoteProduccion) as Lotes FROM dbs9098416.detalle_piking where IDH = $IDH  and Estatus is null order by LoteProduccion desc;";
                                                $cargos = "SELECT DISTINCT(LoteProduccion) as Lotes, count(*) as Bultos FROM dbs9098416.detalle_piking WHERE IDH = $IDH  and Estatus is null and EstatusProducto != 'Bloqueado' group by LoteProduccion ORDER BY CASE WHEN LoteProduccion LIKE 'G.M %' THEN STR_TO_DATE(SUBSTRING(LoteProduccion, 5), '%d-%m-%Y') ELSE STR_TO_DATE(LoteProduccion, '%d-%m-%Y') END DESC ;";

                                                $result = $conn->query($cargos);
                                               
                                                if ($result->num_rows > 0) {
                                                    while ($row = $result->fetch_assoc()) {

                                                        echo '<option value="' . $row['Lotes'] . '">' . $row['Lotes'] . '-- ' . $row['Bultos'] . '  Bultos</option>';                                                    }
                                                }
                                                echo '</select>';

                                                echo "</td>";

                                                if(DisponibleparaPikear($IDH)){

                                                    echo "<td>";
                                                    $Clase ='class="btn btn-outline-success" ';
                                                    echo '<a href="javascript:void(0);"  onclick="despacharPicking(' . $NoGuia . ', \'' . $lista_Guias['Material'] . '\', \'' . $lista_Guias['Cajas'] . '\', \'' . $lista_Guias['IDRegistro'] . '\')" '. $Clase.'>Pikear</a>';
                                                    echo "</td>";


                                                   

                                                } else{
                                                    echo "<td>";
                                                    $Clase ='class="btn btn-outline-danger" ';
                                                    echo '<a '. $Clase.'>Necesita Abastecer para Pikear</a>';
                                                    echo "</td>";

                                                }


                                                echo "</tr>";

                                                $lista_Guias = $ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);
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
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>



    

<script>
    function despacharPicking(guia, idh, cajas, Id_Despacho) {
        // Atenuar pantalla
        var overlay = document.getElementById("overlay");
        overlay.style.display = "block"; // Mostrar superposición

        var selectedLote = document.getElementById("txtLotes").value;
        var url = "DespacharPicking.php?Guia=" + guia + "&IDH=" + idh + "&Lote=" + encodeURIComponent(selectedLote) + "&Cantidad=" + cajas + "&IDDespacho=" + Id_Despacho;

        // Simular un retraso para mostrar la superposición
        setTimeout(function() {
            window.location.href = url;
        }, 1000); // 1000ms = 1 segundo
    }
</script>

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                language: {
                    url: 'datatables_espanol.json'
                }

            });
        });
    </script>

    <script>
        document.querySelector('input[type="submit"]').addEventListener('click', function(event) {
            if (!document.querySelector('input[type="file"]').files.length) {
                event.preventDefault();
                $("#errorModal").modal("show");
            }
        });
    </script>

                <script>
                    function mostrarCampoNuevo(button) {
                        var select = button.parentNode.querySelector('.txtLotes');
                        var nuevoValorInput = button.parentNode.querySelector('.nuevoValor');

                        if (select.value === 'nuevo') {
                            nuevoValorInput.style.display = 'block';
                        } else {
                            nuevoValorInput.style.display = 'none';
                        }
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
