<?php
include 'Connect.php';

$NombreUsuario = $_SESSION['Usuario'];
try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT *  FROM dbs9098416.Reubicaciones where Montacarguista = '".$NombreUsuario."' and Estado = 'Pendiente'";
    $ejecutar_sentencia_Movimientos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Movimientos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_DespachoPRODUCCION =$ejecutar_sentencia_Movimientos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}


?>