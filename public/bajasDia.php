<?php

ob_clean();

header('Content-Type: application/json; charset=utf-8');

require_once "../models/Bajas.php";

if (!isset($_GET["fecha"])) {

    echo json_encode([]);
    exit;

}

$modelo = new Bajas();

echo json_encode(

    $modelo->obtenerBajasPorFecha($_GET["fecha"])

);

exit;