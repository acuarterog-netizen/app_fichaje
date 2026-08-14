<?php

require_once "../core/Auth.php";
require_once "../models/Vacaciones.php";
require_once "../models/Usuario.php";
require_once "../models/Empresa.php";
require_once "../models/TipoBaja.php";
require_once "../models/Bajas.php";

Auth::verificarSesion();

if(
    $_SESSION["usuario"]["rol"] != "admin" &&
    $_SESSION["usuario"]["rol"] != "encargado"
){
    header("Location: dashboard.php");
    exit;
}

$usuarioModel = new Usuario();
$empresaModel = new Empresa();
$bajasModel   = new Bajas();
$tipoBaja     = new TipoBaja();

$usuarios  = $usuarioModel->obtenerEmpleados();
$empresas  = $empresaModel->obtenerEmpresas();
$tiposBaja = $tipoBaja->obtenerTipos();

$mensaje = "";


/*
|--------------------------------------------------------------------------
| ELIMINAR BAJA
|--------------------------------------------------------------------------
*/

if(isset($_GET["eliminar"])){

    $bajasModel->eliminarBaja($_GET["eliminar"]);

    header("Location: bajas.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GUARDAR BAJA
|--------------------------------------------------------------------------
*/

if(isset($_POST["guardar"])){

    if(
        empty($_POST["usuario_id"]) ||
        empty($_POST["tipo_baja_id"]) ||
        empty($_POST["fecha_inicio"]) ||
        empty($_POST["fecha_fin"])
    ){

        $mensaje = "Debes completar todos los campos.";

    }else{

        $bajasModel->crearBaja(
            $_POST["usuario_id"],
            $_POST["tipo_baja_id"],
            $_POST["fecha_inicio"],
            $_POST["fecha_fin"],
            ""
        );

        $mensaje = "Baja registrada correctamente.";
    }
}


/*
|--------------------------------------------------------------------------
| CREAR TIPO DE BAJA
|--------------------------------------------------------------------------
*/

if(isset($_POST["guardarTipoBaja"])){

    $tipoBaja = new TipoBaja();

    $tipoBaja->crear(
        $_POST["empresa_tipo"],
        $_POST["nombre_tipo"],
        $_POST["color_tipo"]
    );

    header("Location: bajas.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| OBTENER EVENTOS
|--------------------------------------------------------------------------
*/

$eventos = $bajasModel->obtenerEventosCalendario();


include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css"
>


<h1>Bajas</h1>


<?php if($mensaje != ""): ?>

<div class="alert alert-success">

    <?php echo htmlspecialchars($mensaje); ?>

</div>

<?php endif; ?>


<div class="vacaciones-layout">


    <!-- ==========================================================
         SIDEBAR
    =========================================================== -->

    <aside class="vacaciones-sidebar">


        <div class="fichaje-card">

            <button
                type="button"
                class="btn-main-blue btn-full"
                onclick="abrirDrawer()"
            >

                ➕ Agregar baja

            </button>

        </div>


        <!-- ======================================================
             FILTROS
        ======================================================= -->

        <div class="fichaje-card">

            <h2>Filtros</h2>


            <div class="form-group">

                <label>
                    Empresa
                </label>

                <select
                    id="filtroEmpresaCalendario"
                    class="form-control"
                >

                    <option value="">
                        Todas
                    </option>


                    <?php foreach($empresas as $empresa): ?>

                        <option
                            value="<?php echo $empresa["id"]; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $empresa["nombre"]
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div
                class="form-group"
                style="margin-top:15px;"
            >

                <label>
                    Empleado
                </label>


                <select
                    id="filtroEmpleadoCalendario"
                    class="form-control"
                >

                    <option value="">
                        Todos
                    </option>


                    <?php foreach($usuarios as $u): ?>

                        <option
                            value="<?php echo $u["id"]; ?>"
                            data-empresa="<?php echo $u["empresa_id"]; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $u["nombre"]
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>


    </aside>



    <!-- ==========================================================
         CALENDARIO
    =========================================================== -->

    <section class="vacaciones-calendar">

        <div class="fichaje-card">

            <div id="calendar"></div>

        </div>

    </section>


</div>



<!-- ==============================================================
     OVERLAY
================================================================ -->

<div
    class="overlay-vacaciones"
    id="overlay"
    onclick="cerrarDrawer()"
></div>



<!-- ==============================================================
     DRAWER CREAR BAJA
================================================================ -->

<div
    class="drawer-vacaciones"
    id="drawer"
>


    <div class="drawer-header">

        <h2 id="tituloDrawer">

            Agregar baja

        </h2>


        <button
            type="button"
            class="drawer-close"
            onclick="cerrarDrawer()"
        >

            ×

        </button>

    </div>



    <form method="POST">


        <!-- ======================================================
             EMPRESA
        ======================================================= -->

        <div
            class="form-group"
            id="empresaDiv"
        >

            <label>
                Empresa
            </label>


            <select
                id="empresa"
                name="empresa_id"
                class="form-control"
            >

                <option value="">
                    Seleccione una empresa
                </option>


                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa["id"]; ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $empresa["nombre"]
                        );
                        ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- ======================================================
             EMPLEADO
        ======================================================= -->

        <div
            class="form-group"
            id="empleadoDiv"
        >

            <label>
                Empleado
            </label>


            <select
                id="usuario_id"
                name="usuario_id"
                class="form-control"
                required
            >

                <option value="">
                    Seleccione un empleado
                </option>


                <?php foreach($usuarios as $u): ?>

                    <option
                        value="<?php echo $u["id"]; ?>"
                        data-empresa="<?php echo $u["empresa_id"]; ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $u["nombre"]
                        );
                        ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- ======================================================
             FECHA INICIO
        ======================================================= -->

        <div
            class="form-group"
            id="inicioDiv"
        >

            <label>
                Fecha inicio
            </label>


            <input
                type="date"
                id="fecha_inicio"
                name="fecha_inicio"
                class="form-control"
                required
            >

        </div>



        <!-- ======================================================
             FECHA FIN
        ======================================================= -->

        <div
            class="form-group"
            id="finDiv"
        >

            <label>
                Fecha fin
            </label>


            <input
                type="date"
                id="fecha_fin"
                name="fecha_fin"
                class="form-control"
                required
            >

        </div>



        <!-- ======================================================
             TIPO DE BAJA
        ======================================================= -->

        <div class="form-group">

            <label>
                Tipo de baja
            </label>


            <div class="fila-tipo-baja">


                <select
                    id="tipo_baja_id"
                    name="tipo_baja_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        Seleccione un tipo
                    </option>


                    <?php foreach($tiposBaja as $tipo): ?>

                        <option
                            value="<?php echo htmlspecialchars($tipo["nombre"]); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $tipo["nombre"]
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <button
                    type="button"
                    class="btn-add-tipo"
                    onclick="abrirDrawerTipoBaja()"
                >

                    +

                </button>


            </div>

        </div>



        <!-- ======================================================
             TIPOS DE BAJA EXISTENTES
        ======================================================= -->

        <div class="form-group">

            <label>
                Tipos de baja
            </label>


            <div
                style="
                    display:flex;
                    flex-direction:column;
                    gap:8px;
                    margin-top:10px;
                "
            >


                <?php if(empty($tiposBaja)): ?>

                    <div
                        style="
                            padding:10px;
                            border:1px solid #ddd;
                            border-radius:8px;
                            color:#666;
                        "
                    >

                        No hay tipos de baja.

                    </div>


                <?php else: ?>


                    <?php foreach($tiposBaja as $tipo): ?>

                        <div
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                                padding:10px;
                                border:1px solid #ddd;
                                border-radius:8px;
                                background:#fff;
                            "
                        >


                            <div
                                style="
                                    display:flex;
                                    align-items:center;
                                    gap:8px;
                                "
                            >

                                <span
                                    style="
                                        width:12px;
                                        height:12px;
                                        border-radius:50%;
                                        display:inline-block;
                                        background:<?php
                                            echo htmlspecialchars(
                                                $tipo["color"] ?? "#ff0000"
                                            );
                                        ?>;
                                    "
                                ></span>


                                <span>

                                    <?php
                                    echo htmlspecialchars(
                                        $tipo["nombre"]
                                    );
                                    ?>

                                </span>

                            </div>


                            <!--
                            IMPORTANTE:
                            Este botón queda preparado para
                            eliminar el tipo.
                            -->

                            <button
                                type="button"
                                class="btn-delete"
                                onclick="eliminarTipoBaja(
                                    <?php echo (int)$tipo["id"]; ?>,
                                    '<?php
                                    echo htmlspecialchars(
                                        $tipo["nombre"],
                                        ENT_QUOTES
                                    );
                                    ?>'
                                )"
                            >

                                Eliminar

                            </button>


                        </div>

                    <?php endforeach; ?>


                <?php endif; ?>


            </div>

        </div>



        <!-- ======================================================
             BOTONES
        ======================================================= -->

        <div class="drawer-footer">


            <button
                type="button"
                class="btn-delete"
                onclick="cerrarDrawer()"
            >

                Cancelar

            </button>


            <button
                type="submit"
                name="guardar"
                class="btn-main-blue"
            >

                Guardar

            </button>


        </div>


    </form>


</div>



<!-- ==============================================================
     DRAWER DEL DÍA
================================================================ -->

<div
    class="drawer-vacaciones"
    id="drawerDia"
>


    <div class="drawer-header">

        <h2 id="tituloDrawerDia">
            Bajas
        </h2>


        <button
            type="button"
            class="drawer-close"
            onclick="cerrarDrawerDia()"
        >

            ×

        </button>

    </div>


    <div
        id="contenidoDrawerDia"
        style="padding:20px;"
    ></div>


    <div class="drawer-footer">

        <button
            type="button"
            class="btn-main-blue"
            onclick="cerrarDrawerDia()"
        >

            Volver

        </button>

    </div>

</div>



<!-- ==============================================================
     DRAWER NUEVO TIPO DE BAJA
================================================================ -->

<div
    class="drawer-vacaciones"
    id="drawerTipoBaja"
>


    <div class="drawer-header">

        <h2>
            Nuevo tipo de baja
        </h2>


        <button
            type="button"
            class="drawer-close"
            onclick="cerrarDrawerTipoBaja()"
        >

            ×

        </button>

    </div>



    <form method="POST">


        <div class="form-group">

            <label>
                Empresa
            </label>


            <select
                class="form-control"
                id="empresaTipo"
                name="empresa_tipo"
                required
            >

                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa["id"]; ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $empresa["nombre"]
                        );
                        ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <div class="form-group">

            <label>
                Nombre
            </label>


            <input
                type="text"
                class="form-control"
                id="nombreTipo"
                name="nombre_tipo"
                required
            >

        </div>



        <div class="form-group">

            <label>
                Color
            </label>


            <input
                type="color"
                id="colorTipo"
                name="color_tipo"
                value="#ff0000"
            >

        </div>



        <div class="drawer-footer">


            <button
                type="button"
                class="btn-delete"
                onclick="cerrarDrawerTipoBaja()"
            >

                Cancelar

            </button>


            <button
                type="submit"
                name="guardarTipoBaja"
                class="btn-main-blue"
            >

                Guardar

            </button>


        </div>


    </form>

