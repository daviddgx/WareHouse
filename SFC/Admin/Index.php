<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../Media/Icono.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sistema de Montacargas</title>
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
        <a href="Index.php" class="active"><i class="fas fa-tachometer-alt"></i> DashBoard</a>
        <a href="Operadores.php"><i class="fas fa-users"></i> Operadores</a>
        <a class="toggle-submenu"><i class="fas fa-truck"></i> Vehiculos <i class="fas fa-chevron-down float-end"></i></a>
        <div class="submenu">
            <a href="Vehiculos.php">Admin Vehiculos</a>
            <a href="AutVehiculos.php">Autorizacion por vehiculo</a>
            <a href="AutOperador.php">Autorizacion por operador</a>
            <a href="ClonConfig.php">Clonar configuracion de vehiculos</a>
            <a href="MensajesVehiculos.php">Mensajes de vehiculos</a>
        </div>
        <a href="Reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a>
        <a href="Configuracion.php"><i class="fas fa-cogs"></i> Configuracion</a>
    </div>

    <div class="content" style="background-color: #f8f9fa; padding: 30px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: 20px; margin-left: 270px; margin-top: 120px;">
        <div class="card" style="width: 95%; margin: auto;">
            <div class="card-body">
                <h5 class="card-title">DashBoard</h5>
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="bg-primary text-white p-3 mb-3 rounded">
                            <h6>Incidentes</h6>
                            <h3>4</h3>
                            <p>Click para Detalles</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-primary text-white p-3 mb-3 rounded">
                            <h6>Impactos</h6>
                            <h3>2</h3>
                            <p>Click para Detalles</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-primary text-white p-3 mb-3 rounded">
                            <h6>Checklist Fallidos</h6>
                            <h3>0</h3>
                            <p>Click para Detalles</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-primary text-white p-3 mb-3 rounded">
                            <h6>Tamaño de la Flota</h6>
                            <h3>5 Vehículos / 17 Operadores</h3>
                            <p>Click para Detalles</p>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">Impactos</div>
                            <div class="card-body">
                                <canvas id="impactChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">Utilización</div>
                            <div class="card-body">
                                <canvas id="utilizationChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">Eficiencia</div>
                            <div class="card-body">
                                <canvas id="efficiencyChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">Uso y Eficiencia</div>
                            <div class="card-body">
                                <canvas id="usageEfficiencyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const usageCtx = document.getElementById('usageEfficiencyChart').getContext('2d');
        const usageEfficiencyChart = new Chart(usageCtx, {
            type: 'doughnut',
            data: {
                labels: ['Tiempo en Uso', 'Tiempo de Trabajo'],
                datasets: [{
                    data: [130.08, 40.50],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Uso y Eficiencia'
                    }
                }
            }
        });

        const impactCtx = document.getElementById('impactChart').getContext('2d');
        const impactChart = new Chart(impactCtx, {
            type: 'bar',
            data: {
                labels: ['19/11/2024', '21/11/2024', '23/11/2024', '25/11/2024'],
                datasets: [{
                    label: 'Impactos',
                    data: [1, 0, 0, 2],
                    backgroundColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Impactos Recientes'
                    }
                }
            }
        });

        const utilizationCtx = document.getElementById('utilizationChart').getContext('2d');
        const utilizationChart = new Chart(utilizationCtx, {
            type: 'bar',
            data: {
                labels: ['19/11/2024', '21/11/2024', '23/11/2024', '25/11/2024'],
                datasets: [{
                    label: 'Horas de Utilización',
                    data: [6, 12, 9, 18],
                    backgroundColor: '#17a2b8'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Utilización de Vehículos'
                    }
                }
            }
        });

        const efficiencyCtx = document.getElementById('efficiencyChart').getContext('2d');
        const efficiencyChart = new Chart(efficiencyCtx, {
            type: 'line',
            data: {
                labels: ['19/11', '21/11', '23/11', '25/11'],
                datasets: [
                    {
                        label: 'Eficiencia',
                        data: [20, 24, 18, 30],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        fill: true
                    },
                    {
                        label: 'Ineficiencia',
                        data: [5, 6, 8, 10],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Eficiencia de Vehículos'
                    }
                }
            }
        });
    </script>
</body>
</html>
