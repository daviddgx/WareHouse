<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT Nombre_Bodega,Descripcion,(select count(*) from posiciones where Bodega = warehauses.Nombre_Bodega ) as posiciones,(select count(*) from posiciones where Bodega = warehauses.Nombre_Bodega and Estado = 'Ocupada') as posicionesocupadas,(select count(*) from posiciones where Bodega = warehauses.Nombre_Bodega and EstatusUbicacion = 'Cuarentena') as cuarentena,(select count(*) from posiciones where Bodega = warehauses.Nombre_Bodega and EstatusUbicacion = 'Calidad') as calidad, estado FROM dbs9098416.warehauses";
    $ejecutar_sentencia_Productos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Productos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_Productos =$ejecutar_sentencia_Productos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}


?>

