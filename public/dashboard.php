<?php

require_once "../core/Auth.php";
require_once "../models/Usuario.php";
require_once "../models/Fichaje.php";

Auth::verificarSesion();

$usuarioModel = new Usuario();
$fichajeModel = new Fichaje();

$totalUsuarios = $usuarioModel->contarUsuarios();

$fichajesHoy = $fichajeModel->contarFichajesHoy();

$ultimosFichajes = $fichajeModel->ultimosFichajes();

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Dashboard</h1>

<div class="dashboard-cards">

    <div class="card-dashboard">

        <h3>Empleados</h3>

        <p>
            <?php echo $totalUsuarios['total']; ?>
        </p>

    </div>

    <div class="card-dashboard">

        <h3>Fichajes hoy</h3>

        <p>
            <?php echo $fichajesHoy['total']; ?>
        </p>

    </div>

    <div class="card-dashboard">

        <h3>Rol</h3>

        <p>
            <?php echo $_SESSION['usuario']['rol']; ?>
        </p>

    </div>

</div>

<br>

<div class="fichaje-card">

    <h2>Últimos fichajes</h2>

    <table class="tabla-gestion">

        <tr>

            <th>Empleado</th>
            <th>Fecha</th>
            <th>Entrada</th>
            <th>Salida</th>

        </tr>

        <?php foreach($ultimosFichajes as $fichaje): ?>

            <tr>

                <td>
                    <?php echo $fichaje['nombre']; ?>
                </td>

                <td>
                    <?php echo $fichaje['fecha']; ?>
                </td>

                <td>
                    <?php echo $fichaje['hora_entrada']; ?>
                </td>

                <td>
                    <?php echo $fichaje['hora_salida']; ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php

include "../views/layouts/footer.php";

?>