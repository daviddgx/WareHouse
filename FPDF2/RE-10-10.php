<?php

require('./fpdf.php');

date_default_timezone_set('America/Guatemala');

$fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");




$NoGuia = "";

$NoGuia = $_GET["Guia"];



$Turno1 = "06:00";
$Turno2 = "18:00";



class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {


        date_default_timezone_set('America/Guatemala');
        $NoGuia2 = $_GET["Guia"];

        $fechaConsulta = date("Y") . '-' . date("m") . '-' . date("d");

        include '../LQS_EUQ/Connect.php';
        $conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
        $ejecutar_sentencia_Despachos = $conn->query("SELECT * FROM dbs9098416.Guias where Transporte = $NoGuia2;");
        $lista_DespachoPRODUCCION =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);



        $fechaActualizacion = $fechaConsulta;
        //Varialbes de Entorno desde la base de datos
        $NombrePiloto = "";
        $Transporte = "";
        $NoPlaca = "";
        $Marchamo = "";
        $Destino = "";
        $Factura = "";

        $Verificador = "";
        $ResponsableChequeo = "";
        $ResponsablePreparador = "";
        $Montacarguista = "";
        $Ayudante = "";
        $Fecha = $fechaActualizacion;
        $Guia = "";
        $PlacaFurgon = "";
        $DPIPiloto = "";





        $NombrePiloto = $lista_DespachoPRODUCCION['Piloto'];
        $Transporte = $lista_DespachoPRODUCCION['Transportista'];
        $NoPlaca = $lista_DespachoPRODUCCION['Placa'];
        $Marchamo = $lista_DespachoPRODUCCION['Marchamo'];
        $Destino = $lista_DespachoPRODUCCION['NombreDestino'];
        $Factura = $lista_DespachoPRODUCCION['Factura'];

        $Verificador = $lista_DespachoPRODUCCION['Verificador'];
        $ResponsableChequeo = $lista_DespachoPRODUCCION['RespChequeo'];
        $ResponsablePreparador = $lista_DespachoPRODUCCION['RespPrepara'];
        $Montacarguista = $lista_DespachoPRODUCCION['Montacarguista'];
        $Ayudante = $lista_DespachoPRODUCCION['Ayudante'];
        $Fecha = $lista_DespachoPRODUCCION['FechaEngrega'];
        $Guia = $NoGuia2;
        $PlacaFurgon =$lista_DespachoPRODUCCION['PlacaFurgon'];
        $DPIPiloto = $lista_DespachoPRODUCCION['DPIPiloto'];







        $this->Image('logo.png', 8, 13, 25); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
        $this->SetFont('Arial', 'B', 12); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto

        $this->SetTextColor(0, 0, 0); //color
        //creamos una celda o fila
        $this->Cell(25); // Movernos a la derecha
        $this->Cell(125, 10, mb_convert_encoding('FORMATO DE CARGA DE EXPORTACION', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
        $this->SetFont('Arial', 'B', 12); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
        $this->Cell(40, 10, mb_convert_encoding('CODIGO RE-10-10', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)

        $this->SetFont('Arial', 'B', 10); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto

        $this->SetTextColor(0, 0, 0); //color
        //creamos una celda o fila
        $this->Cell(25); // Movernos a la derecha
        $this->Cell(30, 10, mb_convert_encoding('Procedimiento', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
        $this->Cell(95, 10, mb_convert_encoding('(PR-10-04) DESPACHO DE PRODUCTO TERMINADO', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
        $this->Cell(40, 10, mb_convert_encoding('Version 3', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
        $this->SetTextColor(103); //color

        /* Destino del envio  */
        $this->Cell(1);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(15, 10, mb_convert_encoding("Destino: ". $Destino, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);

        /* Placa */
        $this->Cell(60);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Placa: ". $NoPlaca, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);


        /* Responsable de verificacion */
        $this->Cell(40);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Verificador: ". $Verificador, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
        $this->Ln(5);

        /* Transporte */
        $this->Cell(1);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(15, 10, mb_convert_encoding("Transporte: ". $Transporte, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);


        /* Placa Furgon */
        $this->Cell(60);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(15, 10, mb_convert_encoding("Furgon: ". $PlacaFurgon, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);


        /* Responsable Doble Chequeo */
        $this->Cell(30);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Responsable Chequeo: ". $ResponsableChequeo, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
        $this->Ln(5);



        /* Marchamo */
        $this->Cell(1);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Marchamo: ". $Marchamo, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);

        /* Responsable Preparacion */
        $this->Cell(70);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Responsable de Preparar: ". $ResponsablePreparador, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
        $this->Ln(5);

        /* Destino */
        $this->Cell(1);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 10, mb_convert_encoding("Piloto: ". $NombrePiloto, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);


        /* Montacarguista */
        $this->Cell(65);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Montacarguista: ". $Montacarguista, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
        $this->Ln(5);

        /* Guia de Carga*/
        $this->Cell(1);  // mover a la derecha
        $this->SetTextColor(1); //color
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Guia de Carga : ". $Guia, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);

        $this->SetTextColor(103); //color
        /* Fecha */
        $this->Cell(70);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Fecha: ". $Fecha, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);

        /* Ayudante */
        $this->Cell(40);  // mover a la derecha
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 10, mb_convert_encoding("Ayudante: ". $Ayudante, 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
        $this->Ln(5);
        $this->Ln(5);



        /* CAMPOS DE LA TABLA */
        //color
        $this->SetFillColor(228, 0, 0); //colorFondo
        $this->SetTextColor(255, 255, 255); //colorTexto
        $this->SetDrawColor(163, 163, 163); //colorBorde
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(20, 10, mb_convert_encoding('IDH', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Cell(53, 10, mb_convert_encoding('DESCRIPCION', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Cell(21, 10, mb_convert_encoding('CANTIDAD', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Cell(27, 10, mb_convert_encoding('NUMERO FILA', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Cell(27, 10, mb_convert_encoding('CANT. POR FILA', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Cell(23, 10, mb_convert_encoding('TOTAL FILA', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Cell(21, 10, mb_convert_encoding('TOTAL GENERAL', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        $this->Ln(5);
        $this->Ln(5);
    }


    // Pie de página
    function Footer()
    {


        $this->SetY(-17); // Posición: a 1,5 cm del final
        $this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
        $this->Cell(0, 10, mb_convert_encoding('La información registrada es propiedad de Henkel La Luz S.A. queda  ', 'ISO-8859-1', 'UTF-8') , 0, 0, 'C'); //pie de pagina(numero de pagina)
        $this->Ln(3);
        $this->Cell(0, 10, mb_convert_encoding('Prohibida su reproducción total o parcial  ', 'ISO-8859-1', 'UTF-8') , 0, 0, 'C'); //pie de pagina(numero de pagina)

        $this->SetY(-17); // Posición: a 1,5 cm del final
        $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
        $hoy = date('d/m/Y');
        $this->Cell(355, 10, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); // pie de pagina(fecha de pagina)
    }
}




$pdf = new PDF();
$pdf->AddPage(); /* aqui entran dos para parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */

$pdf->AliasNbPages(); //muestra la pagina / y total de paginas

$i = 0;
$pdf->SetFont('Arial', '', 7);
$pdf->SetDrawColor(163, 163, 163); //colorBorde
include '../LQS_EUQ/Connect.php';
$conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
$ejecutar_sentencia_Despachos = $conn->query("SELECT
    Material,b.Descripcion,
    SUM(Pallets * Cajas) + SUM(Piking) AS Total
FROM
    DetalleGuias_Carga
    
    inner join productos b on Material = b.IDH  where Transporte = $NoGuia
GROUP BY
    Material
ORDER BY
    Material;");
$lista_DespachoPRODUCCION =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);


for ($i = 0; $i < $lista_DespachoPRODUCCION; $i++) {
    /* TABLA */
    $pdf->Cell(20, 7, mb_convert_encoding($lista_DespachoPRODUCCION['Material'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(53, 7, mb_convert_encoding($lista_DespachoPRODUCCION['Descripcion'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(21, 7,  mb_convert_encoding($lista_DespachoPRODUCCION['Total'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(27, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(27, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(23, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(21, 7, " ", 1, 1, 'C', 0);

    $pdf->Cell(20, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(53, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(21, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(27, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(27, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(23, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(21, 7, " ", 1, 1, 'C', 0);

    $pdf->Cell(20, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(53, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(21, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(27, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(27, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(23, 7, " ", 1, 0, 'C', 0);
    $pdf->Cell(21, 7, " ", 1, 1, 'C', 0);

    $pdf->Cell(20, 15.0, " ", 0, 0, 'C', 0);
    $pdf->Cell(53, 7.5, " ", 0, 0, 'C', 0);
    $pdf->Cell(21, 7.5, " ", 0, 0, 'C', 0);
    $pdf->Cell(27, 7.5, " ", 0, 0, 'C', 0);
    $pdf->Cell(27, 7.5, " ", 0, 0, 'C', 0);
    $pdf->Cell(23, 7.5, " ", 0, 0, 'C', 0);
    $pdf->Cell(21, 7.5, " ", 0, 1, 'C', 0);

    $lista_DespachoPRODUCCION = $ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);
}

$pdf->Cell(21, 6.6, " ", 0, 0, 'C', 0);
$pdf->Cell(21, 6.6, " ", 0, 0, 'C', 0);
$pdf->Cell(110, 20, "_____________________________________             _____________________________________             _____________________________________", 0, 1, 'C', 0);
$pdf->Cell(190, 6.6, "             Firma del Piloto                                                            Firma del Verificador                                                          Firma doble de chequeo", 0, 1, 'C', 0);


$pdf->Output('Carga de exportacion '.$NoGuia.'.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)
