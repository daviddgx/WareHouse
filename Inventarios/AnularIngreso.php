<?php

include "../Innet_MTC/Innet_MTC.php";
$Ubicacion = $_GET['Ubicacion'];


AnularIngreso($Ubicacion);
LiberarUbicacionAnulada($Ubicacion);


// Regresar a la pagina 


header('Location: Print_Cardex.php');

?>