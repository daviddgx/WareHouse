<?php
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = "select distinct(Carril) as Carril from dbs9098416.posiciones  where Bodega = '".$Area."' and Estado = 'Libre'
";

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
?>

<script type="text/javascript">
    $(document).ready(function(){

        recargarListaPosiciones();
        $('#ListaCarril').change(function(){
          //  recargarListaPosiciones();

        });


    })
</script>








