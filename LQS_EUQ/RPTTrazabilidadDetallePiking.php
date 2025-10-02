<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    
    $sqlDatos = "SELECT Ubicacion,IDH,sum(UnidadesEnPallet) as Bultos,Origen,LoteProduccion,Estatus,Transporte FROM `detalle_piking` where IDH = $IDHConsulta and date(FechaProduccion) = '$FechaProduccion' GROUP by Ubicacion,IDH,UnidadesEnPallet,Origen,LoteProduccion,Estatus,Transporte";


$ejecutar_sentencia_Piking = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Piking)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_PikingDetalle =$ejecutar_sentencia_Piking->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
