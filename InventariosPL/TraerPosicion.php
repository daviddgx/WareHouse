<?php
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Carril = $_POST['Carril'];
$Cadena ='';

if ($Area == '10'){

}else {


    $conn = new mysqli($servername, $username, $password, $dbname);
    $cargos = "SELECT DISTINCT Posicion
FROM dbs9098416.posiciones
WHERE Bodega = '" . $Area . "' AND Carril = '" . $Carril . "' AND Estado = 'Libre'
ORDER BY SUBSTRING(Posicion, 1, 1), CAST(SUBSTRING(Posicion, 2) AS SIGNED);
";

    echo '<label>Posicion</label>
                                                    <select required onchange="cambiarValorPosicion()" class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="ListaPosicion" id="ListaPosicion" ng-model="properties.value" ng-options="ctrl.getValue(option) as (ctrl.getLabel(option) | uiTranslate) for option in properties.availableValues" ng-required="properties.required" ng-disabled="properties.disabled">
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
}
?>

<script type="text/javascript">
    $(document).ready(function(){

        recargarListaNiveles();
        $('#ListaPosicion').change(function(){
            recargarListaNiveles();
        });
    })
</script>

<script type="text/javascript">
    function recargarListaNiveles() {
        console.warn( "Entro a Lista Niveles" );
        $.ajax({
            type: "POST",
            url: "TraerNiveles.php",

            data: "Posicion="+$('#ListaPosicion').val() + "&Bodega=<?php echo $Area?>"+"&Carril=<?php echo $Carril?>",

            success:function(r) {
                $('#Select_Niveles').html(r);
            }
        });
    }
</script>
