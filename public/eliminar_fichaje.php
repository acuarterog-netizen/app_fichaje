<?php

require_once "../core/Auth.php";
require_once "../models/Fichaje.php";

Auth::verificarSesion();
Auth::esAdmin();

if(!isset($_GET['id'])) {

    header("Location: historial.php");
    exit;

}

$id = $_GET['id'];

$fichajeModel = new Fichaje();

/* ==========================================================================
   ELIMINAR
========================================================================== */

$fichajeModel->eliminarFichaje($id);

/* ==========================================================================
   REDIRECT
========================================================================== */

header("Location: historial.php");
exit;