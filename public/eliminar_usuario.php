<?php

require_once "../core/Auth.php";
require_once "../models/Usuario.php";

Auth::verificarSesion();
Auth::esAdmin();

$usuarioModel = new Usuario();

$id = $_GET['id'];

$usuarioModel->eliminarUsuario($id);

header("Location: usuarios.php");
exit;