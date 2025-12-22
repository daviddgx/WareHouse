<?php

require('./fpdf.php');

$HoraTrabajoInicio = "";
$HoraTrabajoFinal  = "";
date_default_timezone_set('America/Guatemala');
$HoraTrabajoInicio = $_GET["FInicio"];
$HoraTrabajoFinal  = $_GET["FFinal"];


$Turno1 = "06:00";
$Turno2 = "18:00";

class PDF extends FPDF
{
   // Cabecera de página
   function Header()
   {
       $HoraTrabajoInicio = $_GET["FInicio"];
       $HoraTrabajoFinal  = $_GET["FFinal"];



      $this->Image('logo.png', 8, 13, 25); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->SetFont('Arial', 'B', 19); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto

      $this->SetTextColor(0, 0, 0); //color
      //creamos una celda o fila
       $this->Cell(25); // Movernos a la derecha
      $this->Cell(110, 8, mb_convert_encoding('INFORME DE PRODUCCION PL', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
      $this->Cell(55, 8, mb_convert_encoding('Codigo RE-10-03', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)

       $this->SetFont('Arial', 'B', 7); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto

       $this->SetTextColor(0, 0, 0); //color
       //creamos una celda o fila
       $this->Cell(25); // Movernos a la derecha
       $this->Cell(40, 7, mb_convert_encoding('Procedimiento', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
       $this->Cell(70, 7, mb_convert_encoding('(PR-10-01) Recepcion de produccion', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
       $this->Cell(55, 7, mb_convert_encoding('Version 4', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)




      $this->SetTextColor(103); //color



       /* Fecha Inicio */
       $this->Cell(40);  // mover a la derecha
       $this->SetFont('Arial', 'B', 10);

       $this->Cell(60, 7, mb_convert_encoding("Fecha Inicio : ". date('d-m-Y H:i:s', strtotime($HoraTrabajoInicio)), 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);


       /* Fecha Final */
       $this->Cell(5);  // mover a la derecha
       $this->SetFont('Arial', 'B', 10);
       $this->Cell(59, 7, mb_convert_encoding("Fecha Final  : ". date('d-m-Y H:i:s', strtotime($HoraTrabajoFinal)), 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
       $this->Ln(5);
       $this->Ln(3); // Salto de línea



      /* CAMPOS DE LA TABLA */
      //color
      $this->SetFillColor(228, 0, 0); //colorFondo
      $this->SetTextColor(255, 255, 255); //colorTexto
      $this->SetDrawColor(163, 163, 163); //colorBorde
      $this->SetFont('Arial', 'B', 7);
      $this->Cell(21, 6, mb_convert_encoding('IDH', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(51, 6, mb_convert_encoding('DESCRIPCION', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(21, 6, mb_convert_encoding('ESTADO', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(21, 6, mb_convert_encoding('OPERADOR', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(21, 6, mb_convert_encoding('VERIFICADOR', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(15, 6, mb_convert_encoding('PALLETS', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(21, 6, mb_convert_encoding('BULTOS', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(21, 6, mb_convert_encoding('UNIDADES', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 1);

   }


   // Pie de página
   function Footer()
   {

       $this->SetY(-180); // Posición: a 1,5 cm del final
       $this->SetFont('Arial', 'B', 10); //tipo fuente, cursiva, tamañoTexto
       $hoy = "_________________________________";
       $this->Cell(120, 1, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); //

       $this->SetY(-175); // Posición: a 1,5 cm del final
       $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
       $hoy = "Nombre / Firma Supervisor de turno";
       $this->Cell(120, 1, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); //


       $this->SetY(-180); // Posición: a 1,5 cm del final
       $this->SetFont('Arial', 'B', 10); //tipo fuente, cursiva, tamañoTexto
       $hoy = "_________________________________";
       $this->Cell(260, 1, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); //

       $this->SetY(-175); // Posición: a 1,5 cm del final
       $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
       $hoy = "Nombre / Firma Encargado de bodega";
       $this->Cell(260, 1, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); //

      $this->SetY(-175); // Posición: a 1,5 cm del final
      $this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Cell(0, 10, mb_convert_encoding('La información registrada es propiedad de Henkel La Luz S.A. queda  ', 'ISO-8859-1', 'UTF-8') , 0, 0, 'C'); //pie de pagina(numero de pagina)
       $this->Ln(3);
      $this->Cell(0, 10, mb_convert_encoding('Prohibida su reproducción total o parcial  ', 'ISO-8859-1', 'UTF-8') , 0, 0, 'C'); //pie de pagina(numero de pagina)

      $this->SetY(-175); // Posición: a 1,5 cm del final
      $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
      $hoy = date('d/m/Y');
      $this->Cell(355, 10, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); // pie de pagina(fecha de pagina)
   }
}



$pdf = new PDF();
$pdf->AddPage(); /* aqui entran dos para parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */

$pdf->AliasNbPages(); //muestra la pagina / y total de paginas

$i = 0;
$pdf->SetFont('Arial', '', 6.5);
$pdf->SetDrawColor(163, 163, 163); //colorBorde
include '../LQS_EUQ/Connect.php';
$conn  = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
$ejecutar_sentencia_Despachos = $conn->query("call dbs9098416.RepoirteIngresosPL('".$HoraTrabajoInicio."', '".$HoraTrabajoFinal."' );");
$lista_DespachoPRODUCCION =$ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);


for ($i = 0; $i < $lista_DespachoPRODUCCION; $i++) {
    /* TABLA */
    $pdf->Cell(21, 5, mb_convert_encoding($lista_DespachoPRODUCCION['IDH'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(51, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Producto'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(21, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Estado'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(21, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Operador'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(21, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Verificador'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(15, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Cantidad'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(21, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Bultos'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
    $pdf->Cell(21, 5, mb_convert_encoding($lista_DespachoPRODUCCION['Unidades'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0);

    $lista_DespachoPRODUCCION = $ejecutar_sentencia_Despachos->fetch(PDO::FETCH_ASSOC);
}



// Lineas de Firma

# Agregar un salto de línea


# Dibujar dos líneas para la firma
// Inicio en X, Inicio en Y, Fin en X, Fin en Y
//$pdf->line(20, 150, 85, 150);  # primera línea horizontal

//$pdf->line(120, 150, 185, 150);  # segunda línea horizontal





$pdf->Output('ReporteIngresos.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)
