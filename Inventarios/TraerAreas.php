<?php
require_once __DIR__ . '/_bootstrap.php';
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Cadena ='';


if ($Area == '10'){

}else {

$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = "SELECT DISTINCT Carril
FROM dbs9098416.posiciones
WHERE Bodega = '".$Area."' AND Estado = 'Libre'
ORDER BY SUBSTRING(Carril, 1, 1), CAST(SUBSTRING(Carril, 2) AS SIGNED);";

  echo '<label>Carril</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtListaCarril" id="ListaCarril" onchange="cambiarValorCarril()" ">
                                                        <option style="display:none; height:50px;" value="" class="ng-binding">
                                                            --- Carril ---
                                                        </option>
    ';

$result = $conn->query($cargos);
if ($result->num_rows > 0)

{
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['Carril'].'">'.$row['Carril'].'</option>';
    }
}

echo $Cadena . '</select>';
}
?>

<script type="text/javascript">
    $(document).ready(function(){

        recargarListaPosiciones();
        $('#ListaCarril').change(function(){
            recargarListaPosiciones();

        });


    })
</script>

<script type="text/javascript">
    function recargarListaPosiciones() {
        console.warn( "Entro a Lista Posiciones" );
        $.ajax({
            type: "POST",
            url: "TraerPosicion.php",

            data: "Carril="+$('#ListaCarril').val()+"&Bodega=<?php echo $Area?>",

            success:function(r) {
                $('#Select_Posicion').html(r);

            }

        });
    }

</script>






