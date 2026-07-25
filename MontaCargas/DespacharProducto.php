<?php
ob_start();
session_start();
require_once 'ValidarSesion.php';
include "../Innet_MTC/Innet_MTC.php";
include "../LQS_EUQ/Auth.php";

function redirigirDespachos($Transporte = '', $Entrega = '')
{
    $destino = 'Lista_DespachosGUIAS.php';

    if ($Transporte !== '') {
        $destino = 'Lista_Despachos.php?Guia=' . rawurlencode($Transporte)
            . '&Entrega=' . rawurlencode($Entrega);
    }

    header('Location: ' . $destino, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirDespachos();
}

$tokenRecibido = isset($_POST['token']) ? $_POST['token'] : '';
$tokenSesion = isset($_SESSION['token_despacho']) ? $_SESSION['token_despacho'] : '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    redirigirDespachos(
        isset($_POST['Transporte']) ? $_POST['Transporte'] : '',
        isset($_POST['Entrega']) ? $_POST['Entrega'] : ''
    );
}

// El token se consume antes de modificar datos. La sesión serializa solicitudes
// concurrentes, por lo que un segundo clic no puede ejecutar el proceso otra vez.
unset($_SESSION['token_despacho']);

$IDRegistro = isset($_POST['Guia']) ? $_POST['Guia'] : '';
$Transporte = isset($_POST['Transporte']) ? $_POST['Transporte'] : '';
$Entrega = isset($_POST['Entrega']) ? $_POST['Entrega'] : '';

if ($IDRegistro === '') {
    redirigirDespachos($Transporte, $Entrega);
}

$consultaMovimiento = $pdo->prepare(
    "SELECT IDH, Posicion, Guia_Carga, Entrega
     FROM dbs9098416.despachos
     WHERE Movimiento = ? AND Operador = ? AND Estado = 'Pendiente'
     LIMIT 1"
);
$consultaMovimiento->execute([$IDRegistro, $_SESSION['Usuario']]);
$movimientoPendiente = $consultaMovimiento->fetch(PDO::FETCH_ASSOC);

if (!$movimientoPendiente) {
    redirigirDespachos($Transporte, $Entrega);
}

// Los datos operativos provienen de la base de datos, no de campos editables
// enviados por el navegador.
$IDH = $movimientoPendiente['IDH'];
$Ubicacion = $movimientoPendiente['Posicion'];
$Transporte = $movimientoPendiente['Guia_Carga'];
$Entrega = $movimientoPendiente['Entrega'];

date_default_timezone_set('America/Guatemala');
$Fecha = date("Y") . '-' . date("m") . '-' . date("d"). ' '. date("H") .':'. date("i") . ':' . date("s") ;
// Actualizar los datos
// pasos para despachar
//1. Actualizar ESTADO de la tabla Despachos
if (!DespacharProducto($IDRegistro, $Fecha, $_SESSION['Usuario'])) {
    // El movimiento ya fue procesado o dejó de estar pendiente.
    redirigirDespachos($Transporte, $Entrega);
}

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

// Patrón Post/Redirect/Get: actualizar o volver atrás no repite el despacho.
redirigirDespachos($Transporte, $Entrega);
?>
