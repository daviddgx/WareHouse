<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT IDH,Producto,date(FechaProduccion) as Produccion ,date(FechaRegistro) as Registro, date(FechaColocado) as Colocado,PalletCompleto,Cantidades,origen,LoteProduccion,Verificador,Operador,Estado, count(*) as registros FROM `asignaciones` where IDH = $IDHConsulta and date(FechaProduccion) = '$FechaProduccion' and Estado <> 'Anulado' group by IDH,Producto,date(FechaProduccion)  ,date(FechaRegistro) , date(FechaColocado) ,PalletCompleto,Cantidades,origen,LoteProduccion,Verificador,Operador,Estado ";


$ejecutar_sentencia_Asignaciones = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Asignaciones)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_AsignacionesDetalle =$ejecutar_sentencia_Asignaciones->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
