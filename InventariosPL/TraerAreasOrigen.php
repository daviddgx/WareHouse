<?php
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = "select distinct(Carril) as Carril from dbs9098416.posiciones  where Bodega = '".$Area."' and Estado = 'Ocupada'
";

  echo '<label>Carril Origen</label>
                                                    <select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched" name="txtListaCarrilOrigen" id="ListaCarrilOrigen" onchange="cambiarValorCarrilOrigen()" ">
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

        recargarListaPosicionesOrigen();
        $('#ListaCarrilOrigen').change(function(){
            recargarListaPosicionesOrigen();

        });


    })
</script>

<script type="text/javascript">
    function recargarListaPosicionesOrigen() {
        console.warn( "Entro a Lista Posiciones" );
        $.ajax({
            type: "POST",
            url: "TraerPosicionOrigen.php",

            data: "Carril="+$('#ListaCarrilOrigen').val()+"&Bodega=<?php echo $Area?>",

            success:function(r) {
                $('#Select_PosicionOrigen').html(r);

            }

        });
    }

</script>






