DROP PROCEDURE IF EXISTS dbs9098416.PonerGuiasEnFirme;

DELIMITER $$

CREATE PROCEDURE dbs9098416.PonerGuiasEnFirme()
SQL SECURITY DEFINER
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO dbs9098416.Guias
    SELECT
        Transporte,
        0,
        FechaPedido,
        FechaEngrega,
        NombreDestino,
        LugarDestino,
        Direccion,
        Transportista,
        'Pendiente',
        Expedicion,
        Canal,
        Pais,
        Incoterms,
        'Pendiente',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        ''
    FROM dbs9098416.Guia_PreCarga
    GROUP BY
        Transporte,
        FechaPedido,
        FechaEngrega,
        NombreDestino,
        LugarDestino,
        Direccion,
        Transportista,
        Expedicion,
        Canal,
        Pais,
        Incoterms;

    UPDATE dbs9098416.Guia_PreCarga
    SET Cajas = REPLACE(Cajas, ',', '')
    WHERE Cajas LIKE '%,%';

    UPDATE dbs9098416.Guia_PreCarga
    SET PesoNeto = REPLACE(PesoNeto, ',', '')
    WHERE PesoNeto LIKE '%,%';

    UPDATE dbs9098416.Guia_PreCarga
    SET PesoBruto = REPLACE(PesoBruto, ',', '')
    WHERE PesoBruto LIKE '%,%';

    UPDATE dbs9098416.Guia_PreCarga
    SET Entrega = '0'
    WHERE NombreDestino IN (
        SELECT Cliente
        FROM dbs9098416.unificacion_entregas
    );

    INSERT INTO dbs9098416.DetalleGuias (
        IDRegistro,
        Transporte,
        Entrega,
        Material,
        Cajas,
        PesoNeto,
        PesoBruto,
        Estatus,
        Ubicacion,
        Tipo
    )
    SELECT
        NULL,
        Transporte,
        Entrega,
        Material,
        SUM(Cajas),
        SUM(PesoNeto),
        SUM(PesoBruto),
        '',
        '',
        NULL
    FROM dbs9098416.Guia_PreCarga
    GROUP BY
        Transporte,
        Entrega,
        Material;

    DELETE FROM dbs9098416.Guia_PreCarga;

    COMMIT;
END$$

DELIMITER ;
