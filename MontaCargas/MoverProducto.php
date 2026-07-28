<?php
ob_start();
require_once 'ValidarSesion.php';
include "../Innet_MTC/Innet_MTC.php";
include "../LQS_EUQ/Auth.php";

function redirigirReubicaciones()
{
    header('Location: Lista_Reubicaciones.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirReubicaciones();
}

$tokenRecibido = isset($_POST['token']) ? $_POST['token'] : '';
$tokenSesion = isset($_SESSION['token_reubicacion']) ? $_SESSION['token_reubicacion'] : '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    redirigirReubicaciones();
}

unset($_SESSION['token_reubicacion']);
$IDRegistro = isset($_POST['Guia']) ? $_POST['Guia'] : '';

if ($IDRegistro === '') {
    redirigirReubicaciones();
}

$consultaMovimiento = $pdo->prepare(
    "SELECT IDH, Origen, Destino
     FROM dbs9098416.Reubicaciones
     WHERE id = ? AND Montacarguista = ? AND Estado = 'Pendiente'
     LIMIT 1"
);
$consultaMovimiento->execute([$IDRegistro, $_SESSION['Usuario']]);
$movimientoPendiente = $consultaMovimiento->fetch(PDO::FETCH_ASSOC);

if (!$movimientoPendiente) {
    redirigirReubicaciones();
}

$IDH = $movimientoPendiente['IDH'];
$Origen = $movimientoPendiente['Origen'];
$Destino = $movimientoPendiente['Destino'];

date_default_timezone_set('America/Guatemala');
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;

// Actualizar los datos

//2. Actualizar la BITACORA para registrar el Movimiento

$Movimiento = "Reubicacion";
HistoriarUbicacion($Origen,$Movimiento,$IDRegistro);

$Evento = "Reubicacion";
$TipoEvento = "Operacion";
$EstadoAnterior = "En Ubicacion: ".$Origen;
$EstadoNuevo ="A Ubicacion: ".$Destino."Segun Reubicacion:". $IDRegistro;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);
//3. Actualizar el estado de las ubicaciones, la origen y la destino sin perder los datos de fechas de cuarenten, produccion y lote.





//3.1 Mover Material a Ubicacion Destino y Bitacorear
MoverMaterial($Origen,$Destino);
//3.2 Liberar Ubicacion Origen
LiberarUbicacion($Origen);

//Corregir Estatus de Ubicacion destino "Movimiento"

CorregirEstatusUbicacion($Destino);

$Evento = "Ubicacion Liberada";
$TipoEvento = "Operacion";
$EstadoAnterior = "Ocupada por: ".$IDH ;
$EstadoNuevo = "Reubicacion, Registro de Reubicacion: ".$IDRegistro;
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;
RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo);

$Movimiento = "Reubicacion ID: ".$IDRegistro;
HistoriarUbicacion($Destino,$Movimiento,$IDRegistro);

//1. Actualizar ESTADO de la tabla Reubicaciones a Reubicado
ReubicarProducto($IDRegistro,$Fecha);
// Post/Redirect/Get evita repetir la operacion al recargar o volver atras.
redirigirReubicaciones();
