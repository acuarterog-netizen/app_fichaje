<?php

require_once "../core/Auth.php";
require_once "../models/Fichaje.php";
require_once "../models/Empresa.php";
require_once "../models/Vacaciones.php";
require_once "../models/Bajas.php";
require_once "../config/Database.php";

Auth::verificarSesion();
Auth::esEncargadoOAdmin();

$fichajeModel = new Fichaje();
$empresaModel = new Empresa();
$vacacionesModel = new Vacaciones();
$bajasModel = new Bajas();

$database = new Database();
$conexion = $database->conectar();

$empresas = $empresaModel->obtenerEmpresas();


/* ==========================================================================
   ACCIONES DE VACACIONES Y BAJAS
========================================================================== */


/*
   Guardamos los filtros para volver al mismo historial después
   de editar o eliminar un registro.
*/
function urlHistorial($extra = []){

    $parametros = [

        'busqueda' =>
            $_GET['busqueda'] ?? '',

        'fecha' =>
            $_GET['fecha'] ?? '',

        'mes' =>
            $_GET['mes'] ?? '',

        'empresa_id' =>
            $_GET['empresa_id'] ?? ''
    ];


    foreach($extra as $clave => $valor){

        $parametros[$clave] = $valor;

    }


    $parametros = array_filter(
        $parametros,
        function($valor){

            return
                $valor !== '' &&
                $valor !== null;

        }
    );


    return
        'historial.php' .
        (
            !empty($parametros)
                ? '?' . http_build_query($parametros)
                : ''
        );
}


/*
==========================================================================
ELIMINAR UN DÍA DE VACACIONES
==========================================================================

Si las vacaciones son:

10/08 - 15/08

y eliminamos el día 12/08,

el modelo se encarga de mantener:

10/08 - 11/08
13/08 - 15/08

Por eso se utiliza el método existente del modelo.
*/
if(
    isset($_GET['eliminar_vacacion']) &&
    isset($_GET['fecha_vacacion'])
){

    $vacacionesModel->eliminarVacaciones(
        (int)$_GET['eliminar_vacacion'],
        $_GET['fecha_vacacion']
    );


    header(
        'Location: ' .
        urlHistorial()
    );

    exit;
}


/*
==========================================================================
ELIMINAR UNA BAJA
==========================================================================
*/
if(
    isset($_GET['eliminar_baja'])
){

    $bajasModel->eliminarBaja(
        (int)$_GET['eliminar_baja']
    );


    header(
        'Location: ' .
        urlHistorial()
    );

    exit;
}


/*
==========================================================================
GUARDAR EDICIÓN DE VACACIONES
==========================================================================
*/
if(
    isset($_POST['editar_vacacion']) &&
    isset($_POST['vacacion_id'])
){

    $id =
        (int)$_POST['vacacion_id'];


    $fechaInicio =
        $_POST['fecha_inicio'] ?? '';


    $fechaFin =
        $_POST['fecha_fin'] ?? '';


    $comentario =
        $_POST['comentario'] ?? '';


    if(
        $fechaInicio !== '' &&
        $fechaFin !== '' &&
        $fechaInicio <= $fechaFin
    ){

        $sql = "
            UPDATE vacaciones
            SET
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                comentario = :comentario
            WHERE id = :id
        ";


        $stmt =
            $conexion->prepare(
                $sql
            );


        $stmt->execute([

            ':fecha_inicio' =>
                $fechaInicio,

            ':fecha_fin' =>
                $fechaFin,

            ':comentario' =>
                $comentario,

            ':id' =>
                $id
        ]);

    }


    header(
        'Location: ' .
        urlHistorial()
    );

    exit;
}


/*
==========================================================================
GUARDAR EDICIÓN DE BAJA
==========================================================================
*/
if(
    isset($_POST['editar_baja']) &&
    isset($_POST['baja_id'])
){

    $id =
        (int)$_POST['baja_id'];


    $tipo =
        trim(
            $_POST['tipo'] ?? ''
        );


    $fechaInicio =
        $_POST['fecha_inicio'] ?? '';


    $fechaFin =
        $_POST['fecha_fin'] ?? '';


    $comentario =
        $_POST['comentario'] ?? '';


    if(
        $tipo !== '' &&
        $fechaInicio !== '' &&
        $fechaFin !== '' &&
        $fechaInicio <= $fechaFin
    ){

        $sql = "
            UPDATE bajas
            SET
                tipo = :tipo,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                comentario = :comentario
            WHERE id = :id
        ";


        $stmt =
            $conexion->prepare(
                $sql
            );


        $stmt->execute([

            ':tipo' =>
                $tipo,

            ':fecha_inicio' =>
                $fechaInicio,

            ':fecha_fin' =>
                $fechaFin,

            ':comentario' =>
                $comentario,

            ':id' =>
                $id
        ]);

    }


    header(
        'Location: ' .
        urlHistorial()
    );

    exit;
}


