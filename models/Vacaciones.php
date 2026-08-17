<?php

require_once "../config/Database.php";

class Vacaciones {

    private $conexion;


    public function __construct(){

        $database =
            new Database();

        $this->conexion =
            $database->conectar();
    }


    /* ==========================================================
       CREAR VACACIONES
    ========================================================== */

    public function crearVacaciones(
    $usuario_id,
    $fecha_inicio,
    $fecha_fin,
    $comentario = "",
    $creado_por = null
){

    /*
    ==========================================================
    COMPROBAR SI YA EXISTE UN FICHAJE EN ESAS FECHAS
    ==========================================================
    */

    $sqlComprobar = "
        SELECT fecha
        FROM fichajes
        WHERE usuario_id = :usuario_id
        AND fecha BETWEEN :fecha_inicio AND :fecha_fin
        LIMIT 1
    ";

    $stmtComprobar = $this->conexion->prepare($sqlComprobar);

    $stmtComprobar->execute([
        ':usuario_id'  => $usuario_id,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin'    => $fecha_fin
    ]);

    $fichaje = $stmtComprobar->fetch(PDO::FETCH_ASSOC);


    /*
    ==========================================================
    SI YA HAY FICHAJE
    ==========================================================
    */

    if($fichaje){

        return [
            'ok' => false,
            'tipo' => 'fichaje',
            'fecha' => $fichaje['fecha']
        ];
    }


    /*
    ==========================================================
    CREAR VACACIONES
    ==========================================================
    */

    $sql = "
        INSERT INTO vacaciones
        (
            usuario_id,
            fecha_inicio,
            fecha_fin,
            comentario,
            creado_por,
            estado
        )
        VALUES
        (
            :usuario_id,
            :fecha_inicio,
            :fecha_fin,
            :comentario,
            :creado_por,
            'aprobada'
        )
    ";

    $stmt = $this->conexion->prepare($sql);

    $resultado = $stmt->execute([
        ':usuario_id'   => $usuario_id,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin'    => $fecha_fin,
        ':comentario'   => $comentario,
        ':creado_por'   => $creado_por
    ]);


    if($resultado){

        return [
            'ok' => true
        ];

    }

    return [
        'ok' => false,
        'tipo' => 'error'
    ];
}


    /* ==========================================================
       OBTENER TODAS LAS VACACIONES
    ========================================================== */

    public function obtenerVacaciones(){

        $sql = "
            SELECT
                vacaciones.*,
                usuarios.id AS usuario_id,
                usuarios.nombre,
                usuarios.empresa_id,
                empresas.nombre AS empresa

            FROM vacaciones

            INNER JOIN usuarios
                ON vacaciones.usuario_id =
                   usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id =
                   empresas.id

            ORDER BY
                fecha_inicio DESC
        ";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute();


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       VACACIONES POR EMPLEADO
    ========================================================== */

    public function obtenerVacacionesUsuario(
        $usuario_id
    ){

        $sql = "
            SELECT *

            FROM vacaciones

            WHERE usuario_id =
                  :usuario_id

            ORDER BY
                fecha_inicio DESC
        ";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute([
            ':usuario_id' =>
                $usuario_id
        ]);


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       VACACIONES PARA HISTORIAL
       
       Si se indica mes:
       - Solo devuelve periodos que coincidan
         con ese mes.
       - Recorta el inicio y final al mes.
       
       Si no se indica mes:
       - Devuelve todos los periodos aprobados.
    ========================================================== */

