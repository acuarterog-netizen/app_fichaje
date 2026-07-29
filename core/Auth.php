<?php
class Auth {

    /* ==========================================================================
       VERIFICAR SESIÓN
    ========================================================================== */

    public static function verificarSesion() {
        session_start();

        if(!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit;
        }
    }

    /* ==========================================================================
       SOLO ADMIN
    ========================================================================== */

    public static function esAdmin() {
        if($_SESSION['usuario']['rol'] != 'admin') {
            die("Acceso denegado");
        }
    }

    /* ==========================================================================
       ADMIN O ENCARGADO
    ========================================================================== */

    public static function esEncargadoOAdmin() {
        $rol = $_SESSION['usuario']['rol'];

        if(
            $rol != 'admin'
            &&
            $rol != 'encargado'
        ) {
            die("Acceso denegado");
        }
    }
}