<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}

include '../LQS_EUQ/Connect.php';

$posicion = $_GET['Posicion'];

try {
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);

    $sqlDatos = " UPDATE dbs9098416.posiciones 
    SET 
        Estado = 'Libre',
        IDH = NULL,
        PaletCompleto = NULL,
        UnidadesEnPallet = NULL,
        Origen = NULL,
        FechaProduccion = NULL,
        LoteProduccion = NULL,
        fechaIngreso = NULL,
        FechaVencimiento = NULL,
        FechaCuarentena = NULL,
        Cantidad = NULL,
        EstatusProducto = NULL,
        Verificador = NULL,
        UsuarioMontaCargas = NULL,
        turno = NULL,
        EstatusUbicacion = NULL,
        observaciones = NULL
    WHERE
        Ubicacion = '$posicion' and Estado = 'Ocupada';";
        //echo $sqlDatos;
    $ejecutar_sentencia_Guias = $conn->query($sqlDatos);

    // rEGISTRO de Bitacora
    $UsuarioActual = $_SESSION['Usuario'];
    $FechaIngreso = date("Y-m-d H:i:s");


    $SQL = "insert into `Bitar_ConteoCiego` values('$posicion','Eliminar','$FechaIngreso','$UsuarioActual','Se elimina desde mantenimiento')";
    //echo $SQL;
    $ejecutar_sentencia_Guias = $conn->query($SQL);

    header('Location: Invent_Eliminar.php?MSG=SCS');

  

    


} catch (Exception $ex) {
    // Captura la excepción y procesala de alguna manera
    // (por ejemplo, registrando el error en un archivo de log)
    error_log("Error: " . $ex->getMessage());
    echo "Error: " . $ex->getMessage();
}
ob_end_flush();

?>
