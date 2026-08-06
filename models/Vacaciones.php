<?php

require_once "../config/Database.php";

class Vacaciones {

    private $conexion;

    public function __construct(){

        $database = new Database();

        $this->conexion = $database->conectar();

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

        $sql = "INSERT INTO vacaciones

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

        )";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':usuario_id'=>$usuario_id,
            ':fecha_inicio'=>$fecha_inicio,
            ':fecha_fin'=>$fecha_fin,
            ':comentario'=>$comentario,
            ':creado_por'=>$creado_por

        ]);

    }

    /* ==========================================================
       OBTENER TODAS LAS VACACIONES
    ========================================================== */

    public function obtenerVacaciones(){

    $sql = "SELECT

                vacaciones.*,

                usuarios.id AS usuario_id,

                usuarios.nombre,

                usuarios.empresa_id,

                empresas.nombre AS empresa

            FROM vacaciones

            INNER JOIN usuarios
            ON vacaciones.usuario_id = usuarios.id

            LEFT JOIN empresas
            ON usuarios.empresa_id = empresas.id

            ORDER BY fecha_inicio DESC";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

    /* ==========================================================
       VACACIONES POR EMPLEADO
    ========================================================== */

    public function obtenerVacacionesUsuario($usuario_id){

        $sql = "SELECT *

                FROM vacaciones

                WHERE usuario_id = :usuario_id

                ORDER BY fecha_inicio DESC";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':usuario_id'=>$usuario_id

        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /* ==========================================================
       CREAR FESTIVO
    ========================================================== */

    public function crearFestivo(

        $empresa_id,
        $fecha,
        $nombre

    ){

        $sql = "INSERT INTO festivos

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

        )";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':empresa_id'=>$empresa_id,
            ':fecha'=>$fecha,
            ':nombre'=>$nombre

        ]);

    }

    /* ==========================================================
       OBTENER FESTIVOS
    ========================================================== */

    public function obtenerFestivos(){

        $sql = "SELECT

                    festivos.*,

                    empresas.nombre AS empresa

                FROM festivos

                LEFT JOIN empresas

                ON festivos.empresa_id = empresas.id

                ORDER BY fecha";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

        /* ==========================================================
       ELIMINAR FESTIVO
    ========================================================== */

    public function eliminarFestivo($id){

        $sql = "DELETE
                FROM festivos
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':id'=>$id

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

        $sql = "INSERT INTO bloqueos_fechas

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

        )";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':empresa_id'=>$empresa_id,
            ':fecha_inicio'=>$fecha_inicio,
            ':fecha_fin'=>$fecha_fin,
            ':motivo'=>$motivo

        ]);

    }

    /* ==========================================================
       OBTENER BLOQUEOS
    ========================================================== */

    public function obtenerBloqueos(){

    $sql = "SELECT

                bloqueos_fechas.*,

                empresas.nombre AS empresa

            FROM bloqueos_fechas

            LEFT JOIN empresas

            ON bloqueos_fechas.empresa_id = empresas.id

            ORDER BY fecha_inicio";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

    /* ==========================================================
       ELIMINAR BLOQUEO
    ========================================================== */

    public function eliminarBloqueo($id){

        $sql = "DELETE

                FROM bloqueos_fechas

                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':id'=>$id

        ]);

    }

    /* ==========================================================
       EVENTOS PARA EL CALENDARIO
    ========================================================== */

    public function obtenerEventosCalendario(){

    $eventos = [];

    /*
    =============================================
    AGRUPAR VACACIONES POR DÍA
    =============================================
    */

    $diasVacaciones = [];

    foreach($this->obtenerVacaciones() as $v){

        $inicio = strtotime($v["fecha_inicio"]);
        $fin    = strtotime($v["fecha_fin"]);

        while($inicio <= $fin){

            $fecha = date("Y-m-d",$inicio);

            if(!isset($diasVacaciones[$fecha])){

                $diasVacaciones[$fecha] = [

                    "empleados" => [],
                    "usuarios"  => [],

                ];

            }

            $diasVacaciones[$fecha]["vacaciones"][] = [

    "id" => $v["id"],
    "empleado" => $v["nombre"],
    "empresa" => $v["empresa"]

];

$diasVacaciones[$fecha]["usuarios"][] = (int)$v["usuario_id"];

            $inicio = strtotime("+1 day",$inicio);

        }

    }

    /*
    =============================================
    CREAR EVENTOS DE VACACIONES
    =============================================
    */

    foreach($diasVacaciones as $fecha => $datos){

        $eventos[] = [

            "id"    => "vac_".$fecha,

            "title" => "●",

            "start" => $fecha,

            "end"   => date(
                "Y-m-d",
                strtotime($fecha." +1 day")
            ),

            "color" => "#22c55e",

            "allDay" => true,

            "extendedProps" => [

    "tipo" => "vacaciones",

    "vacaciones" => $datos["vacaciones"],

    "usuarios" => array_values(array_unique($datos["usuarios"]))

]

        ];

    }

    /*
    =============================================
    FESTIVOS
    =============================================
    */

    foreach($this->obtenerFestivos() as $f){

        $eventos[] = [

            "id" => "fest_".$f["id"],

            "title" => "📅",

            "start" => $f["fecha"],

            "end" => date(
                "Y-m-d",
                strtotime($f["fecha"]." +1 day")
            ),

            "color" => "#f59e0b",

            "allDay" => true,

            "extendedProps" => [

                "tipo" => "festivo",

                "empresa" => $f["empresa"] ?? "",

                "motivo" => $f["motivo"] ?? ""

            ]

        ];

    }

    /*
    =============================================
    BLOQUEOS
    =============================================
    */

    foreach($this->obtenerBloqueos() as $b){

        $eventos[] = [

            "id" => "bloq_".$b["id"],

            "title" => "",

            "start" => $b["fecha_inicio"],

            "end" => date(
                "Y-m-d",
                strtotime($b["fecha_fin"]." +1 day")
            ),

            "display" => "background",

            "color" => "#ef4444",

            "extendedProps" => [

                "tipo" => "bloqueo"

            ]

        ];

    }

    return $eventos;

}

        /* ==========================================================
       SOLICITUDES PENDIENTES
    ========================================================== */

    public function obtenerSolicitudesPendientes(){

        $sql = "SELECT

                    vacaciones.*,

                    usuarios.nombre,

                    empresas.nombre AS empresa

                FROM vacaciones

                INNER JOIN usuarios

                ON vacaciones.usuario_id = usuarios.id

                LEFT JOIN empresas

                ON usuarios.empresa_id = empresas.id

                WHERE vacaciones.estado='pendiente'

                ORDER BY vacaciones.fecha_inicio ASC";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /* ==========================================================
       APROBAR VACACIONES
    ========================================================== */

    public function aprobarSolicitud($id){

        $sql = "UPDATE vacaciones

                SET estado='aprobada'

                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':id'=>$id

        ]);

    }

    /* ==========================================================
       DENEGAR VACACIONES
    ========================================================== */

    public function denegarSolicitud($id){

        $sql = "UPDATE vacaciones

                SET estado='denegada'

                WHERE id=:id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([

            ':id'=>$id

        ]);

    }

    /* ==========================================================
       COMPROBAR SI EXISTE SOLAPAMIENTO
    ========================================================== */

    public function existeSolapamiento(

        $usuario_id,
        $inicio,
        $fin

    ){

        $sql = "SELECT id

                FROM vacaciones

                WHERE usuario_id=:usuario_id

                AND estado<>'denegada'

                AND (

                    fecha_inicio<=:fin

                    AND

                    fecha_fin>=:inicio

                )

                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':usuario_id'=>$usuario_id,
            ':inicio'=>$inicio,
            ':fin'=>$fin

        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /* ==========================================================
       COMPROBAR SI LA FECHA ESTÁ BLOQUEADA
    ========================================================== */

    public function fechaBloqueada(

        $empresa_id,
        $fecha

    ){

        $sql = "SELECT id

                FROM bloqueos_fechas

                WHERE empresa_id=:empresa_id

                AND :fecha BETWEEN fecha_inicio AND fecha_fin

                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':empresa_id'=>$empresa_id,
            ':fecha'=>$fecha

        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /* ==========================================================
       DÍAS DE VACACIONES CONSUMIDOS
    ========================================================== */

    public function diasConsumidos($usuario_id){

        $sql = "SELECT

                    fecha_inicio,
                    fecha_fin

                FROM vacaciones

                WHERE usuario_id=:usuario_id

                AND estado='aprobada'";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([

            ':usuario_id'=>$usuario_id

        ]);

        $dias = 0;

        while($fila = $stmt->fetch(PDO::FETCH_ASSOC)){

            $inicio = strtotime($fila['fecha_inicio']);

            $fin = strtotime($fila['fecha_fin']);

            $dias += (($fin-$inicio)/86400)+1;

        }

        return $dias;

    }

    /* ==========================================================
       DÍAS DISPONIBLES
    ========================================================== */

    public function diasDisponibles($usuario_id,$total=30){

        return $total - $this->diasConsumidos($usuario_id);

    }
