<?php
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Carril = $_POST['Carril'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);
$cargos = "SELECT IDH, count(*) as Cantidad FROM dbs9098416.posiciones where Bodega = '".$Area."' and Carril = '".$Carril."' and EstatusUbicacion = 'Cuarentena' group by IDH";


  echo '<label>IDH a desbloquear</label>
                                                    <select required onchange="cambiarValorIDHOrigen()" class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaIDHOrigen" id="ListaIDHOrigen" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- IDH ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo '<option value="' . $row['IDH'] . '">' . $row['IDH'] . ' -- '.$row['Cantidad'].' Pallets</option>';
    }
}

echo $Cadena . '</select>';
?>


