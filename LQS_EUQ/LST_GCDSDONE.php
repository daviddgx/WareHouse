<?php
include 'Connect.php';


try {
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);

    $sqlDatos = "SELECT Transporte,Fechapedido,FechaEngrega,date(despachos.FechaRealizado) as FechaDeDespacho,NombreDestino,Lugar,Transportista,Pais,Estatus,Piloto  FROM `Guias` 
INNER join despachos on despachos.Guia_Carga = Transporte where date(despachos.FechaRealizado) BETWEEN '$HoraTrabajoInicio' and '$HoraTrabajoFinal'
group by Transporte";
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

