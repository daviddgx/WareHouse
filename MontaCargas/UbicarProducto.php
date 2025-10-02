<?php
ob_start();
include "../Innet_MTC/Innet_MTC.php";
//Capturar el registro
$IDRegistro = $_GET['Guia'];
$IDH = $_GET['IDH'];
$Ubicacion = $_GET['Ubicacion'];

date_default_timezone_set('America/Guatemala');
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;
$FechaCuarentena = date("Y") . '-' . date("m") . '-' . date("d") ;

// PENDIENTE //
// Actualizar los datos
//1. Actualizar ESTADO de la tabla Asignaciones a Ingresado

//ANCHOR - Logica para ingresar a Piking
//ANCHOR -  se corrige porque las ubicaciones no inician con 10, se obtiene los datos de Piking

if(ValidarSiEsPiking($Ubicacion)){

    IgresoPiking($IDRegistro);
    IngresarProducto($IDRegistro,$Fecha);

    //2. Actualizar la BITACORA para registrar el Movimiento
$Evento = "IngresoPK";
$TipoEvento = "Operacion";
$EstadoAnterior = "Por Ingresar";
$EstadoNuevo ="Ingresado ID_Registro: ".$IDRegistro;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);
//3. Actualizar el estado de la Ubicacion con el ID calcular si necesita cuarentena y poner los valores.
//3.1 Traer Datos del Producto
//3.2 Crear las variables con los datos del producto

}else{

    ActualizarUbicacion($IDRegistro,$Ubicacion);
    CaluclarCuarentena($FechaCuarentena);
    IngresarProducto($IDRegistro,$Fecha);

    //2. Actualizar la BITACORA para registrar el Movimiento
$Evento = "Ingreso";
$TipoEvento = "Operacion";
$EstadoAnterior = "Por Ingresar";
$EstadoNuevo ="Ingresado ID_Registro: ".$IDRegistro;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);
//3. Actualizar el estado de la Ubicacion con el ID calcular si necesita cuarentena y poner los valores.
//3.1 Traer Datos del Producto
//3.2 Crear las variables con los datos del producto

}


//Redireccionar la pagina a Lista_Asignaciones.php
header('Location: Lista_Asignaciones.php?IDH='.$IDH.'');
ob_end_flush();


?>