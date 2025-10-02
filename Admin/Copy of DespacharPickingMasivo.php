<?php
ob_start();
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

$Guia = $_GET['Guia'];

$BultosDespachables = 0;

function Alcanza($pdo, $Lote, $IDH, $Guia) {
    $sentencia = $pdo->prepare("SELECT COUNT(*) AS Disponibles FROM detalle_piking WHERE LoteProduccion LIKE ? AND IDH = ? AND Estatus IS NULL");

    echo"--------------------
    ";
    echo "SQL para Alcanza: " . $sentencia;
    
    $sentencia->execute(["%$Lote%", $IDH]);
    $CountDisponible =  $sentencia->fetch(PDO::FETCH_ASSOC);

    $sentenciaDes = $pdo->prepare("SELECT Cajas FROM DetalleGuias WHERE transporte = ? AND tipo = 'Piking' AND Material = ?");
    $sentenciaDes->execute([$Guia, $IDH]);
    $CountDespachar =  $sentenciaDes->fetch(PDO::FETCH_ASSOC);

    global $BultosDespachables;
    if ($CountDisponible['Disponibles'] >= $CountDespachar['Cajas']) {
        return true;
    } else {
        $BultosDespachables = $CountDisponible['Disponibles'];
        return false;
    }
}

function Proceso($Lote, $IDH, $Guia,$Cantidad,$ID_DespachoDG){

try{


    $conexion = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    global $BultosDespachables ;


    if (Alcanza($conexion, $Lote, $IDH, $Guia)) {
        $FechaActualREG = date('Y-m-d');
        $SQL00 = "INSERT INTO despachos (Guia_Carga, Entrega, IDH, Descripcion, Posicion, Rampa, Fecha_Hora_Despacho, FechaRealizado, Operador, Estado) VALUES (?, NULL, ?, NULL, NULL, NULL, ?, ?, 'PIKING', 'Despachado')";
       
        echo"--------------------
        ";
        echo "SQL para SQL00: " . $SQL00;
       
        $stmt00 = $conexion->prepare($SQL00);
        $stmt00->execute([$Guia, $IDH, $FechaActualREG, $FechaActualREG]);
    
        $SQL0 = "UPDATE detalle_piking SET Estatus = 'Despachado', ID_Despacho = ?, Transporte = ? WHERE IDH = ? AND Estatus IS NULL AND LoteProduccion LIKE ? LIMIT 1";
       
        echo"--------------------
        ";
        echo "SQL para SQL0: " . $SQL0;
       
        $stmt0 = $conexion->prepare($SQL0);
        for ($i = 1; $i <= $Cantidad; $i++) {
            $stmt0->execute([$ID_DespachoDG, $Guia, $IDH, "%$Lote%"]);
        }
    
        $SQL1 = "UPDATE DetalleGuias SET Estatus = 'Despachado' WHERE Transporte = ? AND Material = ? AND Tipo = 'Piking'";
        
        echo"--------------------
        ";
        echo "SQL para SQL1: " . $SQL1;
        
        $stmt1 = $conexion->prepare($SQL1);
        $stmt1->execute([$Guia, $IDH]);
    } else {
        $SQL0 = "UPDATE detalle_piking SET Estatus = 'Despachado', ID_Despacho = ?, Transporte = ? WHERE IDH = ? AND Estatus IS NULL AND LoteProduccion LIKE ? LIMIT 1";
        $stmt0 = $conexion->prepare($SQL0);
        echo"--------------------
        ";
        echo "SQL para SQL0 cuando no alcanza:  " . $SQL0;
        
        for ($i = 1; $i <= $BultosDespachables; $i++) {
            $stmt0->execute([$ID_DespachoDG, $Guia, $IDH, "%$Lote%"]);
        }
    
        $NuevoValorBultosDespachables = $Cantidad - $BultosDespachables;
    
        $SQL1 = "UPDATE DetalleGuias SET Cajas = ? WHERE Transporte = ? AND Material = ? AND Tipo = 'Piking'";
        
        echo"--------------------
        ";
        echo "SQL para SQL1 Cuano no alcanza : " . $SQL1;
        
        $stmt1 = $conexion->prepare($SQL1);
        $stmt1->execute([$NuevoValorBultosDespachables, $Guia, $IDH]);
    }

} catch (Exception $e) {
    echo "Error en Proceso: " . $e->getMessage();
}

}

// Bucle para los registros de IDH y LOTE

$conn = new mysqli($servername, $username, $password, $dbname);
$cargos = "SELECT DG.IdRegistro,DG.Material,DG.Cajas, DP.LoteProduccion FROM `DetalleGuias` DG
inner Join detalle_piking DP on  DP.IDH = DG.Material
where DG.Transporte = $Guia and DG.Ubicacion = 'Piking' and DP.Estatus is null and DG.Estatus = ''
group by DG.IdRegistro,DP.IDH,DP.LoteProduccion order by DP.FechaProduccion asc";

echo"--------------------
";
echo "SQL para Cargos, Primera Parte: " . $cargos;



$result = $conn->query($cargos);

$NoBucle = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo"--------------------
";
echo "Bucle principal, Reccorrido No: " . $NoBucle;
        Proceso($row['LoteProduccion'],$row['Material'],$Guia,$row['Cajas'],$row['IdRegistro']);

        

        echo "Fin del proceso principal Bucle No: " .$NoBucle;

        $NoBucle += 1;
                                          }
}

// Fin del bucle para los registros de IDH y LOTE

header('Location: RegistroPiking.php?Guia=' . $Guia);
ob_end_flush();
?>