    public function obtenerVacacionesEmpleado(
        $usuario_id,
        $mes = ""
    ){

        if($mes == ""){

            $sql = "
                SELECT
                    id,
                    fecha_inicio,
                    fecha_fin,
                    comentario

                FROM vacaciones

                WHERE usuario_id =
                      :usuario_id

                AND estado =
                    'aprobada'

                ORDER BY
                    fecha_inicio DESC
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([
                ':usuario_id' =>
                    $usuario_id
            ]);

        }else{

            $inicioMes =
                $mes . "-01";


            $finMes =
                date(
                    "Y-m-t",
                    strtotime(
                        $inicioMes
                    )
                );


            $sql = "
                SELECT
                    id,
                    fecha_inicio,
                    fecha_fin,
                    comentario

                FROM vacaciones

                WHERE usuario_id =
                      :usuario_id

                AND estado =
                    'aprobada'

                AND fecha_inicio <=
                    :fin_mes

                AND fecha_fin >=
                    :inicio_mes

                ORDER BY
                    fecha_inicio DESC
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([

                ':usuario_id' =>
                    $usuario_id,

                ':inicio_mes' =>
                    $inicioMes,

                ':fin_mes' =>
                    $finMes

            ]);

        }


        $resultado = [];


        while(
            $fila =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                )
        ){

            $inicio =
                $fila['fecha_inicio'];

            $fin =
                $fila['fecha_fin'];


            /*
            ----------------------------------------------------------
            RECORTAR AL MES
            ----------------------------------------------------------
            */

            if($mes != ""){

                if(
                    $inicio <
                    $inicioMes
                ){

                    $inicio =
                        $inicioMes;
                }


                if(
                    $fin >
                    $finMes
                ){

                    $fin =
                        $finMes;
                }

            }


            $timestampInicio =
                strtotime($inicio);

            $timestampFin =
                strtotime($fin);


            $dias =
                floor(
                    (
                        $timestampFin -
                        $timestampInicio
                    ) / 86400
                ) + 1;


            $fila['fecha_inicio'] =
                $inicio;

            $fila['fecha_fin'] =
                $fin;

            $fila['dias'] =
                max(
                    0,
                    $dias
                );


            $resultado[] =
                $fila;
        }


