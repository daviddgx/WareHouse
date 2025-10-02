<?php
$error = "";
$mensajeExito = "";
//$servername="localhost:3306";
$servername="db5010754261.hosting-data.io";
$username="dbu2953297";
$password="zecsyd-nafwoQ-kypky3";
$dbname="dbs9098416";
//$servernamePDO="mysql:dbname=dbs9098416;host=localhost:3306";
$servernamePDO="mysql:dbname=dbs9098416;host=db5010754261.hosting-data.io";


try {
    //code...
    $pdo = new PDO($servernamePDO,$username,$password,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
    //echo "Conectado..";
} catch (PDOException $th) {
    //throw $th;
    //echo "Error al conectar: ".$th->getMessage();
}

?>


