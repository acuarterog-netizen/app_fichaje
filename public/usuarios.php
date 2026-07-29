<?php

require_once "../core/Auth.php";
require_once "../models/Usuario.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$usuarioModel = new Usuario();
$empresaModel = new Empresa();

/* ==========================================================================
   EMPRESAS
========================================================================== */

$empresas = $empresaModel->obtenerEmpresas();

/* ==========================================================================
   FILTROS
========================================================================== */

$busqueda = $_GET['busqueda'] ?? "";
$empresa_id = $_GET['empresa_id'] ?? "";

/* ==========================================================================
   USUARIOS
========================================================================== */

$usuarios = $usuarioModel->filtrarUsuarios(
    $busqueda,
    $empresa_id
);

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Gestión de usuarios</h1>

<div
    style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    "
>

    <div>

        <strong>
            Total usuarios:
            <?php echo count($usuarios); ?>
        </strong>

    </div>

    <a
        class="btn-main-blue"
        href="crear_usuario.php"
    >
        Nuevo empleado
    </a>

</div>

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

            <label>Buscar</label>

            <input
                type="text"
                name="busqueda"
                class="form-control"
                placeholder="Nombre o email..."
                value="<?php echo $busqueda; ?>"
            >

        </div>

        <div class="form-group">

            <label>Empresa</label>

            <select
                name="empresa_id"
                class="form-control"
            >

                <option value="">
                    Todas
                </option>

                <option
                    value="sin_empresa"
                    <?php if($empresa_id=="sin_empresa") echo "selected"; ?>
                >
                    Sin empresa
                </option>

                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa['id']; ?>"
                        <?php if($empresa_id==$empresa['id']) echo "selected"; ?>
                    >

                        <?php echo $empresa['nombre']; ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <button
            class="btn-main-blue"
            type="submit"
        >
            Filtrar
        </button>

        <a
            href="usuarios.php"
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

<div class="fichaje-card">

    <table class="tabla-gestion">

        <tr>

            <th>ID</th>

            <th>Nombre</th>

            <th>Email</th>

            <th>Empresa</th>

            <th>Rol</th>

            <th>Acciones</th>

        </tr>

        <?php foreach($usuarios as $usuario): ?>

            <tr>

                <td>

                    <?php echo $usuario['id']; ?>

                </td>

                <td>

                    <?php echo $usuario['nombre']; ?>

                </td>

                <td>

                    <?php echo $usuario['email']; ?>

                </td>

                <td>

                    <?php
                    echo $usuario['empresa_nombre'] ?? "Sin empresa";
                    ?>

                </td>

                <td>

                    <span class="badge-rol">

                        <?php echo $usuario['rol']; ?>

                    </span>

                </td>

                <td>

                    <a
                        class="btn-edit"
                        href="editar_usuario.php?id=<?php echo $usuario['id']; ?>"
                    >
                        Editar
                    </a>

                    <a
                        class="btn-delete"
                        href="eliminar_usuario.php?id=<?php echo $usuario['id']; ?>"
                        onclick="return confirm('¿Eliminar usuario?')"
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