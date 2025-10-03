<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Carril = $_POST['Carril'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);
$cargos = "SELECT IDH, count(*) as Cantidad FROM dbs9098416.posiciones where Bodega = '".$Area."' and Carril = '".$Carril."' and EstatusUbicacion = 'Calidad' group by IDH";


  echo '<label>IDH a desbloquear</label>
                                                    <select required onchange="cambiarValorIDHOrigen()" class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaIDHOrigen" id="ListaIDHOrigen" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- IDH ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo '<option value="' . $row['IDH'] . '">' . utf8_encode($row['IDH']) . ' -- '.utf8_encode($row['Cantidad']).' Pallets</option>';
    }
}

echo $Cadena . '</select>';
ob_end_flush();
?>


