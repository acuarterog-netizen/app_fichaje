<?php

require_once "../core/Auth.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$empresaModel = new Empresa();

/* ==========================================================================
   ELIMINAR EMPRESA
========================================================================== */

if(isset($_GET['eliminar'])) {

    $empresaModel->eliminarEmpresa($_GET['eliminar']);

    header("Location: empresas.php");

    exit;

}

/* ==========================================================================
   FILTROS
========================================================================== */

$busqueda = $_GET['busqueda'] ?? "";

/* ==========================================================================
   OBTENER EMPRESAS
========================================================================== */

$empresas = $empresaModel->filtrarEmpresas($busqueda);

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Gestión de empresas</h1>

<div class="acciones-header">

    <a
        class="btn-main-blue"
        href="crear_empresa.php"
    >
        Nueva empresa
    </a>

</div>

<!-- FILTROS -->

<div class="fichaje-card">

    <form
        method="GET"
        style="
            display:flex;
            gap:15px;
            flex-wrap:wrap;
            align-items:end;
        "
    >

        <div class="form-group">

            <label>Buscar empresa</label>

            <input
                type="text"
                name="busqueda"
                class="form-control"
                placeholder="Nombre, CIF, titular o dirección..."
                value="<?php echo $busqueda; ?>"
            >

        </div>

        <button
            class="btn-main-blue"
            type="submit"
        >
            Filtrar
        </button>

        <a
            href="empresas.php"
            class="btn-delete"
            style="
                align-self:center;
                margin-bottom:10px;
            "
        >
            Limpiar
        </a>

    </form>

</div>

<br>

<!-- TABLA -->

<div class="fichaje-card">

    <table class="tabla-gestion">

        <tr>

            <th>ID</th>

            <th>Empresa</th>

            <th>CIF</th>

            <th>Titular</th>

            <th>Dirección</th>

            <th>Acciones</th>

        </tr>

        <?php foreach($empresas as $empresa): ?>

            <tr>

                <td>

                    <?php echo $empresa['id']; ?>

                </td>

                <td>

                    <?php echo $empresa['nombre']; ?>

                </td>

                <td>

                    <?php echo $empresa['cif']; ?>

                </td>

                <td>

                    <?php echo $empresa['titular']; ?>

                </td>

                <td>

                    <?php echo $empresa['direccion']; ?>

                </td>

                <td>

                    <a
                        class="btn-edit"
                        href="editar_empresa.php?id=<?php echo $empresa['id']; ?>"
                    >
                        Editar
                    </a>

                    <a
                        class="btn-delete"
                        href="empresas.php?eliminar=<?php echo $empresa['id']; ?>"
                        onclick="return confirm('¿Eliminar esta empresa?')"
                    >
                        Eliminar
                    </a>

                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php

include "../views/layouts/footer.php";

?>