<?php

require_once "../core/Auth.php";
require_once "../models/Vacaciones.php";
require_once "../models/Usuario.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();

if(
    $_SESSION["usuario"]["rol"] != "admin" &&
    $_SESSION["usuario"]["rol"] != "encargado"
){
    header("Location: dashboard.php");
    exit;
}

$vacacionesModel = new Vacaciones();


/*
============================================================
ELIMINAR VACACIONES
============================================================
*/

if(isset($_GET["eliminar"])){

    $vacacionesModel->eliminarVacaciones(
        $_GET["eliminar"],
        $_GET["fecha"]
    );

    header("Location: vacaciones.php");
    exit;
}


/*
============================================================
ELIMINAR FESTIVO
============================================================
*/

if(isset($_GET["eliminar_festivo"])){

    $vacacionesModel->eliminarFestivo(
        $_GET["eliminar_festivo"]
    );

    header("Location: vacaciones.php");
    exit;
}


$usuarioModel = new Usuario();
$empresaModel = new Empresa();

$usuarios = $usuarioModel->obtenerEmpleados();
$empresas = $empresaModel->obtenerEmpresas();

$mensaje = "";


/*
============================================================
GUARDAR
============================================================
*/

if(isset($_POST["guardar"])){

    /*
    ========================================================
    GUARDAR VACACIONES
    ========================================================
    */

    if($_POST["tipo"] == "vacaciones"){

        $vacacionesModel->crearVacaciones(
            $_POST["usuario_id"],
            $_POST["fecha_inicio"],
            $_POST["fecha_fin"],
            $_POST["comentario"]
        );

        $mensaje = "Vacaciones registradas correctamente.";
    }


    /*
    ========================================================
    GUARDAR FESTIVO
    ========================================================
    */

    if($_POST["tipo"] == "festivo"){

        $vacacionesModel->crearFestivoEmpresa(
            $_POST["empresa_id"],
            $_POST["fecha"],
            $_POST["nombre"]
        );

        $mensaje = "Festivo registrado correctamente.";
    }
}


$eventos = $vacacionesModel->obtenerEventosCalendario();

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css"
>


<h1>Vacaciones</h1>


<?php if($mensaje != ""): ?>

<div class="alert alert-success">
    <?php echo $mensaje; ?>
</div>

<?php endif; ?>


<div class="vacaciones-layout">


    <!-- ======================================================
         SIDEBAR
    ======================================================= -->

    <aside class="vacaciones-sidebar">


        <div class="fichaje-card">

            <button
                class="btn-main-blue btn-full"
                onclick="abrirDrawer('vacaciones')"
            >

                ➕ Agregar vacaciones

            </button>


            <button
                class="btn-main-blue btn-full"
                style="margin-top:10px;"
                onclick="abrirDrawer('festivo')"
            >

                📅 Agregar festivo

            </button>

        </div>


        <!-- ==================================================
             FILTROS
        =================================================== -->

        <div class="fichaje-card">

            <h2>Filtros</h2>


            <div class="form-group">

                <label>Empresa</label>

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

                            <?php echo $empresa["nombre"]; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div
                class="form-group"
                style="margin-top:15px;"
            >

                <label>Empleado</label>

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

                            <?php echo $u["nombre"]; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

    </aside>


    <!-- ======================================================
         CALENDARIO
    ======================================================= -->

    <section class="vacaciones-calendar">

        <div class="fichaje-card">

            <div id="calendar"></div>

        </div>

    </section>

</div>


<!-- ==========================================================
     OVERLAY
=========================================================== -->

<div
    class="overlay-vacaciones"
    id="overlay"
    onclick="cerrarDrawer()"
></div>


<!-- ==========================================================
     DRAWER AGREGAR
=========================================================== -->

<div
    class="drawer-vacaciones"
    id="drawer"
