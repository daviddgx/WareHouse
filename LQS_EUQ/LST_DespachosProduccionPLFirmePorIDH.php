<?php
include 'Connect.php';

try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);


date_default_timezone_set('America/Guatemala');
$hora = date('G:i:s', time());
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$Turno1 = "06:00:00";
$Turno2 = "18:00:00";
$FechaTrabajoAnterior="";
$HoraTrabajoInicio="";
$HoraTrabajoFinal="";

if(strtotime($hora) < strtotime($Turno2) && strtotime($hora) > strtotime($Turno1)  ){
    $txtTurno = "1";
    $HoraTrabajoInicio = $fechaConsulta." ".$Turno1 ;
    $HoraTrabajoFinal  = $fechaConsulta." ".$Turno2;

}else{

    if(strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")) {

        $HoraTrabajoInicio = $fechaConsulta." ".$Turno2 ;
        $HoraTrabajoFinal  = $fechaConsulta." ".$hora;
    }else{

        $FechaTrabajoAnterior= date('Y-m-d', strtotime($fechaConsulta . ' -1 day')); // Resta un día a la fecha actual
        $HoraTrabajoInicio  = $FechaTrabajoAnterior." ".$Turno2;
        $HoraTrabajoFinal = $fechaConsulta." ".$hora;


    }
}

    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT *,concat(b.nombre,' ',b.Apellido) as nombreOperador  FROM dbs9098416.asignaciones     join usuarios_app b
on  b.Nombre_Usuario = asignaciones.Operador where IDH = $txtIDHQRY and  EstatusProducto = 'PL' and  Estado = 'Pendiente' order by CAST(SUBSTRING(Posicion, 2) AS SIGNED) DESC;";

$ejecutar_sentencia_Despachos = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Despachos)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_DespachoPRODUCCION =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
