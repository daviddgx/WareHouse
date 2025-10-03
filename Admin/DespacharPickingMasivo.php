<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
ob_start();
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

$Guia = $_GET['Guia'];

$BultosDespachables = 0;

function Alcanza( $Lote, $IDH, $Guia,$CajasDespacho) {
   
    $error = "";
    $mensajeExito = "";
    $servername="db5010754261.hosting-data.io";
    $username="dbu2953297";
    $password="zecsyd-nafwoQ-kypky3";
    $dbname="dbs9098416";
    global $BultosDespachables;
    
    $conn0 = new mysqli($servername, $username, $password, $dbname);
    $SQL = "SELECT COUNT(*) AS Disponibles FROM detalle_piking WHERE LoteProduccion = '$Lote' AND IDH = $IDH AND Estatus IS NULL";

    echo"-------------------- ";
    echo " Cajas para Alcanza: " . $CajasDespacho;

    $result = $conn0->query($SQL);

    $Disponibles = 0;
    if ($result->num_rows > 0) {
        while ($row1 = $result->fetch_assoc()) {
            $Disponibles = $row1['Disponibles'];
        }
    }

    if($Disponibles>= $CajasDespacho){
        return true;
    }else{
          $BultosDespachables = $Disponibles;
        return false;

    }

}



// Bucle para los registros de IDH y LOTE

$conn0 = new mysqli($servername, $username, $password, $dbname);
$cargos = "SELECT DG.IdRegistro ,DG.Material,DG.Cajas , DP.LoteProduccion FROM `DetalleGuias` DG
inner Join detalle_piking DP on  DP.IDH = DG.Material
where DG.Transporte = $Guia and DG.Ubicacion = 'Piking' and DP.Estatus is null and DG.Estatus = ''
group by DG.IdRegistro,DP.IDH order by DP.FechaProduccion asc";


$result = $conn0->query($cargos);
$NoBucle = 0;

if ($result->num_rows > 0) {
    while ($row1 = $result->fetch_assoc()) {

       // echo"--------------------";
       // echo "Bucle principal, Reccorrido No: " . $NoBucle;
        //Proceso($row['LoteProduccion'],$row['Material'],$Guia,$row['Cajas'],$row['IdRegistro']);
        $Lote = $row1['LoteProduccion'];
        $IDH =$row1['Material'];
        $CajasDespacho = $row1['Cajas'];
        $ID_DespachoDG = $row1['IdRegistro'];

        echo"---InicioDatos---". "\n";
        echo " Lote: " .$Lote."\n";
        echo " IDH: " .$IDH."\n";
        echo " CajasDespacho: " .$CajasDespacho."\n";
        echo " BultosDespachables: " .$BultosDespachables."\n";
        echo " ID_DespachoDG: " .$ID_DespachoDG."\n";
        echo"--FinDatos----". "\n";
        

try {
        // Nueva forma directa
       // echo "Creacion de Conexiones: " . $NoBucle;
        $conexion = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
      //  echo "Validacion si alcanza " . $NoBucle;
        if (Alcanza( $Lote, $IDH, $Guia,$CajasDespacho)) {
            echo "Alcanza TRUE, No. de Bucle: " . $NoBucle;
            $FechaActualREG = date('Y-m-d H:i:s');
            $SQL00 = "INSERT INTO despachos (Guia_Carga, Entrega, IDH, Descripcion, Posicion, Rampa, Fecha_Hora_Despacho, FechaRealizado, Operador, Estado) VALUES (?, NULL, ?, NULL, NULL, NULL, ?, ?, 'PIKING', 'Despachado')";
            $stmt00 = $conexion->prepare($SQL00);
            $stmt00->execute([$Guia, $IDH, $FechaActualREG, $FechaActualREG]);

        
            $SQL0 = "UPDATE detalle_piking SET Estatus = 'Despachado', ID_Despacho = $ID_DespachoDG, Transporte = $Guia  WHERE IDH = $IDH AND Estatus IS NULL AND LoteProduccion = '$Lote' LIMIT $CajasDespacho";
            echo "   \n-- Si Alcanza --\n  "; 
            echo $SQL0;
            echo "   \n--\n  ";
            $stmt0 = $conexion->prepare($SQL0);
            $stmt0->execute();
            
        
            $SQL1 = "UPDATE DetalleGuias SET Estatus = 'Despachado' WHERE Transporte = ? AND Material = ? AND Tipo = 'Piking'";
            $stmt1 = $conexion->prepare($SQL1);
            $stmt1->execute([$Guia, $IDH]);

        } else {
        
            echo "Alcanza FALSE, No. de Bucle: " . $NoBucle;
            echo"-- Bultos despachables: $BultosDespachables se intentan despachar: $CajasDespacho para el IDH:  $IDH";
            $SQL0 = "UPDATE detalle_piking SET Estatus = 'Despachado', ID_Despacho = $ID_DespachoDG, Transporte = $Guia  WHERE IDH = $IDH AND Estatus IS NULL AND LoteProduccion = '$Lote' LIMIT $BultosDespachables";
            
            echo $SQL0;
            echo "   \n--\n  ";
            $stmt0 = $conexion->prepare($SQL0);
            $stmt0->execute();
            
        
            $NuevoValorBultosDespachables = $CajasDespacho - $BultosDespachables;
            $SQL1 = "UPDATE DetalleGuias SET Cajas = ? WHERE Transporte = ? AND Material = ? AND Tipo = 'Piking'";
            $stmt1 = $conexion->prepare($SQL1);
            $stmt1->execute([$NuevoValorBultosDespachables, $Guia, $IDH]);

            $BultosDespachables = 0;
        }

    } catch (Exception $e) {
      //  echo "Error: " . $e->getMessage();
    }
    
       // echo "Fin del proceso principal Bucle No: " .$NoBucle;

        $NoBucle += 1;
                                          }
}

// Fin del bucle para los registros de IDH y LOTE

header('Location: RegistroPiking.php?Guia=' . $Guia);
ob_end_flush();
?>
