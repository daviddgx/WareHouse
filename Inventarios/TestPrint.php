<?php
require_once __DIR__ . '/_bootstrap.php';

if (isset($_POST['imprimir'])) {

    $contenido = "Contenido a imprimir";

    $nombre_archivo = "nombre_archivo.pdf";

    // Configuramos la impresora de PDF de Windows como la impresora por defecto
    $handle = printer_open("Microsoft Print to PDF");

    // Configuramos las opciones de impresión
    $options = array(
        'JobName' => 'Nombre del trabajo de impresión',
        'PaperSize' => PRINTER_FORMAT_CUSTOM,
        'Width' => 215.9,
        'Height' => 139.7,
        'LeftMargin' => 0,
        'RightMargin' => 0,
        'TopMargin' => 0,
        'BottomMargin' => 0,
    );

    // Abrimos el archivo de impresión
    printer_start_doc($handle, $nombre_archivo);

    // Iniciamos una página
    printer_start_page($handle);

    // Imprimimos el contenido
    printer_draw_text($handle, $contenido, 10, 10);

    // Finalizamos la página y el archivo de impresión
    printer_end_page($handle);
    printer_end_doc($handle);

    // Cerramos la conexión con la impresora
    printer_close($handle);

    echo "Impresión finalizada.";
}


?>
