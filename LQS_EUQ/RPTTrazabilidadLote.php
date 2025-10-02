<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT IDH,date(FechaProduccion) as Produccion ,date(FechaColocado) as Ingreso ,Origen, count(*) as registros FROM `asignaciones` where IDH =  $IDHConsulta  and Estado <> 'Anulado' group by IDH, date(FechaProduccion),date(FechaColocado), Origen order by FechaProduccion asc ";


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
