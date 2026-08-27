<?php
ob_start();

function darValorDespachos($NombreUsuario){
    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT COUNT(*) as Despachos FROM dbs9098416.despachos WHERE Operador = :operador AND Estado = 'Pendiente'");
    $sentencia->bindParam(':operador', $NombreUsuario);
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Despachos'] != 0){
        return '<span class="badge badge-primary notify-no "> '.$Count['Despachos'].' </span>';
    }else {
        return '';
    }

}


function darValorAsignaciones($NombreUsuario){

    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT COUNT(*) as Asignaciones FROM dbs9098416.asignaciones WHERE Operador = '".$NombreUsuario."' AND Estado = 'Pendiente'");
    $sentencia->execute();
    $Count = ['Asignaciones' => $sentencia->fetchColumn()];

    if ($Count['Asignaciones'] != 0){
        return '<span class="badge badge-primary notify-no "> '.$Count['Asignaciones'].' </span>';
    }else {
        return '';
    }
}

function darValorReubicaciones($NombreUsuario){

    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones where Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente'");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return '<span class="badge badge-primary notify-no "> '.$Count['Asignaciones'].' </span>';
    }else {
        return '';
    }
}

function darValorPiking($NombreUsuario){

    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones  FROM dbs9098416.piking where Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente'");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return '<span class="badge badge-primary notify-no "> '.$Count['Asignaciones'].' </span>';
    }else {
        return '';
    }
}



