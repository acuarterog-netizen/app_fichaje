<?php

require_once "../core/Auth.php";
require_once "../models/Fichaje.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();

Auth::esEncargadoOAdmin();

$fichajeModel = new Fichaje();

$empresaModel = new Empresa();

$empresas = $empresaModel->obtenerEmpresas();

/* ==========================================================================
   FILTROS
========================================================================== */

$busqueda = $_GET['busqueda'] ?? "";

$fecha = $_GET['fecha'] ?? "";

$mes = $_GET['mes'] ?? "";

$empresa_id = $_GET['empresa_id'] ?? "";

/* ==========================================================================
   OBTENER FICHAJES FILTRADOS
========================================================================== */

$fichajes = $fichajeModel->filtrarFichajes(

    $busqueda,
    $fecha,
    $mes,
    $empresa_id

);

include "../views/layouts/header.php";

include "../views/layouts/sidebar.php";

?>

<h1>Historial completo</h1>

<!-- BOTONES -->

<div
    style="
        display:flex;
        gap:15px;
        flex-wrap:wrap;
        margin-bottom:20px;
    "
>

    <!-- EXPORTAR PDF -->

    <a
        href="exportar_pdf.php?busqueda=<?php echo $busqueda; ?>&fecha=<?php echo $fecha; ?>&mes=<?php echo $mes; ?>&empresa_id=<?php echo $empresa_id; ?>"
        class="btn-main-blue"
    >
        Exportar PDF
    </a>

    <!-- SOLO ADMIN -->

    <?php if($_SESSION['usuario']['rol'] == 'admin'): ?>

        <a
            href="crear_fichaje.php"
            class="btn-main-blue"
        >
            Añadir fichaje
        </a>

    <?php endif; ?>

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

        <!-- BUSCADOR -->

        <div class="form-group">

            <label>Buscar empleado</label>

            <input
                type="text"
                name="busqueda"
                class="form-control"
                placeholder="Nombre..."
                value="<?php echo $busqueda; ?>"
            >

        </div>

        <!-- FECHA -->

        <div class="form-group">

            <label>Fecha</label>

            <input
                type="date"
                name="fecha"
                class="form-control"
                value="<?php echo $fecha; ?>"
            >

        </div>

        <!-- MES -->

        <div class="form-group">

            <label>Mes</label>

            <input
                type="month"
                name="mes"
                class="form-control"
                value="<?php echo $mes; ?>"
            >

        </div>

        <!-- EMPRESA -->

        <div class="form-group">

            <label>Empresa</label>

            <select
                name="empresa_id"
                class="form-control"
            >

                <option value="">
                    Todas
                </option>

                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa['id']; ?>"

                        <?php
                        if($empresa_id == $empresa['id']) {
                            echo 'selected';
                        }
                        ?>
                    >

                        <?php echo $empresa['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- BOTÓN -->

        <button
            class="btn-main-blue"
            type="submit"
        >
            Filtrar
        </button>

        <!-- LIMPIAR -->

        <a
            href="historial.php"
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
            <th>Empleado</th>
            <th>Empresa</th>
            <th>Fecha</th>
            <th>Entrada</th>
            <th>Descanso</th>
            <th>Salida</th>

            <?php if($_SESSION['usuario']['rol'] == 'admin'): ?>

                <th>Acciones</th>

            <?php endif; ?>
        </tr>
        <?php foreach($fichajes as $fichaje): ?>
            <tr>
                <td>
                    <?php echo $fichaje['nombre']; ?>
                </td>

                <td>
                    <?php
                    echo $fichaje['empresa_nombre']
                    ?? 'Sin empresa';
                    ?>
                </td>
                <td>
                    <?php echo $fichaje['fecha']; ?>
                </td>
                <td>
                    <?php echo substr($fichaje['hora_entrada'], 0, 5); ?>
                </td>
                <td>

                    <?php
                    echo substr(
                        $fichaje['inicio_descanso'],
                        0,
                        5
                    );
                    ?>

                    -

                    <?php
                    echo substr(
                        $fichaje['fin_descanso'],
                        0,
                        5
                    );
                    ?>
                </td>
                <td>
                    <?php echo substr($fichaje['hora_salida'], 0, 5); ?>
                </td>

                <!-- SOLO ADMIN -->

                <?php if($_SESSION['usuario']['rol'] == 'admin'): ?>
                    <td>
                        <a
                            class="btn-edit"
                            href="editar_fichaje.php?id=<?php echo $fichaje['id']; ?>"
                        >
                            Editar
                        </a>

                        <a
                            class="btn-delete"
                            href="eliminar_fichaje.php?id=<?php echo $fichaje['id']; ?>"
                            onclick="return confirm('¿Eliminar este fichaje?')"
                        >
                            Eliminar
                        </a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php
include "../views/layouts/footer.php";
?>