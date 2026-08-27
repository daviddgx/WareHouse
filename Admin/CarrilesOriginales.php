<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
include '../LQS_EUQ/Auth.php';

$area = isset($_POST['Bodega']) && is_scalar($_POST['Bodega'])
    ? trim((string) $_POST['Bodega'])
    : '';
$carrilSeleccionado = isset($_POST['CarrilSeleccionado']) && is_scalar($_POST['CarrilSeleccionado'])
    ? trim((string) $_POST['CarrilSeleccionado'])
    : '';

echo '<select required class="funy form-control ng-pristine ng-valid ng-valid-required ng-touched"
              name="txtListaCarril" id="ListaCarril" onchange="cambiarValorCarril()">
        <option style="display:none; height:50px;" value="" class="ng-binding">
            --- Carril ---
        </option>';

if ($area !== '') {
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_errno) {
            throw new Exception('No se pudo conectar a la base de datos.');
        }
        $conn->set_charset('utf8');

        $consulta = $conn->prepare(
            "SELECT Carril, COUNT(*) AS Ocupados
             FROM dbs9098416.posiciones
             WHERE Bodega = ?
               AND Estado = 'Ocupada'
               AND EstatusUbicacion NOT IN ('Calidad', 'Cuarentena')
             GROUP BY Carril"
        );
        $consulta->bind_param('s', $area);
        $consulta->execute();
        $consulta->bind_result($carril, $ocupados);

        while ($consulta->fetch()) {
            $seleccionado = ((string) $carril === (string) $carrilSeleccionado)
                ? ' selected'
                : '';
            echo '<option value="'
                . htmlspecialchars($carril, ENT_QUOTES, 'UTF-8')
                . '"' . $seleccionado . '>'
                . htmlspecialchars($carril, ENT_QUOTES, 'UTF-8')
                . ' -- ' . (int) $ocupados . ' Ubicaciones Ocupadas</option>';
        }

        $consulta->close();
        $conn->close();
    } catch (Exception $ex) {
        error_log('CarrilesOriginales: ' . $ex->getMessage());
    }
}

echo '</select>';
ob_end_flush();
?>

<script type="text/javascript">
    $(document).ready(function(){
        recargarListaIDHOrigen();
        $('#ListaCarril').change(function(){
            recargarListaIDHOrigen();
        });
    });

    function recargarListaIDHOrigen() {
        $.ajax({
            type: "POST",
            url: "TraerIDHOrigen.php",
            data: {
                Carril: $('#ListaCarril').val(),
                Bodega: <?php echo json_encode($area); ?>
            },
            success:function(r) {
                $('#IDHOrigen').html(r);
            }
        });
    }
</script>
