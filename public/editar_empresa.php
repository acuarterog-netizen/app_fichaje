
<?php

require_once "../core/Auth.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$empresaModel = new Empresa();

/* ==========================================================================
   COMPROBAR ID
========================================================================== */

if(!isset($_GET['id'])){

    header("Location: empresas.php");
    exit;

}

$id = $_GET['id'];

/* ==========================================================================
   OBTENER EMPRESA
========================================================================== */

$empresa = $empresaModel->obtenerEmpresaPorId($id);

if(!$empresa){

    header("Location: empresas.php");
    exit;

}

/* ==========================================================================
   GUARDAR CAMBIOS
========================================================================== */

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $horas_jornada = (float) $_POST['horas_jornada'];

    if($horas_jornada > 8){

        die("La jornada laboral no puede superar las 8 horas.");

    }

    $empresaModel->editarEmpresa(

        $id,

        trim($_POST['nombre']),

        trim($_POST['cif']),

        trim($_POST['titular']),

        trim($_POST['direccion']),

        $_POST['hora_entrada'],

        $_POST['hora_salida'],

        (int) $_POST['descanso'],

        $horas_jornada

    );

    header("Location: empresas.php");
    exit;

}

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Editar empresa</h1>

<div class="fichaje-card">

    <form method="POST">

        <!-- NOMBRE -->

        <div class="form-group">

            <label>Nombre</label>

            <input
                type="text"
                name="nombre"
                class="form-control"
                value="<?php echo $empresa['nombre']; ?>"
                required
            >

        </div>

        <!-- CIF -->

        <div class="form-group">

            <label>CIF</label>

            <input
                type="text"
                name="cif"
                class="form-control"
                value="<?php echo $empresa['cif']; ?>"
                required
            >

        </div>

        <!-- TITULAR -->

        <div class="form-group">

            <label>Titular / Razón social</label>

            <input
                type="text"
                name="titular"
                class="form-control"
                value="<?php echo $empresa['titular']; ?>"
                required
            >

        </div>

        <!-- DIRECCIÓN -->

        <div class="form-group">

            <label>Dirección</label>

            <input
                type="text"
                name="direccion"
                class="form-control"
                value="<?php echo $empresa['direccion']; ?>"
                required
            >

        </div>

        <!-- HORA ENTRADA -->

        <div class="form-group">

            <label>Hora de entrada</label>

            <input
                type="time"
                name="hora_entrada"
                class="form-control"
                value="<?php echo substr($empresa['hora_entrada'],0,5); ?>"
                required
            >

        </div>

        <!-- HORA SALIDA -->

        <div class="form-group">

            <label>Hora de salida</label>

            <input
                type="time"
                name="hora_salida"
                class="form-control"
                value="<?php echo substr($empresa['hora_salida'],0,5); ?>"
                required
            >

        </div>

        <!-- DESCANSO -->

        <div class="form-group">

            <label>Descanso (minutos)</label>

            <input
                type="number"
                name="descanso"
                class="form-control"
                value="<?php echo $empresa['descanso']; ?>"
                min="0"
                required
            >

        </div>

        <!-- HORAS JORNADA -->

        <div class="form-group">

            <label>Horas de jornada</label>

            <input
                type="number"
                name="horas_jornada"
                class="form-control"
                value="<?php echo $empresa['horas_jornada']; ?>"
                min="0"
                max="8"
                step="0.5"
                required
            >

            <small>La jornada máxima permitida es de 8 horas.</small>

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