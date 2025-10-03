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
$conexion = new mysqli($servername, $username, $password, $dbname);


$sql = "CALL PonerGuiasEnFirme()";
if (mysqli_query($conexion, $sql)) {
    header("Location: Traking_Guias.php");
} else {
    header("Location: Traking_Guias.php");
}

ob_end_flush();
?>