</div>



<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>


<script>

let calendar;


/*
|--------------------------------------------------------------------------
| ABRIR DRAWER
|--------------------------------------------------------------------------
*/

function abrirDrawer(){

    const drawer =
        document.getElementById("drawer");

    const overlay =
        document.getElementById("overlay");

    if(!drawer || !overlay){
        return;
    }

    drawer.classList.add("show");
    overlay.classList.add("show");


    /*
    --------------------------------------------------------------
    REINICIAR EMPRESA
    --------------------------------------------------------------
    */

    const empresa =
        document.getElementById("empresa");

    if(empresa){

        empresa.value = "";

    }


    /*
    --------------------------------------------------------------
    REINICIAR EMPLEADO
    --------------------------------------------------------------
    */

    const empleado =
        document.getElementById("usuario_id");

    if(empleado){

        empleado.value = "";

        Array
            .from(empleado.options)
            .forEach(function(opcion){

                opcion.hidden = false;

            });

    }


    /*
    --------------------------------------------------------------
    REINICIAR FECHAS
    --------------------------------------------------------------
    */

    const fechaInicio =
        document.getElementById("fecha_inicio");

    const fechaFin =
        document.getElementById("fecha_fin");

    if(fechaInicio){
        fechaInicio.value = "";
    }

    if(fechaFin){
        fechaFin.value = "";
    }


    /*
    --------------------------------------------------------------
    REINICIAR TIPO
    --------------------------------------------------------------
    */

    const tipo =
        document.getElementById("tipo_baja_id");

    if(tipo){
        tipo.value = "";
    }


    /*
    --------------------------------------------------------------
    MOSTRAR TODOS LOS CAMPOS
    --------------------------------------------------------------
    */

    const empresaDiv =
        document.getElementById("empresaDiv");

    const empleadoDiv =
        document.getElementById("empleadoDiv");

    const inicioDiv =
        document.getElementById("inicioDiv");

    const finDiv =
        document.getElementById("finDiv");

    if(empresaDiv){
        empresaDiv.classList.remove("oculto");
    }

    if(empleadoDiv){
        empleadoDiv.classList.remove("oculto");
    }

    if(inicioDiv){
        inicioDiv.classList.remove("oculto");
    }

    if(finDiv){
        finDiv.classList.remove("oculto");
    }

}



