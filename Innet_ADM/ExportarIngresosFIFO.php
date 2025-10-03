<?php
ob_start();
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
    $fileName = "Ingresos_del_{$formattedStartDate}_al_{$formattedEndDate}.csv";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        echo "Se generó un error en la conexión";
    }

    // Select all data from the table
    $sql = "SELECT asg.IDH,asg.Producto, pr.LINEA, asg.Posicion, asg.FechaRegistro, asg.FechaColocado, asg.Estado, asg.Operador, asg.PalletCompleto, asg.Cantidades, asg.Origen, asg.FechaProduccion,asg.LoteProduccion, asg.FechaVencimiento, asg.FechaCuarentena,Verificador FROM asignaciones asg
inner join productos pr on asg.IDH = pr.idh where asg.FechaProduccion BETWEEN '$HoraTrabajoInicio' AND '$HoraTrabajoFinal'; ";
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
            fputcsv($output, $row, ';');
        }

        fclose($output);
    } else {
        echo "No data to export.";
    }

    $conn->close();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'ejecutar_funcion') {
    exportToExcel();
}
exportToExcel();


ob_end_flush();
?>