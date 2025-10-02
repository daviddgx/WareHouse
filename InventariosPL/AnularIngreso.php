<?php
ob_start();
session_start();
include "../Innet_MTC/Innet_MTC.php";

if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
}

if (isset($_GET['Ingreso'])) {
    $IDIngreso = $_GET['Ingreso'];
    $IDH = $_GET['IDH'];
    $Ubicacion = $_GET['Ubicacion'];
}  else {
    header('Location: Print_CardexMasivoCargadosIDH.php');
}

LiberarUbicacion($Ubicacion);
AnularIngreso($IDIngreso);

$ruta = "Location: Print_CardexMasivoCargados.php?IDH=".$IDH;
header($ruta );
ob_end_flush();
?>