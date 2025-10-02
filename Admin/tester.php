<?php
// 3. Inserter Unidades Pallet en "Detalle Guias"
//3.1 recorre el detalle del despacho para ingresar las unidades a calcular, dependiendo si hay palles o piking

include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';
$conexion = new mysqli($servername, $username, $password, $dbname);
$conexion2 = new mysqli($servername, $username, $password, $dbname);
$Transporte = 11174752;


$query = "SELECT * FROM dbs9098416.DetalleGuias_Carga where Transporte = $Transporte and Pallets > 0;";

if ($result = $conexion->query($query)) {
    /* obtener el array de objetos */
    while ($row = mysqli_fetch_array($result)) {
        echo "Valores del Wile:". $row['Pallets'] ;
        // Repetir el Echo por cada producto
        for ($i = 1; $i <= $row['Pallets']; $i++) {
            // Insertar cada vez
            $SQLINS = "Insert into dbs9098416.DetalleGuias VALUES (null,".$row['Transporte'].",".$row['Entrega'].",".$row['Material'].",".$row['Cajas'].",".$row['PesoNeto'].",".$row['PesoBruto'].", '', '', '".$row['Tipo']."' ) ";
            echo $SQLINS. $i."\n";
            $result2 = $conexion2->query($SQLINS);
        }
    }
    /* liberar el conjunto de resultados */
echo "Procesado correctamente";
} else{

    echo "No se puede procesar";
}

$SQL4 = "Insert into dbs9098416.DetalleGuias (Select null,Transporte,Entrega,Material,Cajas,PesoNeto,PesoBruto,'','',Tipo from dbs9098416.DetalleGuias_Carga where Transporte = '".$Transporte."'  and Tipo = 'Piking');";
$result2 = $conexion2->query($SQL4);

?>
