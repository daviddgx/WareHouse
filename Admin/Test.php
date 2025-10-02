<?php

$Transporte = 11113597;
$Entrega = 902781598 ;



date_default_timezone_set('America/Guatemala');

$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date(' G:i:s ', time());
$fechaActualizacion = $fechaConsulta." ".$hora;

$FechaQRU = $fechaConsulta." ".$hora;

echo $FechaQRU;

?>
