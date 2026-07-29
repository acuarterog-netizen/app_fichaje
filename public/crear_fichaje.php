<?php
require_once __DIR__ . "/../core/Auth.php";
require_once __DIR__ . "/../models/Fichaje.php";
require_once __DIR__ . "/../models/Usuario.php";

Auth::verificarSesion();
Auth::esAdmin();

$fichajeModel = new Fichaje();
$usuarioModel = new Usuario();

$usuarios = $usuarioModel->obtenerUsuarios();

$mensaje = "";

/* ==========================================================================
   CREAR FICHAJE
========================================================================== */

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $usuario_id = $_POST['usuario_id'];
    $fecha = $_POST['fecha'];

    $horaEntrada = $_POST['hora_entrada'];
    $inicioDescanso = $_POST['inicio_descanso'];
    $finDescanso = $_POST['fin_descanso'];
    $horaSalida = $_POST['hora_salida'];

    $resultado = $fichajeModel->crearFichajeManual(

        $usuario_id,
        $fecha,
        $horaEntrada,
        $inicioDescanso,
        $finDescanso,
        $horaSalida

    );

    if($resultado) {

        $mensaje = "Fichaje creado correctamente";

    } else {

        $mensaje = "Error al crear el fichaje";

    }

}

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Añadir fichaje</h1>

<?php if($mensaje != ""): ?>

    <div class="alert alert-success">

        <?php echo $mensaje; ?>

    </div>

<?php endif; ?>

<div class="fichaje-card">

    <form method="POST">

        <div class="form-group">

            <label>Empleado</label>

            <select
                name="usuario_id"
                class="form-control select-buscador"
                required
            >

                <?php foreach($usuarios as $usuario): ?>

                    <option value="<?php echo $usuario['id']; ?>">

                        <?php echo $usuario['nombre']; ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label>Fecha</label>

            <input
                type="date"
                name="fecha"
                class="form-control"
                required
            >

        </div>

        <div class="form-group">

            <label>Hora entrada</label>

            <input
                type="time"
                name="hora_entrada"
                class="form-control"
                required
            >

        </div>

        <div class="form-group">

            <label>Inicio descanso</label>

            <input
                type="time"
                name="inicio_descanso"
                class="form-control"
                required
            >

        </div>

        <div class="form-group">

            <label>Fin descanso</label>

            <input
                type="time"
                name="fin_descanso"
                class="form-control"
                required
            >

        </div>

        <div class="form-group">

            <label>Hora salida</label>

            <input
                type="time"
                name="hora_salida"
                class="form-control"
                required
            >

        </div>

        <button
            type="submit"
            class="btn-main-blue"
        >
            Crear fichaje
        </button>

    </form>

</div>

<?php

include "../views/layouts/footer.php";

?>