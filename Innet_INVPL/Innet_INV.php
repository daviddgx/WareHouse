<?php

function DarValorProducciones(){

    include '../LQS_EUQ/Auth.php';

    date_default_timezone_set('America/Guatemala');
    $hora = date(' G:i:s ', time());
    $fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
    $Turno1 = "06:00";
    $Turno2 = "18:00";
    $FechaTrabajoAnterior="";
    $HoraTrabajoInicio="";
    $HoraTrabajoFinal="";

    if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
        $txtTurno = "1";
        $HoraTrabajoInicio = $fechaConsulta." ".$Turno1 ;
        $HoraTrabajoFinal  = $fechaConsulta." ".$Turno2;

    }else{

        if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")) {

            $HoraTrabajoInicio = $fechaConsulta." ".$Turno2 ;
            $HoraTrabajoFinal  = $fechaConsulta." ".$hora;
        }else{

            $FechaTrabajoAnterior= date('Y-m-d', strtotime($fechaConsulta . ' -1 day')); // Resta un día a la fecha actual
            $HoraTrabajoInicio  = $FechaTrabajoAnterior." ".$Turno2;
            $HoraTrabajoFinal = $fechaConsulta." ".$hora;

        }
    }

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where EstatusProducto is null and FechaRegistro BETWEEN '".$HoraTrabajoInicio."' and '".$HoraTrabajoFinal."';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function DarValorLineas(){

    include '../LQS_EUQ/Auth.php';

    date_default_timezone_set('America/Guatemala');
    $hora = date(' G:i:s ', time());
    $fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
    $Turno1 = "06:00";
    $Turno2 = "18:00";
    $FechaTrabajoAnterior="";
    $HoraTrabajoInicio="";
    $HoraTrabajoFinal="";

    if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
        $txtTurno = "1";
        $HoraTrabajoInicio = $fechaConsulta." ".$Turno1 ;
        $HoraTrabajoFinal  = $fechaConsulta." ".$Turno2;

    }else{

        if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")) {

            $HoraTrabajoInicio = $fechaConsulta." ".$Turno2 ;
            $HoraTrabajoFinal  = $fechaConsulta." ".$hora;
        }else{

            $FechaTrabajoAnterior= date('Y-m-d', strtotime($fechaConsulta . ' -1 day')); // Resta un día a la fecha actual
            $HoraTrabajoInicio  = $FechaTrabajoAnterior." ".$Turno2;
            $HoraTrabajoFinal = $fechaConsulta." ".$hora;

        }
    }

    $sentencia = $pdo->prepare("select count(DISTINCT(operador)) as operadores from dbs9098416.asignaciones where EstatusProducto is null and FechaRegistro BETWEEN '".$HoraTrabajoInicio."' and '".$HoraTrabajoFinal."';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['operadores'] != 0){
        return  $Count['operadores'];
    }else {
        return '0';
    }
}

function DarValorListaColocadas(){

    include '../LQS_EUQ/Auth.php';

    date_default_timezone_set('America/Guatemala');
    $hora = date(' G:i:s ', time());
    $fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
    $Turno1 = "06:00";
    $Turno2 = "18:00";
    $FechaTrabajoAnterior="";
    $HoraTrabajoInicio="";
    $HoraTrabajoFinal="";

    if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
        $txtTurno = "1";
        $HoraTrabajoInicio = $fechaConsulta." ".$Turno1 ;
        $HoraTrabajoFinal  = $fechaConsulta." ".$Turno2;

    }else{

        if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")) {

            $HoraTrabajoInicio = $fechaConsulta." ".$Turno2 ;
            $HoraTrabajoFinal  = $fechaConsulta." ".$hora;
        }else{

            $FechaTrabajoAnterior= date('Y-m-d', strtotime($fechaConsulta . ' -1 day')); // Resta un día a la fecha actual
            $HoraTrabajoInicio  = $FechaTrabajoAnterior." ".$Turno2;
            $HoraTrabajoFinal = $fechaConsulta." ".$hora;

        }
    }

    $sentencia = $pdo->prepare("select count(1) as ingresos from dbs9098416.asignaciones where EstatusProducto is null and Estado = 'Ingresado' and FechaRegistro BETWEEN '".$HoraTrabajoInicio."' and '".$HoraTrabajoFinal."';");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['ingresos'] != 0){
        return  $Count['ingresos'];
    }else {
        return '0';
    }
}

function DarValorListaPendientes(){

    include '../LQS_EUQ/Auth.php';

    date_default_timezone_set('America/Guatemala');
    $hora = date(' G:i:s ', time());
    $fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
    $Turno1 = "06:00";
    $Turno2 = "18:00";
    $FechaTrabajoAnterior="";
    $HoraTrabajoInicio="";
    $HoraTrabajoFinal="";

    if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
        $txtTurno = "1";
        $HoraTrabajoInicio = $fechaConsulta." ".$Turno1 ;
        $HoraTrabajoFinal  = $fechaConsulta." ".$Turno2;

    }else{

        if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")) {

            $HoraTrabajoInicio = $fechaConsulta." ".$Turno2 ;
            $HoraTrabajoFinal  = $fechaConsulta." ".$hora;
        }else{

            $FechaTrabajoAnterior= date('Y-m-d', strtotime($fechaConsulta . ' -1 day')); // Resta un día a la fecha actual
            $HoraTrabajoInicio  = $FechaTrabajoAnterior." ".$Turno2;
            $HoraTrabajoFinal = $fechaConsulta." ".$hora;

        }
    }

    $sentencia = $pdo->prepare("select count(1) as pendientes from dbs9098416.asignaciones where EstatusProducto is null and Estado = 'Pendiente'  and FechaRegistro BETWEEN '".$HoraTrabajoInicio."' and '".$HoraTrabajoFinal."'; ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['pendientes'] != 0){
        return  $Count['pendientes'];
    }else {
        return '0';
    }
}

function ReservarUbicacion($Ubicacion){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("update dbs9098416.posiciones set Estado ='Reservado' where Ubicacion = '".$Ubicacion."';");
    $sentencia->execute();

}

function PonerEnFirmeLasUbicaciones(){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("CALL FirmeUbicacionesPL();");
    $sentencia->execute();

    header('Location: Print_CardexMasivoCargados.php');

}

function CorregirUbicacion($UbicacionNueva,$IDAsignacion){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("CALL CorregirUbicacion($IDAsignacion, '$UbicacionNueva');");
    $sentencia->execute();

}

function CorregirUbicacionPL($UbicacionNueva,$IDAsignacion){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("CALL CorregirUbicacionPL($IDAsignacion, '$UbicacionNueva');");
    $sentencia->execute();

}

function ImprimirCardex($Ubicacion){



?>

    <script>
        window.open("Cardex.php?Ubicacion=<?php echo $Ubicacion; ?>", "_blank", "width=1200,height=1200");
    </script>

<?php

}

?>