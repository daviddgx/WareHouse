<?php
session_start();
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
    exit();
}

function exportToExcel() {
    include '../LQS_EUQ/Connect.php';
    $tableName = "dbs9098416.posiciones";

    // ✅ Obtener fecha actual en formato día-mes-año
    $fecha = date('d-m-Y');
    $fileName = "Archivo_FIFO_$fecha.csv";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $sql = "SELECT PS.Bodega, PS.Carril, PS.Posicion, PS.Nivel, PS.Ubicacion, PS.Estado, PS.IDH, PR.Descripcion, PR.Linea, PS.PaletCompleto, PS.UnidadesEnPallet, PS.Origen, PS.FechaProduccion, PS.LoteProduccion, PS.FechaIngreso, PS.FechaVencimiento, PS.FechaCuarentena, PS.Cantidad, PS.EstatusProducto, PS.Verificador, PS.UsuarioMontaCargas, PS.Turno, PS.EstatusUbicacion, PS.Observaciones
            FROM `posiciones` PS  
            INNER JOIN productos PR ON PR.IDH = PS.IDH";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // ✅ Encabezados para forzar descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        $output = fopen("php://output", "w");

        // ✅ Encabezados de columna
        $row = $result->fetch_assoc();
        $headings = array_keys($row);
        fputcsv($output, $headings, ';');  // separador ;

        // ✅ Datos
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row, ';'); // separador ;
        }

        fclose($output);
    } else {
        echo "Sin datos para exportar.";
    }

    $conn->close();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'ejecutar_funcion') {
    exportToExcel();
}

exportToExcel();
?>
