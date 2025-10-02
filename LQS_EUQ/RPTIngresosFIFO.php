<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT 
    ASG.Numero,
    ASG.IDH,
    ASG.Producto,
    PR.Linea,
    ASG.Posicion,
    ASG.FechaRegistro, 
    ASG.FechaColocado,
    ASG.Estado,
    ASG.Operador,
    ASG.PalletCompleto,
    ASG.Cantidades,
    PR.PESOBRUTOCAJA AS PesoBrutoCaja,
    ROUND(PR.PESOBRUTOCAJA * ASG.Cantidades / 1000, 2) AS PesoBrutoIngreso_TONS,
    ASG.Origen,
    ASG.FechaProduccion,
    ASG.LoteProduccion,
    ASG.FechaIngreso,
    ASG.FechaVencimiento,
    ASG.FechaCuarentena,
    ASG.Verificador,
    ASG.EstatusProducto,
    ASG.Observaciones  
FROM asignaciones AS ASG
LEFT JOIN productos AS PR ON PR.IDH = ASG.IDH
WHERE ASG.FechaRegistro BETWEEN '$HoraTrabajoInicio' AND '$HoraTrabajoFinal' and ASG.Observaciones <> 'grafica';";


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
