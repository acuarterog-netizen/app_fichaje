<?php

require_once "../vendor/autoload.php";
require_once "../core/Auth.php";
require_once "../models/Fichaje.php";

use Dompdf\Dompdf;

Auth::verificarSesion();
Auth::esEncargadoOAdmin();

$fichajeModel = new Fichaje();

/* ==========================================================================
   FILTROS
========================================================================== */

$busqueda = $_GET['busqueda'] ?? "";

$fecha = $_GET['fecha'] ?? "";

/* ==========================================================================
   OBTENER DATOS FILTRADOS
========================================================================== */

$fichajes = $fichajeModel->filtrarFichajes(

    $busqueda,
    $fecha

);

/* ==========================================================================
   HTML PDF
========================================================================== */

$html = '

<style>

body {

    font-family: Arial, sans-serif;
    padding: 20px;

}

h1 {

    text-align: center;
    margin-bottom: 20px;

}

.filtros {

    margin-bottom: 20px;
    font-size: 12px;

}

table {

    width: 100%;
    border-collapse: collapse;

}

th {

    background: #2563eb;
    color: white;
    padding: 12px;
    font-size: 12px;

}

td {

    padding: 10px;
    border-bottom: 1px solid #ddd;
    font-size: 11px;

}

tr:nth-child(even) {

    background: #f5f5f5;

}

</style>

<h1>Historial de Fichajes</h1>

<div class="filtros">

';

if($busqueda != "") {

    $html .= '<strong>Empleado:</strong> ' . $busqueda . '<br>';

}

if($fecha != "") {

    $html .= '<strong>Fecha:</strong> ' . $fecha . '<br>';

}

$html .= '

</div>

<table>

    <thead>

        <tr>

            <th>Empleado</th>
            <th>Fecha</th>
            <th>Entrada</th>
            <th>Inicio descanso</th>
            <th>Fin descanso</th>
            <th>Salida</th>

        </tr>

    </thead>

    <tbody>

';

foreach($fichajes as $fichaje) {

    $html .= '

        <tr>

            <td>' . $fichaje['nombre'] . '</td>

            <td>' . $fichaje['fecha'] . '</td>

            <td>' . (!empty($fichaje['hora_entrada']) ? substr($fichaje['hora_entrada'],0,5) : '-') . '</td>

<td>' . (!empty($fichaje['inicio_descanso']) ? substr($fichaje['inicio_descanso'],0,5) : '-') . '</td>

<td>' . (!empty($fichaje['fin_descanso']) ? substr($fichaje['fin_descanso'],0,5) : '-') . '</td>

<td>' . (!empty($fichaje['hora_salida']) ? substr($fichaje['hora_salida'],0,5) : '-') . '</td>

        </tr>

    ';
}

$html .= '

    </tbody>

</table>

';

/* ==========================================================================
   GENERAR PDF
========================================================================== */
ob_clean();

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$dompdf->stream(

    "historial_filtrado.pdf",

    ["Attachment" => true]

);

exit;