<?php

function GetNombreBodegas() {

    include '../LQS_EUQ/Connect.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        // En producción podrías loguear el error en vez de mostrarlo
        die("Error de conexión: " . $conn->connect_error);
    }

    $sql = "
        SELECT 
    CONCAT('Bodega - ', CAST(p.Bodega AS SIGNED)) AS Descripcion,
    SUM(CASE WHEN p.Estado = 'Ocupada' THEN 1 ELSE 0 END) AS posiciones_ocupadas,
    SUM(CASE WHEN p.Estado = 'Libre' THEN 1 ELSE 0 END) AS posiciones_libres,
    COUNT(*) AS total_posiciones,
    ROUND(
        (SUM(CASE WHEN p.Estado = 'Ocupada' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0)) * 100
    ) AS porcentaje_ocupacion
FROM posiciones p
WHERE p.Bodega <> 9
  AND NOT EXISTS (
      SELECT 1
      FROM posisciones_temporalesCNF t
      WHERE t.ubicacion = p.Ubicacion
  )
GROUP BY p.Bodega
ORDER BY CAST(p.Bodega AS SIGNED) ASC;
    ";

    $bodegas = array();
    $result = $conn->query($sql);

    if ($result === false) {
        $conn->close();
        die("Error en query GetNombreBodegas: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $bodegas[] = $row["Descripcion"];
    }

    $result->free();
    $conn->close();

    return $bodegas;
}

function GetPorcentajeOcupacion() {

    include '../LQS_EUQ/Connect.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $sql = "
        SELECT 
            CAST(p.Bodega AS SIGNED) AS Bodega,
            SUM(CASE WHEN p.Estado = 'Ocupada' THEN 1 ELSE 0 END) AS posiciones_ocupadas,
            SUM(CASE WHEN p.Estado = 'Libre' THEN 1 ELSE 0 END) AS posiciones_libres,
            COUNT(*) AS total_posiciones,
            ROUND(
                (SUM(CASE WHEN p.Estado = 'Ocupada' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0)) * 100
            ) AS porcentaje_ocupacion
        FROM posiciones p
        WHERE p.Bodega <> 9
          AND NOT EXISTS (
              SELECT 1
              FROM posisciones_temporalesCNF t
              WHERE t.ubicacion = p.Ubicacion
          )
        GROUP BY p.Bodega

        UNION ALL

        SELECT 
            99 AS Bodega,
            SUM(CASE WHEN p.Estado = 'Ocupada' THEN 1 ELSE 0 END) AS posiciones_ocupadas,
            SUM(CASE WHEN p.Estado = 'Libre' THEN 1 ELSE 0 END) AS posiciones_libres,
            COUNT(*) AS total_posiciones,
            ROUND(
                (SUM(CASE WHEN p.Estado = 'Ocupada' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0)) * 100
            ) AS porcentaje_ocupacion
        FROM posiciones p
        WHERE p.Bodega <> 9
          AND NOT EXISTS (
              SELECT 1
              FROM posisciones_temporalesCNF t
              WHERE t.ubicacion = p.Ubicacion
          )

        ORDER BY Bodega;
    ";

    $Datos = array();
    $result = $conn->query($sql);

    if ($result === false) {
        $conn->close();
        die("Error en query GetPorcentajeOcupacion: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $Datos[] = $row["porcentaje_ocupacion"];
    }

    $result->free();
    $conn->close();

    return $Datos;
}
