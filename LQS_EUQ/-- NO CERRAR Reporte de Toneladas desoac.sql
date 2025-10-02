-- NO CERRAR Reporte de Toneladas desoachadas

SELECT DATE(FechaDespacho) AS Fecha, SUM(PesoDeDespacho) / 1000 AS TotalPesoDespacho
FROM (
    -- Consulta original
    SELECT DISTINCT
        D.Estado, D.Posicion, P.Nivel, D.Descripcion, P.Bodega, D.IDH, 
        DATE(PH.FechaProduccion) AS FechaProduccion, DATE(PH.FechaVencimiento) AS FechaVencimiento,
        D.Operador, 'Turno', 'Tapado/Libre',G.NombreDestino, G.Transportista, D.Guia_Carga as Transporte, 
        TIME(D.FechaRealizado) AS HoraDeDespacho, 'Notas', 
        IFNULL(TIMESTAMPDIFF(MONTH, date(D.Fecha_Hora_Despacho), date(PH.FechaVencimiento)), 'No se puede calcular') AS MesesVidaUtil, 
        'Tapando/NoTapando', PH.EstatusUbicacion AS ProductoEsta, PR.CAJASXPALET, PR.LINEA, PR.PESOBRUTOCAJA as PesoPorCaja,PH.UnidadesEnPallet as Cajas, (PR.PESOBRUTOCAJA * PH.UnidadesEnPallet) as PesoDeDespacho,
        D.Fecha_Hora_Despacho AS FechaDespacho, MONTHNAME(Fecha_Hora_Despacho) AS MES, DATE_FORMAT(Fecha_Hora_Despacho, '%W') AS nombre_dia,
        CONCAT(
            TIMESTAMPDIFF(DAY, D.Fecha_Hora_Despacho, D.FechaRealizado), ' días, ',
            HOUR(TIMEDIFF(D.FechaRealizado, D.Fecha_Hora_Despacho)), ' horas, ',
            MINUTE(TIMEDIFF(D.FechaRealizado, D.Fecha_Hora_Despacho)), ' minutos, ',
            SECOND(TIMEDIFF(D.FechaRealizado, D.Fecha_Hora_Despacho)), ' segundos'
        ) AS TiempoDeDespacho  
    FROM despachos D
    INNER JOIN posiciones P ON P.Ubicacion = D.Posicion
    INNER JOIN posiciones_historico PH ON PH.ID_Movimiento = D.Movimiento AND PH.TipoMovimiento = 'Despacho'
    INNER JOIN Guias G ON G.Transporte = D.Guia_Carga 
    INNER JOIN productos PR ON PR.IDH = D.IDH
    WHERE DATE(D.Fecha_Hora_Despacho) BETWEEN '2024-01-01' AND '2024-01-31' 
    UNION
    SELECT DISTINCT
        DG.Estatus,
        CP.Ubicacion AS 'Origen-Piking',
        CP.Nivel AS 'Nivel',
        PR.Descripcion,
        'Picking' AS Tipo,
        DG.Material,
        'FechaProduccion',
        'FechaVencimiento',
        DS.Operador,
        'Turno',
        'Tapado/Libre',
        GS.NombreDestino AS Cliente,
        GS.Transportista AS Trasportista,
        DG.Transporte,
        'HoraDespacho',
        'Notas',
        'MesesVidaUtil',
        'Tapado/NoTapado',
        'Libre',
        DSGC.Piking as Bultos,
        PR.LINEA, PR.PESOBRUTOCAJA AS PesoPorCajas,DSGC.Piking as Cajas,(PR.PESOBRUTOCAJA * DSGC.Piking)as PesoDeDespacho,
        DATE(Fecha_Hora_Despacho) AS FechaDespacho,
        DATE_FORMAT(Fecha_Hora_Despacho, '%W') AS nombre_dia,
        DATE_FORMAT(Fecha_Hora_Despacho, '%M') AS nombre_mes,
        'TiempoDeDespacho'
    FROM
        `DetalleGuias` DG
    INNER JOIN
        `productos` PR ON DG.Material = PR.IDH
        INNER JOIN
        despachos DS ON DS.Guia_Carga = DG.transporte
    INNER JOIN
        config_piking CP ON DG.Material = CP.IDH
    INNER JOIN
        Guias GS ON DG.Transporte = GS.Transporte
        inner join
        DetalleGuias_Carga DSGC on DSGC.Transporte = DG.transporte and DSGC.Material = DG.Material
    WHERE
    DG.Tipo = 'Piking'
        AND DATE(Fecha_Hora_Despacho) BETWEEN '2024-01-01' AND '2024-01-31'
) AS subquery
GROUP BY Fecha
ORDER BY Fecha;