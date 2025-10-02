<?php
include 'Connect.php';


try{
    $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);



    //paso 3 hacer la sentencia sql y ejecutarla
    $sqlDatos = "SELECT  A.Bodega AS A_BODEGA, 	A.Carril AS A_CARRIL,	A.Posicion AS A_POSICION,	A.Nivel AS A_NIVEL, 	A.Ubicacion AS A_UBICACION, 	A.Estado AS A_ESTADO,	A.IDH AS A_IDH,	A.PaletCompleto AS A_PALLETCOMPLETO,	A.UnidadesEnPallet AS A_UNIDADESENPALLET,	A.Origen AS A_ORIGEN,	A.FechaProduccion AS A_FECHAPRODUCCION,	A.LoteProduccion AS A_LOTEPRODUCCION,	A.FechaIngreso AS A_FECHAINGRESO,	A.FechaVencimiento AS A_FECHAVENCIMIENTO,	A.FechaCuarentena AS A_FECHACUARENTENA,	A.Cantidad AS A_CANTIDAD,	A.EstatatusProducto	AS A_ESTATUSPRODUCTO, A.Verificador AS A_VERIFICADOR,	A.UsuarioMontaCargas AS A_MONTACARGUISTA,	A.Turno AS A_TURNO,	A.EstatusUbicacion AS A_ESTATUSUBICACION,	A.Observaciones AS A_OBSERVACIONES,	B.Movimiento AS B_MOVIMIENTO,	B.Guia_Carga AS B_GUIACARGA,	B.Entrega AS B_ENTREGA,	B.IDH AS B_IDH,	B.Descripcion AS B_DESCRIPCION,	B.Posicion AS B_POSICION,	B.Rampa AS B_RAMPA,	B.Fecha_Hora_Despacho AS B_FECHADESPACHO,	B.FechaRealizado AS B_FECHAREALIZADO,	B.Operador AS B_OPERADOR,	B.Estado AS B_ESTATUS
FROM (
    SELECT *,
           SUBSTRING(ID_Movimiento, LOCATE('Despacho ID:', ID_Movimiento) + CHAR_LENGTH('Despacho ID:')) AS Numero_Despacho
    FROM posiciones_historico
    WHERE ID_Movimiento LIKE 'Despacho ID:%' 
) A
INNER JOIN despachos B ON B.Movimiento = A.Numero_Despacho
where FechaRealizado between '$HoraTrabajoInicio' and '$HoraTrabajoFinal'";


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
