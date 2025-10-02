<?php
// db.php — conexión MySQL compartida

date_default_timezone_set('America/Guatemala');

/**
 * Obtiene una conexión compartida a MySQL.
 */
function db(): mysqli {
  static $conn = null;
  if ($conn instanceof mysqli) {
    return $conn;
  }

  $host_name = 'db5018656066.hosting-data.io';
  $database  = 'dbs14782099';
  $user_name = 'dbu3844412';
  $password  = 'Inicio1994=.1998Final';

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  try {
    $conn = new mysqli($host_name, $user_name, $password, $database);
    $conn->set_charset('utf8mb4');
  } catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('<p>Failed to connect to MySQL: ' . $e->getMessage() . '</p>');
  }

  return $conn;
}
