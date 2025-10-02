<?php

function DarValorProducciones()
{

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("SELECT count(1) as Asignaciones FROM dbs9098416.Reubicaciones ;");
    $sentencia->execute();
    $Count = $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Asignaciones'] != 0) {
        return $Count['Asignaciones'];
    } else {
        return '0';
    }
}

function DarValorLineas()
{

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("select count(DISTINCT(operador)) as operadores from dbs9098416.asignaciones ;");
    $sentencia->execute();
    $Count = $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['operadores'] != 0) {
        return $Count['operadores'];
    } else {
        return '0';
    }
}

function DarValorListaColocadas()
{

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("select count(1) as ingresos from dbs9098416.asignaciones where Estado = 'Ingresado'");
    $sentencia->execute();
    $Count = $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['ingresos'] != 0) {
        return $Count['ingresos'];
    } else {
        return '0';
    }
}

function DarValorListaPendientes()
{

    include '../LQS_EUQ/Auth.php';

    $sentencia = $pdo->prepare("select count(1) as pendientes from dbs9098416.asignaciones where Estado = 'Pendiente'");
    $sentencia->execute();
    $Count = $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['pendientes'] != 0) {
        return $Count['pendientes'];
    } else {
        return '0';
    }
}

?>