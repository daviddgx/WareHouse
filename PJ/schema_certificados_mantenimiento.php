<?php

/**
 * Garantiza que exista la tabla de certificados de mantenimiento.
 */
function ensure_certificados_mantenimiento_schema(mysqli $conn): void {
  $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS certificados_mantenimiento (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      numero_certificado VARCHAR(30) NOT NULL UNIQUE,
      otorgada_a VARCHAR(180) NOT NULL,
      marca_modelo_serie VARCHAR(255) NOT NULL,
      ubicacion VARCHAR(255) NOT NULL,
      capacidad_dias INT UNSIGNED NOT NULL,
      texto_encabezado_equipo VARCHAR(255) NOT NULL DEFAULT 'El sistema de Grabación DVR marca Hikvision serie:',
      texto_cuerpo_certificacion TEXT NOT NULL,
      fecha_inicio_vigencia DATE NOT NULL,
      fecha_fin_vigencia DATE NOT NULL,
      resultado_auditoria VARCHAR(100) NOT NULL DEFAULT 'SATISFACTORIO',
      fecha_recertificacion DATE NOT NULL,
      autorizado_por VARCHAR(120) NOT NULL,
      correo_contacto VARCHAR(160) NOT NULL,
      anulado TINYINT(1) NOT NULL DEFAULT 0,
      fecha_anulacion DATETIME NULL,
      fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      KEY idx_numero_certificado (numero_certificado),
      KEY idx_otorgada_a (otorgada_a),
      KEY idx_fecha_fin_vigencia (fecha_fin_vigencia)
    ) $tableOptions
  SQL);

  // Migración ligera para instalaciones existentes.
  $hasEncabezado = $conn->query("SHOW COLUMNS FROM certificados_mantenimiento LIKE 'texto_encabezado_equipo'");
  if ($hasEncabezado && $hasEncabezado->num_rows === 0) {
    $conn->query("ALTER TABLE certificados_mantenimiento ADD COLUMN texto_encabezado_equipo VARCHAR(255) NOT NULL DEFAULT 'El sistema de Grabación DVR marca Hikvision serie:' AFTER capacidad_dias");
  }

  $hasCuerpo = $conn->query("SHOW COLUMNS FROM certificados_mantenimiento LIKE 'texto_cuerpo_certificacion'");
  if ($hasCuerpo && $hasCuerpo->num_rows === 0) {
    $conn->query("ALTER TABLE certificados_mantenimiento ADD COLUMN texto_cuerpo_certificacion TEXT NOT NULL AFTER texto_encabezado_equipo");
    $conn->query("UPDATE certificados_mantenimiento
      SET texto_cuerpo_certificacion = CONCAT(
        'Ubicado en ', ubicacion,
        '. Cuenta con la capacidad de almacenamiento de ', capacidad_dias,
        ' días de grabación continua.'
      )
      WHERE texto_cuerpo_certificacion = '' OR texto_cuerpo_certificacion IS NULL");
  }

  $hasAnulado = $conn->query("SHOW COLUMNS FROM certificados_mantenimiento LIKE 'anulado'");
  if ($hasAnulado && $hasAnulado->num_rows === 0) {
    $conn->query("ALTER TABLE certificados_mantenimiento ADD COLUMN anulado TINYINT(1) NOT NULL DEFAULT 0 AFTER correo_contacto");
  }

  $hasFechaAnulacion = $conn->query("SHOW COLUMNS FROM certificados_mantenimiento LIKE 'fecha_anulacion'");
  if ($hasFechaAnulacion && $hasFechaAnulacion->num_rows === 0) {
    $conn->query("ALTER TABLE certificados_mantenimiento ADD COLUMN fecha_anulacion DATETIME NULL AFTER anulado");
  }
}