>

    <div class="drawer-header">

        <h2 id="tituloDrawer">
            Agregar vacaciones
        </h2>


        <button
            class="drawer-close"
            onclick="cerrarDrawer()"
        >

            ×

        </button>

    </div>


    <form method="POST">


        <input
            type="hidden"
            name="tipo"
            id="tipo"
            value="vacaciones"
        >


        <!-- ==================================================
             EMPRESA PARA VACACIONES
        =================================================== -->

        <div
            class="form-group"
            id="empresaVacacionesDiv"
        >

            <label>Empresa</label>


            <select
                id="empresaVacaciones"
                class="form-control"
            >

                <option value="">
                    Seleccione una empresa
                </option>


                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa["id"]; ?>"
                    >

                        <?php echo $empresa["nombre"]; ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- ==================================================
             EMPLEADO
        =================================================== -->

        <div
            class="form-group"
            id="empleadoDiv"
        >

            <label>Empleado</label>


            <select
                id="usuario_id"
                name="usuario_id"
                class="form-control"
            >

                <option value="">
                    Seleccione un empleado
                </option>


                <?php foreach($usuarios as $u): ?>

                    <option
                        value="<?php echo $u["id"]; ?>"
                        data-empresa="<?php echo $u["empresa_id"]; ?>"
                    >

                        <?php echo $u["nombre"]; ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- ==================================================
             EMPRESA PARA FESTIVO
        =================================================== -->

        <div
            class="form-group oculto"
            id="empresaDiv"
        >

            <label>Empresa</label>


            <select
                name="empresa_id"
                class="form-control"
            >

                <?php foreach($empresas as $empresa): ?>

                    <option
                        value="<?php echo $empresa["id"]; ?>"
                    >

                        <?php echo $empresa["nombre"]; ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- ==================================================
             FECHA INICIO VACACIONES
        =================================================== -->

        <div
            class="form-group"
            id="inicioDiv"
        >

            <label>Fecha inicio</label>


            <input
                type="date"
                id="fecha_inicio"
                name="fecha_inicio"
                class="form-control"
            >

        </div>


        <!-- ==================================================
             FECHA FIN VACACIONES
        =================================================== -->

        <div
            class="form-group"
            id="finDiv"
        >

            <label>Fecha fin</label>


            <input
                type="date"
                id="fecha_fin"
                name="fecha_fin"
                class="form-control"
            >

        </div>


        <!-- ==================================================
             FECHA FESTIVO
        =================================================== -->

        <div
            class="form-group oculto"
            id="fechaFestivo"
        >

            <label>Fecha</label>


            <input
                type="date"
                id="fecha"
                name="fecha"
                class="form-control"
            >

        </div>


        <!-- ==================================================
             NOMBRE DEL FESTIVO
        =================================================== -->

        <div
            class="form-group oculto"
            id="nombreFestivoDiv"
        >

            <label>Nombre del festivo</label>


            <input
                type="text"
                name="nombre"
                id="nombreFestivo"
                class="form-control"
                placeholder="Ej: Día del Pilar"
                maxlength="150"
            >

        </div>


        <!-- ==================================================
             COMENTARIO VACACIONES
        =================================================== -->

        <div
            class="form-group"
            id="comentarioDiv"
        >

            <label>Comentario</label>


            <textarea
                name="comentario"
                rows="4"
                class="form-control"
                placeholder="Escribe un comentario..."
            ></textarea>

        </div>


        <!-- ==================================================
             BOTONES
        =================================================== -->

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


<!-- ==========================================================
     DRAWER DEL DÍA
=========================================================== -->

<div
    class="drawer-vacaciones"
    id="drawerDia"
>

    <div class="drawer-header">

        <h2 id="tituloDrawerDia">
            Vacaciones
        </h2>


        <button
            class="drawer-close"
            onclick="cerrarDrawerDia()"
        >

            ×

        </button>

    </div>


    <div
        id="contenidoDrawerDia"
        style="padding:20px;"
    >
    </div>


    <div class="drawer-footer">

        <button
            class="btn-main-blue"
            onclick="cerrarDrawerDia()"
        >

            Volver

        </button>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>


<script>

let calendar;


/*
============================================================
ABRIR DRAWER
============================================================
*/

