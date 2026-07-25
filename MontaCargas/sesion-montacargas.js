(function () {
    'use strict';

    var TIEMPO_AVISO = 30 * 60 * 1000;
    var TIEMPO_CORTESIA = 5 * 60 * 1000;
    var INTERVALO_SINCRONIZACION = 60 * 1000;
    var ultimaActividad = Date.now();
    var ultimaSincronizacion = 0;
    var modalVisible = false;
    var cerrandoSesion = false;
    var elementoCuenta = null;

    function formatoCuenta(milisegundos) {
        var segundos = Math.max(0, Math.ceil(milisegundos / 1000));
        var minutos = Math.floor(segundos / 60);
        var resto = segundos % 60;

        return minutos + ':' + String(resto).padStart(2, '0');
    }

    function cerrarSesion() {
        if (cerrandoSesion) {
            return;
        }

        cerrandoSesion = true;
        window.location.replace('../Innet/logout.php');
    }

    function sincronizarActividad(forzar) {
        var ahora = Date.now();

        if (!forzar && (ahora - ultimaSincronizacion) < INTERVALO_SINCRONIZACION) {
            return;
        }

        ultimaSincronizacion = ahora;

        fetch('Sesion_Actividad.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: 'accion=actividad'
        }).then(function (respuesta) {
            if (respuesta.status === 401) {
                cerrarSesion();
            }
        }).catch(function () {
            // Sin conexión se conserva el reloj local; no se extiende la sesión.
        });
    }

    function registrarActividad() {
        if (modalVisible || cerrandoSesion) {
            return;
        }

        ultimaActividad = Date.now();
        sincronizarActividad(false);
    }

    function ocultarModal() {
        var modal = document.getElementById('modal-inactividad-mtc');

        if (modal) {
            modal.classList.remove('visible');
            modal.setAttribute('aria-hidden', 'true');
        }

        modalVisible = false;
    }

    function continuarSesion() {
        ultimaActividad = Date.now();
        ocultarModal();
        sincronizarActividad(true);
    }

    function crearModal() {
        var modal = document.createElement('div');
        modal.id = 'modal-inactividad-mtc';
        modal.className = 'modal-inactividad-mtc';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML =
            '<div class="modal-inactividad-contenido" role="dialog" aria-modal="true" ' +
                'aria-labelledby="titulo-inactividad-mtc">' +
                '<div class="modal-inactividad-icono" aria-hidden="true">⌛</div>' +
                '<h2 id="titulo-inactividad-mtc">¿Aún desea trabajar en el módulo?</h2>' +
                '<p>Su sesión se cerrará en</p>' +
                '<div id="cuenta-inactividad-mtc" class="cuenta-inactividad-mtc">5:00</div>' +
                '<div class="modal-inactividad-acciones">' +
                    '<button type="button" id="continuar-sesion-mtc" class="btn btn-success">Sí, continuar</button>' +
                    '<button type="button" id="cerrar-sesion-mtc" class="btn btn-outline-danger">Cerrar sesión</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);
        elementoCuenta = document.getElementById('cuenta-inactividad-mtc');
        document.getElementById('continuar-sesion-mtc').addEventListener('click', continuarSesion);
        document.getElementById('cerrar-sesion-mtc').addEventListener('click', cerrarSesion);
    }

    function mostrarModal(restante) {
        var modal = document.getElementById('modal-inactividad-mtc');

        if (!modal) {
            crearModal();
            modal = document.getElementById('modal-inactividad-mtc');
        }

        modalVisible = true;
        elementoCuenta.textContent = formatoCuenta(restante);
        modal.classList.add('visible');
        modal.setAttribute('aria-hidden', 'false');
        document.getElementById('continuar-sesion-mtc').focus();
    }

    function verificarTiempo() {
        var inactividad = Date.now() - ultimaActividad;
        var restante = TIEMPO_AVISO + TIEMPO_CORTESIA - inactividad;

        if (restante <= 0) {
            cerrarSesion();
            return;
        }

        if (inactividad >= TIEMPO_AVISO) {
            if (!modalVisible) {
                mostrarModal(restante);
            } else if (elementoCuenta) {
                elementoCuenta.textContent = formatoCuenta(restante);
            }
        }
    }

    function verificarAlRegresar() {
        if (document.visibilityState === 'visible') {
            verificarTiempo();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        crearModal();

        ['pointerdown', 'touchstart', 'keydown', 'scroll'].forEach(function (evento) {
            document.addEventListener(evento, registrarActividad, { passive: true });
        });

        document.addEventListener('visibilitychange', verificarAlRegresar);
        window.addEventListener('pageshow', verificarTiempo);
        window.setInterval(verificarTiempo, 1000);
        sincronizarActividad(true);
    });
}());