/*
|--------------------------------------------------------------------------
| CERRAR DRAWER
|--------------------------------------------------------------------------
*/

function cerrarDrawer(){

    const drawer =
        document.getElementById("drawer");

    const overlay =
        document.getElementById("overlay");

    if(drawer){
        drawer.classList.remove("show");
    }

    if(overlay){
        overlay.classList.remove("show");
    }

}



/*
|--------------------------------------------------------------------------
| CERRAR DRAWER DEL DÍA
|--------------------------------------------------------------------------
*/

function cerrarDrawerDia(){

    const drawer =
        document.getElementById("drawerDia");

    const overlay =
        document.getElementById("overlay");

    if(drawer){
        drawer.classList.remove("show");
    }

    if(overlay){
        overlay.classList.remove("show");
    }

}



/*
|--------------------------------------------------------------------------
| ABRIR DRAWER TIPO DE BAJA
|--------------------------------------------------------------------------
*/

function abrirDrawerTipoBaja(){

    const drawer =
        document.getElementById("drawerTipoBaja");

    const overlay =
        document.getElementById("overlay");

    if(drawer){
        drawer.classList.add("show");
    }

    if(overlay){
        overlay.classList.add("show");
    }

}



/*
|--------------------------------------------------------------------------
| CERRAR DRAWER TIPO DE BAJA
|--------------------------------------------------------------------------
*/

