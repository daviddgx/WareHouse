<?php
include 'Connect.php';

$NombreUsuario = $_SESSION['Usuario'];
try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "select PK.id, PK.IDH, PK.Descripcion, PK.Origen, PK.Destino, PK.Fecha_Ingreso, PK.Fecha_Movimiento, PK.Estado, PK.Operador, PK.Montacarguista, PK.Nota, PS.UnidadesEnPallet as Bultos from piking PK
    inner join posiciones PS on PS.Ubicacion = PK.Origen where PK.Montacarguista = '".$NombreUsuario."' and PK.Estado = 'Pendiente';";

    $ejecutar_sentencia_Movimientos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Movimientos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_Movimientos =$ejecutar_sentencia_Movimientos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}
?>