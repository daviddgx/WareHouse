<?php
ob_start();

declare(strict_types=1);

session_start();
$currentDate = date('Y-m-d');

header('Content-Type: application/json');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Sesión inválida',
    ]);
}

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
