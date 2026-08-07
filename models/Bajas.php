<?php

require_once "../config/Database.php";

class Bajas {

    private $conexion;

    public function __construct(){

        $database = new Database();
        $this->conexion = $database->conectar();

    }
public function crearBaja(

    $usuario_id,
    $tipo,
    $fecha_inicio,
    $fecha_fin,
    $comentario = ""

){

    $sql = "INSERT INTO bajas(

                usuario_id,
                tipo,
                fecha_inicio,
                fecha_fin,
                comentario

            )

            VALUES(

                :usuario_id,
                :tipo,
                :fecha_inicio,
                :fecha_fin,
                :comentario

            )";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([

        ":usuario_id"=>$usuario_id,
        ":tipo"=>$tipo,
        ":fecha_inicio"=>$fecha_inicio,
        ":fecha_fin"=>$fecha_fin,
        ":comentario"=>$comentario

    ]);

}
public function obtenerEventosCalendario(){

    $sql = "SELECT

            bajas.id,

            usuarios.id AS usuario_id,

            usuarios.empresa_id,

            usuarios.nombre,

            bajas.fecha_inicio,

            bajas.fecha_fin,

            tipos_baja.color

        FROM bajas

        INNER JOIN usuarios
            ON bajas.usuario_id = usuarios.id

        LEFT JOIN tipos_baja
            ON bajas.tipo = tipos_baja.nombre";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute();

    $eventos = [];

    while($fila = $stmt->fetch(PDO::FETCH_ASSOC)){

        $eventos[] = [

    "id"=>$fila["id"],

    "title"=>$fila["nombre"],

    "start"=>$fila["fecha_inicio"],

    "end"=>date(
        "Y-m-d",
        strtotime($fila["fecha_fin"]." +1 day")
    ),

    "color"=>$fila["color"] ?: "#dc3545",

    "extendedProps"=>[

        "empresa"=>$fila["empresa_id"],

        "usuario"=>$fila["usuario_id"]

    ]

];

    }

    return $eventos;

}

public function obtenerBajasPorFecha($fecha){

    $sql = "SELECT

                MIN(bajas.id) AS id,
                usuarios.id AS usuario_id,
                usuarios.nombre,
                empresas.nombre AS empresa,
                tipos_baja.nombre AS tipo,
                tipos_baja.color

            FROM bajas

            INNER JOIN usuarios
                ON bajas.usuario_id = usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

            LEFT JOIN tipos_baja
                ON bajas.tipo = tipos_baja.nombre

            WHERE :fecha BETWEEN bajas.fecha_inicio
                             AND bajas.fecha_fin

            GROUP BY
                usuarios.id,
                usuarios.nombre,
                empresas.nombre,
                tipos_baja.nombre,
                tipos_baja.color

            ORDER BY usuarios.nombre";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ":fecha"=>$fecha
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

public function eliminarBaja($id){

    $sql = "DELETE FROM bajas
            WHERE id = :id";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ":id"=>$id
    ]);

}
}