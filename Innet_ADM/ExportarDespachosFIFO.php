<?php
ob_start();
session_start();
if ($_SESSION['Usuario'] == '') {
    header('Location: ../Innet/505.html');
} else {

}



function exportToExcel() {
    $HoraTrabajoInicio = $_GET['fecha1'];
    $HoraTrabajoFinal = $_GET['fecha2'];
    include '../LQS_EUQ/Connect.php';

    $fileName = "Despachos del dia";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        echo "Se genero un error en la conexion";
    }

    // Select all data from the table
    $sql = "SELECT  A.Bodega AS A_BODEGA, 	A.Carril AS A_CARRIL,	A.Posicion AS A_POSICION,	A.Nivel AS A_NIVEL, 	A.Ubicacion AS A_UBICACION, 	A.Estado AS A_ESTADO,	A.IDH AS A_IDH,	A.PaletCompleto AS A_PALLETCOMPLETO,	A.UnidadesEnPallet AS A_UNIDADESENPALLET,	A.Origen AS A_ORIGEN,	A.FechaProduccion AS A_FECHAPRODUCCION,	A.LoteProduccion AS A_LOTEPRODUCCION,	A.FechaIngreso AS A_FECHAINGRESO,	A.FechaVencimiento AS A_FECHAVENCIMIENTO,	A.FechaCuarentena AS A_FECHACUARENTENA,	A.Cantidad AS A_CANTIDAD,	A.EstatatusProducto	AS A_ESTATUSPRODUCTO, A.Verificador AS A_VERIFICADOR,	A.UsuarioMontaCargas AS A_MONTACARGUISTA,	A.Turno AS A_TURNO,	A.EstatusUbicacion AS A_ESTATUSUBICACION,	A.Observaciones AS A_OBSERVACIONES,	B.Movimiento AS B_MOVIMIENTO,	B.Guia_Carga AS B_GUIACARGA,	B.Entrega AS B_ENTREGA,	B.IDH AS B_IDH,	B.Descripcion AS B_DESCRIPCION,	B.Posicion AS B_POSICION,	B.Rampa AS B_RAMPA,	B.Fecha_Hora_Despacho AS B_FECHADESPACHO,	B.FechaRealizado AS B_FECHAREALIZADO,	B.Operador AS B_OPERADOR,	B.Estado AS B_ESTATUS
FROM (
    SELECT *,
           SUBSTRING(ID_Movimiento, LOCATE('Despacho ID:', ID_Movimiento) + CHAR_LENGTH('Despacho ID:')) AS Numero_Despacho
    FROM posiciones_historico
    WHERE ID_Movimiento LIKE 'Despacho ID:%' 
) A
INNER JOIN despachos B ON B.Movimiento = A.Numero_Despacho
where FechaRealizado between '$HoraTrabajoInicio' and '$HoraTrabajoFinal'";
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
