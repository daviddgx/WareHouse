<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

$Guia = $_GET['Guia'];
$IDH = $_GET['IDH'];
$Lote = $_GET['Lote'];
$Cantidad = $_GET['Cantidad'];
$ID_DespachoDG = $_GET['IDDespacho'];
$BultosDespachables = 0;

function Alcanza($Lote,$IDH,$Guia){
    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("select count(*) as Disponibles from detalle_piking where LoteProduccion like '%$Lote%' and IDH = $IDH and Estatus is null ");
    $sentencia->execute();
    $CountDisponible =  $sentencia->fetch(PDO::FETCH_LAZY);
    //ANCHOR - No cambiar los datos de cajas, sacar el dato de detalleguiascarga para las cantidades de la tabla
    // Cambier la estrucura de la tabla de Detalle guias y colocar: IDH	FechaProduccion	LoteProduccion	FechaVencimiento	Estatus	ID_Despacho	Transporte
    // Cambiar en piking de montacarguista para que funcione 
    $sentenciaDes = $pdo->prepare("select Cajas from DetalleGuias where transporte = $Guia  and tipo = 'Piking' and Material = $IDH ");
    $sentenciaDes->execute();
    $CountDespachar =  $sentenciaDes->fetch(PDO::FETCH_LAZY);

    if ($CountDisponible['Disponibles'] >= $CountDespachar['Cajas'] ){
        return  true;
    }else {
        global $BultosDespachables;
        $BultosDespachables = $CountDisponible['Disponibles'];
        return false;
         }
}

//0. Validar si la cantidad de bultos del lote alcanzan para pikear los bultos 
if(Alcanza($Lote,$IDH,$Guia)){

//ANCHOR - Registrar una linea de Despachos para guardar la fecha
    $FechaActualREG = date('Y-m-d');
    $conexionACT0 = new mysqli($servername, $username, $password, $dbname);
    $SQL00 ="INSERT INTO `dbs9098416`.`despachos`(`Guia_Carga`,`Entrega`,`IDH`,`Descripcion`,`Posicion`,`Rampa`,`Fecha_Hora_Despacho`,`FechaRealizado`,`Operador`,`Estado`)VALUES('$Guia' ,NULL ,$IDH ,NULL ,NULL ,NULL ,'$FechaActualREG' ,'$FechaActualREG' ,'PIKING' ,'Despachado');  ";
    echo $SQL00;
    $conexionACT0->query($SQL00);
    
//1. hacer un ciclo por la cantidad de bultos 
for ($i = 1; $i <= $Cantidad; $i++) {
    $conexionACT = new mysqli($servername, $username, $password, $dbname);
    $SQL0 ="update  detalle_piking set  Estatus = 'Despachado', ID_Despacho = '$ID_DespachoDG', Transporte = '$Guia' where IDH = $IDH and Estatus is null and LoteProduccion like '%$Lote%' limit 1;";
    $resultado0 = $conexionACT->query($SQL0);
}

// Actualizar la cantidad de bultos pendientes por despachar
// 3. Actualizar el estatus de la guia a desachar cuando se despachen totalmente
$conexion = new mysqli($servername, $username, $password, $dbname);
$SQL1 ="Update DetalleGuias  set Estatus = 'Despachado' where Transporte = $Guia and Material = $IDH and Tipo = 'Piking'";
$resultado = $conexion->query($SQL1);

}else{
 
//1. hacer un ciclo por la cantidad de bultos despachables
for ($i = 1; $i <= $BultosDespachables; $i++) {
    $conexionACT = new mysqli($servername, $username, $password, $dbname);
    $SQL0 ="update  detalle_piking set  Estatus = 'Despachado', ID_Despacho = '$ID_DespachoDG', Transporte = '$Guia' where IDH = $IDH and Estatus is null and LoteProduccion like '%$Lote%' limit 1;";
    $resultado0 = $conexionACT->query($SQL0);
}

// Actualizar la cantidad de bultos pendientes por despachar
// 3. Actualizar el estatus de la guia a desachar cuando se despachen Parcialmente

$NuevoValorBultosDespachables = $Cantidad - $BultosDespachables;

$conexion = new mysqli($servername, $username, $password, $dbname);
$SQL1 ="Update DetalleGuias  set Cajas = $NuevoValorBultosDespachables where Transporte = $Guia and Material = $IDH and Tipo = 'Piking'";
$resultado = $conexion->query($SQL1);
}

header('Location: RegistroPiking.php?Guia='.$Guia);
ob_end_flush();

?>