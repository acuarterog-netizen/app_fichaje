<?php

require_once "../models/Usuario.php";

class AuthController {

    public function login() {

        if($_SERVER['REQUEST_METHOD'] == 'POST') {

            $email = $_POST['email'];
            $password = $_POST['password'];

            $usuarioModel = new Usuario();

            $usuario = $usuarioModel->login($email, $password);

            if($usuario) {

                session_start();

                $_SESSION['usuario'] = $usuario;

                header("Location: dashboard.php");

            } else {

                echo "Email o contraseña incorrectos";

            }

        }

    }
}