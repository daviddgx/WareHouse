<?php

function DarValorTotalPosiciones($Bodega){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as posiciones FROM dbs9098416.posiciones where Bodega = '".$Bodega."'");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['posiciones'] != 0){
        return  $Count['posiciones'];
    }else {
        return '0';
    }
}

function DarValorTotOperadores($Bodega){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(DISTINCT(IDH)) as IDHs FROM dbs9098416.posiciones where Bodega = '".$Bodega."' and IDH not in (0);");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['IDHs'] != 0){
        return  $Count['IDHs'];
    }else {
        return '0';
    }
}

function LimpiarExesoPiking(){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update `detalle_piking` set UnidadesEnPallet = 1  where UnidadesEnPallet > 1");
    $sentencia->execute();
   
   
}

function BloquearCarrilesPiking(){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update `posiciones` set Estado = 'OCP-PK' where  Bodega = 1 and Carril in ('B16') and Estado = 'Ocupada'");
    $sentencia->execute();
   
   
}


function DarValorListaLibres($Bodega){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Libres FROM dbs9098416.posiciones where Bodega = '".$Bodega."' and Estado ='libre';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Libres'] != 0){
        return  $Count['Libres'];
    }else {
        return '0';
    }
}

function DarValorListaOcupadas($Bodega){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Ocupadas FROM dbs9098416.posiciones where Bodega = '".$Bodega."' and Estado ='Ocupada';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Ocupadas'] != 0){
        return  $Count['Ocupadas'];
    }else {
        return '0';
    }
}

function ValidarSiCabeCarril($BodegaOrigen,$CarrillOrigen,$BodegaDestino,$CarrilDestino,$txtUnidadesOrigen){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT Count(*) As Ocupadas FROM dbs9098416.posiciones where Bodega = '$BodegaOrigen' and Carril = '$CarrillOrigen' and Estado in ('Ocupada','Ocupada-PK') limit $txtUnidadesOrigen;");
    $sentencia->execute();
    $CountOcupadas =  $sentencia->fetch(PDO::FETCH_LAZY);


    if($CountOcupadas['Ocupadas'] < $txtUnidadesOrigen){
        return  true;
    } else{

        $sentencia2 = $pdo->prepare("SELECT Count(*) As Disponibles FROM dbs9098416.posiciones where Bodega = '$BodegaDestino' and Carril = '$CarrilDestino' and Estado = 'Libre';");
        $sentencia2->execute();
        $CountDisponibles =  $sentencia2->fetch(PDO::FETCH_LAZY);

        if ($txtUnidadesOrigen > $CountDisponibles['Disponibles'] ){
            return  true;
        }else {
            return false;
        }



    }



}

function InserMovimientoTemporal($BodegaOrigen,$CarrillOrigen,$BodegaDestino,$CarrilDestino,$Usuario,$Montacarguista,$Fecha,$txtUnidadesOrigen){

    include '../LQS_EUQ/Auth.php';


$conn = new mysqli($servername, $username, $password, $dbname);
$cargos = "SELECT Ubicacion FROM dbs9098416.posiciones where Bodega = '$BodegaOrigen' and Carril = '$CarrillOrigen' and Estado = 'Ocupada' and Ubicacion not in (SELECT UbicacionOrigen FROM dbs9098416.reubicacionesmasivas where estatus = 'Pendiente') order by CAST(SUBSTRING(Posicion, 2) AS SIGNED) DESC, Nivel desc limit $txtUnidadesOrigen";

    $result = $conn->query($cargos);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $Ubicacion = $row['Ubicacion'] ;
            $sentencia = $pdo->prepare("insert into reubicacionesmasivas values(null,'$BodegaOrigen','$CarrillOrigen','$Ubicacion','$BodegaDestino','$CarrilDestino',null,'Pendiente','$Usuario','$Montacarguista','$Fecha');");
            $sentencia->execute();
        }
    }
}

