<?php

?>





            <!-- Sidebar navigation-->
            
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
