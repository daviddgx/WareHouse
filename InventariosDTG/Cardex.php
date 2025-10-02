<?php

include '../LQS_EUQ/Auth.php';

$Ubicacion = $_GET['Ubicacion'];

// 1. Traer informacion de la ubicacion

$txtIDH = "1234567";
$txtDiasVencimiento = "";
$txtDiasCuarentena  = "";
$txtDescripcion     = "";
$txtFecha           = "";
$txtHora            = "";
$txtTurno           = "";
$txtPallet          = "";
$txtUsuario         = "";
$txtMontacarguista  = "";
$txtLote            = "";
$txtBase            = "";
$txtAlto            = "";
$txtBultos          = "";
$txtCodigoBarras    = "";
$txtNoPallet        = "";

date_default_timezone_set('America/Guatemala');
$hora = date(' G:i:s ', time());
$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");
$fecha = date("d") . '-' . date("m") . '-' . date("Y");
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

    if (strtotime($hora) <= strtotime("23:59:59") && strtotime($hora) >= strtotime("18:00:00")) {
    $txtTurno = "2";
    $HoraTrabajoInicio = $fechaConsulta . " " . $Turno2;
    $HoraTrabajoFinal = $fechaConsulta . " " . "23:59:59";
} else {
    $txtTurno = "2";
    $FechaTrabajoAnterior = date('Y-m-d', strtotime($fechaConsulta . ' -1 day'));
    $HoraTrabajoInicio = $FechaTrabajoAnterior . " " . $Turno2;
    $HoraTrabajoFinal = $fechaConsulta . " " . $hora;
}
}


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {

    $error =
        '<div class="alert alert-danger" role="alert"><p><strong>Existe un problema con la conexion entre el sistema y la base de datos ️! por favor contacte al administrador de la aplicacion e informele de este error.</div>';
    // $row = $result->fetch_assoc();
} else {
$sql = "SELECT asignaciones.*,productos.BASE,productos.Altura,productos.BASE,productos.CAJASXPALET FROM dbs9098416.asignaciones
join productos on asignaciones.IDH = productos.IDH WHERE asignaciones.Posicion = '".$Ubicacion."' AND asignaciones.Estado = 'Pendiente'; ";
$result = $conn->query($sql);
// Fin Obtencion de datos
try {

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $txtIDH = $row['IDH'];

        $txtDiasVencimiento = $row['FechaVencimiento'];
        $txtDiasCuarentena  = $row['FechaCuarentena'];
        $txtDescripcion     = $row['Producto'];

        $txtFecha = date('d-m-Y', strtotime($row['FechaProduccion']));
        
        $txtHora = date('H:i:s', strtotime($row['FechaProduccion']));
        
        $txtTurno           = $txtTurno;
        $txtPallet          = $row['CAJASXPALET'];
        $txtUsuario         = $row['Verificador'];
        $txtMontacarguista  = $row['Operador'];
        $txtLote            = $row['LoteProduccion'];
        $txtBase            = $row['BASE'];
        $txtAlto            = $row['Altura'];
        $txtBultos          = $row['Cantidades'];
        $txtCodigoBarras    = $row['IDH'];

    }

}else{
    $error ="Se genero un error";
}



    $sentencia = $pdo->prepare("SELECT count(*) as Pallets FROM dbs9098416.asignaciones where IDH = '$txtIDH' and FechaRegistro  BETWEEN '".$HoraTrabajoInicio."' and '".$HoraTrabajoFinal."' ;");
    $sentencia->execute();
    $Count =  $sentencia->fetch(PDO::FETCH_LAZY);


        $txtNoPallet =  $Count['Pallets'];


} catch (Exception $ex) {

}
}

// 2. Armar los datos para imprimir el documento


// 3. Inprimir el Documento

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/Sertero/LogoCBP.png">
    <title>Sertero CBP / AdminFIFO</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <![endif]-->


    <title>Imprimir cuadro rojo</title>
    <style>
        /* Estilo para el cuadro rojo */
        .red-box {
            background-color: rgb(255, 0, 0);
            padding: 10px;
            padding-top: 3px;
            width: 12in;
            height: 8.5in;
            margin: 0 0;
            text-align: center;
            color: rgb(255, 255, 255);
            font-family: Arial, sans-serif;
            font-size: 17pt;
            box-sizing: border-box;
            border: 0px solid black;
        }

        .centrarTexto{
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 18pt;

        }

        .TextoBlanco{
            color: rgb(255, 255, 255);
            font-weight: bold;
        }

        /* Estilo para cada caja de elemento */
        .element-box {
            border: 3px solid white;
            padding: 5px;
            padding-top: 15px;
            padding-bottom: 15px;
            margin: 10px 0;
            background-color: rgb(255, 0, 0);
            color: Black;

        }

        /* Estilo para las líneas de texto */
        .line {
            margin: 0;
            text-align: left;
            font-weight: bold;
        }

        /* Estilo para el código de barras */
        .barcode {
            display: block;
            margin: 0 auto;
            padding: 0px 0;
            font-size: 28pt;

            font-family: 'Code 128', sans-serif;
        }
    </style>
</head>

<body>
<div class="red-box">
    <div class="element-box">
        <div class="box-content">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="TextoBlanco" >CODIGO DE PRODUCTO</label>
                        <input name="txtIDH"  style=" font-size: 65pt; " type="text" class="form-control centrarTexto" value="<?php echo $txtIDH ?>" required>
                    </div>

                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="TextoBlanco" >FECHA DE RECEPCION</label>
                        <input name="txtIDH" type="text" style="font-size: 65pt;" class="form-control centrarTexto" value="<?php echo $txtFecha ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                    <div class="col-md-12">
                    <div class="form-group">
                        <label class="TextoBlanco" >DESCRIPCION</label>
                        <input name="txtIDH" type="text" style="font-size: 40pt;" class="form-control centrarTexto" value="<?php echo $txtDescripcion ?>" required>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="element-box">
        <div class="box-content">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Hora</label>
                        <input name="txtIDH" type="text" style="font-size: 25pt; text-align: left" class="form-control " value="<?php echo $txtHora ?>" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="TextoBlanco" >Turno</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;"class="form-control centrarTexto" value="<?php echo $txtTurno ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Pallet</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;"class="form-control centrarTexto" value="<?php echo $txtNoPallet ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Receptor</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;"class="form-control centrarTexto" value="<?php echo $txtUsuario?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Montacarguista</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;"class="form-control centrarTexto" value="<?php echo $txtMontacarguista?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="TextoBlanco" >Lote</label>
                        <input name="txtIDH" type="text" class="form-control centrarTexto" value="<?php echo $txtLote?>" required>
                    </div>
                </div>
            </div>
             </div>
    </div>
    <div class="element-box">
        <div class="box-content">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Base</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;"class="form-control centrarTexto" value="<?php echo $txtBase ?>" required>
                    </div>

                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Alto</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;" class="form-control centrarTexto" value="<?php echo $txtAlto ?>" required>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="TextoBlanco" >Bultos</label>
                        <input name="txtIDH" type="text"  style="font-size: 30pt;" class="form-control centrarTexto" value="<?php echo $txtBultos ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div  class=" form-group" style="font-size: 100px">

                        <svg  data-value="<?php echo $txtIDH ?>"  class="codigo" /> </svg>
                    </div>
                </div>
            </div>
             </div>
    </div>
</div>




<script>
    window.print();
</script>

<script src="../dist/js/JsBarcode.all.min.js"></script>
<script>
    JsBarcode(".codigo").init();
</script>

</body>

</html>