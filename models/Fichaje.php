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
       GENERAR HORA ALEATORIA (+/- 4 MINUTOS)
    ========================================================================== */

    private function generarHoraAleatoria($horaBase) {

        $base = strtotime($horaBase);

        $desfase = rand(-4,4);

        return date(
            "H:i:s",
            $base + ($desfase * 60)
        );

    }


    /* ==========================================================================
       OBTENER HORARIO DE LA EMPRESA DEL USUARIO
    ========================================================================== */

    private function obtenerHorarioEmpresa($usuario_id) {

        $sql = "SELECT
                    empresas.hora_entrada,
                    empresas.hora_salida,
                    empresas.descanso,
                    empresas.horas_jornada

                FROM usuarios

                INNER JOIN empresas
                ON usuarios.empresa_id = empresas.id

                WHERE usuarios.id = :usuario_id";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuario_id
        ]);

        $horario = $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        ==========================================================
        SI NO TIENE EMPRESA

        Mantenemos el horario anterior para no romper
        usuarios que todavía no tengan empresa asignada.
        ==========================================================
        */

        if(!$horario) {

            return [

                'hora_entrada' => '08:00:00',

                'hora_salida' => '17:00:00',

                'descanso' => 60,

                'horas_jornada' => 8.00

            ];

        }


        return $horario;

    }


    /* ==========================================================================
       GENERAR HORARIO AUTOMÁTICO
       
       ENTRADA:
       +/- 4 minutos

       INICIO DESCANSO:
       +/- 3 minutos

       FIN DESCANSO:
       +/- 3 minutos

       SALIDA:
       0 a -4 minutos

       LA JORNADA EFECTIVA NUNCA SUPERA LAS HORAS CONFIGURADAS
    ========================================================================== */

    private function generarHorarioAutomatico($usuario_id) {

        $horario = $this->obtenerHorarioEmpresa($usuario_id);


        $horaEntradaBase = $horario['hora_entrada'];

        $horaSalidaBase = $horario['hora_salida'];

        $minutosDescanso = (int) $horario['descanso'];

        $horasJornada = (float) $horario['horas_jornada'];


        /*
        ==========================================================
        MÁXIMO ABSOLUTO DE 8 HORAS
        ==========================================================
        */

        if($horasJornada > 8) {

            $horasJornada = 8;

        }


        /*
        ==========================================================
        INTENTAMOS GENERAR UNA COMBINACIÓN VÁLIDA

        Esto es importante porque las variaciones del descanso
        pueden hacer que accidentalmente se superen las horas
        máximas.
        ==========================================================
        */

        for($intento = 0; $intento < 100; $intento++) {


            /*
            ======================================================
            ENTRADA

            Horario empresa +/- 4 minutos
            ======================================================
            */

            $entradaBase = strtotime($horaEntradaBase);

            $entrada = $entradaBase + (
                rand(-4,4) * 60
            );


            /*
            ======================================================
            INICIO DESCANSO

            El descanso comienza aproximadamente a mitad
            de la jornada efectiva.

            Ejemplo:

            Jornada = 8 horas
            Entrada = 08:00

            Descanso ≈ 12:00
            ======================================================
            */

            $minutosAntesDescanso = ($horasJornada * 60) / 2;

            $inicioDescansoBase =
                $entrada +
                ($minutosAntesDescanso * 60);


            /*
            ======================================================
            INICIO DESCANSO +/- 3 MINUTOS
            ======================================================
            */

            $inicioDescanso =
                $inicioDescansoBase +
                (rand(-3,3) * 60);


            /*
            ======================================================
            FIN DESCANSO +/- 3 MINUTOS

            Primero calculamos el fin teórico del descanso
            y después aplicamos la variación.
            ======================================================
            */

            $finDescansoBase =
                $inicioDescanso +
                ($minutosDescanso * 60);


            $finDescanso =
                $finDescansoBase +
                (rand(-3,3) * 60);


            /*
            ======================================================
            SALIDA

            La salida base pertenece al horario de la empresa.

            Solo puede adelantarse entre 0 y 4 minutos.

            NUNCA puede retrasarse.
            ======================================================
            */

            $salidaBase = strtotime($horaSalidaBase);

            $salida =
                $salidaBase -
                (rand(0,4) * 60);


            /*
            ======================================================
            COMPROBAR JORNADA EFECTIVA

            Trabajo:

            entrada → inicio descanso

            +

            fin descanso → salida
            ======================================================
            */

            $trabajoAntesDescanso =
                $inicioDescanso - $entrada;


            $trabajoDespuesDescanso =
                $salida - $finDescanso;


            $trabajoTotal =
                $trabajoAntesDescanso +
                $trabajoDespuesDescanso;


            /*
            ======================================================
            CONVERTIR HORAS CONFIGURADAS A SEGUNDOS
            ======================================================
            */

            $maximoTrabajo =
                $horasJornada * 60 * 60;


            /*
            ======================================================
            COMPROBAR QUE:

            1. La entrada sea anterior al descanso.
            2. El descanso tenga sentido.
            3. La salida sea posterior al descanso.
            4. No se superen las horas configuradas.
            ======================================================
            */

            if(

                $entrada < $inicioDescanso &&

                $inicioDescanso < $finDescanso &&

                $finDescanso < $salida &&

                $trabajoTotal <= $maximoTrabajo

            ) {

                return [

                    'hora_entrada' =>
                        date("H:i:s",$entrada),

                    'inicio_descanso' =>
                        date("H:i:s",$inicioDescanso),

                    'fin_descanso' =>
                        date("H:i:s",$finDescanso),

                    'hora_salida' =>
                        date("H:i:s",$salida)

                ];

            }

        }


        /*
        ==========================================================
        SI DESPUÉS DE 100 INTENTOS NO HAY UNA COMBINACIÓN VÁLIDA

        Utilizamos el horario base de la empresa.

        Esto evita que el sistema falle.
        ==========================================================
        */

        $entrada = strtotime($horaEntradaBase);

        $inicioDescanso =
            $entrada +
            (($horasJornada * 60 / 2) * 60);

        $finDescanso =
            $inicioDescanso +
            ($minutosDescanso * 60);

        $salida =
            strtotime($horaSalidaBase);


        return [

            'hora_entrada' =>
                date("H:i:s",$entrada),

            'inicio_descanso' =>
                date("H:i:s",$inicioDescanso),

            'fin_descanso' =>
                date("H:i:s",$finDescanso),

            'hora_salida' =>
                date("H:i:s",$salida)

        ];

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
       CREAR FICHAJE AUTOMÁTICO
    ========================================================================== */

    public function crearFichajeAutomatico($usuario_id) {

        if($this->yaFichoHoy($usuario_id)) {

            return false;

        }


        $fecha = date("Y-m-d");


        /*
        ==========================================================
        GENERAR HORARIO SEGÚN EMPRESA
        ==========================================================
        */

        $horario =
            $this->generarHorarioAutomatico($usuario_id);


        $horaEntrada =
            $horario['hora_entrada'];


        $inicioDescanso =
            $horario['inicio_descanso'];


        $finDescanso =
            $horario['fin_descanso'];


        $horaSalida =
            $horario['hora_salida'];


        /*
        ==========================================================
        INSERTAR FICHAJE
        ==========================================================
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

            $sql .= " AND usuarios.nombre LIKE :busqueda";

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
                " AND DATE_FORMAT(fichajes.fecha, '%Y-%m') = :mes";

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

        $sql = "DELETE FROM " . $this->table . "
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

    public function crearFichajesMasivos($usuarios) {

        $fecha = date("Y-m-d");

        $total = 0;


        foreach($usuarios as $usuario_id) {


            if($this->yaFichoHoy($usuario_id)) {

                continue;

            }


            /*
            ======================================================
            GENERAR HORARIO SEGÚN LA EMPRESA DEL USUARIO
            ======================================================
            */

            $horario =
                $this->generarHorarioAutomatico($usuario_id);


            $horaEntrada =
                $horario['hora_entrada'];


            $inicioDescanso =
                $horario['inicio_descanso'];


            $finDescanso =
                $horario['fin_descanso'];


            $horaSalida =
                $horario['hora_salida'];


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


            $stmt->execute([

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