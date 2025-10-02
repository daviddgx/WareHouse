<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = " SELECT DISTINCT
    D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
    DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
    D.Operador, 'Turno', 'Tapado/Libre',G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
    TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
    IFNULL(TIMESTAMPDIFF(MONTH, date(D.FechaRealizado), date(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
    'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja,PH.UnidadesEnPallet as Cajas, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet) /1000 as PesoDeDespacho,
    D.FechaRealizado AS FechaDespacho, MONTHNAME(FechaRealizado) AS MES, DATE_FORMAT(FechaRealizado, '%W') AS nombre_dia,
    CONCAT(
        TIMESTAMPDIFF(DAY, D.FechaRealizado, D.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(D.FechaRealizado, D.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho  
FROM despachos D
INNER JOIN posiciones P ON P.Ubicacion = D.Posicion
INNER JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
INNER JOIN Guias G ON G.Transporte = D.Guia_Carga 
INNER JOIN productos PR ON PR.IDH = D.IDH
WHERE DATE(D.FechaRealizado) BETWEEN '$HoraTrabajoInicio' AND '$HoraTrabajoFinal' 

union

SELECT DS.Estado,DP.Ubicacion,'N/A', PR.Descripcion,'Picking',DS.IDH,date(DP.FechaProduccion),DP.FechaVencimiento, 'Piking', 'Turno', 'Tapado/Libre',GS.NombreDestino,GS.Transportista,DS.Guia_Carga, TIME(DS.FechaRealizado), 'N/A', IFNULL(TIMESTAMPDIFF(MONTH, date(DS.FechaRealizado), date(DP.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 'Picking', 'Picking', DP.UnidadesEnPallet, PR.LINEA, PR.PESOBRUTOCAJA AS PesoPorCajas,DP.UnidadesEnPallet ,(PR.PESOBRUTOCAJA * DP.UnidadesEnPallet) / 1000 as PesoDeDespacho,
DATE(DS.FechaRealizado) AS FechaDespacho,
        DATE_FORMAT(DS.FechaRealizado, '%M') AS nombre_mes,
        DATE_FORMAT(DS.FechaRealizado, '%W') AS nombre_dia,
        CONCAT(
        TIMESTAMPDIFF(DAY, DS.FechaRealizado, DS.FechaRealizado), ' días, ',
        HOUR(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' horas, ',
        MINUTE(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' minutos, ',
        SECOND(TIMEDIFF(DS.FechaRealizado, DS.FechaRealizado)), ' segundos'
    ) AS TiempoDeDespacho 
         FROM `despachos` DS
inner join detalle_piking DP on DS.Guia_Carga = DP.Transporte and DS.IDH = DP.IDH
INNER join productos PR on DS.IDH = PR.IDH 
INNER JOIN Guias GS on DS.Guia_Carga = GS.Transporte
where Operador = 'PIKING' and date(Fecha_Hora_Despacho) BETWEEN '$HoraTrabajoInicio' AND '$HoraTrabajoFinal' LIMIT 3000;";

$ejecutar_sentencia_Despachos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Despachos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_DespachoPRODUCCION =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
