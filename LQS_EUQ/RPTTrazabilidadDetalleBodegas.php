<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    
    $sqlDatos = "SELECT Ubicacion,IDH,PaletCompleto,UnidadesEnPallet,Origen,LoteProduccion FROM `posiciones` where IDH = $IDHConsulta and date(FechaProduccion) = '$FechaProduccion'";


$ejecutar_sentencia_Bodega = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Bodega)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_BodegaDetalle =$ejecutar_sentencia_Bodega->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
