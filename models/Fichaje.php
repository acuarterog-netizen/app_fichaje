<?php

require_once "../config/Database.php";

class Fichaje {

    private $conexion;
    private $table = "fichajes";


    public function __construct() {

        $database = new Database();

        $this->conexion = $database->conectar();

    }


   /* ==========================================================================
   GENERAR HORA ALEATORIA (+/- 5 MINUTOS)
   ========================================================================== */

private function generarHoraAleatoria($horaBase){

    $base = strtotime($horaBase);

    $desfase = rand(-5,5);

    return date("H:i:s",$base + ($desfase*60));

}


    /* ==========================================================================
       VERIFICAR SI YA FICHÓ HOY
    ========================================================================== */

    public function yaFichoHoy($usuario_id) {

        $fecha = date("Y-m-d");

        $sql = "SELECT * FROM " . $this->table . "
                WHERE usuario_id = :usuario_id
                AND fecha = :fecha";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':usuario_id' => $usuario_id,
            ':fecha' => $fecha

        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       COMPROBAR SI EL EMPLEADO ESTÁ DE VACACIONES O DE BAJA
    ========================================================================== */

    public function estadoNoFichar($usuario_id, $fecha = null) {

        /*
        Si no se indica fecha usamos la fecha de hoy
        */

        if($fecha === null){

            $fecha = date("Y-m-d");

        }


        /*
        ==========================================================
        VACACIONES
        ==========================================================
        */

        $sqlVacaciones = "
            SELECT id
            FROM vacaciones
            WHERE usuario_id = :usuario_id
            AND estado = 'aprobada'
            AND :fecha BETWEEN fecha_inicio AND fecha_fin
            LIMIT 1
        ";

        $stmtVacaciones =
            $this->conexion->prepare($sqlVacaciones);

        $stmtVacaciones->execute([

            ':usuario_id' => $usuario_id,
            ':fecha' => $fecha

        ]);


        if($stmtVacaciones->fetch(PDO::FETCH_ASSOC)){

            return "vacaciones";

        }


        /*
        ==========================================================
        BAJAS
        ==========================================================
        */

        $sqlBajas = "
            SELECT id
            FROM bajas
            WHERE usuario_id = :usuario_id
            AND :fecha BETWEEN fecha_inicio AND fecha_fin
            LIMIT 1
        ";

        $stmtBajas =
            $this->conexion->prepare($sqlBajas);

        $stmtBajas->execute([

            ':usuario_id' => $usuario_id,
            ':fecha' => $fecha

        ]);


        if($stmtBajas->fetch(PDO::FETCH_ASSOC)){

            return "baja";

        }


        /*
        ==========================================================
        PUEDE FICHAR
        ==========================================================
        */

        return false;

    }


    /* ==========================================================================
   CREAR FICHAJE AUTOMÁTICO
   ========================================================================== */

public function crearFichajeAutomatico($usuario_id){


    /*
    ==========================================================
    COMPROBAR VACACIONES / BAJA
    ==========================================================
    */

    $estado = $this->estadoNoFichar(
        $usuario_id,
        date("Y-m-d")
    );


    /*
    Si está de vacaciones o de baja
    no se crea ningún fichaje
    */

    if($estado){

        return false;

    }


    /*
    ==========================================================
    COMPROBAR SI YA FICHÓ HOY
    ==========================================================
    */

    if($this->yaFichoHoy($usuario_id)){

        return false;

    }


    $fecha = date("Y-m-d");


    /*
    ======================================================
    ENTRADA
    08:00 ±5 minutos
    ======================================================
    */

    $entradaTimestamp = strtotime(

        $this->generarHoraAleatoria("08:00:00")

    );


    /*
    ======================================================
    DESCANSO

    Siempre 4 horas después de entrar.
    ======================================================
    */

    $inicioDescansoTimestamp =
        $entradaTimestamp + (4*60*60);


    /*
    ======================================================
    FIN DESCANSO

    Siempre exactamente 1 hora.
    ======================================================
    */

    $finDescansoTimestamp =
        $inicioDescansoTimestamp + (60*60);


    /*
    ======================================================
    SALIDA

    4 horas después del descanso.

    Total trabajado = 8 horas
    Descanso = 1 hora
    Total presencia = 9 horas
    ======================================================
    */

    $salidaTimestamp =
        $finDescansoTimestamp + (4*60*60);


    $horaEntrada =
        date("H:i:s",$entradaTimestamp);

    $inicioDescanso =
        date("H:i:s",$inicioDescansoTimestamp);

    $finDescanso =
        date("H:i:s",$finDescansoTimestamp);

    $horaSalida =
        date("H:i:s",$salidaTimestamp);


    /*
    ======================================================
    INSERTAR FICHAJE
    ======================================================
    */

    $sql = "INSERT INTO ".$this->table."

    (
        usuario_id,
        fecha,
        hora_entrada,
        inicio_descanso,
        fin_descanso,
        hora_salida
    )

    VALUES

    (
        :usuario_id,
        :fecha,
        :hora_entrada,
        :inicio_descanso,
        :fin_descanso,
        :hora_salida
    )";


    $stmt =
        $this->conexion->prepare($sql);


    return $stmt->execute([

        ':usuario_id'=>$usuario_id,

        ':fecha'=>$fecha,

        ':hora_entrada'=>$horaEntrada,

        ':inicio_descanso'=>$inicioDescanso,

        ':fin_descanso'=>$finDescanso,

        ':hora_salida'=>$horaSalida

    ]);

}


    /* ==========================================================================
       HISTORIAL USUARIO
    ========================================================================== */

    public function obtenerHistorialUsuario($usuario_id) {

        $sql = "SELECT * FROM " . $this->table . "

                WHERE usuario_id = :usuario_id

                ORDER BY fecha DESC";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':usuario_id' => $usuario_id

        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       TODOS LOS FICHAJES
    ========================================================================== */

    public function obtenerTodosLosFichajes() {

        $sql = "SELECT fichajes.*,

                       usuarios.nombre,

                       empresas.nombre AS empresa_nombre

                FROM fichajes

                INNER JOIN usuarios
                ON fichajes.usuario_id = usuarios.id

                LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

                ORDER BY fecha DESC";


        $stmt =
            $this->conexion->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       CONTAR FICHAJES HOY
    ========================================================================== */

    public function contarFichajesHoy() {

        $fecha = date("Y-m-d");


        $sql = "SELECT COUNT(*) as total

                FROM " . $this->table . "

                WHERE fecha = :fecha";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute([

            ':fecha' => $fecha

        ]);


        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       ÚLTIMOS FICHAJES
    ========================================================================== */

    public function ultimosFichajes($limite = 5) {

        $sql = "SELECT fichajes.*,

                       usuarios.nombre,

                       empresas.nombre AS empresa_nombre

                FROM fichajes

                INNER JOIN usuarios
                ON fichajes.usuario_id = usuarios.id

                LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

                ORDER BY fichajes.id DESC

                LIMIT $limite";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute();


        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       OBTENER FICHAJE POR ID
    ========================================================================== */

    public function obtenerFichajePorId($id) {

        $sql = "SELECT * FROM " . $this->table . "

                WHERE id = :id";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute([

            ':id' => $id

        ]);


        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       EDITAR FICHAJE
    ========================================================================== */

    public function editarFichaje(

        $id,

        $horaEntrada,

        $inicioDescanso,

        $finDescanso,

        $horaSalida

    ) {


        $sql = "UPDATE " . $this->table . "

                SET

                    hora_entrada = :hora_entrada,

                    inicio_descanso = :inicio_descanso,

                    fin_descanso = :fin_descanso,

                    hora_salida = :hora_salida

                WHERE id = :id";


        $stmt =
            $this->conexion->prepare($sql);


        return $stmt->execute([

            ':hora_entrada' =>
                $horaEntrada,

            ':inicio_descanso' =>
                $inicioDescanso,

            ':fin_descanso' =>
                $finDescanso,

            ':hora_salida' =>
                $horaSalida,

            ':id' =>
                $id

        ]);

    }


    /* ==========================================================================
       FILTRAR FICHAJES
    ========================================================================== */

    public function filtrarFichajes(

        $busqueda = "",

        $fecha = "",

        $mes = "",

        $empresa_id = ""

    ) {


        $sql = "SELECT fichajes.*,

                       usuarios.nombre,

                       empresas.nombre AS empresa_nombre

                FROM fichajes

                INNER JOIN usuarios

                ON fichajes.usuario_id = usuarios.id

                LEFT JOIN empresas

                ON usuarios.empresa_id = empresas.id

                WHERE 1=1";


        $params = [];


        /* =========================
           BUSCADOR
        ========================= */

        if($busqueda != "") {

            $sql .=
                " AND usuarios.nombre LIKE :busqueda";

            $params[':busqueda'] =
                "%" . $busqueda . "%";

        }


        /* =========================
           FECHA
        ========================= */

        if($fecha != "") {

            $sql .=
                " AND fichajes.fecha = :fecha";

            $params[':fecha'] =
                $fecha;

        }


        /* =========================
           MES
        ========================= */

        if($mes != "") {

            $sql .=
                " AND DATE_FORMAT(
                    fichajes.fecha,
                    '%Y-%m'
                ) = :mes";

            $params[':mes'] =
                $mes;

        }


        /* =========================
           EMPRESA
        ========================= */

        if($empresa_id != "") {

            $sql .=
                " AND usuarios.empresa_id = :empresa_id";

            $params[':empresa_id'] =
                $empresa_id;

        }


        /* =========================
           ORDEN
        ========================= */

        $sql .=
            " ORDER BY fichajes.fecha DESC";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute($params);


        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /* ==========================================================================
       ELIMINAR FICHAJE
    ========================================================================== */

    public function eliminarFichaje($id) {

        $sql =
            "DELETE FROM " . $this->table . "

             WHERE id = :id";


        $stmt =
            $this->conexion->prepare($sql);


        return $stmt->execute([

            ':id' => $id

        ]);

    }


    /* ==========================================================================
   CREAR FICHAJES MASIVOS
   ========================================================================== */

public function crearFichajesMasivos($usuarios){

    $fecha = date("Y-m-d");

    $horaBase = time();

    $total = 0;


    foreach($usuarios as $usuario_id){


        /*
        ==========================================================
        COMPROBAR VACACIONES / BAJA
        ==========================================================
        */

        $estado = $this->estadoNoFichar(
            $usuario_id,
            $fecha
        );


        /*
        Si está de vacaciones o de baja
        simplemente se salta ese empleado.
        */

        if($estado){

            continue;

        }


        /*
        ==========================================================
        COMPROBAR SI YA FICHÓ
        ==========================================================
        */

        if($this->yaFichoHoy($usuario_id)){

            continue;

        }


        /*
        ======================================
        Entrada = Hora actual ±5 minutos
        ======================================
        */

        $entrada =
            $horaBase + rand(-5,5)*60;


        /*
        ======================================
        Descanso
        ======================================
        */

        $inicioDescanso =
            $entrada + (4*60*60);


        $finDescanso =
            $inicioDescanso + (60*60);


        /*
        ======================================
        Salida

        8 horas trabajadas
        1 hora descanso
        ======================================
        */

        $salida =
            $finDescanso + (4*60*60);


        /*
        ======================================
        INSERTAR
        ======================================
        */

        $sql = "INSERT INTO ".$this->table."

        (
            usuario_id,
            fecha,
            hora_entrada,
            inicio_descanso,
            fin_descanso,
            hora_salida
        )

        VALUES

        (
            :usuario_id,
            :fecha,
            :hora_entrada,
            :inicio_descanso,
            :fin_descanso,
            :hora_salida
        )";


        $stmt =
            $this->conexion->prepare($sql);


        $stmt->execute([

            ':usuario_id'=>$usuario_id,

            ':fecha'=>$fecha,

            ':hora_entrada'=>
                date("H:i:s",$entrada),

            ':inicio_descanso'=>
                date("H:i:s",$inicioDescanso),

            ':fin_descanso'=>
                date("H:i:s",$finDescanso),

            ':hora_salida'=>
                date("H:i:s",$salida)

        ]);


        $total++;

    }


    return $total;

}


    /* ==========================================================================
       CREAR FICHAJE MANUAL
    ========================================================================== */

    public function crearFichajeManual(

        $usuario_id,

        $fecha,

        $horaEntrada,

        $inicioDescanso,

        $finDescanso,

        $horaSalida

    ) {


        /*
        ==========================================================
        COMPROBAR VACACIONES / BAJA
        ==========================================================
        */

        $estado = $this->estadoNoFichar(

            $usuario_id,

            $fecha

        );


        /*
        Si está de vacaciones o de baja
        NO se crea el fichaje.
        */

        if($estado){

            return false;

        }


        /*
        ==========================================================
        INSERTAR FICHAJE MANUAL
        ==========================================================
        */

        $sql = "INSERT INTO " . $this->table . "

            (

                usuario_id,

                fecha,

                hora_entrada,

                inicio_descanso,

                fin_descanso,

                hora_salida

            )

            VALUES

            (

                :usuario_id,

                :fecha,

                :hora_entrada,

                :inicio_descanso,

                :fin_descanso,

                :hora_salida

            )";


        $stmt =
            $this->conexion->prepare($sql);


        return $stmt->execute([

            ':usuario_id' =>
                $usuario_id,

            ':fecha' =>
                $fecha,

            ':hora_entrada' =>
                $horaEntrada,

            ':inicio_descanso' =>
                $inicioDescanso,

            ':fin_descanso' =>
                $finDescanso,

            ':hora_salida' =>
                $horaSalida

        ]);

    }


}