function UpdateUbicacionesTemporal($BodegaOrigen,$CarrillOrigen,$BodegaDestino,$CarrilDestino){

    include '../LQS_EUQ/Auth.php';



    $conn = new mysqli($servername, $username, $password, $dbname);
    $cargos = "select * from reubicacionesmasivas WHERE BodegaOrigen = '$BodegaOrigen' AND CarrilOrigen = '$CarrillOrigen' AND Estatus = 'Pendiente';";

    $result = $conn->query($cargos);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $QRY2 = "SELECT Ubicacion
               FROM dbs9098416.posiciones
               WHERE Bodega = '$BodegaDestino' AND Carril = '$CarrilDestino' AND Estado = 'Libre'  ORDER BY CAST(SUBSTRING(Posicion, 2) AS SIGNED) ASC  LIMIT 1; ";
            $UbicacionDestino = CalcularUbicacionDestino($QRY2);
            $UbicacionOrigen = $row['UbicacionOrigen'] ;

            $sentencia = $pdo->prepare("UPDATE reubicacionesmasivas   SET UbicacionDestino =  '$UbicacionDestino'            
            WHERE BodegaOrigen = '$BodegaOrigen' AND CarrilOrigen = '$CarrillOrigen' AND Estatus = 'Pendiente' and UbicacionOrigen = '$UbicacionOrigen';");
            $sentencia->execute();
            ActualizarUbicacionDestino($UbicacionDestino);

        }
    }
}

function CalcularUbicacionDestino($QRY){

    include '../LQS_EUQ/Auth.php';


    $conn = new mysqli($servername, $username, $password, $dbname);
    $cargos = "$QRY";

    $Ubicacion = '';
    $result = $conn->query($cargos);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $Ubicacion = $row['Ubicacion'] ;
        }
    }
    return $Ubicacion;
}

function ActualizarUbicacionDestino($UbicacionDestino){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update dbs9098416.posiciones set Estado = 'Movimiento-DEST' where Ubicacion = '$UbicacionDestino';");
    $sentencia->execute();
}

function BloquearOrigenes(){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("Update posiciones set Estado = 'MOVIMIENTO-ORG' where ubicacion in (
    select UbicacionDestino from reubicacionesmasivas where Estatus = 'Pendiente')");
    $sentencia->execute();
}

function BloquearDestinos(){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("Update posiciones set Estado = 'MOVIMIENTO-DEST' where ubicacion in (
    select UbicacionDestino from reubicacionesmasivas where Estatus = 'Pendiente')");
    $sentencia->execute();
}

function EnviarMovimientosAMontacarguuista(){

    include '../LQS_EUQ/Auth.php';

    $QRY = "Insert Into dbs9098416.Reubicaciones
SELECT null,posiciones.IDH,productos.Descripcion,UbicacionOrigen,UbicacionDestino,Fecha,null,'Pendiente',Promotor,Montacarguista,null  FROM dbs9098416.reubicacionesmasivas
Inner join posiciones
on posiciones.Ubicacion = reubicacionesmasivas.UbicacionOrigen
Inner join productos
on posiciones.IDH = productos.IDH
where reubicacionesmasivas.estatus = 'Pendiente';";

    $sentencia = $pdo->prepare("$QRY");
    $sentencia->execute();

    ProcesarMovimientosAsignados();

}

function ProcesarMovimientosAsignados(){

    include '../LQS_EUQ/Auth.php';

    $QRY = "Update reubicacionesmasivas set Estatus = 'Procesada' where Estatus = 'Pendiente'";

    $sentencia = $pdo->prepare("$QRY");
    $sentencia->execute();



}

function LiberarCuarentena(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");



    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT count(*)  as Unidades FROM dbs9098416.posiciones where FechaCuarentena < date('$fecha') and EstatusUbicacion = 'Cuarentena'; ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Unidades'] != 0){
        LiberarUnidadesCuarentena();
        return  $Count['Unidades'];
    }else {
        return '0';
    }

}

function LiberarCuarentenaHoy(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");



    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT Count(*) as Unidades FROM posiciones  WHERE DATE(FechaCuarentena) = CURDATE();");

    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    return  $Count['Unidades'];

}

