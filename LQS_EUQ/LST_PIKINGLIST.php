<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT PR.IDH, PR.Descripcion, PR.MINIMOPICKING, PR.MAXIMOPICKING, CF.Ubicacion, COUNT(PK.IDH) AS Actual
    FROM productos PR
    LEFT JOIN detalle_piking PK ON PR.IDH = PK.IDH AND PK.Estatus IS NULL
    LEFT JOIN config_piking CF ON CF.IDH = PR.IDH
    GROUP BY PR.IDH, PR.Descripcion, PR.MINIMOPICKING, PR.MAXIMOPICKING, CF.Ubicacion";
    $ejecutar_sentencia_Productos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Productos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_Productos =$ejecutar_sentencia_Productos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}


?>

