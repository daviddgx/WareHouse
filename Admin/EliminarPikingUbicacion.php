<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
ob_start();
//require('Config_Guias.php');


if(isset($_GET['Guia'])) {
    $IDH = $_GET['Guia'];
    // Aquí puedes utilizar la variable $guia para realizar las operaciones que necesites
    echo "El valor de Guia es: " . $guia;
} else {
    header("Location: Mantenimiento_Piking.php");
}


include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
$conexion = new mysqli($servername, $username, $password, $dbname);




$sql = "update `config_piking` set IDH = NULL where IDH =$IDH";
if (mysqli_query($conexion, $sql)) {
    header("Location: Mantenimiento_Piking.php");
} else {
    header("Location: Mantenimiento_Piking.php");
}

ob_end_flush();
?>