function  LiberarUnidadesCuarentena(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");
    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("Update dbs9098416.posiciones  set  EstatusUbicacion = 'Libre'  where date(FechaCuarentena) <= date('$fecha') and EstatusUbicacion = 'Cuarentena'; ");
    $sentencia->execute();



}

function CapacidadTotalFIFO(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");



    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT Cant_CapacidadTotal FROM `gaf_capacidadbodegasdiaria` where NombreBodega = 'Todas' order by fecha desc limit 1 ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Cant_CapacidadTotal'] != 0){
               return  $Count['Cant_CapacidadTotal'];
    }else {
        return '0';
    }

}

function UnidadesLibresFIFO(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");



    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT Cant_Libres FROM `gaf_capacidadbodegasdiaria` where NombreBodega = 'Todas' order by fecha desc limit 1 ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Cant_Libres'] != 0){
        return  $Count['Cant_Libres'];
    }else {
        return '0';
    }

}


function UnidadesOcupadasFIFO(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");



    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT Cant_Ocupadas FROM `gaf_capacidadbodegasdiaria` where NombreBodega = 'Todas' order by fecha desc limit 1 ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Cant_Ocupadas'] != 0){
        return  $Count['Cant_Ocupadas'];
    }else {
        return '0';
    }

}

function PorcentajeOcupacion(){

    date_default_timezone_set('America/Guatemala');
    $fecha = date("Y") . '-' . date("m") . '-' . date("d");



    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT
(
  (SELECT COUNT(*)
   FROM posiciones p
   WHERE p.Estado = 'Ocupada'
     AND NOT EXISTS (
       SELECT 1
       FROM posisciones_temporalesCNF t
       WHERE t.ubicacion = p.Ubicacion
     )
  )
  /
  NULLIF(
    (SELECT COUNT(*)
     FROM posiciones p
     WHERE NOT EXISTS (
       SELECT 1
       FROM posisciones_temporalesCNF t
       WHERE t.ubicacion = p.Ubicacion
     )
    ),
    0
  )
) * 100 AS Porcentaje;

");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Porcentaje'] != 0){
        return  $Count['Porcentaje'];
    }else {
        return '0';
    }

}

function ObtenerUltimoEstatusBodegasConsolidado(){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT Fecha, Cant_CapacidadTotal, Cant_Libres, Cant_Ocupadas FROM `gaf_capacidadbodegasdiaria` WHERE NombreBodega = 'Todas' ORDER BY Fecha DESC LIMIT 1;");
    $sentencia->execute();
    $Count = $sentencia->fetch(PDO::FETCH_ASSOC);

    if ($Count) {
        return $Count;
    }

    return null;
}

function Limpiar_EstatusDespachoMalos(){

    try {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("update posiciones set Estado = 'Ocupada' where Ubicacion not in (SELECT Ubicacion FROM DetalleGuias where Estatus = ''  and Ubicacion is not null) and Estado = 'Despacho'");
        $sentencia->execute();

    } catch (Exception $exception){
        echo "Error al limpiar las ubicaiones marcadas como DESPACHO: ". $exception -> getMessage() ;


    }
}

function CorregirGuia($Transporte){


    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT count(*) as CapTotal  FROM dbs9098416.DetalleGuias where Transporte = $Transporte and tipo = 'Pallets' and ubicacion is null;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['CapTotal'] != 0){
        UpdateCorregirGuia($Transporte);

    }

}

function UpdateCorregirGuia($Transporte){

    include '../LQS_EUQ/Auth.php';

    $QRY = "update  dbs9098416.Guias set estatus = 'Corregir' where Transporte = $Transporte;";

    $sentencia = $pdo->prepare("$QRY");
    $sentencia->execute();



}

