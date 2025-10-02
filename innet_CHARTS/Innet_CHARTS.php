<?php

function GetNombreBodegas(){

    include '../LQS_EUQ/Connect.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql ="SELECT CONCAT(COUNT(*),' - Bodega ', Bodega) AS Descripcion 
FROM `posiciones` 
WHERE Estado = 'Ocupada' and bodega <> 9
GROUP BY Bodega 
ORDER BY Bodega + 0;";
    $bodegas = array();


    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array
       
        while($row = $result->fetch_assoc()) {
            $bodegas[] = $row["Descripcion"];
        }
    }

    return $bodegas;

}

function GetPorcentajeOcupacion(){

    include '../LQS_EUQ/Connect.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql ="SELECT 
    CAST(Bodega AS SIGNED) as Bodega,
    SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) as posiciones_ocupadas,
    SUM(CASE WHEN Estado = 'Libre' THEN 1 ELSE 0 END) as posiciones_libres,
    COUNT(*) as total_posiciones,
    ROUND((SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) / COUNT(*)) * 100) as porcentaje_ocupacion
FROM posiciones where  bodega <> 9
GROUP BY Bodega

UNION 

SELECT 
    '99' as Bodega,
    SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) as posiciones_ocupadas,
    SUM(CASE WHEN Estado = 'Libre' THEN 1 ELSE 0 END) as posiciones_libres,
    COUNT(*) as total_posiciones,
    ROUND((SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) / COUNT(*)) * 100) as porcentaje_ocupacion
FROM posiciones where bodega <> 9
ORDER BY CAST(Bodega AS SIGNED);";
    $Datos = array();


    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        // Almacena los nombres de las bodegas en un array
       
        while($row = $result->fetch_assoc()) {
            //Se debe colocar el nombre de las coumnas a retornar, para este caso solo regresa una dimension.
            $Datos[] =  $row["porcentaje_ocupacion"];
        }
    }

    return $Datos;

}