<?php

require_once __DIR__ . "/../core/Auth.php";
require_once __DIR__ . "/../models/Usuario.php";
require_once __DIR__ . "/../models/Empresa.php";

Auth::verificarSesion();
Auth::esAdmin();

$usuarioModel = new Usuario();
$empresaModel = new Empresa();

/* ==========================================================================
   VALIDAR ID
========================================================================== */

if(!isset($_GET['id'])){

    header("Location: usuarios.php");
    exit;

}

$id = $_GET['id'];

/* ==========================================================================
   OBTENER USUARIO
========================================================================== */

$usuario = $usuarioModel->obtenerUsuarioPorId($id);

if(!$usuario){

    header("Location: usuarios.php");
    exit;

}

/* ==========================================================================
   OBTENER EMPRESAS
========================================================================== */

$empresas = $empresaModel->obtenerEmpresas();

/* ==========================================================================
   GUARDAR
========================================================================== */

$mensaje = "";
$tipoMensaje = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];

    $empresa_id = null;

    if(isset($_POST['empresa_id']) && $_POST['empresa_id'] != ""){

        $empresa_id = $_POST['empresa_id'];

    }

    
    if($usuarioModel->existeEmail($email,$id)){

        $mensaje = "Ya existe otro usuario con ese email.";
        $tipoMensaje = "danger";

    }

    else if($usuarioModel->existeDNI($dni,$id)){

        $mensaje = "Ya existe otro usuario con ese DNI.";
        $tipoMensaje = "danger";

    }

    else{

        $usuarioModel->editarUsuario(

            $id,
            $nombre,
            $dni,
            $telefono,
            $email,
            $rol,
            $empresa_id

        );

        header("Location: usuarios.php");
        exit;

    }

}

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<h1>Editar usuario</h1>

<?php if($mensaje!=""): ?>

<div class="alert alert-<?php echo $tipoMensaje; ?>">

    <?php echo $mensaje; ?>

</div>

<?php endif; ?>

<div class="fichaje-card">

<form method="POST">

<!-- NOMBRE -->

<div class="form-group">

<label>Nombre</label>

<input
type="text"
name="nombre"
class="form-control"
value="<?php echo $_POST['nombre'] ?? $usuario['nombre']; ?>"
required>

</div>

<!-- DNI -->

<div class="form-group">

<label>DNI</label>

<input
type="text"
name="dni"
class="form-control"
value="<?php echo $_POST['dni'] ?? $usuario['dni']; ?>"
required>

</div>

<!-- TELÉFONO -->

<div class="form-group">

<label>Teléfono</label>

<input
type="text"
name="telefono"
class="form-control"
value="<?php echo $_POST['telefono'] ?? $usuario['telefono']; ?>"
required>

</div>

<!-- EMAIL -->

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo $_POST['email'] ?? $usuario['email']; ?>"
required>

</div>

<!-- ROL -->

<div class="form-group">

<label>Rol</label>

<select
name="rol"
class="form-control">

<option
value="empleado"
<?php if(($_POST['rol'] ?? $usuario['rol']) == 'empleado') echo 'selected'; ?>>
Empleado
</option>

<option
value="encargado"
<?php if(($_POST['rol'] ?? $usuario['rol']) == 'encargado') echo 'selected'; ?>>
Encargado
</option>

<option
value="admin"
<?php if(($_POST['rol'] ?? $usuario['rol']) == 'admin') echo 'selected'; ?>>
Administrador
</option>

</select>

</div>

<!-- EMPRESA -->

<div class="form-group">

<label>Empresa</label>

<select
name="empresa_id"
class="form-control select-buscador">

<option value="">
Sin empresa
</option>

<?php

$empresaSeleccionada = $_POST['empresa_id'] ?? $usuario['empresa_id'];

foreach($empresas as $empresa):

?>

<option
value="<?php echo $empresa['id']; ?>"
<?php if($empresaSeleccionada == $empresa['id']) echo "selected"; ?>>

<?php echo $empresa['nombre']; ?>

</option>

<?php endforeach; ?>

</select>

</div>

<button
class="btn-main-blue"
type="submit">

Guardar cambios

</button>

</form>

</div>

<?php
include "../views/layouts/footer.php";
?>