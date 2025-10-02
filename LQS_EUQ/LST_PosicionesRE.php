<?php
include 'Connect.php';



try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = " select Bodega,Carril,Posicion,Nivel,Ubicacion,Posiciones.IDH,Descripcion,FechaProduccion,EstatusUbicacion,Observaciones from Posiciones   inner join Productos on Posiciones.IDH = Productos.IDH where Posiciones.Estado = 'Ocupada'; ";

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

