<?php

require_once "../config/Database.php";

class Usuario {

    private $conexion;
    private $table = "usuarios";

    public function __construct() {

        $database = new Database();
        $this->conexion = $database->conectar();

    }

    /* ==========================================================================
       OBTENER TODOS
    ========================================================================== */

    public function obtenerUsuarios() {

        $sql = "SELECT usuarios.*,
                       empresas.nombre AS empresa_nombre

                FROM usuarios

                LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

                ORDER BY empresas.nombre ASC,
                         usuarios.nombre ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       CREAR USUARIO
    ========================================================================== */

    public function crearUsuario(

        $nombre,
        $dni,
        $telefono,
        $email,
        $password,
        $rol,
        $empresa_id = null

    ) {

        if($this->existeEmail($email)){
            return "email_existe";
        }

        if($this->existeDNI($dni)){
            return "dni_existe";
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO ".$this->table."

        (

            nombre,
            dni,
            telefono,
            email,
            password,
            rol,
            empresa_id

        )

        VALUES

        (

            :nombre,
            :dni,
            :telefono,
            :email,
            :password,
            :rol,
            :empresa_id

        )";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':nombre'=>$nombre,
            ':dni'=>$dni,
            ':telefono'=>$telefono,
            ':email'=>$email,
            ':password'=>$passwordHash,
            ':rol'=>$rol,
            ':empresa_id'=>$empresa_id

        ]);

        return "ok";

    }

    /* ==========================================================================
       LOGIN
    ========================================================================== */

    public function login($email,$password){

        $sql = "SELECT *
                FROM ".$this->table."
                WHERE email=:email
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':email'=>$email

        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario){

            if(password_verify($password,$usuario['password'])){

                return $usuario;

            }

        }

