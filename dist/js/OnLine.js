window.addEventListener('online', function() {
    document.getElementById("status").innerHTML = "Online";
    document.getElementById("status").classList.add("online");
    document.getElementById("status").classList.remove("offline");
}, false);

window.addEventListener('offline', function() {
    document.getElementById("status").innerHTML = "Offline";
    document.getElementById("status").classList.add("offline");
    document.getElementById("status").classList.remove("online");
}, false);


function ReloadPage() {
    location.reload();
    return false;
}

function Salir(){
    window.location.href = "../Innet/logout.php";

}

function PerfilAdminFifo(){
    window.location.href = "MiPerfil.php";

}

function carga() {
    document.getElementById('PreLoaderCont').className = 'hide';
    document.getElementById('Contenido').className = 'center  ';
}