function abrirDrawer(tipo){

    const drawer =
        document.getElementById("drawer");

    const overlay =
        document.getElementById("overlay");

    drawer.classList.add("show");

    overlay.classList.add("show");

    document.getElementById("tipo").value = tipo;


    /*
    ==========================================================
    VACACIONES
    ==========================================================
    */

    if(tipo == "vacaciones"){

        document.getElementById(
            "tituloDrawer"
        ).innerHTML =
            "Agregar vacaciones";


        document.getElementById(
            "empresaVacacionesDiv"
        ).classList.remove("oculto");


        document.getElementById(
            "empleadoDiv"
        ).classList.remove("oculto");


        document.getElementById(
            "empresaDiv"
        ).classList.add("oculto");


        document.getElementById(
            "inicioDiv"
        ).classList.remove("oculto");


        document.getElementById(
            "finDiv"
        ).classList.remove("oculto");


        document.getElementById(
            "fechaFestivo"
        ).classList.add("oculto");


        document.getElementById(
            "nombreFestivoDiv"
        ).classList.add("oculto");


        document.getElementById(
            "comentarioDiv"
        ).classList.remove("oculto");

    }


    /*
    ==========================================================
    FESTIVO
    ==========================================================
    */

    else{

        document.getElementById(
            "tituloDrawer"
        ).innerHTML =
            "Agregar festivo";


        document.getElementById(
            "empresaVacacionesDiv"
        ).classList.add("oculto");


        document.getElementById(
            "empleadoDiv"
        ).classList.add("oculto");


        document.getElementById(
            "empresaDiv"
        ).classList.remove("oculto");


        document.getElementById(
            "inicioDiv"
        ).classList.add("oculto");


        document.getElementById(
            "finDiv"
        ).classList.add("oculto");


        document.getElementById(
            "fechaFestivo"
        ).classList.remove("oculto");


        document.getElementById(
            "nombreFestivoDiv"
        ).classList.remove("oculto");


        document.getElementById(
            "comentarioDiv"
        ).classList.add("oculto");

    }

}


/*
============================================================
CERRAR DRAWER
============================================================
*/

function cerrarDrawer(){

    document
        .getElementById("drawer")
        .classList.remove("show");

    document
        .getElementById("overlay")
        .classList.remove("show");

}


/*
============================================================
CERRAR DRAWER DEL DÍA
============================================================
*/

function cerrarDrawerDia(){

    document
        .getElementById("drawerDia")
        .classList.remove("show");

    document
        .getElementById("overlay")
        .classList.remove("show");

}


