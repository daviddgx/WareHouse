<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT dspachos_especiales.*,Descripcion FROM dbs9098416.dspachos_especiales join productos P on dspachos_especiales.IDH = P.IDH;";
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

