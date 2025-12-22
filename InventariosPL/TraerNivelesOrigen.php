<?php
include '../LQS_EUQ/Auth.php';

$Posicion = $_POST['Posicion'];
$Area = $_POST['Bodega'];
$Carril = $_POST['Carril'];


$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);
$cargos = "select distinct(Nivel) as Nivel from dbs9098416.posiciones  where Bodega = '".$Area."' and Carril = '".$Carril."' and Posicion = '".$Posicion."' and Estado = 'Ocupada'";

  echo '<label>Nivel Origen</label>
                                                    <select required onchange="cambiarValorNivelOrigen()" class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaNiveOrigen;" id="ListaNivelOrigen"">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Nivel ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0)

{
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['Nivel'].'">'.$row['Nivel'].'</option>';
    }
}

echo $Cadena . '</select>';
?>


</script>
