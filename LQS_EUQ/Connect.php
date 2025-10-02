<?php

$error = "";
$mensajeExito = "";
$servername = "db5010754261.hosting-data.io";
//$servername="localhost:3306";
$username = "dbu2953297";
$password = "zecsyd-nafwoQ-kypky3";
$dbname = "dbs9098416";

if (!function_exists('lqs_get_connection')) {
    /**
     * Returns a shared mysqli connection using the credentials defined above.
     *
     * @throws Exception when the connection fails.
     */
    function lqs_get_connection(): mysqli
    {
        static $connection = null;

        if ($connection instanceof mysqli) {
            return $connection;
        }

        global $servername, $username, $password, $dbname;

        $connection = new mysqli($servername, $username, $password, $dbname);

        if ($connection->connect_errno) {
            throw new Exception('Database connection failed: ' . $connection->connect_error);
        }

        $connection->set_charset('utf8');

        return $connection;
    }
}

?>