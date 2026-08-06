<?php

require_once "../config/Database.php";

class TipoBaja{

    private $conexion;

    public function __construct(){

        $database = new Database();
        $this->conexion = $database->conectar();

    }

    public function crear($empresa_id,$nombre,$color){

        $sql="INSERT INTO tipos_baja
              (empresa_id,nombre,color)
              VALUES
              (:empresa_id,:nombre,:color)";

        $stmt=$this->conexion->prepare($sql);

        $stmt->execute([

            ":empresa_id"=>$empresa_id,
            ":nombre"=>$nombre,
            ":color"=>$color

        ]);

    }

    public function obtenerTipos(){

    $sql = "SELECT *

            FROM tipos_baja

            ORDER BY nombre";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

}