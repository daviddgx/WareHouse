<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
$Transporte = 11113597;
$Entrega = 902781598 ;



date_default_timezone_set('America/Guatemala');

$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());
$fechaActualizacion = $fechaConsulta." ".$hora;

$FechaQRU = $fechaConsulta." ".$hora;

echo $FechaQRU;

?>
