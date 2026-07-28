-- Bitácora de eliminaciones de IDH realizadas desde el detalle de guías.
-- Ejecutar una sola vez antes de utilizar Admin/QuitarIDHDetalleGuias.php.

CREATE TABLE IF NOT EXISTS dbs9098416.AuditoriaEliminacionesDetalleGuias (
    IdEliminacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    IdEvento CHAR(32) NOT NULL,
    Transporte VARCHAR(100) NOT NULL,
    EntregaSolicitada VARCHAR(100) NOT NULL,
    IDH VARCHAR(100) NOT NULL,
    RegistrosEliminados INT UNSIGNED NOT NULL,
    DetalleEliminado JSON NOT NULL COMMENT 'Copia completa de las filas eliminadas de DetalleGuias',
    Usuario VARCHAR(150) NOT NULL,
    FechaHora DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    DireccionIP VARCHAR(45) NULL,
    IPsProxy VARCHAR(500) NULL,
    AgenteUsuario VARCHAR(1000) NULL,
    IdSesionHash CHAR(64) NULL,
    PRIMARY KEY (IdEliminacion),
    UNIQUE KEY UX_AuditoriaEliminacionGuias_Evento (IdEvento),
    KEY IX_AuditoriaEliminacionGuias_Guia_IDH_Fecha (Transporte, IDH, FechaHora),
    KEY IX_AuditoriaEliminacionGuias_Usuario_Fecha (Usuario, FechaHora)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
