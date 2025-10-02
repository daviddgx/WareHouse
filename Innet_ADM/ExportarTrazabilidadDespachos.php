<?php
session_start();
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
    exit();
}

function exportToExcel() {
    $HoraTrabajoInicio = $_GET['fecha1'];
    $HoraTrabajoFinal = $_GET['fecha2'];
    include '../LQS_EUQ/Connect.php';

    // Formatear las fechas para el nombre del archivo
    $formattedStartDate = date('Y-m-d', strtotime($HoraTrabajoInicio));
    $formattedEndDate = date('Y-m-d', strtotime($HoraTrabajoFinal));
    $fileName = "Despachos_del_{$formattedStartDate}_al_{$formattedEndDate}.csv";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        echo "Se generó un error en la conexión";
        die("Connection failed: " . $conn->connect_error);
    }

    // Select all data from the table
    $sql = "SELECT DISTINCT
    D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
    DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
    D.Operador, 'Turno', 'Tapado/Libre', G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
    TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
    IFNULL(TIMESTAMPDIFF(MONTH, DATE(D.FechaRealizado), DATE(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
    'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja, PH.UnidadesEnPallet as Cajas, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet) / 1000 as PesoDeDespacho,
    D.FechaRealizado AS FechaDespacho, MONTHNAME(FechaRealizado) AS MES, DATE_FORMAT(FechaRealizado, '%W') AS nombre_dia  
FROM despachos D
LEFT JOIN posiciones P ON P.Ubicacion = D.Posicion
LEFT JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
LEFT JOIN Guias G ON G.Transporte = D.Guia_Carga 
LEFT JOIN productos PR ON PR.IDH = D.IDH
WHERE DATE(D.FechaRealizado) BETWEEN '$HoraTrabajoInicio' AND '$HoraTrabajoFinal' and D.Operador <> 'PIKING'

UNION

SELECT DP.Estatus, CF.Ubicacion, 'N/A', PR.Descripcion, 'Picking', DP.IDH, DP.FechaProduccion, DP.FechaVencimiento, DS.Operador, 'N/A', 'N/A', GS.NombreDestino, GS.Transportista, DP.Transporte, TIME(DS.FechaRealizado), 'N/A', 
    IFNULL(TIMESTAMPDIFF(MONTH, DATE(DS.FechaRealizado), DATE(DP.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 'N/A', 'N/A', SUM(DP.UnidadesEnPallet) AS cajasxpallet, PR.LINEA, PR.PESOBRUTOCAJA, 
    SUM(DP.UnidadesEnPallet) as CajasPK, (PR.PESOBRUTOCAJA * SUM(DP.UnidadesEnPallet)) / 1000, DS.FechaRealizado, DATE_FORMAT(DS.FechaRealizado, '%M') AS nombre_mes, DATE_FORMAT(DS.FechaRealizado, '%W') AS nombre_dia
FROM detalle_piking DP
LEFT JOIN productos PR ON DP.IDH = PR.IDH
LEFT JOIN config_piking CF ON DP.IDH = CF.IDH
LEFT JOIN despachos DS ON DP.Transporte = DS.Guia_Carga AND DP.IDH = DS.IDH
LEFT JOIN Guias GS ON DP.Transporte = GS.Transporte
WHERE DATE(DS.Fecha_Hora_Despacho) BETWEEN '$HoraTrabajoInicio' AND '$HoraTrabajoFinal' AND DS.Operador = 'Piking'
GROUP BY DP.Transporte, DP.IDH;";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$fileName");

        // Create a file pointer connected to the output stream
        $output = fopen("php://output", "w");

        // Output the column headings
        $row = $result->fetch_assoc();
        $headings = array_keys($row);
        fputcsv($output, $headings, ';');

        // Loop over the rows, outputting them
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            //ANCHOR - Separador del archivo, ahorita esta en punto y coma
            fputcsv($output, $row, ';');
        }

        fclose($output);
    } else {
        echo "No hay datos para exportar.";
    }

    $conn->close();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'ejecutar_funcion') {
    exportToExcel();
}
exportToExcel();

?>