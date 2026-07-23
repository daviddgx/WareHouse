<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = "select Carril, count(*) as Ocupados from dbs9098416.posiciones  where Bodega = $Area and Estado = 'Ocupada'  and  EstatusUbicacion = 'Calidad'group by Carril";

  echo '
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtListaCarril" id="ListaCarril" onchange="cambiarValorCarril()" ">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Carril ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0)

{
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['Carril'].'">'.$row['Carril'].' -- '.$row['Ocupados'].'  Ubicaciones Ocupadas </option>';
    }
}

echo $Cadena . '</select>';
ob_end_flush();
?>










