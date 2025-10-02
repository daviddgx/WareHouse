<?php
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


