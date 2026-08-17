<?php

require_once "../vendor/autoload.php";
require_once "../core/Auth.php";
require_once "../models/Fichaje.php";
require_once "../models/Vacaciones.php";
require_once "../models/Bajas.php";

use Dompdf\Dompdf;

Auth::verificarSesion();
Auth::esEncargadoOAdmin();


$fichajeModel = new Fichaje();
$vacacionesModel = new Vacaciones();
$bajasModel = new Bajas();


/* ==========================================================================
   FILTROS
========================================================================== */

$busqueda = $_GET['busqueda'] ?? "";
$fecha = $_GET['fecha'] ?? "";
$mes = $_GET['mes'] ?? "";
$empresa_id = $_GET['empresa_id'] ?? "";


/* ==========================================================================
   FICHAJES
========================================================================== */

$fichajes =
    $fichajeModel->filtrarFichajes(
        $busqueda,
        $fecha,
        $mes,
        $empresa_id
    );


/* ==========================================================================
   DETECTAR ÚNICO EMPLEADO
========================================================================== */

$empleadosEncontrados = [];

foreach($fichajes as $fichaje){

    if(
        isset($fichaje['usuario_id']) &&
        !in_array(
            $fichaje['usuario_id'],
            $empleadosEncontrados
        )
    ){

        $empleadosEncontrados[] =
            $fichaje['usuario_id'];
    }

}


$mostrarResumenHoras =
    $busqueda != "" &&
    count($empleadosEncontrados) === 1;


$resumenHoras = null;
$empleadoResumen = "";

$vacacionesEmpleado = [];
$bajasEmpleado = [];


if($mostrarResumenHoras){

    $usuarioId =
        $empleadosEncontrados[0];


    $mesResumen =
        $mes != ""
            ? $mes
            : date("Y-m");


    /*
    --------------------------------------------------------------------------
    HORAS
    --------------------------------------------------------------------------
    */

    $resumenHoras =
        $fichajeModel->obtenerResumenHorasEmpleado(
            $usuarioId,
            $mesResumen
        );


    /*
    --------------------------------------------------------------------------
    NOMBRE
    --------------------------------------------------------------------------
    */

    foreach($fichajes as $fichaje){

        if(
            $fichaje['usuario_id'] ==
            $usuarioId
        ){

            $empleadoResumen =
                $fichaje['nombre'];

            break;
        }

    }


    /*
    --------------------------------------------------------------------------
    VACACIONES
    --------------------------------------------------------------------------
    */

    $vacacionesEmpleado =
        $vacacionesModel->obtenerVacacionesEmpleado(
            $usuarioId,
            $mes
        );


    /*
    --------------------------------------------------------------------------
    BAJAS
    --------------------------------------------------------------------------
    */

    $bajasEmpleado =
        $bajasModel->obtenerBajasEmpleado(
            $usuarioId,
            $mes
        );

}


/* ==========================================================================
   FUNCIONES
========================================================================== */

function segundosAHorasPDF($segundos){

    $segundos =
        max(0, (int)$segundos);

    $horas =
        floor($segundos / 3600);

    $minutos =
        floor(
            ($segundos % 3600) / 60
        );

    return
        $horas . " h " .
        str_pad(
            $minutos,
            2,
            "0",
            STR_PAD_LEFT
        ) .
        " min";
}


function calcularHorasFichajePDF($fichaje){

    if(
        empty($fichaje['hora_entrada']) ||
        empty($fichaje['hora_salida'])
    ){

        return 0;
    }


    $entrada =
        strtotime(
            $fichaje['fecha'] .
            " " .
            $fichaje['hora_entrada']
        );


    $salida =
        strtotime(
            $fichaje['fecha'] .
            " " .
            $fichaje['hora_salida']
        );


    $segundos =
        $salida - $entrada;


    if(
        !empty($fichaje['inicio_descanso']) &&
        !empty($fichaje['fin_descanso'])
    ){

        $inicioDescanso =
            strtotime(
                $fichaje['fecha'] .
                " " .
                $fichaje['inicio_descanso']
            );


        $finDescanso =
            strtotime(
                $fichaje['fecha'] .
                " " .
                $fichaje['fin_descanso']
            );


        $segundos -=
            ($finDescanso - $inicioDescanso);
    }


    return max(0, $segundos);
}


