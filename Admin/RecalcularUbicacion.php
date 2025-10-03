<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
ob_start();
//require('Config_Guias.php');
require '../LQS_EUQ/Auth.php';
require '../LQS_EUQ/Connect.php';
require '../Innet_ADM/Innet_AMD.php';
$Transporte = "";
// Create connection

$ID_DetalleGuia;
$Material;
$Guia;
$Ubicacion;
if (isset($_GET['id'])) {
    
    $ID_DetalleGuia = $_GET['id'];
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn2 = new mysqli($servername, $username, $password, $dbname);
    
    
    $connUPD1 = new mysqli($servername, $username, $password, $dbname);
    $connUPD2 = new mysqli($servername, $username, $password, $dbname);
// 1. Validar que el ID que viene no trae registros establecidos en Ubicacion.

    // Tu consulta SQL
    $query = "SELECT * FROM dbs9098416.DetalleGuias  where IDRegistro = $ID_DetalleGuia;";

// Ejecutar la consulta
    $result = $conn->query($query);
   
// Verificar si hay filas de resultado
    if ($result->num_rows > 0) {
        
        while ($row = $result->fetch_assoc()) {
            // Obtener la guia
            $Guia = $row['Transporte'];
            $Material = $row['Material'];


            if($row['Ubicacion'] <> ""){
                echo "Codigo de ERROR ERR1, Ya tiene Ubicacion asignada";
                $Ruta = 'CorregirGuia.php?Guia='.$Guia.'&MSGCODE=ERR1';
                header('Location: '.$Ruta);
            }else{

                // Buscar Ubicacion para recalcular
                $query = "SELECT Ubicacion FROM dbs9098416.posiciones where idh = $Material and EstatusProducto not in ( 'Cuarentena','Calidad') and EstatusUbicacion not in ( 'Cuarentena','Calidad') and Estado = 'Ocupada'  ORDER BY FechaProduccion ASC, Carril ASC,Posicion DESC, Nivel desc LIMIT 1;";
                echo $query;
                $result2 = $conn2->query($query);

                if ($result2->num_rows > 0) {
                   
                    while ($row2 = $result2->fetch_assoc()) {
                        $Ubicacion = $row2['Ubicacion'];
                        


                        // Actualizar el estado de la ubicacion a "Despacho"
                        $queryUPD1 = "update posiciones set Estado = 'Despacho' where Ubicacion = '$Ubicacion' and Estado = 'Ocupada';";
                        echo $queryUPD1;
                        $connUPD1->query($queryUPD1);


                        // Actualizar la ubicacion en el registro
                        $queryUPD2 = "update DetalleGuias  set Ubicacion = '$Ubicacion' where IDRegistro = $ID_DetalleGuia and Ubicacion is null;";
                        echo $queryUPD2;
                        $connUPD1->query($queryUPD2);


                         //Regresamos con Mensaje OK
                         echo "Codigo de ERROR MSG1, Ya tiene Ubicacion asignada";
                        $Ruta = 'CorregirGuia.php?Guia='.$Guia.'&MSGCODE=MSG1';
                        header('Location: '.$Ruta);

                    }
                } else {
                    echo "Codigo de ERROR ERR2, No hay Ubucaciones disponibles, validar Cuarentena / Calidad";
                $Ruta = 'CorregirGuia.php?Guia='.$Guia.'&MSGCODE=ERR2';
                header('Location: '.$Ruta);
                }





                Echo "Aplica para Re Calcular.";
            }

        }
    }
    
}
else{
    header('Location: ../Innet/505.html');
}
ob_end_flush();
?>