function ReEstablecerEstatus(){

    //QRYS
    // Poner en despacho los que esten en la lista de detalle de guias
    // update `posiciones`  set Estado = 'Despacho' where Ubicacion in (SELECT Ubicacion FROM `DetalleGuias` where Estatus <> 'Despachado' and Ubicacion like '%-%')


    // Poner en Ocupaas las que correspondan

    // Poner en Reserva las que correspondan

    try {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("update `posiciones`  set Estado = 'Despacho' where Ubicacion in (SELECT Ubicacion FROM `DetalleGuias` where Estatus <> 'Despachado' and Ubicacion like '%-%') ");
        $sentencia->execute();

    } catch (Exception $exception){
        echo "Error al reestablecer las filas malas". $exception -> getMessage() ;

    }
}

function InserterDetalleGuias($Transporte,$Entrega,$Material,$Cajas,$PesoNeto,$PesoBruto,$Tipo){

    try {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("Insert into dbs9098416.DetalleGuias VALUES (null,$Transporte,$Entrega,$Material,$Cajas,$PesoNeto,$PesoBruto, '', '', '$Tipo' ) ");
        $sentencia->execute();

        // Cerrar la conexión
        $pdo = null;
    } catch (Exception $exception){
        echo "Se encontro el siguente Error al Insertar el detalle de las guias: ". $exception -> getMessage() ;
    }

}

function Limpiar_Nulls(){

    $Proceso = false;

    // Limpiar las Ubicaciones que estan actualmente con valor NULL
    try {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("CALL LimpiarNullUbicaciones();");
        $sentencia->execute();
        $Proceso = true;
    } catch (Exception $exception){
        echo "Se encontro el siguente Error en el proceso Limpiar_Nulls: ". $exception -> getMessage() ;
        $Proceso = false;

    }
    
}


function AgregarValorAsignaciones(){

    

    // Agrega un registro en asignaciones para que salga 0 en la grafica los dias que no hay priduccion
    try {
        include '../LQS_EUQ/Auth.php';


        $fecha_actual = date("Y-m-d");

        $sentencia = $pdo->prepare("INSERT INTO `asignaciones` (`Numero`, `IDH`, `Producto`, `Posicion`, `FechaRegistro`, `FechaColocado`, `Estado`, `Operador`, `PalletCompleto`, `Cantidades`, `Origen`, `FechaProduccion`, `LoteProduccion`, `FechaIngreso`, `FechaVencimiento`, `FechaCuarentena`, `Verificador`, `EstatusProducto`, `Observaciones`) VALUES (NULL, '2835466', NULL, NULL, '$fecha_actual', '$fecha_actual', 'Ingresado', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'grafica');");

        $sentencia->execute();
    } catch (Exception $exception){
        echo "Se encontro el siguente Error en el proceso AgregarValorAsignaciones: ". $exception -> getMessage() ;
    }
}


function GraphEstatusBodegas(){

    $Proceso = false;

    // Traer los valores de las graficas de dias actuales
    try {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("CALL GenEstatusBodegasDiaria()");
        $sentencia->execute();
        
    } catch (Exception $exception){
        echo "Se necesita revisar el procedimiento de generacion de estauts de bodegas diarias: ". $exception -> getMessage() ;
    }
}

function RecalcularDespacho($Guia,$IDH,$Bodega){

    $Proceso = false;

    // Limpiar las Ubicaciones que estan actualmente
    try {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("CALL ActualizarPosiciones($Guia, $IDH); ");
        $sentencia->execute();
        $Proceso = true;
    } catch (Exception $exception){
        echo "Se encontro el siguente Error en el proceso RecalcularDespacho: ". $exception -> getMessage() ;
        $Proceso = false;

    }

    try {
        include '../LQS_EUQ/Auth.php';
        // Recalcular las Ubicaciones


        $sentencia2 = $pdo->prepare("CALL ReCalcularUbicaciones($Guia, $IDH,'$Bodega'); ");
        $sentencia2->execute();
        $Proceso = true;


     } catch (Exception $exception2){
    echo "Se encontro el siguente Error al ReCalcularUbicaciones las ubicaciones: ". $exception2 -> getMessage() ;
        $Proceso = false;

}




    return $Proceso;
}


?>
