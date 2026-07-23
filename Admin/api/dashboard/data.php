<?php
declare(strict_types=1);
define('ADMIN_SESSION_JSON_RESPONSE', true);
require_once dirname(__DIR__, 2) . '/session_guard.php';

header('Content-Type: application/json');

require_once __DIR__ . '/providers.php';

$fechaFinal = $_GET['fechaFinal'] ?? date('d-m-Y');
$fechaInicial = $_GET['fechaInicial'] ?? date('d-m-Y', strtotime('-8 days'));
$chart = $_GET['chart'] ?? null;

try {
    $data = build_dashboard_data($fechaInicial, $fechaFinal, $chart);
    echo json_encode([
        'success' => true,
        'data' => $data,
    ]);
} catch (Throwable $throwable) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $throwable->getMessage(),
    ]);
}

function build_dashboard_data(string $fechaInicial, string $fechaFinal, ?string $chart = null): array
{
    $builders = [
        'capacidad-por-bodega' => 'dashboard_capacity_by_warehouse',
        'capacidad-diaria' => function () use ($fechaInicial, $fechaFinal) {
            return dashboard_daily_capacity($fechaInicial, $fechaFinal);
        },
    ];

    if ($chart !== null) {
        if (!isset($builders[$chart])) {
            throw new InvalidArgumentException('Gráfica no soportada');
        }

        $builder = $builders[$chart];

        return [$chart => call_user_func($builder)];
    }

    $payload = [];
    foreach ($builders as $key => $builder) {
        $payload[$key] = call_user_func($builder);
    }

    return $payload;
}
ob_end_flush();
?>
