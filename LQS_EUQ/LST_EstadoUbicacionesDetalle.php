<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT DISTINCT(Estado),Bodega,Count(*) as Cantidad FROM `posiciones` GROUP by Estado,Bodega";
    $ejecutar_sentencia_Detalle = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Detalle)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_Detalle =$ejecutar_sentencia_Detalle->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}


?>

