<?php

ob_clean();

header('Content-Type: application/json; charset=utf-8');

require_once "../models/Vacaciones.php";


if(!isset($_GET["fecha"])){

    echo json_encode([]);

    exit;
}


try {

    $modelo = new Vacaciones();

    $festivos =
        $modelo->obtenerFestivosPorFecha(
            $_GET["fecha"]
        );


    echo json_encode(
        $festivos,
        JSON_UNESCAPED_UNICODE
    );


} catch(Exception $e){

    http_response_code(500);

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al obtener los festivos."
    ]);

}


exit;