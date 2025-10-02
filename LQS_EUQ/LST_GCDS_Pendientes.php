<?php
include 'Connect.php';


try {
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);

    $sqlDatos = "select Transporte,FechaPedido,FechaEngrega,NombreDestino,lugar,Transportista,pais,Estatus,Piloto from dbs9098416.Guias where Estatus in('Pendiente' ,'FiFo Calculado','Corregir')  and Piloto !='Pendiente' GROUP by Transporte,FechaPedido,FechaEngrega,NombreDestino,lugar,Transportista,pais,Estatus,Piloto";
    $ejecutar_sentencia_Guias = $conn->query($sqlDatos);

    // Verifica si la consulta retorna resultados

        // Obtiene los datos en forma de un arreglo
        $lista_Guias =$ejecutar_sentencia_Guias->fetch(PDO::FETCH_ASSOC);

} catch (Exception $ex) {
    // Captura la excepción y procesala de alguna manera
    // (por ejemplo, registrando el error en un archivo de log)
    error_log("Error: " . $ex->getMessage());
}


?>

