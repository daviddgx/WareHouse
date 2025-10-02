<?php

?>





            <!-- Sidebar navigation-->

            <div class="sidebar-mini-toggle">
                <button type="button" class="sidebar-toggle-btn" id="sidebar-mini-toggle">
                    <i data-feather="menu"></i>
                    <span>Menú</span>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul id="sidebarnav">
                    <li class="sidebar-item "><a class="sidebar-link sidebar-link" href="index.php" aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span class="hide-menu">Dashboard</span></a></li>
                    <li class="list-divider"></li>

                    <li class="nav-small-cap"><span class="hide-menu">Tareas</span></li>

                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Lista_DespachosGUIAS.php" aria-expanded="false"><i class="fas fa-dolly"></i><span class="hide-menu">Despachos
                  <?php echo $Num_Despachos; ?>
                </span></a>
                    </li>
                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Lista_AsignacionesIDH.php" aria-expanded="false"><i class="fas fa-warehouse"></i><span class="hide-menu">Ingresos
                  <?php echo $Num_Asignaciones; ?>
                </span></a>
                    </li>
                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Lista_Reubicaciones.php" aria-expanded="false"><i class=" fas fa-shipping-fast"></i><span class="hide-menu">Reubicaciones
                     <?php echo $Num_Reubicaciones; ?>
                            </span></a>
                    </li>
                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Lista_Piking.php" aria-expanded="false"><i class="fas fa-box-open"></i><span class="hide-menu">Piking
                     <?php echo $Num_Piking; ?>
                            </span></a>
                    </li>



                    <li class="list-divider"></li>
                    <li class="nav-small-cap"><span class="hide-menu">Consultas</span></li>

                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Historico_Despachos.php" aria-expanded="false"><i class="fas fa-truck-loading"></i><span class="hide-menu">Despachos<br> realizados
                </span></a>
                    </li>


                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Historico_Asignaciones.php" aria-expanded="false"><i class="fas fa-archive"></i><span class="hide-menu">Ingresos<br> Realizados
                </span></a>
                    </li>

                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Historico_Reubicaciones.php" aria-expanded="false"><i class="fas fa-book"></i><span class="hide-menu">Reubicaciones<br> Ralizadas
                </span></a>
                    </li>

                    <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="Historico_Piking.php" aria-expanded="false"><i class="fas fa-boxes"></i><span class="hide-menu">Piking<br> Abastecido
                </span></a>
                    </li>

                </ul>
            </nav>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('sidebar-mini-toggle');
        var mainWrapper = document.getElementById('main-wrapper');
        var leftSidebar = document.querySelector('.left-sidebar');

        if (!toggleBtn || !mainWrapper || !leftSidebar) {
            return;
        }

        function isMiniSidebar() {
            return mainWrapper.getAttribute('data-sidebartype') === 'mini-sidebar' || mainWrapper.classList.contains('mini-sidebar');
        }

        function openSidebar() {
            leftSidebar.classList.add('sidebar-touch-expanded');
            mainWrapper.classList.add('touch-sidebar-open');
        }

        function closeSidebar() {
            leftSidebar.classList.remove('sidebar-touch-expanded');
            mainWrapper.classList.remove('touch-sidebar-open');
        }

        function toggleSidebar() {
            if (!isMiniSidebar()) {
                closeSidebar();
                return;
            }

            if (leftSidebar.classList.contains('sidebar-touch-expanded')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        toggleBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleSidebar();
        });

        document.addEventListener('click', function (event) {
            if (!leftSidebar.contains(event.target) && mainWrapper.classList.contains('touch-sidebar-open')) {
                closeSidebar();
            }
        });

        window.addEventListener('resize', function () {
            if (!isMiniSidebar()) {
                closeSidebar();
            }
        });

        leftSidebar.addEventListener('click', function (event) {
            if (!isMiniSidebar() || !leftSidebar.classList.contains('sidebar-touch-expanded')) {
                return;
            }

            var link = event.target.closest('a');
            if (link && !link.classList.contains('has-arrow')) {
                closeSidebar();
            }
        });
    });
</script>
