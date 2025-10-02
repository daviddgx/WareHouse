<?php
include 'Connect.php';

date_default_timezone_set('America/Guatemala');
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$hora = date('G:i:s', time());

$fechaConsulta = $fechaConsulta;

$NombreUsuario = $_SESSION['Usuario'];
try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT distinct(IDH),IDH,Producto  FROM dbs9098416.asignaciones where Operador = '".$NombreUsuario."' and Estado = 'Pendiente' and DATE(FechaRegistro) <= '$fechaConsulta' " ;
    $ejecutar_sentencia_Asignaciones = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Asignaciones)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_AsignacionesPRODUCCION =$ejecutar_sentencia_Asignaciones->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}


?>