<?php
ob_start();
session_start();
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
    
}

function exportToExcel() {
    $HoraTrabajoInicio = $_GET['fecha1'] ?? '';
    $HoraTrabajoFinal = $_GET['fecha2'] ?? '';
    include '../LQS_EUQ/Connect.php';

    $inicioTimestamp = strtotime($HoraTrabajoInicio);
    $finalTimestamp = strtotime($HoraTrabajoFinal);

    if (
        $inicioTimestamp === false
        || $finalTimestamp === false
        || $finalTimestamp < $inicioTimestamp
    ) {
        http_response_code(400);
        die('El rango de fechas no es válido.');
    }

    /*
     * Se utiliza un rango semiabierto [inicio, fin) para incluir todas las
     * horas del último día sin aplicar DATE() sobre las columnas indexadas.
     */
    $fechaInicioSql = date('Y-m-d 00:00:00', $inicioTimestamp);
    $fechaFinExclusivaSql = date('Y-m-d 00:00:00', strtotime('+1 day', $finalTimestamp));

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

    /*
     * detalle_piking se agrega antes de realizar los JOIN para impedir que
     * varias guías, configuraciones o despachos multipliquen cajas y peso.
     * Los lotes con fechas de producción/vencimiento diferentes permanecen
     * separados.
     */
    $sql = "
SELECT
    D.Estado,
    D.Posicion,
    P.Nivel,
    D.Descripcion,
    P.Bodega,
    D.IDH,
    DATE(PH.FechaProduccion) AS FechaProduccion,
    DATE(PH.FechaVencimiento) AS FechaVencimiento,
    D.Operador,
    'Turno' AS Turno,
    'Tapado/Libre' AS `Tapado/Libre`,
    G.NombreDestino,
    G.Transportista,
    D.Guia_Carga AS Transporte,
    TIME(D.FechaRealizado) AS HoraDeDespacho,
    'Notas' AS Notas,
    IFNULL(
        TIMESTAMPDIFF(
            MONTH,
            DATE(D.FechaRealizado),
            DATE(PH.FechaVencimiento)
        ),
        'No se puede calcular'
    ) AS MesesVidaUtil,
    'Tapando/NoTapando' AS `Tapando/NoTapando`,
    PH.EstatusUbicacion AS ProductoEsta,
    PR.CAJASXPALET,
    PR.LINEA,
    PR.PESOBRUTOCAJA AS PesoPorCaja,
    PH.UnidadesEnPallet AS Cajas,
    ROUND(
        (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet) / 1000,
        3
    ) AS PesoDeDespacho,
    D.FechaRealizado AS FechaDespacho,
    MONTHNAME(D.FechaRealizado) AS MES,
    DATE_FORMAT(D.FechaRealizado, '%W') AS nombre_dia
FROM despachos D
LEFT JOIN (
    SELECT
        Ubicacion,
        GROUP_CONCAT(DISTINCT Nivel ORDER BY Nivel SEPARATOR ' / ') AS Nivel,
        GROUP_CONCAT(DISTINCT Bodega ORDER BY Bodega SEPARATOR ' / ') AS Bodega
    FROM posiciones
    GROUP BY Ubicacion
) P
    ON P.Ubicacion = D.Posicion
LEFT JOIN posiciones_historico PH
    ON PH.ID_Movimiento = D.Movimiento
    AND PH.TipoMovimiento = 'Despacho'
LEFT JOIN (
    SELECT
        Transporte,
        GROUP_CONCAT(
            DISTINCT NombreDestino
            ORDER BY NombreDestino
            SEPARATOR ' / '
        ) AS NombreDestino,
        GROUP_CONCAT(
            DISTINCT Transportista
            ORDER BY Transportista
            SEPARATOR ' / '
        ) AS Transportista
    FROM Guias
    GROUP BY Transporte
) G
    ON G.Transporte = D.Guia_Carga
LEFT JOIN productos PR
    ON PR.IDH = D.IDH
WHERE
    D.FechaRealizado >= ?
    AND D.FechaRealizado < ?
    AND (
        D.Operador IS NULL
        OR UPPER(TRIM(D.Operador)) <> 'PIKING'
    )

UNION ALL

SELECT
    DP.Estatus AS Estado,
    CF.Ubicacion AS Posicion,
    'N/A' AS Nivel,
    PR.Descripcion,
    'Picking' AS Bodega,
    DP.IDH,
    DP.FechaProduccion,
    DP.FechaVencimiento,
    'PIKING' AS Operador,
    'N/A' AS Turno,
    'N/A' AS `Tapado/Libre`,
    G.NombreDestino,
    G.Transportista,
    DP.Transporte,
    TIME(DS.FechaDespacho) AS HoraDeDespacho,
    'N/A' AS Notas,
    IFNULL(
        TIMESTAMPDIFF(
            MONTH,
            DATE(DS.FechaDespacho),
            DP.FechaVencimiento
        ),
        'No se puede calcular'
    ) AS MesesVidaUtil,
    'N/A' AS `Tapando/NoTapando`,
    'N/A' AS ProductoEsta,
    PR.CAJASXPALET,
    PR.LINEA,
    PR.PESOBRUTOCAJA AS PesoPorCaja,
    DP.Cajas,
    ROUND(
        (PR.PESOBRUTOCAJA * DP.Cajas) / 1000,
        3
    ) AS PesoDeDespacho,
    DS.FechaDespacho,
    MONTHNAME(DS.FechaDespacho) AS MES,
    DATE_FORMAT(DS.FechaDespacho, '%W') AS nombre_dia
FROM (
    SELECT
        Transporte,
        IDH,
        Estatus,
        DATE(FechaProduccion) AS FechaProduccion,
        DATE(FechaVencimiento) AS FechaVencimiento,
        SUM(COALESCE(UnidadesEnPallet, 0)) AS Cajas
    FROM detalle_piking
    GROUP BY
        Transporte,
        IDH,
        Estatus,
        DATE(FechaProduccion),
        DATE(FechaVencimiento)
) DP
INNER JOIN (
    SELECT
        Guia_Carga AS Transporte,
        IDH,
        MIN(
            COALESCE(FechaRealizado, Fecha_Hora_Despacho)
        ) AS FechaDespacho
    FROM despachos
    WHERE
        Fecha_Hora_Despacho >= ?
        AND Fecha_Hora_Despacho < ?
        AND UPPER(TRIM(Operador)) = 'PIKING'
    GROUP BY
        Guia_Carga,
        IDH
) DS
    ON DS.Transporte = DP.Transporte
    AND DS.IDH = DP.IDH
LEFT JOIN productos PR
    ON PR.IDH = DP.IDH
LEFT JOIN (
    SELECT
        IDH,
        GROUP_CONCAT(
            DISTINCT Ubicacion
            ORDER BY Ubicacion
            SEPARATOR ' / '
        ) AS Ubicacion
    FROM config_piking
    GROUP BY IDH
) CF
    ON CF.IDH = DP.IDH
LEFT JOIN (
    SELECT
        Transporte,
        GROUP_CONCAT(
            DISTINCT NombreDestino
            ORDER BY NombreDestino
            SEPARATOR ' / '
        ) AS NombreDestino,
        GROUP_CONCAT(
            DISTINCT Transportista
            ORDER BY Transportista
            SEPARATOR ' / '
        ) AS Transportista
    FROM Guias
    GROUP BY Transporte
) G
    ON G.Transporte = DP.Transporte
ORDER BY
    FechaDespacho,
    Transporte,
    IDH,
    FechaVencimiento";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        die('No se pudo preparar la consulta: ' . $conn->error);
    }

    $stmt->bind_param(
        'ssss',
        $fechaInicioSql,
        $fechaFinExclusivaSql,
        $fechaInicioSql,
        $fechaFinExclusivaSql
    );

    if (!$stmt->execute()) {
        http_response_code(500);
        die('No se pudo ejecutar la consulta: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        http_response_code(500);
        die('No se pudo obtener el resultado: ' . $stmt->error);
    }

    if ($result->num_rows > 0) {
        // Output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$fileName");

        // Create a file pointer connected to the output stream
        $output = fopen("php://output", "w");

        // Output the column headings
        $row = $result->fetch_assoc();
        $headings = array_keys($row);
        fputcsv($output, $headings, ',');

        // Loop over the rows, outputting them
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            //ANCHOR - Separador del archivo, ahorita esta en punto y coma
            fputcsv($output, $row, ',');
        }

        fclose($output);
    } else {
        echo "No hay datos para exportar.";
    }

    $stmt->close();
    $conn->close();
}

exportToExcel();


ob_end_flush();
?>
