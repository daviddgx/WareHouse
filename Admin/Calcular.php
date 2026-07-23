<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
//require('Config_Guias.php');
require '../LQS_EUQ/Auth.php';
require '../LQS_EUQ/Connect.php';
require '../Innet_ADM/Innet_AMD.php';
$Transporte = "";
// Create connection
if (isset($_GET['Guia'])) {

    $conexion2 = new mysqli($servername, $username, $password, $dbname);
    $conn = new mysqli($servername, $username, $password, $dbname);
    $connNV = new mysqli($servername, $username, $password, $dbname);
    $Transporte = $_GET['Guia'];


// 1. Buscar Elementos de desachos especiales para quitar las unidades de mas


    // Tu consulta SQL
    $query = "select * from dspachos_especiales where Cliente =  (select NombreDestino from Guias where Transporte = $Transporte group by  NombreDestino) and IDH in (SELECT Material FROM dbs9098416.DetalleGuias where transporte = $Transporte group by Material)";

// Ejecutar la consulta
    $result = $conn->query($query);
    mysqli_close($conn);
// Verificar si hay filas de resultado
    if ($result->num_rows > 0) {
        // Aplicar tu lógica aquí
        while ($row = $result->fetch_assoc()) {
            // Logica para Recortar el Producto

            // 1. Determinar cuantas Lineas tiene el detalle de guia actual y generar el nuevo registro
            $RegistrosAconservar = 0;
            $QRYNVAL = "Select (Select Cajas from DetalleGuias where Material = " . $row['IDH'] . "  and transporte = $Transporte ) / (SELECT Actual * Nuevo as Preterito FROM dbs9098416.dspachos_especiales where Cliente = (select NombreDestino from Guias where Transporte = $Transporte group by NombreDestino) and IDH = " . $row['IDH'] . " ) as NuevoValor";



            $result2 = $connNV->query($QRYNVAL);

            if ($result2->num_rows > 0) {
                while ($row2 = $result2->fetch_assoc()) {

                    $RegistrosAconservar = intval($row2['NuevoValor']);
                }
            }

            // 2.  Eliminar los registros menos la cantdad determinada

            try {
               // $QRYDLL = "DELETE FROM DetalleGuias WHERE Material = " . $row['IDH'] . "   AND transporte = $Transporte   AND Tipo = 'Pallets'   AND IDRegistro NOT IN ( SELECT IDRegistro  FROM ( SELECT IDRegistro  FROM DetalleGuias  WHERE Material = " . $row['IDH'] . "  AND transporte = $Transporte  AND Tipo = 'Pallets'   ORDER BY IDRegistro ASC  LIMIT $RegistrosAconservar ) AS subquery );";
                $QRYDLL = "update DetalleGuias set Cajas = $RegistrosAconservar WHERE Material =  " . $row['IDH'] . "   AND transporte = $Transporte";

                include '../LQS_EUQ/Auth.php';

                $sentencia = $pdo->prepare($QRYDLL);
                $sentencia->execute();
                $pdo = null;

            } catch (Exception $exception) {
                echo "Error al procesar la limitacion de Registros para Despachos especiales, el detalle del error es: (Puede ser por el cierre de las conexiones) " . $exception->getMessage();

            }

        }
    }
    // 2. Insertar valores en tabla temporal
    $SQL1 = "CALL sp_insertar_detalle_guias_carga(?)";
    $conexion = new mysqli($servername, $username, $password, $dbname);
    if ($conexion->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conexion->connect_error);
    }

// Preparar la sentencia
    $stmt = $conexion->prepare($SQL1);
    if (!$stmt) {
        die("Error al preparar la sentencia: " . $conexion->error);
    }

// Asociar los parámetros
    $stmt->bind_param("s", $Transporte);
// Ejecutar la sentencia
    if ($stmt->execute()) {
        // La sentencia se ejecutó con éxito
    } else {
        // Ocurrió un error al ejecutar la sentencia
        echo "Error al ejecutar el SP: " . $stmt->error;
    }
// Cerrar la conexión
    $stmt->close();
    $conexion->close();


                        // 3. Borrar los datos de Tabla "Detalle Guias"


    $SQL2 = "DELETE FROM dbs9098416.DetalleGuias WHERE Transporte = ?";
    $conexion = new mysqli($servername, $username, $password, $dbname);
    if ($conexion->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conexion->connect_error);
    }

// Preparar la sentencia
    $stmt = $conexion->prepare($SQL2);
    if (!$stmt) {
        die("Error al preparar la sentencia: " . $conexion->error);
    }
// Asociar los parámetros
    $stmt->bind_param("s", $Transporte);
// Ejecutar la sentencia
    if ($stmt->execute()) {
        // La sentencia se ejecutó con éxito
    } else {
        // Ocurrió un error al ejecutar la sentencia
        echo "Error al ejecutar la consulta DELETE: " . $stmt->error;
    }
// Cerrar la conexión
    $stmt->close();
    $conexion->close();


    // 4. Inserter Unidades Pallet en "Detalle Guias"
                            //3.1 recorre el detalle del despacho para ingresar las unidades a calcular, dependiendo si hay palles o piking
                            $query = "SELECT * FROM dbs9098416.DetalleGuias_Carga where Transporte = $Transporte and Pallets > 0;";
                            $conexion = new mysqli($servername, $username, $password, $dbname);
                            if ($result = $conexion->query($query)) {
                                /* obtener el array de objetos */
                                while ($row = mysqli_fetch_array($result)) {
                                    // Repetir el Echo por cada producto
                                    for ($i = 1; $i <= $row['Pallets']; $i++) {
                                        // Insertar cada vez
                                        InserterDetalleGuias($row['Transporte'],$row['Entrega'],$row['Material'],$row['Cajas'],$row['PesoNeto'],$row['PesoBruto'],$row['Tipo']);
                                    }
                                }


                            }

                        // 4. Insertar Unidades Piking en "Detalle Guias"
                            $SQL4 = "Insert into dbs9098416.DetalleGuias (Select null,Transporte,Entrega,Material,Cajas,PesoNeto,PesoBruto,'','',Tipo from dbs9098416.DetalleGuias_Carga where Transporte = '".$Transporte."'  and Tipo = 'Piking');";
                            $result2 = $conexion2->query($SQL4);

                        // 5. Calcular las unidades a despachar

    // PROCESO CRITICO

                            //5.1 Calcular las unidades un producto

                            try {

                                include '../LQS_EUQ/Auth.php';
                                $sentencia = $pdo->prepare("call dbs9098416.CalcularUbicacionesDespacho($Transporte);");
                                $sentencia->execute();
                                $pdo = null;
                                Limpiar_EstatusDespachoMalos();

                                try {
                                    //Aqui hay que colocar la rutina para despachar desde la bodega de Piking desde la ubicacion de Piking
                                    $SQLPK = "UPDATE dbs9098416.DetalleGuias set Ubicacion = 'Piking' where Transporte = '".$Transporte."'  and Tipo ='Piking' ";
                                    $conexion->query($SQLPK);
                                    // Colocar la Guia como Corregir si no hay ubicaciones Calculadas en Pallets
                                    CorregirGuia($Transporte);


                            }catch (Exception $e) {

                                header('Location: AsignarUbicaciones.php');
                                exit;
                            }
                            }catch (Exception $e) {

                                header('Location: AsignarUbicaciones.php');
                                exit;
                            }



    $Ruta = 'DetalleUbicacionesCalculadas.php?Guia='.$Transporte;
    header('Location: '.$Ruta);
}


// Aqui termina el IF SET
else {
    header('Location: AsignarUbicaciones.php');
}



ob_end_flush();
?>