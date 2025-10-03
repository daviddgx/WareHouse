<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
if (isset($_GET['Cliente'])) {
    $conexion = new mysqli($servername, $username, $password, $dbname);


$Cliente = $_GET['Cliente'];

    $consulta = "Delete FROM dbs9098416.unificacion_entregas where  Cliente ='".$Cliente."';";

    try {
      //  echo $consulta;
        $resultado = $conexion->query($consulta);
        header("Location: Unificaciondeentregas.php");

    } catch (Exception $e) {

        header('Location: Unificaciondeentregas.php');

    }
}else{
   header('Location: Unificaciondeentregas.php');
}

ob_end_flush();
?>


