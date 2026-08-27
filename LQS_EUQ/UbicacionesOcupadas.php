<?php
include 'Connect.php';

$lista_Guias = array();

try {
    $conn = new PDO(
        'mysql:host=' . $servername . ';dbname=' . $dbname . ';charset=utf8',
        $username,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    $sqlDatos = "SELECT Ubicacion, IDH, UnidadesEnPallet, Origen, FechaIngreso,
                        FechaProduccion, LoteProduccion
                 FROM posiciones
                 WHERE Estado = 'Ocupada' AND Bodega = ? AND Carril = ?";
    $sentenciaGuias = $conn->prepare($sqlDatos);
    $sentenciaGuias->execute(array($txtBodega, $txtCarril));
    $lista_Guias = $sentenciaGuias->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $ex) {
    error_log('UbicacionesOcupadas: ' . $ex->getMessage());
}
?>
