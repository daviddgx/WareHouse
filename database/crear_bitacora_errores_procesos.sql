-- Bitácora central para errores técnicos de procesos.
-- Ejecutar una sola vez en la base de datos dbs9098416.

CREATE TABLE IF NOT EXISTS dbs9098416.BitacoraErroresProcesos (
    IdError BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    CodigoError VARCHAR(40) NOT NULL,
    Proceso VARCHAR(100) NOT NULL,
    Nivel VARCHAR(20) NOT NULL DEFAULT 'ERROR',
    Referencia VARCHAR(100) NULL,
    Usuario VARCHAR(100) NULL,
    Mensaje TEXT NOT NULL,
    ClaseExcepcion VARCHAR(255) NULL,
    Archivo VARCHAR(500) NULL,
    Linea INT UNSIGNED NULL,
    Traza LONGTEXT NULL,
    DatosSolicitud JSON NULL,
    DireccionIP VARCHAR(45) NULL,
    AgenteUsuario VARCHAR(500) NULL,
    FechaError DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    Atendido TINYINT(1) NOT NULL DEFAULT 0,
    FechaAtencion DATETIME(6) NULL,
    AtendidoPor VARCHAR(100) NULL,
    Resolucion TEXT NULL,
    PRIMARY KEY (IdError),
    UNIQUE KEY UK_BitacoraErroresProcesos_Codigo (CodigoError),
    KEY IX_BitacoraErroresProcesos_Fecha (FechaError),
    KEY IX_BitacoraErroresProcesos_Proceso_Fecha (Proceso, FechaError),
    KEY IX_BitacoraErroresProcesos_Referencia (Referencia),
    KEY IX_BitacoraErroresProcesos_Atendido_Fecha (Atendido, FechaError)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

