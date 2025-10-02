<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    
    $sqlDatos = "SELECT PSH.Ubicacion, PSH.Estado, PSH.IDH,DS.Descripcion,PSH.PaletCompleto, PSH.UnidadesEnPallet, PSH.Origen, PSH.LoteProduccion, DS.Guia_Carga,  DS.Rampa, DS.Fecha_Hora_Despacho, DS.FechaRealizado,DS.Operador FROM `posiciones_historico` PSH 
    inner JOIN despachos DS on PSH.ID_Movimiento = DS.Movimiento
    where PSH.IDH = $IDHConsulta and date(PSH.FechaProduccion) = '$FechaProduccion' and TipoMovimiento = 'Despacho'";


$ejecutar_sentencia_Despachos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Despachos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_DespachosDetalle =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
