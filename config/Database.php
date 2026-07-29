-- Active: 1778325147226@@localhost@3306
<?php
class Database {
    private $host = "localhost";
    private $dbname = "control_horario";
    private $user = "root";
    private $password = "";

    public function conectar() {

        try {

            $conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->user,
                $this->password
            );

            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conexion;

        } catch (PDOException $e) {

            die("Error de conexión: " . $e->getMessage());

        }
    }
}