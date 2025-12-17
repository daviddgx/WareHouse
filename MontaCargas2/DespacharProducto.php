<?php
ob_start();
include "../Innet_MTC/Innet_MTC.php";
//Capturar el registro
$IDRegistro = $_GET['Guia'];
$IDH = $_GET['IDH'];
$Ubicacion = $_GET['Ubicacion'];
$Transporte = $_GET['Transporte'];
$Entrega = $_GET['Entrega'];

date_default_timezone_set('America/Guatemala');
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;
// Actualizar los datos
// pasos para despachar
//1. Actualizar ESTADO de la tabla Despachos
DespacharProducto($IDRegistro,$Fecha);

//2. Actualizar la BITACORA para registrar el Movimiento
$Evento = "Despacho";
$TipoEvento = "Operacion";
$EstadoAnterior = "Por Despachar";
$EstadoNuevo ="Despachado";
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);
//3. Actualizar el estado de la Ubicacion para Liberarla
$Movimiento = "Despacho";
HistoriarUbicacion($Ubicacion,$Movimiento,$IDRegistro);
LiberarUbicacion($Ubicacion);

$Evento = "Ubicacion Liberada";
$TipoEvento = "Operacion";
$EstadoAnterior = "Ocupada por: ".$IDH ;
$EstadoNuevo = "Despachado, Registro de Despachos: ".$IDRegistro;
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);

// Actualizar Estatus de detalle de Guia
ActualizarDetalleGuia($Transporte,$Entrega,$Ubicacion);

//Redireccionar la pagina a ListaDespachos.PHP
header('Location: Lista_Despachos.php?Guia='.$Transporte.'&Entrega='.$Entrega.'');
ob_end_flush();
?>