<?php

/**
 * Garantiza que las tablas de Chiles existan en la base de datos.
 */
function ensure_chiles_schema(mysqli $conn): void {
  $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS chiles_periods (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      opened_at DATETIME NOT NULL,
      closed_at DATETIME DEFAULT NULL,
      status ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
      KEY idx_status (status),
      KEY idx_opened_at (opened_at)
    ) $tableOptions
  SQL);

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS chiles_orders (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      period_id INT UNSIGNED NOT NULL,
      sector VARCHAR(30) NOT NULL,
      casa VARCHAR(100) NOT NULL,
      codigo VARCHAR(30) NOT NULL,
      porciones INT UNSIGNED NOT NULL DEFAULT 0,
      unidades INT UNSIGNED NOT NULL DEFAULT 0,
      comentarios TEXT,
      tipo_pago ENUM('Transferencia','Efectivo') NOT NULL DEFAULT 'Transferencia',
      monto_pagado DECIMAL(10,2) DEFAULT NULL,
      cambio DECIMAL(10,2) DEFAULT NULL,
      precio_porcion DECIMAL(10,2) NOT NULL,
      precio_unidad DECIMAL(10,2) NOT NULL,
      total DECIMAL(10,2) NOT NULL,
      estado ENUM('Registrado','Despachado') NOT NULL DEFAULT 'Registrado',
      hora_registro DATETIME NOT NULL,
      hora_entrega DATETIME DEFAULT NULL,
      KEY idx_period_id (period_id),
      KEY idx_estado (estado),
      KEY idx_hora_registro (hora_registro),
      CONSTRAINT fk_chiles_orders_period FOREIGN KEY (period_id)
        REFERENCES chiles_periods(id) ON DELETE CASCADE
    ) $tableOptions
  SQL);
}
