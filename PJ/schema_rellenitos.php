<?php

/**
 * Garantiza que las tablas de Rellenitos existan en la base de datos.
 */
function ensure_rellenitos_schema(mysqli $conn): void {
  $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS rellenito_periods (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      opened_at DATETIME NOT NULL,
      closed_at DATETIME DEFAULT NULL,
      status ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
      KEY idx_status (status),
      KEY idx_opened_at (opened_at)
    ) $tableOptions
  SQL);

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS rellenito_orders (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      period_id INT UNSIGNED NOT NULL,
      sector VARCHAR(100) NOT NULL,
      casa VARCHAR(100) NOT NULL,
      codigo INT UNSIGNED NOT NULL,
      porciones INT UNSIGNED NOT NULL,
      toping ENUM('Azucar','Dulce de Leche','Chocolate','Sin Toping') NOT NULL DEFAULT 'Sin Toping',
      notas TEXT,
      tipo_pago ENUM('Transferencia','Efectivo') NOT NULL DEFAULT 'Transferencia',
      cambio DECIMAL(10,2) DEFAULT NULL,
      precio_unitario DECIMAL(10,2) NOT NULL,
      total DECIMAL(10,2) NOT NULL,
      estado ENUM('Registrado','Despachado') NOT NULL DEFAULT 'Registrado',
      hora_registro DATETIME NOT NULL,
      hora_entrega DATETIME DEFAULT NULL,
      KEY idx_period_id (period_id),
      KEY idx_estado (estado),
      KEY idx_hora_registro (hora_registro),
      CONSTRAINT fk_rellenito_orders_period FOREIGN KEY (period_id)
        REFERENCES rellenito_periods(id) ON DELETE CASCADE
    ) $tableOptions
  SQL);
}
