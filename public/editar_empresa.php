<?php

require_once "../core/Auth.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$empresaModel = new Empresa();

/* ==========================================================================
   COMPROBAR ID
========================================================================== */

if(!isset($_GET['id'])) {

    header("Location: empresas.php");
    exit;

}

$id = $_GET['id'];

/* ==========================================================================
   OBTENER EMPRESA
========================================================================== */

$empresa = $empresaModel->obtenerEmpresaPorId($id);

if(!$empresa) {

    header("Location: empresas.php");
    exit;

}

/* ==========================================================================
   GUARDAR CAMBIOS
========================================================================== */

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $empresaModel->editarEmpresa(

        $id,
        trim($_POST['nombre']),
        trim($_POST['cif']),
        trim($_POST['titular']),
        trim($_POST['direccion'])

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