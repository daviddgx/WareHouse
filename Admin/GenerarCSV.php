<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
include '../LQS_EUQ/Connect.php';

if (isset($_GET['IDH'])) {
    $IDH = $_GET['IDH'];

    // Conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Consulta SQL con consulta preparada
    $sql = "SELECT 
    CONCAT(Bodega, '-', Carril) AS Carril,
    posiciones.IDH,
    DATE_FORMAT(posiciones.FechaProduccion, '%d-%m-%Y') AS FechaProduccion,
    PR.Descripcion,
    EstatusUbicacion,
    COUNT(*) AS Pallets
FROM posiciones
INNER JOIN productos PR ON posiciones.IDH = PR.IDH
WHERE posiciones.IDH = ?
GROUP BY 
    Carril,
    posiciones.IDH,
    posiciones.FechaProduccion,
    PR.Descripcion,
    EstatusUbicacion;";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $IDH);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Crear archivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Reporte_FiFo_' . $IDH . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Carril', 'IDH','FechaProduccion', 'Descripción', 'Estado', 'Pallets']);

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
    } else {
        echo "No se encontraron datos para el IDH seleccionado.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "IDH no especificado.";
}
ob_end_flush();
?>