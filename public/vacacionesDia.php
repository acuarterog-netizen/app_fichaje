<?php

ob_clean();

header('Content-Type: application/json; charset=utf-8');

require_once "../models/Vacaciones.php";

if (!isset($_GET["fecha"])) {
    echo json_encode([]);
    exit;
}

$modelo = new Vacaciones();

echo json_encode(
    $modelo->obtenerVacacionesPorFecha($_GET["fecha"])
);

exit;