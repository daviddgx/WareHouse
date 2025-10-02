<?php
ob_start();
session_start();
include '../LQS_EUQ/Auth.php';



// FuncionLogin



if (!empty($_POST['Entrar'])) {
    $LUser = $_POST['UserLog'];
    $LClave = $_POST['ClaveLog'];
    // Creamos la conexion

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {

        $error =
            '<div class="alert alert-danger" role="alert"><p><strong>Existe un problema con la conexion entre el sistema y la base de datos ☹️! por favor contacte al administrador de la aplicacion e informele de este error.</div>';
        // $row = $result->fetch_assoc();
    } else {
        // Obtencion de datos
        $LClave = md5($LClave);



        $sql = "SELECT * FROM dbs9098416.usuarios_app where Nombre_Usuario = '$LUser' and Clave_Usuario = '$LClave';";
        $result = $conn->query($sql);
        // Fin Obtencion de datos
        try {



            if ($result->num_rows > 0) {
                //Salida de datos del query

                // Cambiamos los IF anidados  por Switch/Case para mejorar el rendimiento

                while ($row = $result->fetch_assoc()) {

                    switch ($row['TipoUsuario']) {
                        case '1':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: ../Admin/index.php');
                            break;

                        case '2':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: ../MontaCargas/index.php');
                            break;

                        case '3':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: ../Inventarios/index.php');
                            break;

                        case '4':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: ../Picking/index.php');
                            break;
                        case '5':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: ../DashBoard/index.php');
                            break;
                        case '6':
                            $_SESSION['Usuario'] = $row['Nombre_Usuario'];
                            $_SESSION['USR'] = $row['Nombre'] . ' ' . $row['Apellido'];
                            $_SESSION['pic'] = $row['Foto'];
                            header('Location: ../InventariosPL/index.php');
                            break;
                    }
                }
            } else {
                $error =
                    '<div class="alert alert-danger" role="alert"><p><strong> Usuario o Clave incorrecta, intentelo de nuevo o actualice su clave en la seccion de ayuda. </div>';
                // $row = $result->fetch_assoc();
            }
        } catch (Exception $ex) {
            $Mensajeerror = '<div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Se encontro un error ☹️! -- </strong> ' . $ex . '
                                </div>';
        }
        //comprovacion de dadtos
        //fin comprovacion de datos
    }

    // Fin de la conexion
}

// FinFuncionLogIN
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Requiered meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=divice-whidth, initial-sace=1, shrink-to-fit=no">
    <title>Sertero CBP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
    <link rel="stylesheet" href="FountAuson/css/font-awesome.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="../dist/css/Custom/custom.css">
    <link href="css/animate.css" rel="stylesheet" type="text/css" />
    <link rel="icon" href="../assets/images/sertero/LogoCBP.png" width="auto" height="auto">
    <link href="../dist/css/Custom/admin.css" rel="stylesheet" type="text/css" />

    <!-- Estilos en Css -->
    <style>
        body {

            background: url("../assets/images/sertero/walpalerwarehose.jpeg");
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            background-attachment: fixed;
            overflow: scroll;
            height: 100vh;

        }

    </style>


</head>

<body>

    <!-- Preloader  -->
    <div id="PreLoaderCont">

        <div class="preloader">
            <br></br>
            <div class="logoPre">
                <img src="../assets/images/Sertero/LogoHenkel.png" width="300px" height="auto">
                Sertero<span style="color:#7ca9ca">CBP</span>
            </div>
            <div class="loader-frame">
                <div class="loader1" id="loader1"></div>
                <div class="loader2" id="loader2"></div>
            </div>
        </div>
    </div>
    <!-- Fin Preloader -->

    <!-- Contenido de la pagina -->


    <!-- Fin Contenido  -->
    <div class="hide" id="Contenido">


        <!--Inicia Formulario Login-->
        <div class="my-content formulario">
            <div class="container">

                <div class="row">
                    <div class="col-sm-6 myform-cont centrado">
                        <div class="myform-top">
                            <div class="" style="text-align: center">
                                <h4>Ingreso al sistema</h4>

                            </div>

                        </div>
                        <div class="myform-botton">
                            <form role="form" action="" method="post" class="">
                                <div class="form-grup">
                                    <input type="text" name="UserLog" placeholder="Usuario" class="form-control" id="form-username">
                                </div>
                                <div class="saltito">
                                    <h1></h1>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="ClaveLog" placeholder="Contaseña" class="form-control" id="form-password">
                                </div>
                                <br>
                                <div> <?php echo $error .
                                            $mensajeExito; ?></div>
                                <input type="submit" name="Entrar" value="Entrar" class="effect-button"></input>
                                <!--<div  data-effect="flip" class="effect-button"><a class="nav-item nav-link formulario" href="DashboardAdministrador.php">Entrar </a></div>-->
                                <!-- <input  type="submit" name="Entrar" class="mybtn "></input> -->
                                <!-- Hacer que el boton nos diriga a la pagina de administracion -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </div>

    <!-- Script para Loader  -->
    <!--    <script src="js/materialize.js"></script>-->

    <script>
        window.addEventListener('load', () => {

            carga();

            function carga() {
                document.getElementById('PreLoaderCont').className = 'hide';
                document.getElementById('Contenido').className = 'center animated pulse ';
            }
        })
    </script>
    <!-- Fin Script de Loader -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <!--    <script src="js/jquery-2.1.0.js"></script>-->
    <!--    <script src="js/common-script.js"></script>-->
    <!--    <script src="js/script.min.js"></script>-->
    <script src="js/animated.js" type="text/javascript"></script>


</body>

</html>