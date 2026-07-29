<?php

require_once "../core/Auth.php";
require_once "../models/Usuario.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$usuarioModel = new Usuario();
$empresaModel = new Empresa();

$mensaje = "";
$tipoMensaje = "";

/* ==========================================================================
   OBTENER EMPRESAS
========================================================================== */

$empresas = $empresaModel->obtenerEmpresas();

/* ==========================================================================
   CREAR USUARIO
========================================================================== */

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $empresa_id = null;

    if(isset($_POST['empresa_id']) && $_POST['empresa_id'] != ""){

        $empresa_id = $_POST['empresa_id'];

    }

    $resultado = $usuarioModel->crearUsuario(

        trim($_POST['nombre']),
        trim($_POST['dni']),
        trim($_POST['telefono']),
        trim($_POST['email']),
        $_POST['password'],
        $_POST['rol'],
        $empresa_id

    );

    if($resultado == "ok"){

        header("Location: usuarios.php");
        exit;

    }

    if($resultado == "email_existe"){

        $mensaje = "Ya existe un usuario con ese email.";
        $tipoMensaje = "danger";

    }

    if($resultado == "dni_existe"){

        $mensaje = "Ya existe un usuario con ese DNI.";
        $tipoMensaje = "danger";

    }

}

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Nuevo usuario</h1>

<?php if($mensaje!=""): ?>

<div class="alert alert-<?php echo $tipoMensaje; ?>">

    <?php echo $mensaje; ?>

</div>

<?php endif; ?>

<div class="fichaje-card">

<form method="POST">

<div class="form-group">

<label>Nombre</label>

<input
class="form-control"
type="text"
name="nombre"
value="<?php echo $_POST['nombre'] ?? ''; ?>"
required>

</div>

<div class="form-group">

<label>DNI</label>

<input
class="form-control"
type="text"
name="dni"
value="<?php echo $_POST['dni'] ?? ''; ?>"
required>

</div>

<div class="form-group">

<label>Teléfono</label>

<input
class="form-control"
type="text"
name="telefono"
value="<?php echo $_POST['telefono'] ?? ''; ?>"
required>

</div>

<div class="form-group">

<label>Email</label>

<input
class="form-control"
type="email"
name="email"
value="<?php echo $_POST['email'] ?? ''; ?>"
required>

</div>

<div class="form-group">

<label>Contraseña</label>

<input
class="form-control"
type="password"
name="password"
required>

</div>

<div class="form-group">

<label>Rol</label>

<select
class="form-control"
name="rol">

<option value="empleado"
<?php if(($_POST['rol'] ?? '')=="empleado") echo "selected"; ?>>
Empleado
</option>

<option value="encargado"
<?php if(($_POST['rol'] ?? '')=="encargado") echo "selected"; ?>>
Encargado
</option>

<option value="admin"
<?php if(($_POST['rol'] ?? '')=="admin") echo "selected"; ?>>
Administrador
</option>

</select>

</div>

<div class="form-group">

<label>Empresa</label>

<select
name="empresa_id"
class="form-control select-buscador">

<option value="">
Sin empresa
</option>

<?php foreach($empresas as $empresa): ?>

<option
value="<?php echo $empresa['id']; ?>"
<?php
if(($_POST['empresa_id'] ?? '') == $empresa['id']){
    echo "selected";
}
?>>

<?php echo $empresa['nombre']; ?>

</option>

<?php endforeach; ?>

</select>

</div>

<button
class="btn-main-blue"
type="submit">

Crear usuario

</button>

</form>

</div>

<?php
include "../views/layouts/footer.php";
?>