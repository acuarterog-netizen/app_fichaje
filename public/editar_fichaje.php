<?php

require_once "../core/Auth.php";
require_once "../models/Fichaje.php";

Auth::verificarSesion();
Auth::esAdmin();

$fichajeModel = new Fichaje();

$id = $_GET['id'];

$fichaje = $fichajeModel->obtenerFichajePorId($id);

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fichajeModel->editarFichaje(

        $id,

        $_POST['hora_entrada'],
        $_POST['inicio_descanso'],
        $_POST['fin_descanso'],
        $_POST['hora_salida']

    );

    header("Location: historial.php");

    exit;
}

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Editar fichaje</h1>

<div class="fichaje-card">

    <form method="POST">

        <div class="form-group">

            <label>Hora entrada</label>

            <input
                type="time"
                name="hora_entrada"
                class="form-control"
                value="<?php echo substr($fichaje['hora_entrada'], 0, 5); ?>"
                required
            >

        </div>

        <div class="form-group">

            <label>Inicio descanso</label>

            <input
                type="time"
                name="inicio_descanso"
                class="form-control"
                value="<?php echo substr($fichaje['inicio_descanso'], 0, 5); ?>"
                required
            >

        </div>

        <div class="form-group">

            <label>Fin descanso</label>

            <input
                type="time"
                name="fin_descanso"
                class="form-control"
                value="<?php echo substr($fichaje['fin_descanso'], 0, 5); ?>"
                required
            >

        </div>

        <div class="form-group">

            <label>Hora salida</label>

            <input
                type="time"
                name="hora_salida"
                class="form-control"
                value="<?php echo substr($fichaje['hora_salida'], 0, 5); ?>"
                required
            >

        </div>

        <button
            class="btn-main-blue"
            type="submit"
        >
            Guardar cambios
        </button>

    </form>

</div>

<?php

include "../views/layouts/footer.php";

?>