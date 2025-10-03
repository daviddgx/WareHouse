<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
if (isset($_GET['IDH'])) {
    $conexion = new mysqli($servername, $username, $password, $dbname);

$IDH = $_GET['IDH'];
$Cliente = $_GET['Cliente'];

    $consulta = "Delete FROM dbs9098416.dspachos_especiales where IDH ='".$IDH."' and Cliente ='".$Cliente."';";

    try {
      //  echo $consulta;
        $resultado = $conexion->query($consulta);
        header("Location: DespachosEspeciales.php");

    } catch (Exception $e) {

        header('Location: DespachosEspeciales.php');

    }
}else{
   header('Location: DespachosEspeciales.php');
}

ob_end_flush();
?>