/*
============================================================
CALENDARIO
============================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function(){

        calendar =
            new FullCalendar.Calendar(

                document.getElementById("calendar"),

                {

                    locale:"es",

                    initialView:"dayGridMonth",

                    firstDay:1,

                    height:"auto",

                    selectable:true,

                    navLinks:true,

                    dayMaxEvents:true,


                    headerToolbar:{

                        left:"prev,next today",

                        center:"title",

                        right:"dayGridMonth"

                    },


                    buttonText:{

                        today:"Hoy",

                        month:"Mes",

                        week:"Semana"

                    },


                    /*
                    ==================================================
                    CLICK EN UN DÍA
                    ==================================================
                    */

                    dateClick:function(info){

                        const fecha =
                            encodeURIComponent(
                                info.dateStr
                            );


                        Promise.all([

                            /*
                            ==========================================
                            VACACIONES
                            ==========================================
                            */

                            fetch(
                                "/app_fichaje/public/vacacionesDia.php?fecha=" +
                                fecha
                            )

                            .then(
                                function(response){

                                    if(!response.ok){

                                        throw new Error(
                                            "Error al cargar las vacaciones."
                                        );

                                    }

                                    return response.json();

                                }
                            ),


                            /*
                            ==========================================
                            FESTIVOS
                            ==========================================
                            */

                            fetch(
                                "/app_fichaje/public/festivosDia.php?fecha=" +
                                fecha
                            )

                            .then(
                                function(response){

                                    if(!response.ok){

                                        throw new Error(
                                            "Error al cargar los festivos."
                                        );

                                    }

                                    return response.json();

                                }
                            )

                        ])


                        .then(
                            function(resultados){

                                const vacaciones =
                                    resultados[0];

                                const festivos =
                                    resultados[1];

                                let html = "";


                                /*
                                ==========================================
                                MOSTRAR FESTIVOS
                                ==========================================
                                */

                                if(
                                    Array.isArray(festivos) &&
                                    festivos.length > 0
                                ){

                                    festivos.forEach(
                                        function(f){

                                            html += `

                                                <div
                                                    class="lista-evento"
                                                    style="position:relative;"
                                                >

                                                    <strong>
                                                        Festivo ${
                                                            f.nombre ||
                                                            "Sin nombre indicado"
                                                        }
                                                    </strong>

                                                    <br>

                                                    <small>
                                                        ${
                                                            f.empresa ||
                                                            "Empresa no indicada"
                                                        }
                                                    </small>

                                                    <div
                                                        style="
                                                            margin-top:15px;
                                                        "
                                                    >

                                                        <button
                                                            type="button"
                                                            class="btn-delete"
                                                            onclick="
                                                                eliminarFestivo(
                                                                    ${f.id},
                                                                    '${info.dateStr}'
                                                                )
                                                            "
                                                        >

                                                            Eliminar

                                                        </button>

                                                    </div>

                                                </div>

                                                <hr>

                                            `;

                                        }
                                    );

                                }


                                /*
                                ==========================================
                                MOSTRAR VACACIONES
                                ==========================================
                                */

                                if(
                                    Array.isArray(vacaciones) &&
                                    vacaciones.length > 0
                                ){

                                    vacaciones.forEach(
                                        function(v){

                                            html += `

                                                <div
                                                    class="lista-evento"
                                                >

                                                    <strong>
                                                        ${v.nombre}
                                                    </strong>

                                                    <br>

                                                    <small>
                                                        ${v.empresa}
                                                    </small>

                                                    <button
    type="button"
    class="btn-delete btn-eliminar-vacacion"
    data-id="${v.id}"
    data-fecha="${info.dateStr}"
>
    Eliminar
</button>

                                                </div>

                                                <hr>

                                            `;

                                        }
                                    );

                                }


                                /*
                                ==========================================
                                SI NO HAY NADA
                                ==========================================
                                */

                                if(

                                    (!Array.isArray(vacaciones) ||
                                    vacaciones.length === 0)

                                    &&

                                    (!Array.isArray(festivos) ||
                                    festivos.length === 0)

                                ){

                                    html =
                                        "<p>No hay vacaciones ni festivos este día.</p>";

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

                                    console.error(
                                        "No existe el drawer del día."
                                    );

                                    return;

                                }


                                titulo.innerHTML =
                                    "Vacaciones y festivos - " +
                                    info.dateStr;


                                contenido.innerHTML =
                                    html;

                                    document
    .querySelectorAll(".btn-eliminar-vacacion")
    .forEach(
        function(boton){

            boton.addEventListener(
                "click",
                function(event){

                    event.preventDefault();
                    event.stopPropagation();

                    const id =
                        this.dataset.id;

                    const fecha =
                        this.dataset.fecha;

                    eliminarVacaciones(
                        id,
                        fecha
                    );

                }
            );

        }
    );

                                drawer.classList.add("show");


                                if(overlay){

                                    overlay.classList.add("show");

                                }

                            }
                        )


                        .catch(
                            function(error){

                                console.error(
                                    "Error cargando vacaciones/festivos:",
                                    error
                                );


                                alert(
                                    "Error al cargar las vacaciones y los festivos."
                                );

                            }
                        );

                    },


                    /*
                    ==================================================
                    EVENTOS DEL CALENDARIO
                    ==================================================
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

                                tipo:
                                    "<?= $evento["extendedProps"]["tipo"] ?? "" ?>",

                                empresa:
                                    "<?= addslashes(
                                        $evento["extendedProps"]["empresa"] ?? ""
                                    ) ?>",

                                empleados:
                                    <?= json_encode(
                                        $evento["extendedProps"]["empleados"] ?? []
                                    ) ?>,

                                usuarios:
                                    <?= json_encode(
                                        $evento["extendedProps"]["usuarios"] ?? []
                                    ) ?>,

                                motivo:
                                    "<?= addslashes(
                                        $evento["extendedProps"]["motivo"] ?? ""
                                    ) ?>"

                            }

                        },

                        <?php endforeach; ?>

                    ]

                }

            );


        calendar.render();


        /*
        ==========================================================
        FILTRAR EMPLEADOS POR EMPRESA
        ==========================================================
        */

        const empresaVacaciones =
            document.getElementById(
                "empresaVacaciones"
            );


        const usuario =
            document.getElementById(
                "usuario_id"
            );


        if(
            empresaVacaciones &&
            usuario
        ){

            const opcionesUsuarios =
                Array.from(
                    usuario.options
                );


            empresaVacaciones.addEventListener(
                "change",
                function(){

                    const empresa =
                        this.value;


                    usuario.innerHTML =
                        '<option value="">Seleccione un empleado</option>';


                    opcionesUsuarios.forEach(
                        function(opcion){

                            if(opcion.value == ""){

                                return;

                            }


                            if(
                                opcion.dataset.empresa ==
                                empresa
                            ){

                                usuario.appendChild(
                                    opcion.cloneNode(true)
                                );

                            }

                        }
                    );

                }
            );

        }


        /*
        ==========================================================
        OCULTAR EVENTOS AL CARGAR
        ==========================================================
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


        /*
        ==========================================================
        FILTROS
        ==========================================================
        */

        const filtroEmpresaCalendario =
            document.getElementById(
                "filtroEmpresaCalendario"
            );


        const filtroEmpleadoCalendario =
            document.getElementById(
                "filtroEmpleadoCalendario"
            );


        const empleadosOriginales =
            Array.from(
                filtroEmpleadoCalendario.options
            );


        filtroEmpresaCalendario.addEventListener(
            "change",
            function(){

                const empresa =
                    this.value;


                filtroEmpleadoCalendario.innerHTML =
                    '<option value="">Todos</option>';


                empleadosOriginales.forEach(
                    function(opcion){

                        if(opcion.value == ""){

                            return;

                        }


                        if(

                            empresa == "" ||

                            opcion.dataset.empresa ==
                            empresa

                        ){

                            filtroEmpleadoCalendario.appendChild(
                                opcion.cloneNode(true)
                            );

                        }

                    }
                );


                filtrarCalendario();

            }
        );


        filtroEmpleadoCalendario.addEventListener(
            "change",
            filtrarCalendario
        );

    }
);


