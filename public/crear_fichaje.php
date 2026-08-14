<?php

require_once __DIR__ . "/../core/Auth.php";
require_once __DIR__ . "/../models/Fichaje.php";
require_once __DIR__ . "/../models/Usuario.php";
require_once __DIR__ . "/../models/Empresa.php";


Auth::verificarSesion();
Auth::esAdmin();


$fichajeModel = new Fichaje();
$usuarioModel = new Usuario();
$empresaModel = new Empresa();


$mensaje = "";
$error = "";


/* ==========================================================================
   EMPRESAS
========================================================================== */

$empresas = $empresaModel->obtenerEmpresas();


/* ==========================================================================
   EMPRESA SELECCIONADA
========================================================================== */

$empresaSeleccionada = $_GET['empresa_id'] ?? "";


/* ==========================================================================
   EMPLEADOS DE LA EMPRESA SELECCIONADA
========================================================================== */

$usuarios = [];


if($empresaSeleccionada != "") {

    $usuarios = $usuarioModel->filtrarUsuarios(
        "",
        $empresaSeleccionada
    );

}


/* ==========================================================================
   CREAR FICHAJE
========================================================================== */

if($_SERVER['REQUEST_METHOD'] == 'POST') {


    $empresa_id = $_POST['empresa_id'] ?? "";

    $usuario_id = $_POST['usuario_id'] ?? "";

    $fecha = $_POST['fecha'] ?? "";

    $horaEntrada = $_POST['hora_entrada'] ?? "";

    $inicioDescanso = $_POST['inicio_descanso'] ?? "";

    $finDescanso = $_POST['fin_descanso'] ?? "";

    $horaSalida = $_POST['hora_salida'] ?? "";


    /*
    ==========================================================================
       COMPROBAR QUE SE HA SELECCIONADO UNA EMPRESA
    ==========================================================================
    */

    if($empresa_id == "") {

        $error = "Debes seleccionar una empresa.";

    }


    /*
    ==========================================================================
       COMPROBAR QUE SE HA SELECCIONADO UN EMPLEADO
    ==========================================================================
    */

    elseif($usuario_id == "") {

        $error = "Debes seleccionar un empleado.";

    }


    /*
    ==========================================================================
       COMPROBAR QUE EL EMPLEADO PERTENECE A LA EMPRESA
    ==========================================================================
    */

    else {


        $empleadosEmpresa =
            $usuarioModel->filtrarUsuarios(
                "",
                $empresa_id
            );


        $empleadoValido = false;


        foreach($empleadosEmpresa as $empleado) {

            if((int)$empleado['id'] === (int)$usuario_id) {

                $empleadoValido = true;

                break;

            }

        }


        if(!$empleadoValido) {

            $error =
                "El empleado seleccionado no pertenece a la empresa elegida.";

        }

    }


    /*
    ==========================================================================
       COMPROBAR FECHA
    ==========================================================================
    */

    if(
        $error == "" &&
        $fecha == ""
    ) {

        $error = "Debes seleccionar una fecha.";

    }


    /*
    ==========================================================================
       COMPROBAR HORARIOS
    ==========================================================================
    */

    if(
        $error == "" &&
        (
            $horaEntrada == "" ||
            $inicioDescanso == "" ||
            $finDescanso == "" ||
            $horaSalida == ""
        )
    ) {

        $error =
            "Debes completar todos los horarios.";

    }


    /*
    ==========================================================================
       CREAR FICHAJE
    ==========================================================================
    */

    if($error == "") {


        $resultado =
            $fichajeModel->crearFichajeManual(

                $usuario_id,

                $fecha,

                $horaEntrada,

                $inicioDescanso,

                $finDescanso,

                $horaSalida

            );


        if($resultado) {

            $mensaje =
                "Fichaje creado correctamente.";


            /*
            ==============================================================
               LIMPIAR FORMULARIO
            ==============================================================
            */

            $empresaSeleccionada = "";

            $usuarios = [];


        } else {

            $error =
                "Error al crear el fichaje.";

        }

    }


    /*
    ==========================================================================
       SI HAY ERROR, MANTENER EMPRESA Y EMPLEADOS
    ==========================================================================
    */

    if($error != "") {

        $empresaSeleccionada = $empresa_id;


        if($empresaSeleccionada != "") {

            $usuarios =
                $usuarioModel->filtrarUsuarios(
                    "",
                    $empresaSeleccionada
                );

        }

    }

}


/* ==========================================================================
   HEADER Y SIDEBAR
========================================================================== */

include "../views/layouts/header.php";

include "../views/layouts/sidebar.php";

?>


<h1>Añadir fichaje</h1>


