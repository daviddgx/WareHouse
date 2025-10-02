<?php
session_start();
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {

}


function exportToExcel() {
    include '../LQS_EUQ/Connect.php';
    $tableName = "dbs9098416.productos";
    $fileName = "Maestro_Materiales";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        echo "Se genero un error en la conexion";
    }

    // Select all data from the table
    $sql = "SELECT * FROM $tableName";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$fileName.csv");

        // Create a file pointer connected to the output stream
        $output = fopen("php://output", "w");

        // Output the column headings
        $row = $result->fetch_assoc();
        $headings = array_keys($row);
        fputcsv($output, $headings);

        // Loop over the rows, outputting them
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
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

?>