function cerrarDrawerTipoBaja(){

    const drawer =
        document.getElementById("drawerTipoBaja");

    const overlay =
        document.getElementById("overlay");

    if(drawer){
        drawer.classList.remove("show");
    }

    if(overlay){
        overlay.classList.remove("show");
    }

}



/*
|--------------------------------------------------------------------------
| ELIMINAR TIPO DE BAJA
|--------------------------------------------------------------------------
*/

function eliminarTipoBaja(id, nombre){

    if(
        !confirm(
            '¿Seguro que quieres eliminar el tipo de baja "' +
            nombre +
            '"?'
        )
    ){

        return;

    }


    /*
    --------------------------------------------------------------
    IMPORTANTE
    --------------------------------------------------------------
    El tipo de baja se elimina mediante el endpoint
    eliminarTipoBaja.php.
    --------------------------------------------------------------
    */

    fetch(
        "eliminarTipoBaja.php",
        {
            method: "POST",

            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded"
            },

            body:
                "id=" +
                encodeURIComponent(id)
        }
    )

    .then(function(response){

        return response.json();

    })

    .then(function(datos){

        if(datos.success){

            location.reload();

        }else{

            alert(
                datos.message ||
                "No se ha podido eliminar el tipo de baja."
            );

        }

    })

    .catch(function(error){

        console.error(error);

        alert(
            "Se ha producido un error al eliminar el tipo de baja."
        );

    });

}



