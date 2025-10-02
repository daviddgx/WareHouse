<?php



function HistoricoTotalMovimientos_IngresosFEC($NombreUsuario,  $FechaInicioConsulta, $FechaFinConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and FechaColocado between date('$FechaInicioConsulta') and date('$FechaFinConsulta');");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoCancelados_IngresosFEC($NombreUsuario,  $FechaInicioConsulta, $FechaFinConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and estado = 'Cancelado' and FechaColocado between date('$FechaInicioConsulta') and date('$FechaFinConsulta');");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoDespachados_IngresosFEC($NombreUsuario,  $FechaInicioConsulta, $FechaFinConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and estado = 'Ingresado' and FechaColocado between date('$FechaInicioConsulta') and date('$FechaFinConsulta');");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}

function HistoricoPendientes_IngresosFEC($NombreUsuario, $FechaInicioConsulta, $FechaFinConsulta){

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and estado = 'Pendiente' and FechaColocado between date('$FechaInicioConsulta') and date('$FechaFinConsulta');");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0){
        return  $Count['Asignaciones'];
    }else {
        return '0';
    }
}


?>