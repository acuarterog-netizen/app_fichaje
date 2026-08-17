<?php

require_once "../config/Database.php";

class Bajas {

    private $conexion;


    public function __construct(){

        $database =
            new Database();

        $this->conexion =
            $database->conectar();
    }


    /* ==========================================================
       CREAR BAJA
    ========================================================== */

    public function crearBaja(
        $usuario_id,
        $tipo,
        $fecha_inicio,
        $fecha_fin,
        $comentario = ""
    ){

        $sql = "
            INSERT INTO bajas
            (
                usuario_id,
                tipo,
                fecha_inicio,
                fecha_fin,
                comentario
            )
            VALUES
            (
                :usuario_id,
                :tipo,
                :fecha_inicio,
                :fecha_fin,
                :comentario
            )
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        return $stmt->execute([

            ":usuario_id" =>
                $usuario_id,

            ":tipo" =>
                $tipo,

            ":fecha_inicio" =>
                $fecha_inicio,

            ":fecha_fin" =>
                $fecha_fin,

            ":comentario" =>
                $comentario

        ]);
    }


    /* ==========================================================
       EVENTOS CALENDARIO
    ========================================================== */

    public function obtenerEventosCalendario(){

        $sql = "
            SELECT

                bajas.id,

                usuarios.id AS usuario_id,

                usuarios.empresa_id,

                usuarios.nombre,

                bajas.fecha_inicio,

                bajas.fecha_fin,

                tipos_baja.nombre AS tipo,

                tipos_baja.color

            FROM bajas

            INNER JOIN usuarios
                ON bajas.usuario_id =
                   usuarios.id

            LEFT JOIN tipos_baja
                ON bajas.tipo =
                   tipos_baja.nombre
        ";


        $stmt =
            $this->conexion->prepare(
                $sql
            );


        $stmt->execute();


        $eventos = [];


        while(
            $fila =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                )
        ){

            $eventos[] = [

                "id" =>
                    $fila["id"],

                "title" =>
                    $fila["nombre"],

                "start" =>
                    $fila["fecha_inicio"],

                "end" =>
                    date(
                        "Y-m-d",
                        strtotime(
                            $fila["fecha_fin"] .
                            " +1 day"
                        )
                    ),

                "color" =>
                    $fila["color"]
                    ?: "#dc3545",

                "extendedProps" => [

                    "empresa" =>
                        $fila[
                            "empresa_id"
                        ],

                    "usuario" =>
                        $fila[
                            "usuario_id"
                        ],

                    "tipo" =>
                        $fila["tipo"]
                        ?? $fila["tipo"]

                ]

            ];
        }


        return $eventos;
    }


    /* ==========================================================
       BAJAS POR FECHA
    ========================================================== */

    public function obtenerBajasPorFecha(
        $fecha
    ){

        $sql = "
            SELECT

                MIN(bajas.id) AS id,

                usuarios.id AS usuario_id,

                usuarios.nombre,

                empresas.nombre AS empresa,

                tipos_baja.nombre AS tipo,

                tipos_baja.color

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

            WHERE :fecha BETWEEN
                  bajas.fecha_inicio
                  AND bajas.fecha_fin

            GROUP BY

                usuarios.id,
                usuarios.nombre,
                empresas.nombre,
                tipos_baja.nombre,
                tipos_baja.color

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
       BAJAS DE UN EMPLEADO
       
       Si se indica mes:
       - Solo devuelve bajas que afectan
         a ese mes.
       - Recorta el periodo al mes.
       
       Si no se indica mes:
       - Devuelve todas las bajas.
    ========================================================== */

    public function obtenerBajasEmpleado(
        $usuario_id,
        $mes = ""
    ){

        if($mes == ""){

            $sql = "
                SELECT

                    bajas.id,

                    bajas.usuario_id,

                    bajas.tipo,

                    tipos_baja.nombre AS tipo_nombre,

                    bajas.fecha_inicio,

                    bajas.fecha_fin,

                    bajas.comentario

                FROM bajas

                LEFT JOIN tipos_baja
                    ON bajas.tipo =
                       tipos_baja.nombre

                WHERE bajas.usuario_id =
                      :usuario_id

                ORDER BY
                    bajas.fecha_inicio DESC
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([
                ":usuario_id" =>
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

                    bajas.id,

                    bajas.usuario_id,

                    bajas.tipo,

                    tipos_baja.nombre AS tipo_nombre,

                    bajas.fecha_inicio,

                    bajas.fecha_fin,

                    bajas.comentario

                FROM bajas

                LEFT JOIN tipos_baja
                    ON bajas.tipo =
                       tipos_baja.nombre

                WHERE bajas.usuario_id =
                      :usuario_id

                AND bajas.fecha_inicio <=
                    :fin_mes

                AND bajas.fecha_fin >=
                    :inicio_mes

                ORDER BY
                    bajas.fecha_inicio DESC
            ";


            $stmt =
                $this->conexion->prepare(
                    $sql
                );


            $stmt->execute([

                ":usuario_id" =>
                    $usuario_id,

                ":inicio_mes" =>
                    $inicioMes,

                ":fin_mes" =>
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
                $fila[
                    "fecha_inicio"
                ];

            $fin =
                $fila[
                    "fecha_fin"
                ];


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


            $fila[
                "fecha_inicio"
            ] =
                $inicio;


            $fila[
                "fecha_fin"
            ] =
                $fin;


            $fila[
                "tipo"
            ] =
                $fila[
                    "tipo_nombre"
                ]
                ??
                $fila[
                    "tipo"
                ];


            $fila[
                "dias"
            ] =
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
       ELIMINAR BAJA
    ========================================================== */

    public function eliminarBaja(
        $id
    ){

        $sql = "
            DELETE FROM bajas
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
    }

}