        return $resultado;
    }


    /* ==========================================================
       ELIMINAR VACACIONES COMPLETAS
    ========================================================== */

    public function eliminarVacacionCompleta(
        $id
    ){

        $sql = "
            DELETE FROM vacaciones
            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([
            ':id' =>
                $id
        ]);
    }


    /* ==========================================================
       CREAR FESTIVO
    ========================================================== */

    public function crearFestivo(
        $empresa_id,
        $fecha,
        $nombre
    ){

        $sql = "
            INSERT INTO festivos
            (
                empresa_id,
                fecha,
                nombre
            )
            VALUES
            (
                :empresa_id,
                :fecha,
                :nombre
            )
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([

            ':empresa_id' =>
                $empresa_id,

            ':fecha' =>
                $fecha,

            ':nombre' =>
                $nombre

        ]);
    }


    /* ==========================================================
       COMPATIBILIDAD CON vacaciones.php
    ========================================================== */

    public function crearFestivoEmpresa(
        $empresa_id,
        $fecha,
        $nombre
    ){

        return $this->crearFestivo(
            $empresa_id,
            $fecha,
            $nombre
        );
    }


    /* ==========================================================
       OBTENER FESTIVOS
    ========================================================== */

    public function obtenerFestivos(){

        $sql = "
            SELECT
                festivos.*,
                empresas.nombre AS empresa

            FROM festivos

            LEFT JOIN empresas
                ON festivos.empresa_id =
                   empresas.id

            ORDER BY
                fecha
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute();


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       ELIMINAR FESTIVO
    ========================================================== */

    public function eliminarFestivo(
        $id
    ){

        $sql = "
            DELETE FROM festivos
            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([
            ':id' =>
                $id
        ]);
    }


    /* ==========================================================
       CREAR BLOQUEO
    ========================================================== */

    public function crearBloqueo(
        $empresa_id,
        $fecha_inicio,
        $fecha_fin,
        $motivo = ""
    ){

        $sql = "
            INSERT INTO bloqueos_fechas
            (
                empresa_id,
                fecha_inicio,
                fecha_fin,
                motivo
            )
            VALUES
            (
                :empresa_id,
                :fecha_inicio,
                :fecha_fin,
                :motivo
            )
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([

            ':empresa_id' =>
                $empresa_id,

            ':fecha_inicio' =>
                $fecha_inicio,

            ':fecha_fin' =>
                $fecha_fin,

            ':motivo' =>
                $motivo

        ]);
    }


    /* ==========================================================
       OBTENER BLOQUEOS
    ========================================================== */

    public function obtenerBloqueos(){

        $sql = "
            SELECT
                bloqueos_fechas.*,
                empresas.nombre AS empresa

            FROM bloqueos_fechas

            LEFT JOIN empresas
                ON bloqueos_fechas.empresa_id =
                   empresas.id

            ORDER BY
                fecha_inicio
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute();


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       ELIMINAR BLOQUEO
    ========================================================== */

    public function eliminarBloqueo(
        $id
    ){

        $sql = "
            DELETE FROM bloqueos_fechas
            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([
            ':id' =>
                $id
        ]);
    }


    /* ==========================================================
       EVENTOS PARA CALENDARIO
    ========================================================== */

    public function obtenerEventosCalendario(){

        $eventos = [];


        /*
        ----------------------------------------------------------
        VACACIONES
        ----------------------------------------------------------
        */

        $diasVacaciones = [];


        foreach(
            $this->obtenerVacaciones()
            as $v
        ){

            $inicio =
                strtotime(
                    $v["fecha_inicio"]
                );

            $fin =
                strtotime(
                    $v["fecha_fin"]
                );


            while(
                $inicio <= $fin
            ){

                $fecha =
                    date(
                        "Y-m-d",
                        $inicio
                    );


                if(
                    !isset(
                        $diasVacaciones[
                            $fecha
                        ]
                    )
                ){

                    $diasVacaciones[
                        $fecha
                    ] = [

                        "vacaciones" =>
                            [],

                        "usuarios" =>
                            []

                    ];
                }


                $diasVacaciones[
                    $fecha
                ]["vacaciones"][] = [

                    "id" =>
                        $v["id"],

                    "empleado" =>
                        $v["nombre"],

                    "empresa" =>
                        $v["empresa"]

                ];


                $diasVacaciones[
                    $fecha
                ]["usuarios"][] =
                    (int)$v["usuario_id"];


                $inicio =
                    strtotime(
                        "+1 day",
                        $inicio
                    );
            }

        }


        foreach(
            $diasVacaciones
            as $fecha => $datos
        ){

            $eventos[] = [

                "id" =>
                    "vac_" . $fecha,

                "title" =>
                    "●",

                "start" =>
                    $fecha,

                "end" =>
                    date(
                        "Y-m-d",
                        strtotime(
                            $fecha .
                            " +1 day"
                        )
                    ),

                "color" =>
                    "#22c55e",

                "allDay" =>
                    true,

                "extendedProps" => [

                    "tipo" =>
                        "vacaciones",

                    "vacaciones" =>
                        $datos[
                            "vacaciones"
                        ],

                    "usuarios" =>
                        array_values(
                            array_unique(
                                $datos[
                                    "usuarios"
                                ]
                            )
                        ),

                    "empresa" =>
                        $datos[
                            "vacaciones"
                        ][0]["empresa"]
                        ?? ""

                ]

            ];
        }


        /*
        ----------------------------------------------------------
        FESTIVOS
        ----------------------------------------------------------
        */

        foreach(
            $this->obtenerFestivos()
            as $f
        ){

            $eventos[] = [

                "id" =>
                    "fest_" . $f["id"],

                "title" =>
                    "📅",

                "start" =>
                    $f["fecha"],

                "end" =>
                    date(
                        "Y-m-d",
                        strtotime(
                            $f["fecha"] .
                            " +1 day"
                        )
                    ),

                "color" =>
                    "#f59e0b",

                "allDay" =>
                    true,

                "extendedProps" => [

                    "tipo" =>
                        "festivo",

                    "empresa" =>
                        $f["empresa"]
                        ?? "",

                    "motivo" =>
                        $f["nombre"]
                        ?? ""

                ]

            ];
        }


        /*
        ----------------------------------------------------------
        BLOQUEOS
        ----------------------------------------------------------
        */

        foreach(
            $this->obtenerBloqueos()
            as $b
        ){

            $eventos[] = [

                "id" =>
                    "bloq_" . $b["id"],

                "title" =>
                    "",

                "start" =>
                    $b["fecha_inicio"],

                "end" =>
                    date(
                        "Y-m-d",
                        strtotime(
                            $b["fecha_fin"] .
                            " +1 day"
                        )
                    ),

                "display" =>
                    "background",

                "color" =>
                    "#ef4444",

                "extendedProps" => [

                    "tipo" =>
                        "bloqueo"

                ]

            ];
        }


        return $eventos;
    }


    /* ==========================================================
       COMPATIBILIDAD
    ========================================================== */

    public function obtenerEventos(){

        return
            $this->obtenerEventosCalendario();
    }


    /* ==========================================================
       SOLICITUDES PENDIENTES
    ========================================================== */

    public function obtenerSolicitudesPendientes(){

        $sql = "
            SELECT
                vacaciones.*,
                usuarios.nombre,
                empresas.nombre AS empresa

            FROM vacaciones

            INNER JOIN usuarios
                ON vacaciones.usuario_id =
                   usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id =
                   empresas.id

            WHERE vacaciones.estado =
                  'pendiente'

            ORDER BY
                vacaciones.fecha_inicio ASC
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute();


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       APROBAR
    ========================================================== */

    public function aprobarSolicitud(
        $id
    ){

        $sql = "
            UPDATE vacaciones

            SET estado =
                'aprobada'

            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([
            ':id' =>
                $id
        ]);
    }


    /* ==========================================================
       DENEGAR
    ========================================================== */

    public function denegarSolicitud(
        $id
    ){

        $sql = "
            UPDATE vacaciones

            SET estado =
                'denegada'

            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([
            ':id' =>
                $id
        ]);
    }


    /* ==========================================================
       SOLAPAMIENTO
    ========================================================== */

    public function existeSolapamiento(
        $usuario_id,
        $inicio,
        $fin
    ){

        $sql = "
            SELECT id

            FROM vacaciones

            WHERE usuario_id =
                  :usuario_id

            AND estado <>
                'denegada'

            AND (
                fecha_inicio <= :fin
                AND
                fecha_fin >= :inicio
            )

            LIMIT 1
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([

            ':usuario_id' =>
                $usuario_id,

            ':inicio' =>
                $inicio,

            ':fin' =>
                $fin

        ]);


        return
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       FECHA BLOQUEADA
    ========================================================== */

    public function fechaBloqueada(
        $empresa_id,
        $fecha
    ){

        $sql = "
            SELECT id

            FROM bloqueos_fechas

            WHERE empresa_id =
                  :empresa_id

            AND :fecha BETWEEN
                fecha_inicio
                AND fecha_fin

            LIMIT 1
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([

            ':empresa_id' =>
                $empresa_id,

            ':fecha' =>
                $fecha

        ]);


        return
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       DÍAS CONSUMIDOS
    ========================================================== */

    public function diasConsumidos(
        $usuario_id
    ){

        $sql = "
            SELECT
                fecha_inicio,
                fecha_fin

            FROM vacaciones

            WHERE usuario_id =
                  :usuario_id

            AND estado =
                'aprobada'
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([
            ':usuario_id' =>
                $usuario_id
        ]);


        $dias = 0;


        while(
            $fila =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                )
        ){

            $inicio =
                strtotime(
                    $fila['fecha_inicio']
                );

            $fin =
                strtotime(
                    $fila['fecha_fin']
                );


            $dias +=
                (
                    (
                        $fin -
                        $inicio
                    ) / 86400
                ) + 1;
        }


        return $dias;
    }


    /* ==========================================================
       DÍAS DISPONIBLES
    ========================================================== */

    public function diasDisponibles(
        $usuario_id,
        $total = 30
    ){

        return
            $total -
            $this->diasConsumidos(
                $usuario_id
            );
    }


    /* ==========================================================
       RESUMEN
    ========================================================== */

    public function obtenerResumenEmpleado(
        $usuario_id
    ){

        return [

            "consumidos" =>
                $this->diasConsumidos(
                    $usuario_id
                ),

            "disponibles" =>
                $this->diasDisponibles(
                    $usuario_id
                ),

            "total" =>
                30

        ];
    }


    /* ==========================================================
       TOTAL EVENTOS
    ========================================================== */

    public function totalEventos(){

        return count(
            $this->obtenerEventosCalendario()
        );
    }


    /* ==========================================================
       VACACIONES POR FECHA
    ========================================================== */

    public function obtenerVacacionesPorFecha(
        $fecha
    ){

        $sql = "
            SELECT

                MIN(vacaciones.id)
                    AS id,

                usuarios.id
                    AS usuario_id,

                usuarios.nombre,

                empresas.nombre
                    AS empresa

            FROM vacaciones

            INNER JOIN usuarios
                ON vacaciones.usuario_id =
                   usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id =
                   empresas.id

            WHERE :fecha BETWEEN
                  vacaciones.fecha_inicio
                  AND vacaciones.fecha_fin

            AND vacaciones.estado =
                'aprobada'

            GROUP BY
                usuarios.id,
                usuarios.nombre,
                empresas.nombre

            ORDER BY
                usuarios.nombre
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([
            ":fecha" =>
                $fecha
        ]);


        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /* ==========================================================
       ELIMINAR UN DÍA DE VACACIONES
    ========================================================== */

    public function eliminarVacaciones(
        $id,
        $fecha
    ){

        $sql = "
            SELECT *

            FROM vacaciones

            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([
            ":id" =>
                $id
        ]);


        $vacacion =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        if(!$vacacion){
            return;
        }


        $inicio =
            $vacacion["fecha_inicio"];

        $fin =
            $vacacion["fecha_fin"];


        /*
        ----------------------------------------------------------
        SOLO UN DÍA
        ----------------------------------------------------------
        */

        if($inicio == $fin){

            $sql = "
                DELETE FROM vacaciones
                WHERE id = :id
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([
                ":id" =>
                    $id
            ]);

            return;
        }


        /*
        ----------------------------------------------------------
        PRIMER DÍA
        ----------------------------------------------------------
        */

        if($fecha == $inicio){

            $nuevoInicio =
                date(
                    "Y-m-d",
                    strtotime(
                        $inicio .
                        " +1 day"
                    )
                );


            $sql = "
                UPDATE vacaciones

                SET fecha_inicio =
                    :inicio

                WHERE id = :id
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([

                ":inicio" =>
                    $nuevoInicio,

                ":id" =>
                    $id

            ]);

            return;
        }


        /*
        ----------------------------------------------------------
        ÚLTIMO DÍA
        ----------------------------------------------------------
        */

        if($fecha == $fin){

            $nuevoFin =
                date(
                    "Y-m-d",
                    strtotime(
                        $fin .
                        " -1 day"
                    )
                );


            $sql = "
                UPDATE vacaciones

                SET fecha_fin =
                    :fin

                WHERE id = :id
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([

                ":fin" =>
                    $nuevoFin,

                ":id" =>
                    $id

            ]);

            return;
        }


        /*
        ----------------------------------------------------------
        DÍA INTERMEDIO
        ----------------------------------------------------------
        */

        $finPrimera =
            date(
                "Y-m-d",
                strtotime(
                    $fecha .
                    " -1 day"
                )
            );


        $inicioSegunda =
            date(
                "Y-m-d",
                strtotime(
                    $fecha .
                    " +1 day"
                )
            );


        /*
        ACTUALIZAR PRIMER PERIODO
        */

        $sql = "
            UPDATE vacaciones

            SET fecha_fin =
                :fin

            WHERE id = :id
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([

            ":fin" =>
                $finPrimera,

            ":id" =>
                $id

        ]);


        /*
        CREAR SEGUNDO PERIODO
        */

        $sql = "
            INSERT INTO vacaciones
            (
                usuario_id,
                fecha_inicio,
                fecha_fin,
                estado,
                comentario
            )

            VALUES
            (
                :usuario,
                :inicio,
                :fin,
                :estado,
                :comentario
            )
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute([

            ":usuario" =>
                $vacacion[
                    "usuario_id"
                ],

            ":inicio" =>
                $inicioSegunda,

            ":fin" =>
                $fin,

            ":estado" =>
                $vacacion[
                    "estado"
                ],

            ":comentario" =>
                $vacacion[
                    "comentario"
                ]

        ]);
    }

/* ==========================================================
   FESTIVOS POR FECHA
========================================================== */

public function obtenerFestivosPorFecha($fecha){

    $sql = "SELECT
                festivos.id,
                festivos.empresa_id,
                festivos.fecha,
                festivos.nombre,
                empresas.nombre AS empresa
            FROM festivos

            LEFT JOIN empresas
                ON festivos.empresa_id = empresas.id

            WHERE festivos.fecha = :fecha

            ORDER BY empresas.nombre, festivos.nombre";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ':fecha' => $fecha
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}