/*
==========================================================================
OBTENER REGISTRO QUE SE QUIERE EDITAR
==========================================================================
*/

$editarVacacion = null;
$editarBaja = null;


if(
    isset($_GET['editar_vacacion'])
){

    $stmt =
        $conexion->prepare(
            "
            SELECT
                vacaciones.*,
                usuarios.nombre,
                empresas.nombre AS empresa_nombre

            FROM vacaciones

            INNER JOIN usuarios
                ON vacaciones.usuario_id =
                   usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id =
                   empresas.id

            WHERE vacaciones.id = :id

            LIMIT 1
            "
        );


    $stmt->execute([

        ':id' =>
            (int)$_GET['editar_vacacion']

    ]);


    $editarVacacion =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );
}


if(
    isset($_GET['editar_baja'])
){

    $stmt =
        $conexion->prepare(
            "
            SELECT
                bajas.*,
                usuarios.nombre,
                empresas.nombre AS empresa_nombre,
                tipos_baja.nombre AS tipo_nombre

            FROM bajas

            INNER JOIN usuarios
                ON bajas.usuario_id =
                   usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id =
                   empresas.id

            LEFT JOIN tipos_baja
                ON bajas.tipo =
                   tipos_baja.nombre

            WHERE bajas.id = :id

            LIMIT 1
            "
        );


    $stmt->execute([

        ':id' =>
            (int)$_GET['editar_baja']

    ]);


    $editarBaja =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );
}


/* ==========================================================================
   FILTROS
========================================================================== */

$busqueda =
    $_GET['busqueda'] ?? "";


$fecha =
    $_GET['fecha'] ?? "";


$mes =
    $_GET['mes'] ?? "";


$empresa_id =
    $_GET['empresa_id'] ?? "";


/* ==========================================================================
   OBTENER FICHAJES
========================================================================== */

$fichajes =
    $fichajeModel->filtrarFichajes(

        $busqueda,

        $fecha,

        $mes,

        $empresa_id

    );


/* ==========================================================================
   DETECTAR EMPLEADOS
========================================================================== */

$empleadosEncontrados = [];


foreach(
    $fichajes
    as $fichaje
){

    if(
        isset(
            $fichaje['usuario_id']
        ) &&
        !in_array(
            $fichaje['usuario_id'],
            $empleadosEncontrados
        )
    ){

        $empleadosEncontrados[] =
            $fichaje['usuario_id'];

    }

}


/*
==============================================================================
SOLO MOSTRAR RESUMEN CUANDO HAY UN ÚNICO EMPLEADO
==============================================================================
*/

$mostrarResumenHoras =
    $busqueda != "" &&
    count($empleadosEncontrados) === 1;


$resumenHoras = null;

$empleadoResumen = null;

$vacacionesEmpleado = [];

$bajasEmpleado = [];


if(
    $mostrarResumenHoras
){

    $usuarioIdResumen =
        $empleadosEncontrados[0];


    /*
    --------------------------------------------------------------------------
    RESUMEN DE HORAS
    --------------------------------------------------------------------------
    */

    $mesResumen =
        $mes != ""
            ? $mes
            : date("Y-m");


    $resumenHoras =
        $fichajeModel->obtenerResumenHorasEmpleado(

            $usuarioIdResumen,

            $mesResumen

        );


    /*
    --------------------------------------------------------------------------
    NOMBRE DEL EMPLEADO
    --------------------------------------------------------------------------
    */

    foreach(
        $fichajes
        as $fichaje
    ){

        if(
            $fichaje['usuario_id'] ==
            $usuarioIdResumen
        ){

            $empleadoResumen =
                $fichaje['nombre'];

            break;

        }

    }


    /*
    Las vacaciones y bajas se cargan
    más abajo directamente desde la base
    de datos para convertir cada día
    en una fila del historial.
    */

}


/* ==========================================================================
   VACACIONES Y BAJAS COMO FILAS DEL HISTORIAL
========================================================================== */

$registrosHistorial =
    $fichajes;


/*
   Las vacaciones y las bajas SOLO se añaden
   cuando se ha filtrado por un único empleado.
*/

