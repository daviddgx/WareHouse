<?php

/**
 * Crea las tablas del módulo de finanzas si no existen.
 */
function ensure_finanzas_schema(mysqli $conn): void {
  $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS finanzas_categorias (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(120) NOT NULL,
      tipo ENUM('Ingreso', 'Gasto') NOT NULL,
      estado ENUM('Activa', 'Inactiva') NOT NULL DEFAULT 'Activa',
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uq_categoria_tipo (nombre, tipo),
      KEY idx_tipo_estado (tipo, estado)
    ) $tableOptions
  SQL);

  $conn->query(<<<SQL
    CREATE TABLE IF NOT EXISTS finanzas_movimientos (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      categoria_id INT UNSIGNED NOT NULL,
      tipo ENUM('Ingreso', 'Gasto') NOT NULL,
      descripcion VARCHAR(255) NOT NULL,
      monto DECIMAL(12,2) NOT NULL,
      fecha_movimiento DATE NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY idx_fecha_tipo (fecha_movimiento, tipo),
      KEY idx_categoria (categoria_id),
      CONSTRAINT fk_finanzas_categoria FOREIGN KEY (categoria_id)
        REFERENCES finanzas_categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) $tableOptions
  SQL);

  $seedStmt = $conn->prepare(
    "INSERT IGNORE INTO finanzas_categorias (nombre, tipo, estado) VALUES (?, ?, 'Activa')"
  );

  $categoriasBase = [
    ['Salario', 'Ingreso'],
    ['Ventas', 'Ingreso'],
    ['Otros ingresos', 'Ingreso'],
    ['Alimentación', 'Gasto'],
    ['Transporte', 'Gasto'],
    ['Servicios', 'Gasto'],
    ['Otros gastos', 'Gasto'],
  ];

  foreach ($categoriasBase as [$nombre, $tipo]) {
    $seedStmt->bind_param('ss', $nombre, $tipo);
    $seedStmt->execute();
  }

  $seedStmt->close();
}
