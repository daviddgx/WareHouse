<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "call dbs9098416.RepoirteIngresosMT('".$HoraTrabajoInicio."', '".$HoraTrabajoFinal."','".$UsuarioTrabajo."' ); ";


    $ejecutar_sentencia_Asignaciones = $conn->query($sqlDatos);
    if(!$ejecutar_sentencia_Asignaciones)
    {
        echo 'Hay un error en la sentencia de SQL: '.$sqlDatos;
    }else{
        //paso 4 trer los datos en forma de un arreglo
        $lista_AsignacionesPRODUCCION =$ejecutar_sentencia_Asignaciones->fetch(PDO::FETCH_ASSOC);

    }

}catch(Exception $ex){
    echo $ex;

}

?>
