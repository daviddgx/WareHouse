<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
include '../LQS_EUQ/Auth.php';

$IDH = $_POST['IDH'];

$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = " SELECT Bodega as Bodegas, count(*) as Pallets  FROM dbs9098416.posiciones where PaletCompleto = 'Si' and IDH = $IDH and Estado = 'Ocupada'  and  EstatusUbicacion NOT IN ('Cuarentena', 'Calidad') group by Bodega; ";

echo '<label>Bodega</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtListaBodega" id="ListaBodega"  onchange="cambiarValorCarril()">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Bodega ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0)

{
    while ($row = $result->fetch_assoc()) {
               echo '<option value="'.$row['Bodegas'].'">'. $row['Bodegas'] . " -- " .$row['Pallets']. " Pallet(s)" . '</option>';
    }
}

echo $Cadena . '</select>';
ob_end_flush();
?>