/*
|--------------------------------------------------------------------------
| FILTRAR EMPLEADOS SEGÚN EMPRESA
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const empresa =
            document.getElementById("empresa");

        const empleado =
            document.getElementById("usuario_id");


        if(empresa && empleado){

            const opcionesOriginales =
                Array.from(
                    empleado.options
                );


            empresa.addEventListener(
                "change",
                function(){

                    const empresaSeleccionada =
                        this.value;


                    /*
                    --------------------------------------------------
                    REINICIAR EMPLEADO
                    --------------------------------------------------
                    */

                    empleado.innerHTML =
                        '<option value="">Seleccione un empleado</option>';


                    /*
                    --------------------------------------------------
                    SI NO HAY EMPRESA
                    --------------------------------------------------
                    */

                    if(empresaSeleccionada === ""){

                        return;

                    }


                    /*
                    --------------------------------------------------
                    AÑADIR SOLO EMPLEADOS DE ESA EMPRESA
                    --------------------------------------------------
                    */

                    opcionesOriginales.forEach(
                        function(opcion){

                            if(opcion.value === ""){

                                return;

                            }


                            if(
                                String(
                                    opcion.dataset.empresa
                                ) ===
                                String(
                                    empresaSeleccionada
                                )
                            ){

                                empleado.appendChild(
                                    opcion.cloneNode(true)
                                );

                            }

                        }
                    );

                }
            );

        }



        /*
        ==============================================================
        CALENDARIO
        ============================================================== 
        */

        const elementoCalendar =
            document.getElementById(
                "calendar"
            );


        if(!elementoCalendar){

            return;

        }


        calendar =
            new FullCalendar.Calendar(
                elementoCalendar,
                {

                    locale:"es",

                    initialView:
                        "dayGridMonth",

                    firstDay:1,

                    height:"auto",

                    selectable:true,

                    navLinks:true,

                    dayMaxEvents:true,


                    headerToolbar:{

                        left:
                            "prev,next today",

                        center:
                            "title",

                        right:
                            "dayGridMonth"

                    },


                    buttonText:{

                        today:
                            "Hoy",

                        month:
                            "Mes",

                        week:
                            "Semana"

                    },


                    /*
                    --------------------------------------------------
                    CLICK EN UN DÍA
                    --------------------------------------------------
                    */

                    dateClick:function(info){

                        fetch(
                            "/app_fichaje/public/bajasDia.php?fecha=" +
                            encodeURIComponent(
                                info.dateStr
                            )
                        )

                        .then(
                            response =>
                                response.json()
                        )

                        .then(
                            function(datos){

                                let html = "";


                                if(
                                    datos.length === 0
                                ){

                                    html =
                                        "<p>No hay bajas este día.</p>";

                                }else{


                                    datos.forEach(
                                        function(v){

                                            html += `

<div class="lista-evento">

    <strong>
        ${v.nombre}
    </strong>

    <br>

    <small>
        ${v.empresa}
    </small>

    <br>

    <small>
        ${v.tipo || ""}
    </small>

    <br>

    <button
        type="button"
        class="btn-delete"
        onclick="eliminarBaja(${v.id})"
    >

        Eliminar

    </button>

</div>

`;

                                        }
                                    );

                                }


                                const titulo =
                                    document.getElementById(
                                        "tituloDrawerDia"
                                    );


                                const contenido =
                                    document.getElementById(
                                        "contenidoDrawerDia"
                                    );


                                const drawer =
                                    document.getElementById(
                                        "drawerDia"
                                    );


                                const overlay =
                                    document.getElementById(
                                        "overlay"
                                    );


                                if(
                                    !titulo ||
                                    !contenido ||
                                    !drawer
                                ){

                                    return;

                                }


                                titulo.innerHTML =
                                    "Bajas - " +
                                    info.dateStr;


                                contenido.innerHTML =
                                    html;


                                drawer.classList.add(
                                    "show"
                                );


                                if(overlay){

                                    overlay.classList.add(
                                        "show"
                                    );

                                }

                            }
                        )

                        .catch(
                            function(error){

                                console.error(error);

                                alert(
                                    "Error al cargar las bajas."
                                );

                            }
                        );

                    },


                    /*
                    --------------------------------------------------
                    SELECCIONAR RANGO
                    --------------------------------------------------
                    */

                    select:function(info){

                        const fechaInicio =
                            document.getElementById(
                                "fecha_inicio"
                            );


                        const fechaFin =
                            document.getElementById(
                                "fecha_fin"
                            );


                        if(fechaInicio){

                            fechaInicio.value =
                                info.startStr;

                        }


                        if(fechaFin){

                            let fin =
                                new Date(
                                    info.end
                                );


                            fin.setDate(
                                fin.getDate() - 1
                            );


                            fechaFin.value =
                                fin
                                    .toISOString()
                                    .split("T")[0];

                        }


                        abrirDrawer();

                    },


                    /*
                    --------------------------------------------------
                    EVENTOS
                    --------------------------------------------------
                    */

                    events:[

                        <?php foreach($eventos as $evento): ?>

                        {

                            id:
                                "<?= $evento["id"] ?>",

                            title:
                                "<?= addslashes($evento["title"]) ?>",

                            start:
                                "<?= $evento["start"] ?>",

                            end:
                                "<?= $evento["end"] ?>",

                            backgroundColor:
                                "<?= $evento["color"] ?>",

                            borderColor:
                                "<?= $evento["color"] ?>",

                            textColor:
                                "#ffffff",

                            allDay:
                                true,

                            extendedProps:{

                                empresa:
                                    "<?= $evento["extendedProps"]["empresa"] ?>",

                                usuario:
                                    "<?= $evento["extendedProps"]["usuario"] ?>"

                            }

                        },

                        <?php endforeach; ?>

                    ]

                }

            );


        calendar.render();



        /*
        ==============================================================
        FILTROS DEL CALENDARIO
        ============================================================== 
        */

        const filtroEmpresa =
            document.getElementById(
                "filtroEmpresaCalendario"
            );


        const filtroEmpleado =
            document.getElementById(
                "filtroEmpleadoCalendario"
            );


        if(
            filtroEmpresa &&
            filtroEmpleado
        ){

            const empleadosOriginales =
                Array.from(
                    filtroEmpleado.options
                );


            filtroEmpresa.addEventListener(
                "change",
                function(){

                    const empresaSeleccionada =
                        this.value;


                    filtroEmpleado.innerHTML =
                        '<option value="">Todos</option>';


                    empleadosOriginales.forEach(
                        function(opcion){

                            if(
                                opcion.value === ""
                            ){

                                return;

                            }


                            if(
                                empresaSeleccionada === "" ||
                                String(
                                    opcion.dataset.empresa
                                ) ===
                                String(
                                    empresaSeleccionada
                                )
                            ){

                                filtroEmpleado.appendChild(
                                    opcion.cloneNode(true)
                                );

                            }

                        }
                    );


                    filtrarCalendario();

                }
            );


            filtroEmpleado.addEventListener(
                "change",
                filtrarCalendario
            );

        }


        /*
        ==============================================================
        OCULTAR EVENTOS AL INICIO
        ============================================================== 
        */

        calendar
            .getEvents()
            .forEach(
                function(evento){

                    evento.setProp(
                        "display",
                        "none"
                    );

                }
            );


        filtrarCalendario();

    }
);



