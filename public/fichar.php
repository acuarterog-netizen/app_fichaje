<?php

require_once "../core/Auth.php";
require_once "../models/Fichaje.php";
require_once "../models/Usuario.php";
require_once "../models/Empresa.php";


Auth::verificarSesion();


/*
==========================================================
COMPROBAR ROL
==========================================================
*/

$rol = $_SESSION["usuario"]["rol"];


if(
    $rol != "admin" &&
    $rol != "encargado"
){

    header("Location: dashboard.php");
    exit;

}


$fichajeModel = new Fichaje();
$usuarioModel = new Usuario();
$empresaModel = new Empresa();


$mensaje = "";


/*
==========================================================
FILTROS
==========================================================
*/

$busqueda = $_GET['busqueda'] ?? "";


/*
==========================================================
EMPRESA
==========================================================
*/

/*
    ADMIN Y ENCARGADO PUEDEN ELEGIR
    CUALQUIER EMPRESA
*/

$empresaFiltro = $_GET['empresa'] ?? "";

$empresas = $empresaModel->obtenerEmpresas();


/*
==========================================================
EMPLEADOS
==========================================================
*/

$usuarios = $usuarioModel->filtrarUsuarios(
    $busqueda,
    $empresaFiltro
);


/*
==========================================================
FICHAR SELECCIONADOS
==========================================================
*/

if(isset($_POST['fichar_seleccionados'])){


    if(
        isset($_POST['usuarios']) &&
        count($_POST['usuarios']) > 0
    ){

        $usuariosSeleccionados = $_POST['usuarios'];


        /*
        ==================================================
        CREAR FICHAJES
        ==================================================
        */

        $resultado =
            $fichajeModel->crearFichajesMasivos(
                $usuariosSeleccionados
            );


        $mensaje =
            $resultado .
            " empleado(s) fichado(s) correctamente.";


    }else{

        $mensaje =
            "No has seleccionado ningún empleado.";

    }


    /*
    ==================================================
    RECARGAR EMPLEADOS DESPUÉS DEL FICHAJE
    ==================================================
    */

    $usuarios = $usuarioModel->filtrarUsuarios(
        $busqueda,
        $empresaFiltro
    );

}


include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>


<h1>Fichaje de empleados</h1>


<?php if($mensaje != ""): ?>

    <div class="alert alert-success">

        <?php echo $mensaje; ?>

    </div>

<?php endif; ?>


<div class="fichaje-card">


    <form
        method="GET"
        style="
            display:flex;
            gap:20px;
            flex-wrap:wrap;
            align-items:end;
        "
    >


        <!-- BUSCAR EMPLEADO -->

        <div class="form-group">

            <label>Buscar empleado</label>

            <input
                type="text"
                name="busqueda"
                class="form-control"
                placeholder="Nombre o email"
                value="<?php echo htmlspecialchars($busqueda); ?>"
            >

        </div>


        <!-- EMPRESA -->

        <div class="form-group">

            <label>Empresa</label>

            <select
                name="empresa"
                class="form-control select-buscador"
            >

                <option value="">Todas</option>


                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa['id']; ?>"
                        <?php

                        if(
                            $empresaFiltro ==
                            $empresa['id']
                        ){

                            echo "selected";

                        }

                        ?>
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
            href="fichar.php"
            class="btn-delete"
        >

            Limpiar

        </a>


    </form>


</div>


<br>


<form method="POST">


    <div class="fichaje-card">


        <table class="tabla-gestion">


            <tr>

                <th>

                    <input
                        type="checkbox"
                        id="seleccionarTodos"
                    >

                </th>

                <th>ID</th>

                <th>Empleado</th>

                <th>Empresa</th>

                <th>Email</th>

                <th>Estado</th>

            </tr>


            <?php foreach($usuarios as $usuario): ?>


                <?php

                $fichado =
                    $fichajeModel->yaFichoHoy(
                        $usuario['id']
                    );

                ?>


                <tr>


                    <td>

                        <?php if(!$fichado): ?>

                            <input
                                type="checkbox"
                                class="checkEmpleado"
                                name="usuarios[]"
                                value="<?php echo $usuario['id']; ?>"
                            >

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php echo $usuario['id']; ?>

                    </td>


                    <td>

                        <?php echo $usuario['nombre']; ?>

                    </td>


                    <td>

                        <?php echo $usuario['empresa_nombre']; ?>

                    </td>


                    <td>

                        <?php echo $usuario['email']; ?>

                    </td>


                    <td>

                        <?php

                        if($fichado){

                            echo
                                "<span class='badge-rol'>Fichado</span>";

                        }else{

                            echo
                                "<span
                                    class='badge-rol'
                                    style='background:#dc3545;'
                                >
                                    Pendiente
                                </span>";

                        }

                        ?>

                    </td>


                </tr>


            <?php endforeach; ?>


        </table>


        <br>


        <button
            class="btn-main-blue"
            type="submit"
            name="fichar_seleccionados"
        >

            Fichar seleccionados

        </button>


    </div>


</form>


<br>


<script>

document
.getElementById("seleccionarTodos")
.addEventListener(
    "change",
    function(event){

        document
        .querySelectorAll(".checkEmpleado")
        .forEach(function(check){

            check.checked =
                event.target.checked;

        });

    }
);

</script>


<?php

include "../views/layouts/footer.php";

?>