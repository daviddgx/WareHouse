<?php
ob_start();
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
}
include '../LQS_EUQ/Auth.php';

$Area = $_POST['Bodega'];
$Cadena ='';



$conn = new mysqli($servername, $username, $password, $dbname);

$cargos = "select Carril, count(*) as Ocupados from dbs9098416.posiciones  where Bodega = $Area and Estado = 'Ocupada'   and EstatusUbicacion not in ( 'Calidad','Cuarentena') group by Carril";

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
        echo '<option value="'.$row['Carril'].'">'.utf8_encode($row['Carril']).' -- '.utf8_encode($row['Ocupados']).'  Ubicaciones Ocupadas </option>';
    }
}

echo $Cadena . '</select>';
ob_end_flush();
?>

<script type="text/javascript">
    $(document).ready(function(){

        recargarListaIDHOrigen();
        $('#ListaCarril').change(function(){
            recargarListaIDHOrigen();

        });


    })
</script>

<script type="text/javascript">
    function recargarListaIDHOrigen() {
        console.warn( "Entro a Lista IDH" );
        $.ajax({
            type: "POST",
            url: "TraerIDHOrigen.php",

            data: "Carril="+$('#ListaCarril').val()+"&Bodega=<?php echo $Area?>",

            success:function(r) {
                $('#IDHOrigen').html(r);

            }

        });
    }

</script>










