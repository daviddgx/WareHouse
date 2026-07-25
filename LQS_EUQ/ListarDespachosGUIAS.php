<?php
include 'Connect.php';

$NombreUsuario = $_SESSION['Usuario'];

try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT despachos.Guia_Carga,
                        MIN(despachos.Entrega) AS Entrega,
                        Guias.NombreDestino AS Destino
                 FROM dbs9098416.despachos
                 INNER JOIN Guias ON Guias.Transporte = despachos.Guia_Carga
                 WHERE despachos.Operador = '".$NombreUsuario."'
                   AND despachos.Estado = 'Pendiente'
                 GROUP BY despachos.Guia_Carga, Guias.NombreDestino";
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
