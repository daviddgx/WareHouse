<?php
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Carril = $_POST['Carril'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);
$cargos = "select distinct(Posicion) as Posicion from dbs9098416.posiciones  where  Bodega = '".$Area."' and Carril = '".$Carril."' and Estado = 'Libre'
";

  echo '<label>Posicion</label>
                                                    <select required onchange="cambiarValorPosicion2()" class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaPosicion2" id="ListaPosicion2" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Posicion ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo '<option value="' . $row['Posicion'] . '">' . utf8_encode($row['Posicion']) . '</option>';
    }
}

echo $Cadena . '</select>';
?>

<script type="text/javascript">
    $(document).ready(function(){

        recargarListaNiveles2();
        $('#ListaPosicion2').change(function(){
            recargarListaNiveles2();
        });
    })
</script>

<script type="text/javascript">
    function recargarListaNiveles2() {
        console.warn( "Entro a Lista Niveles" );
        $.ajax({
            type: "POST",
            url: "TraerNiveles2.php",

            data: "Posicion="+$('#ListaPosicion2').val() + "&Bodega=<?php echo $Area?>"+"&Carril=<?php echo $Carril?>",

            success:function(r) {
                $('#Select_Niveles2').html(r);
            }
        });
    }
</script>