<?php if($mensaje != ""): ?>

    <div class="alert alert-success">

        <?php echo htmlspecialchars($mensaje); ?>

    </div>

<?php endif; ?>


<?php if($error != ""): ?>

    <div
        class="alert alert-danger"
        style="
            background:#f8d7da;
            color:#842029;
            padding:12px 15px;
            border-radius:6px;
            margin-bottom:20px;
        "
    >

        <?php echo htmlspecialchars($error); ?>

    </div>

<?php endif; ?>


<div class="fichaje-card">


    <form method="POST">


        <!-- ==============================================================
             EMPRESA
        ============================================================== -->

        <div class="form-group">

            <label>Empresa</label>


            <select
                name="empresa_id"
                class="form-control"
                required
                onchange="cambiarEmpresa(this.value)"
            >

                <option value="">
                    Seleccione una empresa
                </option>


                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa['id']; ?>"
                        <?php

                        if(
                            (string)$empresaSeleccionada ===
                            (string)$empresa['id']
                        ) {

                            echo "selected";

                        }

                        ?>
                    >

                        <?php
                        echo htmlspecialchars(
                            $empresa['nombre']
                        );
                        ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- ==============================================================
             EMPLEADO
        ============================================================== -->

        <div class="form-group">

            <label>Empleado</label>


            <select
                name="usuario_id"
                class="form-control"
                required
                <?php

                if($empresaSeleccionada == "") {

                    echo "disabled";

                }

                ?>
            >


                <?php if($empresaSeleccionada == ""): ?>

                    <option value="">
                        Primero seleccione una empresa
                    </option>


                <?php else: ?>


                    <option value="">
                        Seleccione un empleado
                    </option>


                    <?php foreach($usuarios as $usuario): ?>

                        <option
                            value="<?php echo $usuario['id']; ?>"
                            <?php

                            if(
                                isset($_POST['usuario_id']) &&
                                $_POST['usuario_id'] == $usuario['id']
                            ) {

                                echo "selected";

                            }

                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $usuario['nombre']
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>


                    <?php if(count($usuarios) == 0): ?>

                        <option value="">
                            No hay empleados en esta empresa
                        </option>

                    <?php endif; ?>


                <?php endif; ?>


            </select>

        </div>



        <!-- ==============================================================
             FECHA
        ============================================================== -->

        <div class="form-group">

            <label>Fecha</label>


            <input
                type="date"
                name="fecha"
                class="form-control"
                value="<?php

                    echo htmlspecialchars(
                        $_POST['fecha'] ?? ""
                    );

                ?>"
                required
            >

        </div>



        <!-- ==============================================================
             HORA ENTRADA
        ============================================================== -->

        <div class="form-group">

            <label>Hora entrada</label>


            <input
                type="time"
                name="hora_entrada"
                class="form-control"
                value="<?php

                    echo htmlspecialchars(
                        $_POST['hora_entrada'] ?? ""
                    );

                ?>"
                required
            >

        </div>



        <!-- ==============================================================
             INICIO DESCANSO
        ============================================================== -->

        <div class="form-group">

            <label>Inicio descanso</label>


            <input
                type="time"
                name="inicio_descanso"
                class="form-control"
                value="<?php

                    echo htmlspecialchars(
                        $_POST['inicio_descanso'] ?? ""
                    );

                ?>"
                required
            >

        </div>



        <!-- ==============================================================
             FIN DESCANSO
        ============================================================== -->

        <div class="form-group">

            <label>Fin descanso</label>


            <input
                type="time"
                name="fin_descanso"
                class="form-control"
                value="<?php

                    echo htmlspecialchars(
                        $_POST['fin_descanso'] ?? ""
                    );

                ?>"
                required
            >

        </div>



        <!-- ==============================================================
             HORA SALIDA
        ============================================================== -->

        <div class="form-group">

            <label>Hora salida</label>


            <input
                type="time"
                name="hora_salida"
                class="form-control"
                value="<?php

                    echo htmlspecialchars(
                        $_POST['hora_salida'] ?? ""
                    );

                ?>"
                required
            >

        </div>



        <!-- ==============================================================
             BOTÓN
        ============================================================== -->

        <button
            type="submit"
            class="btn-main-blue"
            <?php

            if($empresaSeleccionada == "") {

                echo "disabled";

            }

            ?>
        >

            Crear fichaje

        </button>


    </form>


</div>



<script>

function cambiarEmpresa(empresaId) {

    if(empresaId === "") {

        window.location.href = "crear_fichaje.php";

        return;

    }


    window.location.href =
        "crear_fichaje.php?empresa_id=" +
        encodeURIComponent(empresaId);

}

</script>


<?php

include "../views/layouts/footer.php";

?>