/* ==========================================================
   COMPATIBILIDAD
========================================================== */

public function crearFestivoEmpresa(
    $empresa_id,
    $fecha,
    $comentario
){

    return $this->crearFestivo(
        $empresa_id,
        $fecha,
        $comentario
    );

}

public function obtenerEventos(){

    return $this->obtenerEventosCalendario();

}
/* ==========================================================
   RESUMEN DE VACACIONES POR EMPLEADO
========================================================== */

public function obtenerResumenEmpleado($usuario_id){

    return [

        "consumidos"=>$this->diasConsumidos($usuario_id),

        "disponibles"=>$this->diasDisponibles($usuario_id),

        "total"=>30

    ];

}

/* ==========================================================
   TOTAL DE EVENTOS
========================================================== */

public function totalEventos(){

    return count($this->obtenerEventosCalendario());

}

public function obtenerVacacionesPorFecha($fecha){

    $sql = "SELECT

                MIN(vacaciones.id) AS id,
                usuarios.id AS usuario_id,
                usuarios.nombre,
                empresas.nombre AS empresa

            FROM vacaciones

            INNER JOIN usuarios
                ON vacaciones.usuario_id = usuarios.id

            LEFT JOIN empresas
                ON usuarios.empresa_id = empresas.id

            WHERE :fecha BETWEEN vacaciones.fecha_inicio
                             AND vacaciones.fecha_fin

            GROUP BY usuarios.id, usuarios.nombre, empresas.nombre

            ORDER BY usuarios.nombre";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ":fecha"=>$fecha
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

public function eliminarVacaciones($id){

    $sql = "DELETE FROM vacaciones
            WHERE id = :id";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ":id"=>$id
    ]);

}

}