if(
    $mostrarResumenHoras
){

    $usuarioIdHistorial =
        (int)$empleadosEncontrados[0];


    /*
    --------------------------------------------------------------------------
    VACACIONES
    --------------------------------------------------------------------------
    */

    $sqlVacaciones = "

        SELECT

            vacaciones.id,

            vacaciones.usuario_id,

            vacaciones.fecha_inicio,

            vacaciones.fecha_fin,

            vacaciones.comentario,

            usuarios.nombre,

            empresas.nombre AS empresa_nombre

        FROM vacaciones

        INNER JOIN usuarios

            ON vacaciones.usuario_id =
               usuarios.id

        LEFT JOIN empresas

            ON usuarios.empresa_id =
               empresas.id

        WHERE vacaciones.usuario_id =
              :usuario_id

        AND vacaciones.estado =
            'aprobada'

    ";


    $paramsVacaciones = [

        ':usuario_id' =>
            $usuarioIdHistorial

    ];


    if(
        $empresa_id != ""
    ){

        $sqlVacaciones .=
            "
            AND usuarios.empresa_id =
                :empresa_id
            ";


        $paramsVacaciones[
            ':empresa_id'
        ] =
            $empresa_id;

    }


    if(
        $fecha != ""
    ){

        $sqlVacaciones .=
            "
            AND :fecha BETWEEN
                vacaciones.fecha_inicio
                AND vacaciones.fecha_fin
            ";


        $paramsVacaciones[
            ':fecha'
        ] =
            $fecha;

    }

    elseif(
        $mes != ""
    ){

        $primerDiaMes =
            $mes . "-01";


        $ultimoDiaMes =
            date(
                "Y-m-t",
                strtotime(
                    $primerDiaMes
                )
            );


        $sqlVacaciones .=
            "
            AND vacaciones.fecha_inicio
                <= :ultimo_dia_mes

            AND vacaciones.fecha_fin
                >= :primer_dia_mes
            ";


        $paramsVacaciones[
            ':ultimo_dia_mes'
        ] =
            $ultimoDiaMes;


        $paramsVacaciones[
            ':primer_dia_mes'
        ] =
            $primerDiaMes;

    }


    $sqlVacaciones .=
        "
        ORDER BY
            vacaciones.fecha_inicio DESC
        ";


    $stmtVacaciones =
        $conexion->prepare(
            $sqlVacaciones
        );


    $stmtVacaciones->execute(
        $paramsVacaciones
    );


    $vacacionesEmpleado =
        $stmtVacaciones->fetchAll(
            PDO::FETCH_ASSOC
        );


    /*
    --------------------------------------------------------------------------
    BAJAS
    --------------------------------------------------------------------------
    */

    $sqlBajas = "

        SELECT

            bajas.id,

            bajas.usuario_id,

            bajas.tipo,

            bajas.fecha_inicio,

            bajas.fecha_fin,

            bajas.comentario,

            usuarios.nombre,

            empresas.nombre AS empresa_nombre,

            tipos_baja.nombre AS tipo_nombre

        FROM bajas

        INNER JOIN usuarios

            ON bajas.usuario_id =
               usuarios.id

        LEFT JOIN empresas

            ON usuarios.empresa_id =
               empresas.id

        LEFT JOIN tipos_baja

            ON bajas.tipo =
               tipos_baja.nombre

        WHERE bajas.usuario_id =
              :usuario_id

    ";


    $paramsBajas = [

        ':usuario_id' =>
            $usuarioIdHistorial

    ];


    if(
        $empresa_id != ""
    ){

        $sqlBajas .=
            "
            AND usuarios.empresa_id =
                :empresa_id
            ";


        $paramsBajas[
            ':empresa_id'
        ] =
            $empresa_id;

    }


    if(
        $fecha != ""
    ){

        $sqlBajas .=
            "
            AND :fecha BETWEEN
                bajas.fecha_inicio
                AND bajas.fecha_fin
            ";


        $paramsBajas[
            ':fecha'
        ] =
            $fecha;

    }

    elseif(
        $mes != ""
    ){

        $primerDiaMes =
            $mes . "-01";


        $ultimoDiaMes =
            date(
                "Y-m-t",
                strtotime(
                    $primerDiaMes
                )
            );


        $sqlBajas .=
            "
            AND bajas.fecha_inicio
                <= :ultimo_dia_mes

            AND bajas.fecha_fin
                >= :primer_dia_mes
            ";


        $paramsBajas[
            ':ultimo_dia_mes'
        ] =
            $ultimoDiaMes;


        $paramsBajas[
            ':primer_dia_mes'
        ] =
            $primerDiaMes;

    }


    $sqlBajas .=
        "
        ORDER BY
            bajas.fecha_inicio DESC
        ";


    $stmtBajas =
        $conexion->prepare(
            $sqlBajas
        );


    $stmtBajas->execute(
        $paramsBajas
    );


    $bajasEmpleado =
        $stmtBajas->fetchAll(
            PDO::FETCH_ASSOC
        );


    /*
    --------------------------------------------------------------------------
    CREAR UNA FILA POR CADA DÍA DE VACACIONES
    --------------------------------------------------------------------------
    */

    foreach(
        $vacacionesEmpleado
        as $vacacion
    ){

        $inicio =
            new DateTime(
                $vacacion['fecha_inicio']
            );


        $fin =
            new DateTime(
                $vacacion['fecha_fin']
            );


        /*
           Recortar al filtro seleccionado.
        */

        if(
            $fecha != ""
        ){

            $fechaFiltro =
                new DateTime(
                    $fecha
                );


            $inicio =
                clone $fechaFiltro;


            $fin =
                clone $fechaFiltro;

        }

        elseif(
            $mes != ""
        ){

            $inicioMes =
                new DateTime(
                    $mes . "-01"
                );


            $finMes =
                new DateTime(
                    date(
                        "Y-m-t",
                        strtotime(
                            $mes . "-01"
                        )
                    )
                );


            if(
                $inicio < $inicioMes
            ){

                $inicio =
                    clone $inicioMes;

            }


            if(
                $fin > $finMes
            ){

                $fin =
                    clone $finMes;

            }

        }


        for(

            $dia = clone $inicio;

            $dia <= $fin;

            $dia->modify("+1 day")

        ){

            $fechaDia =
                $dia->format(
                    "Y-m-d"
                );


            /*
               Si existe un fichaje ese día,
               el fichaje normal tiene prioridad.
            */

            $yaExisteFichaje =
                false;


            foreach(
                $fichajes
                as $fichaje
            ){

                if(

                    (int)$fichaje[
                        'usuario_id'
                    ] ===
                    $usuarioIdHistorial

                    &&

                    $fichaje['fecha'] ===
                    $fechaDia

                ){

                    $yaExisteFichaje =
                        true;

                    break;

                }

            }


            if(
                $yaExisteFichaje
            ){

                continue;

            }


            $registrosHistorial[] = [

                'id' =>
                    $vacacion['id'],

                'usuario_id' =>
                    $vacacion['usuario_id'],

                'nombre' =>
                    $vacacion['nombre'],

                'empresa_nombre' =>
                    $vacacion[
                        'empresa_nombre'
                    ] ??
                    'Sin empresa',

                'fecha' =>
                    $fechaDia,

                'hora_entrada' =>
                    null,

                'inicio_descanso' =>
                    null,

                'fin_descanso' =>
                    null,

                'hora_salida' =>
                    null,

                'tipo_registro' =>
                    'vacaciones',

                'tipo_baja' =>
                    null,

                'registro_id' =>
                    $vacacion['id']

            ];

        }

    }


    /*
    --------------------------------------------------------------------------
    CREAR UNA FILA POR CADA DÍA DE BAJA
    --------------------------------------------------------------------------
    */

    foreach(
        $bajasEmpleado
        as $baja
    ){

        $inicio =
            new DateTime(
                $baja['fecha_inicio']
            );


        $fin =
            new DateTime(
                $baja['fecha_fin']
            );


        if(
            $fecha != ""
        ){

            $fechaFiltro =
                new DateTime(
                    $fecha
                );


            $inicio =
                clone $fechaFiltro;


            $fin =
                clone $fechaFiltro;

        }

        elseif(
            $mes != ""
        ){

            $inicioMes =
                new DateTime(
                    $mes . "-01"
                );


            $finMes =
                new DateTime(
                    date(
                        "Y-m-t",
                        strtotime(
                            $mes . "-01"
                        )
                    )
                );


            if(
                $inicio < $inicioMes
            ){

                $inicio =
                    clone $inicioMes;

            }


            if(
                $fin > $finMes
            ){

                $fin =
                    clone $finMes;

            }

        }


        for(

            $dia = clone $inicio;

            $dia <= $fin;

            $dia->modify("+1 day")

        ){

            $fechaDia =
                $dia->format(
                    "Y-m-d"
                );


            /*
               Si existe un fichaje ese día,
               el fichaje normal tiene prioridad.
            */

            $yaExisteFichaje =
                false;


            foreach(
                $fichajes
                as $fichaje
            ){

                if(

                    (int)$fichaje[
                        'usuario_id'
                    ] ===
                    $usuarioIdHistorial

                    &&

                    $fichaje['fecha'] ===
                    $fechaDia

                ){

                    $yaExisteFichaje =
                        true;

                    break;

                }

            }


            if(
                $yaExisteFichaje
            ){

                continue;

            }


            $tipoBaja =

                $baja[
                    'tipo_nombre'
                ]

                ??

                $baja[
                    'tipo'
                ]

                ??

                'BAJA';


            $registrosHistorial[] = [

                'id' =>
                    $baja['id'],

                'usuario_id' =>
                    $baja['usuario_id'],

                'nombre' =>
                    $baja['nombre'],

                'empresa_nombre' =>
                    $baja[
                        'empresa_nombre'
                    ] ??
                    'Sin empresa',

                'fecha' =>
                    $fechaDia,

                'hora_entrada' =>
                    null,

                'inicio_descanso' =>
                    null,

                'fin_descanso' =>
                    null,

                'hora_salida' =>
                    null,

                'tipo_registro' =>
                    'baja',

                'tipo_baja' =>
                    $tipoBaja,

                'registro_id' =>
                    $baja['id']

            ];

        }

    }


    /*
    --------------------------------------------------------------------------
    ORDENAR TODO JUNTO POR FECHA
    --------------------------------------------------------------------------
    */

    usort(

        $registrosHistorial,

        function(
            $a,
            $b
        ){

            return strcmp(

                $b['fecha'],

                $a['fecha']

            );

        }

    );

}


