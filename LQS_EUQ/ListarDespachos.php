<?php
include 'Connect.php';

$NombreUsuario = $_SESSION['Usuario'];

try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT *  FROM dbs9098416.despachos where Operador = '".$NombreUsuario."' and Estado = 'Pendiente'  and Guia_Carga ='".$GuiaDeCarga."'";

    $ejecutar_sentencia_Despachos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Despachos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_DespachoPRODUCCION =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}


?>