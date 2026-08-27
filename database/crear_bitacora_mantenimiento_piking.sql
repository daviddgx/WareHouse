-- Bitácora de ajustes realizados desde Mantenimiento_Piking_QuitarInventario.php.
-- La sentencia es idempotente y no altera una tabla existente.

CREATE TABLE IF NOT EXISTS dbs9098416.BitacoraMantenimientoPiking (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Fecha DATETIME NOT NULL,
    Usuario VARCHAR(250) NOT NULL,
    IDH INT NOT NULL,
    LoteProduccion VARCHAR(50) NOT NULL,
    BultosSolicitados INT UNSIGNED NOT NULL,
    BultosEliminados INT UNSIGNED NOT NULL,
    Descripcion VARCHAR(1000) NOT NULL,
    DetalleEliminacion LONGTEXT NOT NULL,
    PRIMARY KEY (Id),
    INDEX IDX_BitacoraMantenimientoPiking_Fecha (Fecha),
    INDEX IDX_BitacoraMantenimientoPiking_IDH_Lote (IDH, LoteProduccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
