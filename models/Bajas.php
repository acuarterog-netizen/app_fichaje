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

            "color"=>$fila["color"] ?: "#dc3545"

        ];

    }

    return $eventos;

}
}