function escaparPDF($texto){

    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* ==========================================================================
   HTML
========================================================================== */

$html = '

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<style>

body {

    font-family:
        "DejaVu Sans",
        Arial,
        sans-serif;

    padding:20px;

    color:#222;

}

h1 {

    text-align:center;

    margin-bottom:20px;

}

h2 {

    margin-top:20px;

    margin-bottom:12px;

}

h3 {

    margin-top:0;

}

.filtros {

    margin-bottom:20px;

    font-size:12px;

}

.resumen {

    width:100%;

    margin-bottom:20px;

    border-collapse:collapse;

}

.resumen td {

    width:50%;

    border:1px solid #ddd;

    padding:15px;

    text-align:center;

}

.resumen-titulo {

    font-size:11px;

    color:#666;

    margin-bottom:6px;

}

.resumen-valor {

    font-size:22px;

    font-weight:bold;

    color:#2563eb;

}

.seccion {

    margin-bottom:20px;

}

.evento {

    border:1px solid #ddd;

    padding:8px;

    margin-bottom:6px;

    font-size:10px;

}

.evento-titulo {

    font-weight:bold;

    font-size:11px;

}

.evento-dias {

    color:#666;

    margin-top:3px;

}

table.registro {

    width:100%;

    border-collapse:collapse;

    table-layout:fixed;

}

table.registro th,
table.registro td {

    padding:8px 6px;

    border-bottom:1px solid #ddd;

    font-size:9px;

    text-align:center;

    vertical-align:middle;

    word-wrap:break-word;

}

table.registro th {

    background:#2563eb;

    color:white;

    font-size:9px;

    font-weight:bold;

}

table.registro th:nth-child(1),
table.registro td:nth-child(1) {
    width:15%;
}

table.registro th:nth-child(2),
table.registro td:nth-child(2) {
    width:13%;
}

table.registro th:nth-child(3),
table.registro td:nth-child(3) {
    width:11%;
}

table.registro th:nth-child(4),
table.registro td:nth-child(4) {
    width:9%;
}

table.registro th:nth-child(5),
table.registro td:nth-child(5) {
    width:13%;
}

table.registro th:nth-child(6),
table.registro td:nth-child(6) {
    width:13%;
}

table.registro th:nth-child(7),
table.registro td:nth-child(7) {
    width:9%;
}

table.registro th:nth-child(8),
table.registro td:nth-child(8) {
    width:17%;
}

table.registro tr:nth-child(even) {

    background:#f5f5f5;

}

</style>

</head>

<body>

<h1>
    Historial de Fichajes
</h1>

<div class="filtros">
';


if($mostrarResumenHoras){

    $html .=
        '<strong>Empleado:</strong> ' .
        escaparPDF($empleadoResumen) .
        '<br>';

}


if($fecha != ""){

    $html .=
        '<strong>Fecha:</strong> ' .
        escaparPDF($fecha) .
        '<br>';

}


if($mes != ""){

    $html .=
        '<strong>Mes:</strong> ' .
        escaparPDF($mes) .
        '<br>';

}


$html .= '

</div>
';


/* ==========================================================================
   RESUMEN
========================================================================== */

if(
    $mostrarResumenHoras &&
    $resumenHoras !== null
){

    $mesResumen =
        $mes != ""
            ? $mes
            : date("Y-m");


    $html .= '

    <table class="resumen">

        <tr>

            <td>

                <div class="resumen-titulo">
                    HORAS TOTALES
                </div>

                <div class="resumen-valor">

                    ' .
                    segundosAHorasPDF(
                        $resumenHoras[
                            "segundos_totales"
                        ]
                    ) .
                    '

                </div>

            </td>


            <td>

                <div class="resumen-titulo">

                    HORAS TRABAJADAS ' .
                    escaparPDF(
                        $mesResumen
                    ) .
                    '

                </div>

                <div class="resumen-valor">

                    ' .
                    segundosAHorasPDF(
                        $resumenHoras[
                            "segundos_mes"
                        ]
                    ) .
                    '

                </div>

            </td>

        </tr>

    </table>

    ';


    /* ======================================================================
       VACACIONES
    ====================================================================== */

    if(!empty($vacacionesEmpleado)){

        $html .= '

        <div class="seccion">

            <h2>
                Vacaciones
            </h2>
        ';


        foreach($vacacionesEmpleado as $vacacion){

            $html .= '

            <div class="evento">

                <div class="evento-titulo">

                    ' .
                    date(
                        "d/m/Y",
                        strtotime(
                            $vacacion['fecha_inicio']
                        )
                    ) .
                    '

                    -

                    ' .
                    date(
                        "d/m/Y",
                        strtotime(
                            $vacacion['fecha_fin']
                        )
                    ) .
                    '

                </div>

                <div class="evento-dias">

                    ' .
                    escaparPDF(
                        $vacacion['dias']
                    ) .
                    '

                    ' .
                    (
                        $vacacion['dias'] == 1
                            ? "día"
                            : "días"
                    ) .
                    '

                </div>

            </div>

            ';

        }


        $html .= '

        </div>

        ';

    }


    /* ======================================================================
       BAJAS
    ====================================================================== */

    if(!empty($bajasEmpleado)){

        $html .= '

        <div class="seccion">

            <h2>
                Bajas
            </h2>
        ';


        foreach($bajasEmpleado as $baja){

            $html .= '

            <div class="evento">

                <div class="evento-titulo">

                    ' .
                    date(
                        "d/m/Y",
                        strtotime(
                            $baja['fecha_inicio']
                        )
                    ) .
                    '

                    -

                    ' .
                    date(
                        "d/m/Y",
                        strtotime(
                            $baja['fecha_fin']
                        )
                    ) .
                    '

                </div>

                <div>

                    ' .
                    escaparPDF(
                        $baja['tipo']
                        ?? 'Baja'
                    ) .
                    '

                </div>

                <div class="evento-dias">

                    ' .
                    escaparPDF(
                        $baja['dias']
                    ) .
                    '

                    ' .
                    (
                        $baja['dias'] == 1
                            ? "día"
                            : "días"
                    ) .
                    '

                </div>

            </div>

            ';

        }


        $html .= '

        </div>

        ';

    }

}


/* ==========================================================================
   REGISTRO DE HORAS
========================================================================== */

$html .= '

<h2>
    Registro de horas
</h2>

<table class="registro">

<thead>

<tr>

    <th>
        Empleado
    </th>

    <th>
        Empresa
    </th>

    <th>
        Fecha
    </th>

    <th>
        Entrada
    </th>

    <th>
        Inicio descanso
    </th>

    <th>
        Fin descanso
    </th>

    <th>
        Salida
    </th>

    <th>
        Horas trabajadas
    </th>

</tr>

</thead>

<tbody>

';


foreach($fichajes as $fichaje){

    $segundos =
        calcularHorasFichajePDF(
            $fichaje
        );


    $html .= '

<tr>

    <td>' .
        escaparPDF(
            $fichaje['nombre']
        ) .
    '</td>

    <td>' .
        escaparPDF(
            $fichaje['empresa_nombre']
            ?? 'Sin empresa'
        ) .
    '</td>

    <td>' .
        escaparPDF(
            $fichaje['fecha']
        ) .
    '</td>

    <td>' .
        (
            !empty(
                $fichaje['hora_entrada']
            )
            ? substr(
                $fichaje['hora_entrada'],
                0,
                5
            )
            : '-'
        ) .
    '</td>

    <td>' .
        (
            !empty(
                $fichaje['inicio_descanso']
            )
            ? substr(
                $fichaje['inicio_descanso'],
                0,
                5
            )
            : '-'
        ) .
    '</td>

    <td>' .
        (
            !empty(
                $fichaje['fin_descanso']
            )
            ? substr(
                $fichaje['fin_descanso'],
                0,
                5
            )
            : '-'
        ) .
    '</td>

    <td>' .
        (
            !empty(
                $fichaje['hora_salida']
            )
            ? substr(
                $fichaje['hora_salida'],
                0,
                5
            )
            : '-'
        ) .
    '</td>

    <td>' .
        segundosAHorasPDF(
            $segundos
        ) .
    '</td>

</tr>

';

}


$html .= '

</tbody>

</table>

</body>

</html>

';


/* ==========================================================================
   GENERAR PDF
========================================================================== */

ob_clean();

$dompdf =
    new Dompdf();


$dompdf->loadHtml(
    $html,
    "UTF-8"
);


$dompdf->setPaper(
    "A4",
    "landscape"
);


$dompdf->render();


$dompdf->stream(
    "historial_fichajes.pdf",
    [
        "Attachment" => true
    ]
);

exit;