/*
============================================================
FILTROS DEL CALENDARIO
============================================================
*/

function filtrarCalendario(){

    const empresa =
        document.getElementById(
            "filtroEmpresaCalendario"
        ).value;


    const empleado =
        document.getElementById(
            "filtroEmpleadoCalendario"
        ).value;


    calendar
        .getEvents()
        .forEach(
            function(evento){

                let mostrar = true;


                /*
                ================================================
                FILTRO EMPRESA
                ================================================
                */

                if(

                    empresa !== "" &&

                    evento.extendedProps.empresa !=
                    empresa

                ){

                    mostrar = false;

                }


                /*
                ================================================
                FILTRO EMPLEADO
                ================================================
                */

                if(

                    mostrar &&

                    empleado !== "" &&

                    evento.extendedProps.tipo ===
                    "vacaciones"

                ){

                    const usuarios =
                        (
                            evento.extendedProps.usuarios ||
                            []
                        ).map(Number);


                    if(
                        !usuarios.includes(
                            Number(empleado)
                        )
                    ){

                        mostrar = false;

                    }

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
============================================================
EDITAR VACACIÓN
============================================================
*/

function editarVacacion(id){

    alert(
        "Editar vacaciones " + id
    );

}


/*
============================================================
ELIMINAR VACACIONES
============================================================
*/

function eliminarVacaciones(
    id,
    fecha
){

    if(
        !confirm(
            "¿Eliminar este día de vacaciones?"
        )
    ){

        return;

    }


    window.location =
        "vacaciones.php?eliminar=" +
        id +
        "&fecha=" +
        encodeURIComponent(fecha);

}


/*
============================================================
ELIMINAR FESTIVO
============================================================
*/

function eliminarFestivo(
    id,
    fecha
){

    if(
        !confirm(
            "¿Seguro que quieres eliminar este festivo?"
        )
    ){

        return;

    }


    window.location =
        "vacaciones.php?eliminar_festivo=" +
        id +
        "&fecha=" +
        encodeURIComponent(fecha);

}


/*
============================================================
RESETEAR DRAWER
============================================================
*/

document
    .querySelectorAll(
        '[onclick^="abrirDrawer"]'
    )
    .forEach(
        function(boton){

            boton.addEventListener(
                "click",
                function(){

                    const empresa =
                        document.getElementById(
                            "empresaVacaciones"
                        );


                    const usuario =
                        document.getElementById(
                            "usuario_id"
                        );


                    const nombreFestivo =
                        document.getElementById(
                            "nombreFestivo"
                        );


                    if(empresa){

                        empresa.value = "";

                    }


                    if(usuario){

                        usuario.value = "";


                        Array.from(
                            usuario.options
                        ).forEach(
                            function(opcion){

                                opcion.hidden = false;

                            }
                        );

                    }


                    if(nombreFestivo){

                        nombreFestivo.value = "";

                    }

                }
            );

        }
    );

</script>


<?php

include "../views/layouts/footer.php";

?>