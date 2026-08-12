<?php

$pagina = basename($_SERVER['PHP_SELF']);

?>

<div class="sidebar">

    <h2>Control Horario</h2>

    <nav>

        <?php if($_SESSION['usuario']['rol']=="admin"): ?>

            <!-- DASHBOARD -->

            <a
                href="dashboard.php"
                class="<?php if($pagina=="dashboard.php") echo "active"; ?>"
            >
                📊 Dashboard
            </a>


            <!-- FICHAR -->

            <a
                href="fichar.php"
                class="<?php if($pagina=="fichar.php") echo "active"; ?>"
            >
                ⏱️ Fichar
            </a>


            <!-- HISTORIAL -->

            <a
                href="historial.php"
                class="<?php if($pagina=="historial.php") echo "active"; ?>"
            >
                📅 Historial
            </a>


            <!-- VACACIONES -->

            <a
                href="vacaciones.php"
                class="<?php if($pagina=="vacaciones.php") echo "active"; ?>"
            >
                🌴 Vacaciones
            </a>


            <!-- BAJAS -->

            <a
                href="bajas.php"
                class="<?php if($pagina=="bajas.php") echo "active"; ?>"
            >
                🤒 Bajas
            </a>


            <!-- EMPRESAS -->

            <a
                href="empresas.php"
                class="<?php if($pagina=="empresas.php") echo "active"; ?>"
            >
                🏢 Empresas
            </a>


            <!-- USUARIOS -->

            <a
                href="usuarios.php"
                class="<?php if($pagina=="usuarios.php") echo "active"; ?>"
            >
                👥 Usuarios
            </a>


            <!-- PERFIL -->

            <a
                href="perfil.php"
                class="<?php if($pagina=="perfil.php") echo "active"; ?>"
            >
                👤 Mi perfil
            </a>


            <!-- CERRAR SESIÓN -->

            <a href="logout.php">
                🚪 Cerrar sesión
            </a>

        <?php endif; ?>


        <?php if($_SESSION['usuario']['rol']=="encargado"): ?>

            <!-- FICHAR -->

            <a
                href="fichar.php"
                class="<?php if($pagina=="fichar.php") echo "active"; ?>"
            >
                ⏱️ Fichar
            </a>


            <!-- HISTORIAL -->

            <a
                href="historial.php"
                class="<?php if($pagina=="historial.php") echo "active"; ?>"
            >
                📅 Historial
            </a>

        <?php endif; ?>

    </nav>


    <!-- DARK MODE -->

    <button
        id="toggle-darkmode"
        class="btn-main-blue"
        style="
            margin-top:25px;
            width:100%;
        "
    >
        🌙 Modo oscuro
    </button>

</div>


<div class="content-wrapper">

    <div class="top-header">

        Bienvenido,

        <strong>
            <?php echo $_SESSION['usuario']['nombre']; ?>
        </strong>

    </div>

    <div class="main-content">