<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../Media/Icono.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operadores - Sistema de Montacargas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            padding-bottom: 70px;
        }
        .sidebar {
            height: calc(100% - 56px);
            position: fixed;
            left: 0;
            top: 56px;
            background-color: #343a40;
            padding-top: 20px;
            width: 250px;
            transition: all 0.3s;
        }
        .sidebar a {
            padding: 15px;
            text-decoration: none;
            font-size: 1rem;
            color: #fff;
            display: block;
        }
        .sidebar a.active {
            background-color: #007bff;
            color: #fff;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar .submenu {
            padding-left: 20px;
            display: none;
        }
        .submenu.show {
            display: block;
        }
        .sidebar a.toggle-submenu {
            cursor: pointer;
        }
        .content {
            margin-left: 270px;
            margin-top: 100px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            .content {
                margin-left: 0;
            }
        }
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.9); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <img src="../Media/Icono.png" alt="Loading..." width="100" height="100">
        <div class="spinner-border text-primary mt-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="mt-3">Sertero Sistema de Gestion de Montacargas</h5>
    </div>
    <header class="bg-dark text-white p-3 fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="../Media/Icono.png" alt="Logo" width="25" height="25" class="me-2">
                <span class="me-4" title="Sistema de gestion de montacargas">Sertero SGM</span>
            </div>
            <div class="d-flex align-items-center">
                <span id="connection-status" class="badge rounded-pill bg-success me-3">Online</span>
                <i class="fas fa-truck-moving me-2"></i>
                <span class="me-3">Bienvenido, Usuario</span>
                <a href="../index.php" class="btn btn-outline-light btn-sm">LogOut</a>
            </div>
        </div>
    </header>

    <div class="sidebar">
        <a href="Index.php"><i class="fas fa-tachometer-alt"></i> DashBoard</a>
        <a href="Operadores.php"  style="background-color: #0056b3;"><i class="fas fa-users"></i> Operadores</a>
        <a class="toggle-submenu"><i class="fas fa-truck"></i> Vehiculos <i class="fas fa-chevron-down float-end"></i></a>
        <div class="submenu">
            <a href="Vehiculos.php" >Admin Vehiculos</a>
            <a href="AutVehiculos.php" >Autorizacion por vehiculo</a>
            <a href="AutOperador.php" class="active">Autorizacion por operador</a>
            <a href="ClonConfig.php">Clonar configuracion de vehiculos</a>
            <a href="MensajesVehiculos.php">Mensajes de vehiculos</a>
        </div>
        <a href="Reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a>
        <a href="Configuracion.php"><i class="fas fa-cogs"></i> Configuracion</a>
    </div>

    <div class="content" style="background-color: #f8f9fa; padding: 30px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: 20px; margin-left: 270px; margin-top: 120px;">
        <!-- Contenido principal vacío para agregar posteriormente -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        document.querySelectorAll('.toggle-submenu').forEach(function (element) {
            element.addEventListener('click', function () {
                const submenu = this.nextElementSibling;
                if (submenu && submenu.classList.contains('submenu')) {
                    submenu.classList.toggle('show');
                }
            });
        });

        function updateConnectionStatus() {
            const statusElement = document.getElementById('connection-status');
            if (navigator.onLine) {
                statusElement.textContent = 'Online';
                statusElement.classList.remove('bg-danger');
                statusElement.classList.add('bg-success');
            } else {
                statusElement.textContent = 'Offline';
                statusElement.classList.remove('bg-success');
                statusElement.classList.add('bg-danger');
            }
        }

        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        updateConnectionStatus();
    </script>
    <script>
        window.addEventListener('load', function() {
            document.getElementById('loader').style.display = 'none';
        });
    </script>
    <footer class="bg-dark text-white p-3 mt-5">
        <div class="container">
            <p class="text-center mb-0">&copy; <span id="current-year"></span> &reg; All Rights Reserved by Sertero. Designed and Developed by Qbit-Lab.</p>
        </div>
    </footer>

    <script>
        document.getElementById('current-year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
