<?php
require_once "../core/Auth.php";
require_once "../models/Usuario.php";

Auth::verificarSesion();

$usuarioModel = new Usuario();
$usuario = $_SESSION['usuario'];

$mensaje = "";
$error = "";

/* ==========================================================================
   ACTUALIZAR PERFIL
========================================================================== */

if(isset($_POST['actualizar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);

    $resultado = $usuarioModel->actualizarPerfil(

        $usuario['id'],
        $nombre,
        $email
    );

    if($resultado) {
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['email'] = $email;

        $usuario['nombre'] = $nombre;
        $usuario['email'] = $email;

        $mensaje = "Perfil actualizado correctamente";

    } else {
        $error = "Error al actualizar el perfil";
    }
}

/* ==========================================================================
   CAMBIAR PASSWORD
========================================================================== */

if(isset($_POST['cambiar_password'])) {
    $passwordActual = $_POST['password_actual'];
    $passwordNueva = $_POST['password_nueva'];
    $confirmarPassword = $_POST['confirmar_password'];

    if($passwordNueva != $confirmarPassword) {
        $error = "Las contraseñas no coinciden";

    } else {

        $resultado = $usuarioModel->cambiarPassword(
            $usuario['id'],
            $passwordActual,
            $passwordNueva
        );
        if($resultado == "ok") {
            $mensaje = "Contraseña actualizada correctamente";
        } elseif($resultado == "password_incorrecta") {
            $error = "La contraseña actual es incorrecta";
        } else {
            $error = "Error al cambiar la contraseña";
        }
    }
}

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";
?>
<h1>Mi perfil</h1>

<?php if($mensaje != ""): ?>
    <div class="alert alert-success">
        <?php echo $mensaje; ?>
    </div>

<?php endif; ?>
<?php if($error != ""): ?>

    <div class="alert alert-error">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- PERFIL -->

<div class="fichaje-card">
    <div
        style="
            display:flex;
            align-items:center;
            gap:20px;
            margin-bottom:30px;
        "
    >

        <div class="avatar">
            <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
        </div>

        <div>
            <h2 style="margin:0;">
                <?php echo $usuario['nombre']; ?>
            </h2>

            <p style="margin-top:5px; color:#64748b;">
                <?php echo $usuario['email']; ?>
            </p>

            <span class="badge-rol">
                <?php echo strtoupper($usuario['rol']); ?>
            </span>
        </div>
    </div>

    <!-- FORMULARIO PERFIL -->
    <form method="POST">
        <input
            type="hidden"
            name="actualizar_perfil"
            value="1"
        >
        <div class="form-group">
            <label>Nombre</label>
            <input
                type="text"
                name="nombre"
                class="form-control"
                value="<?php echo $usuario['nombre']; ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Email</label>
            <input
                type="email"
                name="email"
                class="form-control"
                value="<?php echo $usuario['email']; ?>"
                required
            >
        </div>

        <button
            type="submit"
            class="btn-main-blue"
        >
            Guardar cambios
        </button>
    </form>
</div>
<!-- PASSWORD -->
<div class="fichaje-card">
    <h2>
        Cambiar contraseña
    </h2>
    <form method="POST">
        <input
            type="hidden"
            name="cambiar_password"
            value="1"
        >
        <div class="form-group">
            <label>Contraseña actual</label>
            <input
                type="password"
                name="password_actual"
                class="form-control"
                required
            >
        </div>
        <div class="form-group">
            <label>Nueva contraseña</label>
            <input
                type="password"
                name="password_nueva"
                class="form-control"
                required
            >
        </div>
        <div class="form-group">
            <label>Confirmar contraseña</label>
            <input
                type="password"
                name="confirmar_password"
                class="form-control"
                required
            >
        </div>
        <button
            type="submit"
            class="btn-main-blue"
        >
            Cambiar contraseña
        </button>
    </form>
</div>

<?php
include "../views/layouts/footer.php";
?>