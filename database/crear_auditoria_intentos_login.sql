-- Auditoría de accesos al sistema.
-- Ejecutar una sola vez en la base de datos dbs9098416.
-- No se almacena la contraseña ni su hash.

CREATE TABLE IF NOT EXISTS dbs9098416.AuditoriaIntentosLogin (
    IdIntento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Usuario VARCHAR(100) NOT NULL,
    Resultado ENUM('SATISFACTORIO', 'FALLIDO') NOT NULL,
    Motivo VARCHAR(100) NULL,
    FechaHora DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    DireccionIP VARCHAR(45) NULL,
    IPsProxy VARCHAR(500) NULL,
    NombreDispositivo VARCHAR(255) NULL,
    AgenteUsuario VARCHAR(1000) NULL,
    Plataforma VARCHAR(100) NULL,
    Idioma VARCHAR(100) NULL,
    MetodoHTTP VARCHAR(10) NULL,
    URI VARCHAR(500) NULL,
    Referente VARCHAR(1000) NULL,
    PaisCodigo CHAR(2) NULL,
    Region VARCHAR(150) NULL,
    Ciudad VARCHAR(150) NULL,
    Latitud DECIMAL(10,7) NULL,
    Longitud DECIMAL(10,7) NULL,
    IdSesionHash CHAR(64) NULL,
    DatosAdicionales JSON NULL,
    PRIMARY KEY (IdIntento),
    KEY IX_AuditoriaLogin_Usuario_Fecha (Usuario, FechaHora),
    KEY IX_AuditoriaLogin_Resultado_Fecha (Resultado, FechaHora),
    KEY IX_AuditoriaLogin_IP_Fecha (DireccionIP, FechaHora)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

