<?php

require('./fpdf.php');

class PDF extends FPDF
{

   // Cabecera de página
   function Header()
   {
      //include '../../recursos/Recurso_conexion_bd.php';//llamamos a la conexion BD

      //$consulta_info = $conexion->query(" select *from hotel ");//traemos datos de la empresa desde BD
      //$dato_info = $consulta_info->fetch_object();
      $this->Image('logo.png', 270, 5, 20); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->SetFont('Arial', 'B', 19); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Cell(95); // Movernos a la derecha
      $this->SetTextColor(0, 0, 0); //color
      //creamos una celda o fila
      $this->Cell(110, 15, mb_convert_encoding('NOMBRE EMPRESA', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
      $this->Ln(3); // Salto de línea
      $this->SetTextColor(103); //color

      /* UBICACION */
      $this->Cell(180);  // mover a la derecha
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(96, 10, mb_convert_encoding("Ubicación : ", 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
      $this->Ln(5);

      /* TELEFONO */
      $this->Cell(180);  // mover a la derecha
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(59, 10, mb_convert_encoding("Teléfono : ", 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
      $this->Ln(5);

      /* COREEO */
      $this->Cell(180);  // mover a la derecha
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(85, 10, mb_convert_encoding("Correo : ", 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
      $this->Ln(5);

      /* TELEFONO */
      $this->Cell(180);  // mover a la derecha
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(85, 10, mb_convert_encoding("Sucursal : ", 'ISO-8859-1', 'UTF-8'), 0, 0, '', 0);
      $this->Ln(10);

      /* TITULO DE LA TABLA */
      //color
      $this->SetTextColor(228, 100, 0);
      $this->Cell(100); // mover a la derecha
      $this->SetFont('Arial', 'B', 15);
      $this->Cell(100, 10, mb_convert_encoding("REPORTE DE HABITACIONES ", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C', 0);
      $this->Ln(7);

      /* CAMPOS DE LA TABLA */
      //color
      $this->SetFillColor(228, 100, 0); //colorFondo
      $this->SetTextColor(255, 255, 255); //colorTexto
      $this->SetDrawColor(163, 163, 163); //colorBorde
      $this->SetFont('Arial', 'B', 11);
      $this->Cell(30, 10, mb_convert_encoding('N°', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(40, 10, mb_convert_encoding('NÚMERO', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(40, 10, mb_convert_encoding('TIPO', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(40, 10, mb_convert_encoding('PRECIO', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(85, 10, mb_convert_encoding('CARACTERÍSTICAS', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
      $this->Cell(40, 10, mb_convert_encoding('ESTADO', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 1);
   }

   // Pie de página
   function Footer()
   {
      $this->SetY(-15); // Posición: a 1,5 cm del final
      $this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C'); //pie de pagina(numero de pagina)

      $this->SetY(-15); // Posición: a 1,5 cm del final
      $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
      $hoy = date('d/m/Y');
      $this->Cell(540, 10, mb_convert_encoding($hoy, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); // pie de pagina(fecha de pagina)
   }
}

//include '../../recursos/Recurso_conexion_bd.php';
//require '../../funciones/CortarCadena.php';
/* CONSULTA INFORMACION DEL HOSPEDAJE */
//$consulta_info = $conexion->query(" select *from hotel ");
//$dato_info = $consulta_info->fetch_object();

$pdf = new PDF();
$pdf->AddPage("landscape"); /* aqui entran dos para parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */
$pdf->AliasNbPages(); //muestra la pagina / y total de paginas

$i = 0;
$pdf->SetFont('Arial', '', 12);
$pdf->SetDrawColor(163, 163, 163); //colorBorde

/*$consulta_reporte_alquiler = $conexion->query("  ");*/

/*while ($datos_reporte = $consulta_reporte_alquiler->fetch_object()) {      
   }*/
$i = $i + 1;
/* TABLA */
$pdf->Cell(30, 10, mb_convert_encoding("N°", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
$pdf->Cell(40, 10, mb_convert_encoding("numero", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
$pdf->Cell(40, 10, mb_convert_encoding("nombre", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
$pdf->Cell(40, 10, mb_convert_encoding("precio", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
$pdf->Cell(85, 10, mb_convert_encoding("info", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 0);
$pdf->Cell(40, 10, mb_convert_encoding("total", 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 0);


$pdf->Output('Prueba2.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)
