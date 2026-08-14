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

    $nombre = trim($_POST['nombre']);
    $cif = trim($_POST['cif']);
    $titular = trim($_POST['titular']);
    $direccion = trim($_POST['direccion']);

    $hora_entrada = $_POST['hora_entrada'];
    $hora_salida = $_POST['hora_salida'];

    $descanso = (int) $_POST['descanso'];

    $horas_jornada = (float) $_POST['horas_jornada'];


    /*
    ==========================================================================
    VALIDAR JORNADA MÁXIMA
    ==========================================================================
    */

    if($horas_jornada > 8) {

        die("La jornada laboral no puede superar las 8 horas.");

    }


    /*
    ==========================================================================
    CREAR EMPRESA
    ==========================================================================
    */

    $empresaModel->crearEmpresa(

        $nombre,

        $cif,

        $titular,

        $direccion,

        $hora_entrada,

        $hora_salida,

        $descanso,

        $horas_jornada

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


        <!-- ================================================================
             NOMBRE
        ================================================================= -->

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


        <!-- ================================================================
             CIF
        ================================================================= -->

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


        <!-- ================================================================
             TITULAR
        ================================================================= -->

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


        <!-- ================================================================
             DIRECCIÓN
        ================================================================= -->

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


        <!-- ================================================================
             HORARIO DE ENTRADA
        ================================================================= -->

        <div class="form-group">

            <label>Hora de entrada</label>

            <input
                type="time"
                name="hora_entrada"
                class="form-control"
                value="08:00"
                required
            >

        </div>


        <!-- ================================================================
             HORARIO DE SALIDA
        ================================================================= -->

        <div class="form-group">

            <label>Hora de salida</label>

            <input
                type="time"
                name="hora_salida"
                class="form-control"
                value="17:00"
                required
            >

        </div>


        <!-- ================================================================
             DESCANSO
        ================================================================= -->

        <div class="form-group">

            <label>Descanso (minutos)</label>

            <input
                type="number"
                name="descanso"
                class="form-control"
                value="60"
                min="0"
                required
            >

        </div>


        <!-- ================================================================
             HORAS DE JORNADA
        ================================================================= -->

        <div class="form-group">

            <label>Horas de jornada</label>

            <input
                type="number"
                name="horas_jornada"
                class="form-control"
                value="8"
                min="0"
                max="8"
                step="0.5"
                required
            >

            <small>
                La jornada máxima permitida es de 8 horas.
            </small>

        </div>


        <!-- ================================================================
             BOTÓN
        ================================================================= -->

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