/* ==========================================================================
   CONVERTIR SEGUNDOS A HORAS
========================================================================== */

function segundosAHoras(
    $segundos
){

    $segundos =
        max(
            0,
            (int)$segundos
        );


    $horas =
        floor(
            $segundos / 3600
        );


    $minutos =
        floor(
            (
                $segundos % 3600
            ) / 60
        );


    return

        $horas .
        " h " .

        str_pad(
            $minutos,
            2,
            "0",
            STR_PAD_LEFT
        ) .

        " min";
}


/* ==========================================================================
   CALCULAR HORAS DE UN FICHAJE
========================================================================== */

function calcularHorasFichaje(
    $fichaje
){

    if(

        empty(
            $fichaje[
                'hora_entrada'
            ]
        )

        ||

        empty(
            $fichaje[
                'hora_salida'
            ]
        )

    ){

        return 0;

    }


    $entrada =

        strtotime(

            $fichaje[
                'fecha'
            ]

            .

            " "

            .

            $fichaje[
                'hora_entrada'
            ]

        );


    $salida =

        strtotime(

            $fichaje[
                'fecha'
            ]

            .

            " "

            .

            $fichaje[
                'hora_salida'
            ]

        );


    $segundos =

        $salida -
        $entrada;


    if(

        !empty(
            $fichaje[
                'inicio_descanso'
            ]
        )

        &&

        !empty(
            $fichaje[
                'fin_descanso'
            ]
        )

    ){

        $inicioDescanso =

            strtotime(

                $fichaje[
                    'fecha'
                ]

                .

                " "

                .

                $fichaje[
                    'inicio_descanso'
                ]

            );


        $finDescanso =

            strtotime(

                $fichaje[
                    'fecha'
                ]

                .

                " "

                .

                $fichaje[
                    'fin_descanso'
                ]

            );


        $segundos -=

            (
                $finDescanso -
                $inicioDescanso
            );

    }


    return max(
        0,
        $segundos
    );
}


