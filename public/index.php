<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Control Horario</title>

    <!-- CSS -->

    <link
        rel="stylesheet"
        href="css/estilos.css"
    >

    <!-- GOOGLE FONT -->

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
        rel="stylesheet"
    >

</head>

<body class="body-login">

    <div class="login-wrapper">

        <div class="login-card">

            <h2
                style="
                    text-align:center;
                    margin-bottom:25px;
                "
            >
                Iniciar sesión
            </h2>

            <form
                action="procesar_login.php"
                method="POST"
            >

                <input
                    type="email"
                    name="email"
                    placeholder="Correo electrónico"
                    required
                >

                <input
                    type="password"
                    name="password"
                    placeholder="Contraseña"
                    required
                >

                <button
                    type="submit"
                    class="btn-main-blue"
                    style="
                        width:100%;
                        margin-top:10px;
                    "
                >
                    Entrar
                </button>

            </form>

        </div>

    </div>

</body>

</html>