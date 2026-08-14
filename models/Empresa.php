<?php

require_once "../config/Database.php";

class Empresa {

    private $conexion;
    private $table = "empresas";


    public function __construct() {

        $database = new Database();

        $this->conexion = $database->conectar();

    }


    /* ==========================================================================
       OBTENER TODAS
    ========================================================================== */

    public function obtenerEmpresas() {

        $sql = "SELECT *
                FROM " . $this->table . "
                ORDER BY nombre ASC";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       OBTENER POR ID
    ========================================================================== */

    public function obtenerEmpresaPorId($id) {

        $sql = "SELECT *
                FROM " . $this->table . "
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       CREAR EMPRESA
    ========================================================================== */

    public function crearEmpresa(
        $nombre,
        $cif,
        $titular,
        $direccion,
        $hora_entrada,
        $hora_salida,
        $descanso,
        $horas_jornada
    ) {

        $sql = "INSERT INTO " . $this->table . "
                (
                    nombre,
                    cif,
                    titular,
                    direccion,
                    hora_entrada,
                    hora_salida,
                    descanso,
                    horas_jornada
                )
                VALUES
                (
                    :nombre,
                    :cif,
                    :titular,
                    :direccion,
                    :hora_entrada,
                    :hora_salida,
                    :descanso,
                    :horas_jornada
                )";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':nombre' => $nombre,

            ':cif' => $cif,

            ':titular' => $titular,

            ':direccion' => $direccion,

            ':hora_entrada' => $hora_entrada,

            ':hora_salida' => $hora_salida,

            ':descanso' => $descanso,

            ':horas_jornada' => $horas_jornada

        ]);

    }


    /* ==========================================================================
       EDITAR EMPRESA
    ========================================================================== */

    public function editarEmpresa(
        $id,
        $nombre,
        $cif,
        $titular,
        $direccion,
        $hora_entrada,
        $hora_salida,
        $descanso,
        $horas_jornada
    ) {

        $sql = "UPDATE " . $this->table . "
                SET
                    nombre = :nombre,
                    cif = :cif,
                    titular = :titular,
                    direccion = :direccion,
                    hora_entrada = :hora_entrada,
                    hora_salida = :hora_salida,
                    descanso = :descanso,
                    horas_jornada = :horas_jornada

                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':id' => $id,

            ':nombre' => $nombre,

            ':cif' => $cif,

            ':titular' => $titular,

            ':direccion' => $direccion,

            ':hora_entrada' => $hora_entrada,

            ':hora_salida' => $hora_salida,

            ':descanso' => $descanso,

            ':horas_jornada' => $horas_jornada

        ]);

    }


    /* ==========================================================================
       FILTRAR EMPRESAS
    ========================================================================== */

    public function filtrarEmpresas($busqueda = "") {

        $sql = "SELECT *
                FROM " . $this->table . "
                WHERE 1=1";

        $params = [];

        if($busqueda != "") {

            $sql .= " AND (
                        nombre LIKE :busqueda
                        OR cif LIKE :busqueda
                        OR titular LIKE :busqueda
                        OR direccion LIKE :busqueda
                    )";

            $params[':busqueda'] = "%" . $busqueda . "%";

        }

        $sql .= " ORDER BY nombre ASC";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       ELIMINAR EMPRESA
    ========================================================================== */

    public function eliminarEmpresa($id) {

        $sql = "DELETE
                FROM " . $this->table . "
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);

    }

}