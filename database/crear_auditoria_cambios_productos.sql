-- Bitácora detallada de cambios realizados desde la edición de productos.
-- Ejecutar una sola vez antes de utilizar Admin/EditarProducto.php.

CREATE TABLE IF NOT EXISTS dbs9098416.AuditoriaCambiosProductos (
    IdCambio BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    IdEvento CHAR(32) NOT NULL COMMENT 'Agrupa los campos modificados en una misma operación',
    IDH BIGINT NOT NULL,
    Campo VARCHAR(64) NOT NULL,
    ValorAnterior TEXT NULL,
    ValorNuevo TEXT NULL,
    Usuario VARCHAR(150) NOT NULL,
    FechaHora DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    DireccionIP VARCHAR(45) NULL,
    IPsProxy VARCHAR(500) NULL,
    AgenteUsuario VARCHAR(1000) NULL,
    IdSesionHash CHAR(64) NULL,
    PRIMARY KEY (IdCambio),
    KEY IX_AuditoriaProductos_IDH_Fecha (IDH, FechaHora),
    KEY IX_AuditoriaProductos_Usuario_Fecha (Usuario, FechaHora),
    KEY IX_AuditoriaProductos_Evento (IdEvento),
    KEY IX_AuditoriaProductos_Campo_Fecha (Campo, FechaHora)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