/*
|--------------------------------------------------------------------------
| FILTRAR CALENDARIO
|--------------------------------------------------------------------------
*/

function filtrarCalendario(){

    if(!calendar){
        return;
    }


    const filtroEmpresa =
        document.getElementById(
            "filtroEmpresaCalendario"
        );


    const filtroEmpleado =
        document.getElementById(
            "filtroEmpleadoCalendario"
        );


    if(
        !filtroEmpresa ||
        !filtroEmpleado
    ){

        return;

    }


    const empresa =
        filtroEmpresa.value;


    const empleado =
        filtroEmpleado.value;


    calendar
        .getEvents()
        .forEach(
            function(evento){

                let mostrar = true;


                /*
                --------------------------------------------------
                FILTRO EMPRESA
                --------------------------------------------------
                */

                if(
                    empresa !== "" &&
                    String(
                        evento.extendedProps.empresa
                    ) !==
                    String(
                        empresa
                    )
                ){

                    mostrar = false;

                }


                /*
                --------------------------------------------------
                FILTRO EMPLEADO
                --------------------------------------------------
                */

                if(
                    empleado !== "" &&
                    String(
                        evento.extendedProps.usuario
                    ) !==
                    String(
                        empleado
                    )
                ){

                    mostrar = false;

                }


                evento.setProp(
                    "display",
                    mostrar
                        ? "auto"
                        : "none"
                );

            }
        );

}



/*
|--------------------------------------------------------------------------
| ELIMINAR BAJA
|--------------------------------------------------------------------------
*/

function eliminarBaja(id){

    if(
        !confirm(
            "¿Eliminar esta baja?"
        )
    ){

        return;

    }


    window.location =
        "bajas.php?eliminar=" +
        encodeURIComponent(id);

}



/*
|--------------------------------------------------------------------------
| ELIMINAR VACACIONES
|--------------------------------------------------------------------------
*/

function eliminarVacaciones(id){

    if(
        !confirm(
            "¿Eliminar estas vacaciones?"
        )
    ){

        return;

    }


    window.location =
        "vacaciones.php?eliminar=" +
        encodeURIComponent(id);

}

</script>


<?php

include "../views/layouts/footer.php";

?>