include "../views/layouts/header.php";

include "../views/layouts/sidebar.php";

?>


<h1>
    Historial completo
</h1>


<!-- =========================================================================
     BOTONES
========================================================================== -->

<div
    style="
        display:flex;
        gap:15px;
        flex-wrap:wrap;
        margin-bottom:20px;
    "
>


    <?php if($mostrarResumenHoras): ?>

        <a
            href="exportar_pdf.php?busqueda=<?php echo urlencode($busqueda); ?>&fecha=<?php echo urlencode($fecha); ?>&mes=<?php echo urlencode($mes); ?>&empresa_id=<?php echo urlencode($empresa_id); ?>"
            class="btn-main-blue"
        >
            Exportar PDF
        </a>

    <?php endif; ?>


    <?php if(
        $_SESSION['usuario']['rol'] == 'admin'
    ): ?>

        <a
            href="crear_fichaje.php"
            class="btn-main-blue"
        >
            Añadir fichaje
        </a>

    <?php endif; ?>


</div>


<!-- =========================================================================
     FILTROS
========================================================================== -->

<div class="fichaje-card">

    <form
        method="GET"
        style="
            display:flex;
            gap:15px;
            flex-wrap:wrap;
            align-items:end;
        "
    >


        <div class="form-group">

            <label>
                Buscar empleado
            </label>

            <input
                type="text"
                name="busqueda"
                class="form-control"
                placeholder="Nombre..."
                value="<?php echo htmlspecialchars($busqueda); ?>"
            >

        </div>


        <div class="form-group">

            <label>
                Fecha
            </label>

            <input
                type="date"
                name="fecha"
                class="form-control"
                value="<?php echo htmlspecialchars($fecha); ?>"
            >

        </div>


        <div class="form-group">

            <label>
                Mes
            </label>

            <input
                type="month"
                name="mes"
                class="form-control"
                value="<?php echo htmlspecialchars($mes); ?>"
            >

        </div>


        <div class="form-group">

            <label>
                Empresa
            </label>

            <select
                name="empresa_id"
                class="form-control"
            >

                <option value="">
                    Todas
                </option>


                <?php foreach(
                    $empresas
                    as $empresa
                ): ?>

                    <option
                        value="<?php echo $empresa['id']; ?>"

                        <?php

                        if(
                            $empresa_id ==
                            $empresa['id']
                        ){

                            echo 'selected';

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


        <button
            type="submit"
            class="btn-main-blue"
        >
            Filtrar
        </button>


        <a
            href="historial.php"
            class="btn-delete"
            style="
                align-self:center;
                margin-bottom:10px;
            "
        >
            Limpiar
        </a>


    </form>

</div>


<br>


<!-- =========================================================================
     RESUMEN DEL EMPLEADO
========================================================================== -->

<?php if(
    $mostrarResumenHoras &&
    $resumenHoras !== null
): ?>


<div
    style="
        display:grid;
        grid-template-columns:
            repeat(auto-fit,minmax(220px,1fr));
        gap:20px;
        margin-bottom:20px;
    "
>


    <!-- HORAS TOTALES -->

    <div class="fichaje-card">

        <h3 style="margin-top:0;">

            <?php

            echo htmlspecialchars(
                $empleadoResumen
            );

            ?>

        </h3>


        <p
            style="
                margin-bottom:5px;
                color:#666;
            "
        >
            Horas totales
        </p>


        <strong
            style="
                font-size:28px;
                color:#2563eb;
            "
        >

            <?php

            echo segundosAHoras(

                $resumenHoras[
                    'segundos_totales'
                ]

            );

            ?>

        </strong>

    </div>


    <!-- HORAS DEL MES -->

    <div class="fichaje-card">

        <h3 style="margin-top:0;">

            Horas del mes

        </h3>


        <p
            style="
                margin-bottom:5px;
                color:#666;
            "
        >

            <?php

            echo htmlspecialchars(

                $mes != ""

                    ? $mes

                    : date("Y-m")

            );

            ?>

        </p>


        <strong
            style="
                font-size:28px;
                color:#2563eb;
            "
        >

            <?php

            echo segundosAHoras(

                $resumenHoras[
                    'segundos_mes'
                ]

            );

            ?>

        </strong>

    </div>


</div>


<?php endif; ?>


<!-- =========================================================================
     EDITAR VACACIÓN / BAJA
========================================================================== -->

<?php if(
    $editarVacacion
): ?>

<div
    class="fichaje-card"
    style="margin-bottom:20px;"
>

    <h2 style="margin-top:0;">

        Editar vacaciones

    </h2>


    <p style="color:#666;">

        Empleado:

        <strong>

            <?php

            echo htmlspecialchars(
                $editarVacacion[
                    'nombre'
                ]
            );

            ?>

        </strong>

    </p>


    <form method="POST">


        <input
            type="hidden"
            name="editar_vacacion"
            value="1"
        >


        <input
            type="hidden"
            name="vacacion_id"
            value="<?php
                echo (int)$editarVacacion['id'];
            ?>"
        >


        <div
            style="
                display:grid;
                grid-template-columns:
                    repeat(auto-fit,minmax(220px,1fr));
                gap:15px;
            "
        >


            <div class="form-group">

                <label>
                    Fecha inicio
                </label>


                <input
                    type="date"
                    name="fecha_inicio"
                    class="form-control"
                    required
                    value="<?php

                        echo htmlspecialchars(
                            $editarVacacion[
                                'fecha_inicio'
                            ]
                        );

                    ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    Fecha fin
                </label>


                <input
                    type="date"
                    name="fecha_fin"
                    class="form-control"
                    required
                    value="<?php

                        echo htmlspecialchars(
                            $editarVacacion[
                                'fecha_fin'
                            ]
                        );

                    ?>"
                >

            </div>


        </div>


        <div class="form-group">

            <label>
                Comentario
            </label>


            <textarea
                name="comentario"
                class="form-control"
                rows="3"
            ><?php

                echo htmlspecialchars(
                    $editarVacacion[
                        'comentario'
                    ] ?? ''
                );

            ?></textarea>

        </div>


        <div
            style="
                display:flex;
                gap:10px;
                flex-wrap:wrap;
            "
        >


            <button
                type="submit"
                class="btn-main-blue"
            >
                Guardar cambios
            </button>


            <a
                href="<?php

                    echo htmlspecialchars(
                        urlHistorial()
                    );

                ?>"
                class="btn-delete"
            >
                Cancelar
            </a>


        </div>


    </form>

</div>

<?php endif; ?>


<?php if(
    $editarBaja
): ?>

<div
    class="fichaje-card"
    style="margin-bottom:20px;"
>

    <h2 style="margin-top:0;">

        Editar baja

    </h2>


    <p style="color:#666;">

        Empleado:

        <strong>

            <?php

            echo htmlspecialchars(
                $editarBaja[
                    'nombre'
                ]
            );

            ?>

        </strong>

    </p>


    <form method="POST">


        <input
            type="hidden"
            name="editar_baja"
            value="1"
        >


        <input
            type="hidden"
            name="baja_id"
            value="<?php
                echo (int)$editarBaja['id'];
            ?>"
        >


        <div class="form-group">

            <label>
                Tipo de baja
            </label>


            <input
                type="text"
                name="tipo"
                class="form-control"
                required
                value="<?php

                    echo htmlspecialchars(

                        $editarBaja[
                            'tipo_nombre'
                        ]

                        ??

                        $editarBaja[
                            'tipo'
                        ]

                        ??

                        ''

                    );

                ?>"
            >

        </div>


        <div
            style="
                display:grid;
                grid-template-columns:
                    repeat(auto-fit,minmax(220px,1fr));
                gap:15px;
            "
        >


            <div class="form-group">

                <label>
                    Fecha inicio
                </label>


                <input
                    type="date"
                    name="fecha_inicio"
                    class="form-control"
                    required
                    value="<?php

                        echo htmlspecialchars(
                            $editarBaja[
                                'fecha_inicio'
                            ]
                        );

                    ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    Fecha fin
                </label>


                <input
                    type="date"
                    name="fecha_fin"
                    class="form-control"
                    required
                    value="<?php

                        echo htmlspecialchars(
                            $editarBaja[
                                'fecha_fin'
                            ]
                        );

                    ?>"
                >

            </div>


        </div>


        <div class="form-group">

            <label>
                Comentario
            </label>


            <textarea
                name="comentario"
                class="form-control"
                rows="3"
            ><?php

                echo htmlspecialchars(
                    $editarBaja[
                        'comentario'
                    ] ?? ''
                );

            ?></textarea>

        </div>


        <div
            style="
                display:flex;
                gap:10px;
                flex-wrap:wrap;
            "
        >


            <button
                type="submit"
                class="btn-main-blue"
            >
                Guardar cambios
            </button>


            <a
                href="<?php

                    echo htmlspecialchars(
                        urlHistorial()
                    );

                ?>"
                class="btn-delete"
            >
                Cancelar
            </a>


        </div>


    </form>

</div>

<?php endif; ?>


<!-- =========================================================================
     TABLA DE FICHAJES
========================================================================== -->

<div class="fichaje-card">


    <table class="tabla-gestion">


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
                Descanso
            </th>


            <th>
                Salida
            </th>


            <th>
                Horas trabajadas
            </th>


            <?php if(

                $_SESSION['usuario']['rol'] ==
                    'admin'

                ||

                $_SESSION['usuario']['rol'] ==
                    'encargado'

            ): ?>

                <th>
                    Acciones
                </th>

            <?php endif; ?>


        </tr>


        <?php foreach(
            $registrosHistorial
            as $fichaje
        ): ?>


            <?php

            $esVacacion =

                isset(
                    $fichaje[
                        'tipo_registro'
                    ]
                )

                &&

                $fichaje[
                    'tipo_registro'
                ] ===
                'vacaciones';


            $esBaja =

                isset(
                    $fichaje[
                        'tipo_registro'
                    ]
                )

                &&

                $fichaje[
                    'tipo_registro'
                ] ===
                'baja';


            $esAusencia =

                $esVacacion ||
                $esBaja;


            $segundosFichaje =

                $esAusencia

                    ? 0

                    : calcularHorasFichaje(
                        $fichaje
                    );

            ?>


            <tr>


                <td>

                    <?php

                    echo htmlspecialchars(

                        $fichaje[
                            'nombre'
                        ]

                    );

                    ?>

                </td>


                <td>

                    <?php

                    echo htmlspecialchars(

                        $fichaje[
                            'empresa_nombre'
                        ]

                        ??

                        'Sin empresa'

                    );

                    ?>

                </td>


                <td>

                    <?php

                    echo htmlspecialchars(

                        $fichaje[
                            'fecha'
                        ]

                    );

                    ?>

                </td>


                <td>

                    <?php

                    echo !empty(

                        $fichaje[
                            'hora_entrada'
                        ]

                    )

                        ? substr(

                            $fichaje[
                                'hora_entrada'
                            ],

                            0,

                            5

                        )

                        : '-';

                    ?>

                </td>


                <td>

                    <?php if(
                        $esAusencia
                    ): ?>

                        -

                    <?php else: ?>

                        <?php

                        echo !empty(

                            $fichaje[
                                'inicio_descanso'
                            ]

                        )

                            ? substr(

                                $fichaje[
                                    'inicio_descanso'
                                ],

                                0,

                                5

                            )

                            : '-';

                        ?>

                        -

                        <?php

                        echo !empty(

                            $fichaje[
                                'fin_descanso'
                            ]

                        )

                            ? substr(

                                $fichaje[
                                    'fin_descanso'
                                ],

                                0,

                                5

                            )

                            : '-';

                        ?>

                    <?php endif; ?>

                </td>


                <td>

                    <?php

                    echo !empty(

                        $fichaje[
                            'hora_salida'
                        ]

                    )

                        ? substr(

                            $fichaje[
                                'hora_salida'
                            ],

                            0,

                            5

                        )

                        : '-';

                    ?>

                </td>


                <td>


                    <?php if(
                        $esVacacion
                    ): ?>

                        <strong
                            style="
                                color:#2563eb;
                            "
                        >
                            VACACIONES
                        </strong>


                    <?php elseif(
                        $esBaja
                    ): ?>

                        <strong
                            style="
                                color:#dc2626;
                            "
                        >

                            <?php

                            echo htmlspecialchars(

                                $fichaje[
                                    'tipo_baja'
                                ]

                                ??

                                'BAJA'

                            );

                            ?>

                        </strong>


                    <?php else: ?>


                        <?php

                        echo segundosAHoras(

                            $segundosFichaje

                        );

                        ?>


                    <?php endif; ?>


                </td>


                <?php if(

                    $_SESSION['usuario']['rol'] ==
                        'admin'

                    ||

                    $_SESSION['usuario']['rol'] ==
                        'encargado'

                ): ?>


                    <td>


                        <?php if(
                            $esVacacion
                        ): ?>


                            <a
                                class="btn-edit"
                                href="<?php

                                    echo htmlspecialchars(

                                        urlHistorial([

                                            'editar_vacacion' =>

                                                $fichaje[
                                                    'registro_id'
                                                ]

                                        ])

                                    );

                                ?>"
                            >
                                Editar
                            </a>


                            <a
                                class="btn-delete"
                                href="<?php

                                    echo htmlspecialchars(

                                        urlHistorial([

                                            'eliminar_vacacion' =>

                                                $fichaje[
                                                    'registro_id'
                                                ],

                                            'fecha_vacacion' =>

                                                $fichaje[
                                                    'fecha'
                                                ]

                                        ])

                                    );

                                ?>"
                                onclick="
                                    return confirm(
                                        '¿Eliminar este día de vacaciones?'
                                    )
                                "
                            >
                                Eliminar
                            </a>


                        <?php elseif(
                            $esBaja
                        ): ?>


                            <a
                                class="btn-edit"
                                href="<?php

                                    echo htmlspecialchars(

                                        urlHistorial([

                                            'editar_baja' =>

                                                $fichaje[
                                                    'registro_id'
                                                ]

                                        ])

                                    );

                                ?>"
                            >
                                Editar
                            </a>


                            <a
                                class="btn-delete"
                                href="<?php

                                    echo htmlspecialchars(

                                        urlHistorial([

                                            'eliminar_baja' =>

                                                $fichaje[
                                                    'registro_id'
                                                ]

                                        ])

                                    );

                                ?>"
                                onclick="
                                    return confirm(
                                        '¿Eliminar esta baja?'
                                    )
                                "
                            >
                                Eliminar
                            </a>


                        <?php else: ?>


                            <a
                                class="btn-edit"
                                href="editar_fichaje.php?id=<?php

                                    echo $fichaje[
                                        'id'
                                    ];

                                ?>"
                            >
                                Editar
                            </a>


                            <a
                                class="btn-delete"
                                href="eliminar_fichaje.php?id=<?php

                                    echo $fichaje[
                                        'id'
                                    ];

                                ?>"
                                onclick="
                                    return confirm(
                                        '¿Eliminar este fichaje?'
                                    )
                                "
                            >
                                Eliminar
                            </a>


                        <?php endif; ?>


                    </td>


                <?php endif; ?>


            </tr>


        <?php endforeach; ?>


        <?php if(
            empty(
                $registrosHistorial
            )
        ): ?>


            <tr>


                <td
                    colspan="<?php

                        echo (

                            $_SESSION[
                                'usuario'
                            ]['rol'] == 'admin'

                            ||

                            $_SESSION[
                                'usuario'
                            ]['rol'] == 'encargado'

                        )

                            ? 8

                            : 7;

                    ?>"
                    style="
                        text-align:center;
                        padding:30px;
                    "
                >

                    No hay registros que coincidan
                    con los filtros.

                </td>


            </tr>


        <?php endif; ?>


    </table>


</div>


<?php

include "../views/layouts/footer.php";

?>