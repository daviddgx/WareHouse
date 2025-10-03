<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../../../Innet/505.html');
    exit();
}
declare(strict_types=1);

require_once __DIR__ . '/../../../LQS_EUQ/Connect.php';

function dashboard_capacity_by_warehouse(): array
{
    $conn = lqs_get_connection();

    $labelsSql = "SELECT CONCAT(COUNT(*),' - Bodega ', Bodega) AS descripcion\n        FROM posiciones WHERE Estado = 'Ocupada' AND Bodega <> 9\n        GROUP BY Bodega ORDER BY Bodega + 0";

    $labelsResult = $conn->query($labelsSql);
    if ($labelsResult === false) {
        throw new RuntimeException($conn->error);
    }

    $labels = [];
    while ($row = $labelsResult->fetch_assoc()) {
        $labels[] = $row['descripcion'];
    }
    $labelsResult->free();

    $percentSql = "SELECT\n            CAST(Bodega AS SIGNED) AS bodega,\n            ROUND((SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) / COUNT(*)) * 100) AS porcentaje\n        FROM posiciones WHERE Bodega <> 9\n        GROUP BY Bodega\n        UNION\n        SELECT\n            99 AS Bodega,\n            ROUND((SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) / COUNT(*)) * 100) AS porcentaje\n        FROM posiciones WHERE Bodega <> 9\n        ORDER BY bodega";

    $percentResult = $conn->query($percentSql);
    if ($percentResult === false) {
        throw new RuntimeException($conn->error);
    }

    $values = [];
    while ($row = $percentResult->fetch_assoc()) {
        $values[] = (int) $row['porcentaje'];
    }
    $percentResult->free();

    return [
        'labels' => $labels,
        'values' => $values,
    ];
}

function dashboard_daily_capacity(string $fechaInicial, string $fechaFinal): array
{
    $conn = lqs_get_connection();

    $sql = "SELECT Fecha, Cant_CapacidadTotal, Cant_Ocupadas\n            FROM gaf_capacidadbodegasdiaria\n            WHERE NombreBodega = 'Todas'\n              AND DATE(Fecha) BETWEEN ? AND ?\n            ORDER BY DATE(Fecha) DESC";

    $statement = $conn->prepare($sql);
    if ($statement === false) {
        throw new RuntimeException($conn->error);
    }

    $fechaInicialISO = date('Y-m-d', strtotime($fechaInicial));
    $fechaFinalISO = date('Y-m-d', strtotime($fechaFinal));

    $statement->bind_param('ss', $fechaInicialISO, $fechaFinalISO);

    if (!$statement->execute()) {
        throw new RuntimeException($statement->error);
    }

    $result = $statement->get_result();

    $labels = [];
    $capacidad = [];
    $ocupadas = [];
    $porcentaje = [];
    $sumPorcentaje = 0;

    while ($row = $result->fetch_assoc()) {
        $labels[] = date('d/m/Y', strtotime($row['Fecha']));
        $capacidad[] = (int) $row['Cant_CapacidadTotal'];
        $ocupadas[] = (int) $row['Cant_Ocupadas'];

        $valorPorcentaje = 0;
        if ((int) $row['Cant_CapacidadTotal'] > 0) {
            $valorPorcentaje = (int) round($row['Cant_Ocupadas'] / $row['Cant_CapacidadTotal'] * 100);
        }

        $porcentaje[] = $valorPorcentaje;
        $sumPorcentaje += $valorPorcentaje;
    }

    $result->free();
    $statement->close();

    $promedio = count($porcentaje) > 0 ? round($sumPorcentaje / count($porcentaje), 2) : 0;

    return [
        'labels' => $labels,
        'capacidad' => $capacidad,
        'ocupadas' => $ocupadas,
        'porcentaje' => $porcentaje,
        'promedio' => $promedio,
    ];
}
ob_end_flush();
?>
