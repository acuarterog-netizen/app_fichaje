<?php

require_once "../core/Auth.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$empresaModel = new Empresa();

/* ==========================================================================
   CREAR EMPRESA
========================================================================== */

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $empresaModel->crearEmpresa(

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

<h1>Nueva empresa</h1>

<div class="fichaje-card">

    <form method="POST">

        <!-- NOMBRE -->

        <div class="form-group">

            <label>Nombre</label>

            <input
                type="text"
                name="nombre"
                class="form-control"
                placeholder="Introduce el nombre de la empresa"
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
                placeholder="Introduce el CIF"
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
                placeholder="Introduce el titular"
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
                placeholder="Introduce la dirección"
                required
            >

        </div>

        <button
            class="btn-main-blue"
            type="submit"
        >
            Crear empresa
        </button>

    </form>

</div>

<?php
include "../views/layouts/footer.php";
?>