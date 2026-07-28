<?php
ob_start();
require_once 'ValidarSesion.php';
include "../Innet_MTC/Innet_MTC.php";
include "../LQS_EUQ/Auth.php";

function redirigirAsignaciones($IDH = '')
{
    $destino = 'Lista_AsignacionesIDH.php';

    if ($IDH !== '') {
        $destino = 'Lista_Asignaciones.php?IDH=' . rawurlencode($IDH);
    }

    header('Location: ' . $destino, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirAsignaciones();
}

$tokenRecibido = isset($_POST['token']) ? $_POST['token'] : '';
$tokenSesion = isset($_SESSION['token_ingreso']) ? $_SESSION['token_ingreso'] : '';
$IDHSolicitado = isset($_POST['IDH']) ? $_POST['IDH'] : '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    redirigirAsignaciones($IDHSolicitado);
}

// Consumir el token antes de modificar datos impide ejecutar dos veces.
unset($_SESSION['token_ingreso']);

$IDRegistro = isset($_POST['Guia']) ? $_POST['Guia'] : '';

if ($IDRegistro === '') {
    redirigirAsignaciones($IDHSolicitado);
}

// IDH, ubicacion y operador se verifican en la base de datos.
$consultaAsignacion = $pdo->prepare(
    "SELECT IDH, Posicion
     FROM dbs9098416.asignaciones
     WHERE Numero = ? AND Operador = ? AND Estado = 'Pendiente'
     LIMIT 1"
);
$consultaAsignacion->execute([$IDRegistro, $_SESSION['Usuario']]);
$asignacionPendiente = $consultaAsignacion->fetch(PDO::FETCH_ASSOC);

if (!$asignacionPendiente) {
    redirigirAsignaciones($IDHSolicitado);
}

$IDH = $asignacionPendiente['IDH'];
$Ubicacion = $asignacionPendiente['Posicion'];

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


// Post/Redirect/Get: recargar o volver atras no reenvia la transaccion.
redirigirAsignaciones($IDH);