        return false;

    }

    /* ==========================================================================
       OBTENER USUARIO POR ID
    ========================================================================== */

    public function obtenerUsuarioPorId($id){

        $sql = "SELECT *
                FROM ".$this->table."
                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':id'=>$id

        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       CONTAR USUARIOS
    ========================================================================== */

    public function contarUsuarios(){

        $sql = "SELECT COUNT(*) AS total
                FROM ".$this->table;

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       EDITAR USUARIO
    ========================================================================== */

    public function editarUsuario(

        $id,
        $nombre,
        $dni,
        $telefono,
        $email,
        $rol,
        $empresa_id = null

    ){

        if($this->existeEmail($email,$id)){
            return "email_existe";
        }

        if($this->existeDNI($dni,$id)){
            return "dni_existe";
        }

        $sql = "UPDATE ".$this->table."

                SET

                    nombre=:nombre,
                    dni=:dni,
                    telefono=:telefono,
                    email=:email,
                    rol=:rol,
                    empresa_id=:empresa_id

                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':nombre'=>$nombre,
            ':dni'=>$dni,
            ':telefono'=>$telefono,
            ':email'=>$email,
            ':rol'=>$rol,
            ':empresa_id'=>$empresa_id,
            ':id'=>$id

        ]);

        return "ok";

    }

        /* ==========================================================================
       ELIMINAR USUARIO
    ========================================================================== */

    public function eliminarUsuario($id){

        $sql = "DELETE
                FROM ".$this->table."
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':id'=>$id

        ]);

    }

    /* ==========================================================================
       ACTUALIZAR PERFIL
    ========================================================================== */

    public function actualizarPerfil($id,$nombre,$email){

        $sql = "UPDATE ".$this->table."

                SET

                    nombre=:nombre,
                    email=:email

                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':nombre'=>$nombre,
            ':email'=>$email,
            ':id'=>$id

        ]);

    }

    /* ==========================================================================
       FILTRAR USUARIOS
    ========================================================================== */

    public function filtrarUsuarios($busqueda = "", $empresa_id = ""){

        $sql = "SELECT usuarios.*,
                       empresas.nombre AS empresa_nombre

                FROM usuarios

                LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

                WHERE 1=1";

        $params = [];

        if($busqueda != ""){

            $sql .= " AND (

                        usuarios.nombre LIKE :busqueda

                        OR usuarios.email LIKE :busqueda

                     )";

            $params[':busqueda'] = "%".$busqueda."%";

        }

        if($empresa_id != ""){

            if($empresa_id == "sin_empresa"){

                $sql .= " AND usuarios.empresa_id IS NULL";

            }else{

                $sql .= " AND usuarios.empresa_id = :empresa_id";

                $params[':empresa_id'] = $empresa_id;

            }

        }

        $sql .= " ORDER BY usuarios.nombre ASC";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       CAMBIAR PASSWORD
    ========================================================================== */

    public function cambiarPassword(

        $id,
        $passwordActual,
        $passwordNueva

    ){

        $usuario = $this->obtenerUsuarioPorId($id);

        if(!$usuario){

            return "usuario_no_existe";

        }

        if(!password_verify($passwordActual,$usuario['password'])){

            return "password_incorrecta";

        }

        $nuevoHash = password_hash(

            $passwordNueva,
            PASSWORD_DEFAULT

        );

        $sql = "UPDATE ".$this->table."

                SET password=:password

                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':password'=>$nuevoHash,
            ':id'=>$id

        ]);

        return "ok";

    }

    /* ==========================================================================
       COMPROBAR EMAIL
    ========================================================================== */

    public function existeEmail($email,$idExcluir=null){

        $sql = "SELECT id
                FROM ".$this->table."
                WHERE email=:email";

        if($idExcluir != null){

            $sql .= " AND id<>:id";

        }

        $sql .= " LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        $params=[

            ':email'=>$email

        ];

        if($idExcluir != null){

            $params[':id']=$idExcluir;

        }

        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       COMPROBAR DNI
    ========================================================================== */

    public function existeDNI($dni,$idExcluir=null){

        $sql = "SELECT id
                FROM ".$this->table."
                WHERE dni=:dni";

        if($idExcluir != null){

            $sql .= " AND id<>:id";

        }

        $sql .= " LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        $params=[

            ':dni'=>$dni

        ];

        if($idExcluir != null){

            $params[':id']=$idExcluir;

        }

        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       OBTENER EMPLEADOS
    ========================================================================== */

    public function obtenerEmpleados($busqueda = "", $empresa_id = ""){

        $sql = "SELECT usuarios.*,
                       empresas.nombre AS empresa_nombre

                FROM usuarios

                LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

                WHERE usuarios.rol IN ('empleado','encargado')";

        $params=[];

        if($busqueda!=""){

            $sql.=" AND (

                        usuarios.nombre LIKE :busqueda

                        OR usuarios.email LIKE :busqueda

                    )";

            $params[':busqueda']="%".$busqueda."%";

        }

        if($empresa_id!=""){

            $sql.=" AND usuarios.empresa_id=:empresa_id";

            $params[':empresa_id']=$empresa_id;

        }

        $sql.=" ORDER BY usuarios.nombre ASC";

        $stmt=$this->conexion->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /* ==========================================================================
       EMPLEADOS SIN FICHAR HOY
    ========================================================================== */

    public function obtenerEmpleadosSinFicharHoy($busqueda="", $empresa_id=""){

        $fecha=date("Y-m-d");

        $sql="SELECT usuarios.*,
                     empresas.nombre AS empresa_nombre

              FROM usuarios

              LEFT JOIN empresas
              ON usuarios.empresa_id=empresas.id

              WHERE usuarios.rol IN ('empleado','encargado')

              AND usuarios.id NOT IN(

                    SELECT usuario_id

                    FROM fichajes

                    WHERE fecha=:fecha

              )";

        $params=[

            ':fecha'=>$fecha

        ];

        if($busqueda!=""){

            $sql.=" AND (

                        usuarios.nombre LIKE :busqueda

                        OR usuarios.email LIKE :busqueda

                    )";

            $params[':busqueda']="%".$busqueda."%";

        }

        if($empresa_id!=""){

            $sql.=" AND usuarios.empresa_id=:empresa_id";

            $params[':empresa_id']=$empresa_id;

        }

        $sql.=" ORDER BY usuarios.nombre ASC";

        $stmt=$this->conexion->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}