<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = "SELECT distinct(LoteProduccion),count(*) as Ocupados FROM `posiciones` where IDH = $Area  group by LoteProduccion";

  echo '
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtListaCarril" id="ListaCarril" onchange="cambiarValorCarril()" ">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Lote a Bloquear ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0)

{
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['LoteProduccion'].'">'.$row['LoteProduccion'].' -- '.$row['Ocupados'].' </option>';
    }
}

echo $Cadena . '</select>';
ob_end_flush();
?>
