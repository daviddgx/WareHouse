<?php

function darValorDespachos($NombreUsuario){
    include '../LQS_EUQ/Connect.php';
    $sentencia = $pdo->prepare("SELECT count(1) as Despachos FROM dbs9098416.despachos_picking where Operador = '".$NombreUsuario."'; ");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);

    if ($Count['Despachos'] != 0){
        return '<span class="badge badge-primary notify-no rounded-circle"> '.$Count['Despachos'].' </span>';
    }else {
        return '';
    }

}
