<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
if (isset($_GET['Guia'])) {$conexion = new mysqli($servername, $username, $password, $dbname);
$Transporte = $_GET['Guia'];


    date_default_timezone_set('America/Guatemala');

    $fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
    $hora = date(' G:i:s ', time());
    $fechaActualizacion = $fechaConsulta." ".$hora;

    $FechaQRU = $fechaConsulta." ".$hora;


    //$consulta = "update dbs9098416.Guias set Estatus = 'Despachando' where Transporte ='".$Transporte."' ;";
    $consulta = "insert into dbs9098416.despachos
   SELECT null,Transporte,Entrega,Material,P.Descripcion,Ubicacion,(select Rampa from Guias  where Transporte =  '".$Transporte."' LIMIT 1 ),'$FechaQRU',null,(select Montacarguista from Guias  where Transporte =  '".$Transporte."' LIMIT 1),'Pendiente' FROM dbs9098416.DetalleGuias 
   inner Join dbs9098416.productos P on Material=P.IDH
   where Transporte = '".$Transporte."' and Tipo = 'Pallets' and Ubicacion is not null and Ubicacion <> 'Piking';";

    try {
        $resultado = $conexion->query($consulta);

        try {
            $consulta = "update dbs9098416.Guias set Estatus = 'Despachando' where Transporte ='".$Transporte."'  ;";
            $resultado = $conexion->query($consulta);


            header('Location:Traking_Guias.php');
        } catch (Exception $e) {


        exit;
    }


        exit;
    } catch (Exception $e) {

        echo $e ->getMessage();
        exit;
    }
}else{
    header('Location: Traking_Guias.php');
}

ob_end_flush();
?>


