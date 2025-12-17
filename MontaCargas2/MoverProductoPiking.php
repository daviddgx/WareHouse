<?php
ob_start();
//Capturar el registro
include "../Innet_MTC/Innet_MTC.php";
//Capturar el registro
$IDH = $_GET['IDH'];
$IDRegistro = $_GET['Guia'];
$Origen = $_GET['Origen'];
$Destino = $_GET['Destino'];

date_default_timezone_set('America/Guatemala');
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;

// Actualizar los datos



//ANCHOR - Mejorar procedimiento, consultar que la ubicacion origen tenga datos antes de hacer los registros.

//2. Actualizar la BITACORA para registrar el Movimiento

$Movimiento = "Piking";
HistoriarUbicacion($Origen,$Movimiento,$IDRegistro);

$Evento = "MoverAPiking";
$TipoEvento = "Operacion";
$EstadoAnterior = "En Ubicacion: ".$Origen;
$EstadoNuevo ="A Ubicacion: ".$Destino."Segun Reabastecimiento: ". $IDRegistro;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);
//3. Actualizar el estado de las ubicaciones, la origen y la destino sin perder los datos de fechas de cuarenten, produccion y lote.





//3.1 Mover Material a Ubicacion Destino y Bitacorear
MoverMaterialPiking($Origen);
//3.2 Liberar Ubicacion Origen
LiberarUbicacion($Origen);

//Corregir Estatus de Ubicacion destino "Movimiento"

CorregirEstatusUbicacionPiking($Origen);

CorregirUbicacionParaPiking($Origen,$Destino);


$Evento = "Ubicacion Liberada";
$TipoEvento = "Operacion";
$EstadoAnterior = "Ocupada por: ".$IDH ;
$EstadoNuevo = "Reubicacion, Registro de Reubicacion a piking: ".$IDRegistro;
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);

$Movimiento = "Piking";
HistoriarUbicacion($Destino,$Movimiento,$IDRegistro);

//1. Actualizar ESTADO de la tabla Reubicaciones a Reubicado



ReubicarProductoPiking($IDRegistro,$Fecha);
//Redireccionar la pagina a ListaDespachos.PHP
header('Location: Lista_Piking.php');
ob_end_flush();
?>