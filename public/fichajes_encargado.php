<?php

require_once "../core/Auth.php";
require_once "../models/Usuario.php";
require_once "../models/Fichaje.php";

Auth::verificarSesion();

if($_SESSION["usuario"]["rol"] != "encargado"){

    header("Location: dashboard.php");
    exit;

}

$idEmpresa = $_SESSION["usuario"]["empresa_id"];

$usuarioModel = new Usuario();
$fichajeModel = new Fichaje();

/*
==========================================
OBTENER SOLO LOS EMPLEADOS DE SU EMPRESA
==========================================
*/

$usuarios = $usuarioModel->obtenerEmpleadosEmpresa($idEmpresa);

include "../views/layouts/header_encargado.php";
include "../views/layouts/sidebar.php";
?>

<div class="cabecera-encargado">

    <h2>Control horario</h2>

    <input
        type="text"
        id="buscarEmpleado"
        class="form-control"
        placeholder="Buscar empleado...">

</div>

<div class="empleados-grid" id="listaEmpleados">

<?php foreach($usuarios as $u): ?>

    <div
        class="empleado-card"
        data-nombre="<?php echo strtolower($u["nombre"]); ?>">

        <div class="nombre-empleado">

            <?php echo $u["nombre"]; ?>

        </div>

        <div class="botones-empleado">

            <button
                class="btn-main-blue btn-grande">

                Entrada

            </button>

            <button
                class="btn-delete btn-grande">

                Salida

            </button>

            <button
                class="btn-main-blue btn-grande">

                Historial

            </button>

        </div>

    </div>

<?php endforeach; ?>

</div>

<script>

document.getElementById("buscarEmpleado").addEventListener("keyup",function(){

    let texto = this.value.toLowerCase();

    document.querySelectorAll(".empleado-card").forEach(function(card){

        let nombre = card.dataset.nombre;

        if(nombre.includes(texto)){

            card.style.display = "";

        }else{

            card.style.display = "none";

        }

    });

});

</script>

<?php
include "../views/layouts/footer.php";
?>