function DarValorTotalMovimientos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Fecha_Hora_Despacho >= date('".$fechaConsulta."') and Operador = '".$NombreUsuario."' ;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorIDHs($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count( distinct(IDH)) as Asignaciones FROM dbs9098416.despachos where Fecha_Hora_Despacho >= date('".$fechaConsulta."') and Operador = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorListaDespachadas($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Fecha_Hora_Despacho >= date('".$fechaConsulta."') and Operador = '".$NombreUsuario."' and Estado = 'Despachado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorListaPendientes($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Fecha_Hora_Despacho >= date('".$fechaConsulta."') and Operador = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


///// Logica para elementos de Ingresos
function DarValorTotalProducciones($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where  Operador = '".$NombreUsuario."';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorIDHsIngresos($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count( distinct(IDH)) as Asignaciones FROM dbs9098416.asignaciones where  Operador = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorListaColocadas($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where  Operador = '".$NombreUsuario."' and Estado = 'Ingresado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorListaPendientesIngresos($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where  Operador = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


/// Funciones para modulo de Reubicaciones

function DarValorTotalReubicaciones($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' ;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorTotalPiking($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(*) as Asignaciones FROM dbs9098416.piking where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' ;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorIDHsMover($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT  Count(distinct(IDH)) as Asignaciones FROM dbs9098416.Reubicaciones where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


function DarValorIDHsPiking($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT  Count(distinct(IDH)) as Asignaciones FROM dbs9098416.piking where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


function DarValorListaColocadasMover($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' and Estado = 'Reubicada';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorListaColocadasPiking($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.piking where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' and Estado = 'Reubicada';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


function DarValorListaPendientesMover($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


function DarValorListaPendientesPiking($NombreUsuario , $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.piking where Fecha_Ingreso >= date('".$fechaConsulta."') and Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

// Funciones de Movimientos

function RegistrarBitacora($IDRegistro,$Fecha,$IDH,$Evento,$TipoEvento,$EstadoAnterior,$EstadoNuevo){
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $Usuario = $_SESSION['Usuario'];
    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("INSERT INTO dbs9098416.bitacora (Movimiento, TipoEvento, Horafecha, IDH, usuario, estado_anterior, estado_nuevo) VALUES (?, ?, ?, ?, ?, ?, ?);");
    $sentencia->execute([$Evento, $TipoEvento, $Fecha, $IDH, $Usuario, $EstadoAnterior, $EstadoNuevo]);

}

function DespacharProducto($IDRegistro, $Fecha, $Operador){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("UPDATE dbs9098416.despachos
                                SET FechaRealizado = ?, Estado = 'Despachado'
                                WHERE Movimiento = ? AND Operador = ? AND Estado = 'Pendiente';");
    $sentencia->execute([$Fecha, $IDRegistro, $Operador]);
    return $sentencia->rowCount() === 1;

}

function ActualizarDetalleGuia($Transporte,$Entrega,$Ubicacion){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update dbs9098416.DetalleGuias set Estatus = 'Despachado' where Transporte = ? and  Entrega = ? and Ubicacion = ?;");
    $sentencia->execute([$Transporte, $Entrega,$Ubicacion]);

}

function HistoriarUbicacion($Ubicacion,$Movimiento,$IdRegistro){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("insert into dbs9098416.posiciones_historico select *,'".$Movimiento."',$IdRegistro FROM dbs9098416.posiciones where  Ubicacion ='".$Ubicacion."';");
    $sentencia->execute();

}

function LiberarUbicacion($Ubicacion){

    include '../LQS_EUQ/Auth.php';

    //$sentencia = $pdo->prepare("Update  dbs9098416.posiciones set Estado ='Libre',IDH=null,PaletCompleto =null,UnidadesEnPallet = null,Origen = null, FechaProduccion = null, LoteProduccion=null,fechaIngreso=null,FechaVencimiento = null, FechaCuarentena=null, Cantidad =null,EstatusProducto = null, Verificador=null,UsuarioMontaCargas=null,turno=null,EstatusUbicacion= null, observaciones =null where Ubicacion='".$Ubicacion."'");
    $sentencia = $pdo->prepare("UPDATE dbs9098416.posiciones SET Estado = 'Libre', IDH = NULL, PaletCompleto = NULL, UnidadesEnPallet = NULL, Origen = NULL, FechaProduccion = NULL, LoteProduccion = NULL, fechaIngreso = NULL, FechaVencimiento = NULL, FechaCuarentena = NULL, Cantidad = NULL, EstatusProducto = NULL, Verificador = NULL, UsuarioMontaCargas = NULL, turno = NULL, EstatusUbicacion = NULL, observaciones = NULL WHERE Ubicacion = ?");
    $sentencia->execute([$Ubicacion]);

}


function CorregirEstatusUbicacion($Ubicacion){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("UPDATE dbs9098416.posiciones SET Estado = 'Ocupada' WHERE Ubicacion = ?");
    $sentencia->execute([$Ubicacion]);

}

function CorregirEstatusUbicacionPiking($Ubicacion){

    include '../LQS_EUQ/Auth.php';

    //$sentencia = $pdo->prepare("Update  dbs9098416.posiciones set Estado ='Libre',IDH=null,PaletCompleto =null,UnidadesEnPallet = null,Origen = null, FechaProduccion = null, LoteProduccion=null,fechaIngreso=null,FechaVencimiento = null, FechaCuarentena=null, Cantidad =null,EstatusProducto = null, Verificador=null,UsuarioMontaCargas=null,turno=null,EstatusUbicacion= null, observaciones =null where Ubicacion='".$Ubicacion."'");
    $sentencia = $pdo->prepare("UPDATE dbs9098416.detalle_piking SET Estado = 'Ocupada' WHERE Ubicacion = ?" );
    $sentencia->execute([$Ubicacion]);

}


function CorregirUbicacionParaPiking($Origen,$Destino){

    include '../LQS_EUQ/Auth.php';

    //$sentencia = $pdo->prepare("Update  dbs9098416.posiciones set Estado ='Libre',IDH=null,PaletCompleto =null,UnidadesEnPallet = null,Origen = null, FechaProduccion = null, LoteProduccion=null,fechaIngreso=null,FechaVencimiento = null, FechaCuarentena=null, Cantidad =null,EstatusProducto = null, Verificador=null,UsuarioMontaCargas=null,turno=null,EstatusUbicacion= null, observaciones =null where Ubicacion='".$Ubicacion."'");
    $sentencia = $pdo->prepare("UPDATE dbs9098416.detalle_piking SET Ubicacion = '$Destino'  WHERE Ubicacion = '$Origen'  " );
    $sentencia->execute();

}




function AnularIngreso($IDIngreso){

    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("update dbs9098416.asignaciones set Estado = 'Anulado' where Numero = ?;");
    $sentencia->execute([$IDIngreso]);

}

function IngresarProducto($IDRegistro,$fecha){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update dbs9098416.asignaciones set Estado = 'Ingresado', FechaColocado = '".$fecha."' where Numero = '".$IDRegistro."';");
    $sentencia->execute();

}

function ActualizarUbicacion($IDRegistro,$Ubicacion){

    include '../LQS_EUQ/Auth.php';
    session_start();
    $Usuario = $_SESSION['Usuario'];

    // $sentencia = $pdo->prepare("Update  dbs9098416.posiciones set Estado ='Ocupada',IDH=(select IDH from dbs9098416.asignaciones where Numero = '".$IDRegistro."')  ,PaletCompleto =(select PalletCompleto from dbs9098416.asignaciones where Numero = '".$IDRegistro."'),UnidadesEnPallet = (select Cantidades from dbs9098416.asignaciones where Numero = '".$IDRegistro."'),Origen = (select Origen from dbs9098416.asignaciones where Numero = '".$IDRegistro."'), FechaProduccion = (select FechaProduccion from dbs9098416.asignaciones where Numero = '".$IDRegistro."'), LoteProduccion=(select LoteProduccion from dbs9098416.asignaciones where Numero = '".$IDRegistro."'),fechaIngreso=(select FechaIngreso from dbs9098416.asignaciones where Numero = '".$IDRegistro."'),FechaVencimiento = (select FechaVencimiento from dbs9098416.asignaciones where Numero = '".$IDRegistro."'), FechaCuarentena=(select FechaCuarentena from dbs9098416.asignaciones where Numero = '".$IDRegistro."'), Cantidad = (select Cantidades from dbs9098416.asignaciones where Numero = '".$IDRegistro."'),EstatusProducto = (select EstatusProducto from dbs9098416.asignaciones where Numero = '".$IDRegistro."'), Verificador=(select Verificador from dbs9098416.asignaciones where Numero = '".$IDRegistro."'),UsuarioMontaCargas='".$Usuario."',turno=null,EstatusUbicacion= null, observaciones =(select Observaciones from dbs9098416.asignaciones where Numero = '".$IDRegistro."') where Ubicacion='".$Ubicacion."'");
    $sentencia = $pdo->prepare("Update dbs9098416.posiciones
  inner join dbs9098416.asignaciones on dbs9098416.asignaciones.Numero = :numero
  set 
    posiciones.Estado = 'Ocupada',
    posiciones.IDH = dbs9098416.asignaciones.IDH,
    posiciones.PaletCompleto = dbs9098416.asignaciones.PalletCompleto,
    posiciones.UnidadesEnPallet = dbs9098416.asignaciones.Cantidades,
    posiciones.Origen = dbs9098416.asignaciones.Origen,
    posiciones.FechaProduccion = dbs9098416.asignaciones.FechaProduccion,
    posiciones.LoteProduccion = dbs9098416.asignaciones.LoteProduccion,
    posiciones.FechaIngreso = dbs9098416.asignaciones.FechaIngreso,
    posiciones.FechaVencimiento = dbs9098416.asignaciones.FechaVencimiento,
    posiciones.FechaCuarentena = dbs9098416.asignaciones.FechaCuarentena,
    posiciones.Cantidad = dbs9098416.asignaciones.Cantidades,
    posiciones.EstatusProducto = dbs9098416.asignaciones.EstatusProducto,
    posiciones.Verificador = dbs9098416.asignaciones.Verificador,
    posiciones.UsuarioMontaCargas = :usuario,
    posiciones.turno = null,
    posiciones.EstatusUbicacion = null,
    posiciones.Observaciones = dbs9098416.asignaciones.Observaciones
  where posiciones.Ubicacion = :ubicacion");
    $sentencia->bindParam(':numero', $IDRegistro);
    $sentencia->bindParam(':usuario', $Usuario);
    $sentencia->bindParam(':ubicacion', $Ubicacion);

    $sentencia->execute();

}

function IgresoPiking($IDRegistro){


       //ANCHOR - Cambios en Abastecer Piking para que funcione Piking 06.01.24
    // Ver cuantos bultos hay
    $CantidadDeBultos = CantidadDeBultosProduccion($IDRegistro);
    //insertar datos segun la cantida de bultos

    for ($i = 1; $i <= $CantidadDeBultos; $i++) {
        include '../LQS_EUQ/Auth.php';
        $sentencia = $pdo->prepare("insert into detalle_piking
        select B.Bodega, B.Carril, B.Posicion,B.Nivel, A.Posicion,'Disponible', A.IDH, PalletCompleto,Cantidades, Origen, FechaProduccion, LoteProduccion, FechaIngreso, FechaVencimiento, FechaCuarentena, Cantidades, EstatusProducto, Verificador, Operador,'','piking',Observaciones,null,null,null from asignaciones A
        inner Join config_piking B on A.Posicion =  B.Ubicacion where A.Numero =  :numero");
            $sentencia->bindParam(':numero', $IDRegistro);
            $sentencia->execute();
    }

    // Forma Anterior



}

function MoverMaterial($Origen,$Destino){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("UPDATE dbs9098416.posiciones AS a
JOIN dbs9098416.posiciones AS b
ON b.Ubicacion = :origen
SET a.Estado = b.Estado,
a.IDH = b.IDH,
a.PaletCompleto = b.PaletCompleto,
a.UnidadesEnPallet = b.UnidadesEnPallet,
a.Origen = b.Origen,
a.FechaProduccion = b.FechaProduccion,
a.LoteProduccion = b.LoteProduccion,
a.fechaIngreso = b.fechaIngreso,
a.FechaVencimiento = b.FechaVencimiento,
a.FechaCuarentena = b.FechaCuarentena,
a.Cantidad = b.Cantidad,
a.EstatusProducto = b.EstatusProducto,
a.Verificador = b.Verificador,
a.UsuarioMontaCargas = b.UsuarioMontaCargas,
a.turno = b.turno,
a.EstatusUbicacion = b.EstatusUbicacion,
a.observaciones = b.observaciones
WHERE a.Ubicacion = :destino");
    $sentencia->bindParam(':origen', $Origen);
    $sentencia->bindParam(':destino', $Destino);
    $sentencia->execute();

}

function CantidadDeBultos($Origen){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT UnidadesEnPallet FROM dbs9098416.posiciones where Ubicacion = '$Origen';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['UnidadesEnPallet'] != 0){
        return  $Count['UnidadesEnPallet'];
    }else {
        return '0';
    }

}


function CantidadDeBultosProduccion($IDRegistro){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT Cantidades FROM dbs9098416.asignaciones where numero = $IDRegistro;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Cantidades'] != 0){
        return  $Count['Cantidades'];
    }else {
        return '0';
    }

}


function MoverMaterialPiking($Origen){

    //ANCHOR - Cambios en Abastecer Piking para que funcione Piking 06.01.24
    // Ver cuantos bultos hay
    $CantidadDeBultos = CantidadDeBultos($Origen);
    //insertar datos segun la cantida de bultos

    for ($i = 1; $i <= $CantidadDeBultos; $i++) {
        include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("insert into detalle_piking SELECT Bodega, Carril, Posicion, Nivel, Ubicacion, Estado, IDH, PaletCompleto, 1, Origen, FechaProduccion, LoteProduccion, FechaIngreso, FechaVencimiento, FechaCuarentena, Cantidad, EstatusProducto, Verificador, UsuarioMontaCargas, Turno, EstatusUbicacion, Observaciones,null,null,null FROM dbs9098416.posiciones where Ubicacion = '$Origen';");
    $sentencia->execute();
    }

}

function ReubicarProducto($IDRegistro,$Fecha){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update  dbs9098416.Reubicaciones set Estado = 'Reubicada',Fecha_Movimiento = '".$Fecha."' where id= '".$IDRegistro."'");
    $sentencia->execute();

}

function ReubicarProductoPiking($IDRegistro,$Fecha){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update  dbs9098416.piking set Estado = 'Reubicada',Fecha_Movimiento = '".$Fecha."' where id= '".$IDRegistro."'");
    $sentencia->execute();

}

function LiberarUbicacionAnulada($IDIngreso){

    include '../LQS_EUQ/Auth.php';
    $sentencia = $pdo->prepare("Update dbs9098416.posiciones set Estado = 'Libre' where Ubicacion = (SELECT Posicion FROM dbs9098416.asignaciones where numero = ?);");
    $sentencia->execute([$IDIngreso]);

}

// Valores de Datos de resumen de Historico

function HistoricoTotalMovimientos_Despachos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Operador = '".$NombreUsuario."';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoCancelados_Despachos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Operador = '".$NombreUsuario."' and estado = 'Cancelado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoDespachados_Despachos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Operador = '".$NombreUsuario."' and estado = 'Despachado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoPendientes_Despachos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.despachos where Operador = '".$NombreUsuario."' and estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}



//validar si la ubicaion esta en piking 

function ValidarSiEsPiking($Ubicacion){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(*) as Asignaciones FROM `config_piking` where Ubicacion = '$Ubicacion';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  true;
    }else {
        return false;
    }
}



function HistoricoTotalMovimientos_Ingresos($NombreUsuario, $fechaConsulta){
    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' ;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
   
}


function HistoricoTotalMovimientos_IngresosPiking($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(*) FROM `piking` where Montacarguista = '$NombreUsuario' and date(Fecha_Ingreso) >= '$fechaConsulta' ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}





function HistoricoCancelados_Ingresos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and estado = 'Cancelado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


function HistoricoCancelados_IngresosPiking($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(*) FROM `piking` where Montacarguista = '$NombreUsuario' and date(Fecha_Ingreso) >= '$fechaConsulta' and Estado = 'Anulado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoDespachados_Ingresos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and estado = 'Ingresado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoDespachados_IngresosPiking($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(*) FROM `piking` where Montacarguista = '$NombreUsuario' and date(Fecha_Ingreso) >= '$fechaConsulta' and Estado = 'Reubicada';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoPendientes_Ingresos($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and estado = 'Pendiente';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoPendientes_IngresosPiking($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(*) FROM `piking` where Montacarguista = '$NombreUsuario' and date(Fecha_Ingreso) >= '$fechaConsulta' and Estado = 'Pendiente'");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoTotalMovimientos_Reubicaciones($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones where Operador = '".$NombreUsuario."' ;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoCancelados_Reubicaciones($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones where Operador = '".$NombreUsuario."' and estado = 'Cancelado';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoDespachados_Reubicaciones($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones WHERE Operador = ? AND estado = 'Reubicada'");
    $sentencia->execute([$NombreUsuario]);
    $Count = $sentencia->fetch(PDO::FETCH_LAZY);

    return ($Count['Asignaciones'] ?? 0);
}

function HistoricoPendientes_Reubicaciones($NombreUsuario, $fechaConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT COUNT(*) AS Asignaciones FROM dbs9098416.Reubicaciones WHERE Operador = :operador AND estado = 'Pendiente'");
    $sentencia->bindParam(':operador', $NombreUsuario, PDO::PARAM_STR);
    $sentencia->execute();
    $count = $sentencia->fetch(PDO::FETCH_ASSOC);

    return $count['Asignaciones'] ?: '0';
}

function CaluclarCuarentena($FechaCuarentena){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update  dbs9098416.posiciones  set  EstatusUbicacion = 'Cuarentena'  where FechaIngreso > date('$FechaCuarentena') and FechaCuarentena > FechaIngreso and EstatusUbicacion is null ;  ");
       $sentencia->execute();

}


ob_end_flush();
?>
