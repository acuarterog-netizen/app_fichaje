<?php

require_once "../core/Auth.php";
require_once "../models/Vacaciones.php";
require_once "../models/Usuario.php";
require_once "../models/Empresa.php";

Auth::verificarSesion();

if(
    $_SESSION["usuario"]["rol"]!="admin" &&
    $_SESSION["usuario"]["rol"]!="encargado"
){
    header("Location: dashboard.php");
    exit;
}

$vacacionesModel = new Vacaciones();
if(isset($_GET["eliminar"])){

    $vacacionesModel->eliminarVacaciones(

    $_GET["eliminar"],
    $_GET["fecha"]

);

    header("Location: vacaciones.php");

    exit;

}
$usuarioModel    = new Usuario();
$empresaModel    = new Empresa();

$usuarios = $usuarioModel->obtenerEmpleados();
$empresas = $empresaModel->obtenerEmpresas();

$mensaje="";

if(isset($_POST["guardar"])){

    if($_POST["tipo"]=="vacaciones"){

        $vacacionesModel->crearVacaciones(

            $_POST["usuario_id"],
            $_POST["fecha_inicio"],
            $_POST["fecha_fin"],
            $_POST["comentario"]

        );

        $mensaje="Vacaciones registradas correctamente.";

    }

    if($_POST["tipo"]=="festivo"){

        $vacacionesModel->crearFestivoEmpresa(

            $_POST["empresa_id"],
            $_POST["fecha"],
            $_POST["comentario"]

        );

        $mensaje="Festivo registrado correctamente.";

    }

}

$eventos = $vacacionesModel->obtenerEventosCalendario();

include "../views/layouts/header.php";
include "../views/layouts/sidebar.php";

?>

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

<h1>Vacaciones</h1>

<?php if($mensaje!=""): ?>

<div class="alert alert-success">

    <?php echo $mensaje; ?>

</div>

<?php endif; ?>

<div class="vacaciones-layout">

    <aside class="vacaciones-sidebar">

        <div class="fichaje-card">

            <button
                class="btn-main-blue btn-full"
                onclick="abrirDrawer('vacaciones')">

                ➕ Agregar vacaciones

            </button>

            <button
                class="btn-main-blue btn-full"
                style="margin-top:10px;"
                onclick="abrirDrawer('festivo')">

                📅 Agregar festivo

            </button>

        </div>

        <div class="fichaje-card">

            <h2>Filtros</h2>

            <div class="form-group">

                <label>Empresa</label>

                <select
                    id="filtroEmpresaCalendario"
                    class="form-control">

                    <option value="">Todas</option>

                    <?php foreach($empresas as $empresa): ?>

                        <option value="<?php echo $empresa["id"]; ?>">

                            <?php echo $empresa["nombre"]; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group" style="margin-top:15px;">

                <label>Empleado</label>

                <select
    id="filtroEmpleadoCalendario"
    class="form-control">

    <option value="">Todos</option>

    <?php foreach($usuarios as $u): ?>

        <option
            value="<?php echo $u["id"]; ?>"
            data-empresa="<?php echo $u["empresa_id"]; ?>">

            <?php echo $u["nombre"]; ?>

        </option>

    <?php endforeach; ?>

</select>

            </div>

        </div>

    </aside>

    <section class="vacaciones-calendar">

        <div class="fichaje-card">

            <div id="calendar"></div>

        </div>

    </section>

</div>

<div
class="overlay-vacaciones"
id="overlay"
onclick="cerrarDrawer()"></div>

<div
class="drawer-vacaciones"
id="drawer">

    <div class="drawer-header">

        <h2 id="tituloDrawer">

            Agregar vacaciones

        </h2>

        <button
            class="drawer-close"
            onclick="cerrarDrawer()">

            ×

        </button>

    </div>

<form method="POST">

<input
type="hidden"
name="tipo"
id="tipo"
value="vacaciones">

<div
    class="form-group"
    id="empresaVacacionesDiv">

    <label>Empresa</label>

    <select
        id="empresaVacaciones"
        class="form-control">

        <option value="">Seleccione una empresa</option>

        <?php foreach($empresas as $empresa): ?>

            <option
                value="<?php echo $empresa["id"]; ?>">

                <?php echo $empresa["nombre"]; ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

<div
    class="form-group"
    id="empleadoDiv">

    <label>Empleado</label>

    <select
    id="usuario_id"
    name="usuario_id"
    class="form-control">

    <option value="">Seleccione un empleado</option>

    <?php foreach($usuarios as $u): ?>

        <option
            value="<?php echo $u["id"]; ?>"
            data-empresa="<?php echo $u["empresa_id"]; ?>">

            <?php echo $u["nombre"]; ?>

        </option>

    <?php endforeach; ?>

</select>

</div>

<div
    class="form-group oculto"
    id="empresaDiv">

    <label>Empresa</label>

    <select
        name="empresa_id"
        class="form-control">

        <?php foreach($empresas as $empresa): ?>

            <option
                value="<?php echo $empresa["id"]; ?>">

                <?php echo $empresa["nombre"]; ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

<div
    class="form-group"
    id="inicioDiv">

    <label>Fecha inicio</label>

    <input
        type="date"
        id="fecha_inicio"
        name="fecha_inicio"
        class="form-control">

</div>

<div
    class="form-group"
    id="finDiv">

    <label>Fecha fin</label>

    <input
        type="date"
        id="fecha_fin"
        name="fecha_fin"
        class="form-control">

</div>

<div
    class="form-group oculto"
    id="fechaFestivo">

    <label>Fecha</label>

    <input
        type="date"
        id="fecha"
        name="fecha"
        class="form-control">

</div>

<div class="form-group">

    <label>Comentario</label>

    <textarea
        name="comentario"
        rows="4"
        class="form-control"
        placeholder="Escribe un comentario..."></textarea>

</div>

<div class="drawer-footer">

    <button
        type="button"
        class="btn-delete"
        onclick="cerrarDrawer()">

        Cancelar

    </button>

    <button
        type="submit"
        name="guardar"
        class="btn-main-blue">

        Guardar

    </button>

</div>

</form>

</div>

<div class="drawer-vacaciones" id="drawerDia">

    <div class="drawer-header">

        <h2 id="tituloDrawerDia">Vacaciones</h2>

        <button
            class="drawer-close"
            onclick="cerrarDrawerDia()">

            ×

        </button>

    </div>

    <div id="contenidoDrawerDia" style="padding:20px;">

    </div>

    <div class="drawer-footer">

        <button
            class="btn-main-blue"
            onclick="cerrarDrawerDia()">

            Volver

        </button>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>

let calendar;

function abrirDrawer(tipo){

    const drawer = document.getElementById("drawer");
    const overlay = document.getElementById("overlay");

    drawer.classList.add("show");
    overlay.classList.add("show");

    document.getElementById("tipo").value = tipo;

    if(tipo=="vacaciones"){

        document.getElementById("tituloDrawer").innerHTML="Agregar vacaciones";

        document.getElementById("empresaVacacionesDiv").classList.remove("oculto");
        document.getElementById("empleadoDiv").classList.remove("oculto");

        document.getElementById("empresaDiv").classList.add("oculto");

        document.getElementById("inicioDiv").classList.remove("oculto");
        document.getElementById("finDiv").classList.remove("oculto");

        document.getElementById("fechaFestivo").classList.add("oculto");

    }else{

        document.getElementById("tituloDrawer").innerHTML="Agregar festivo";

        document.getElementById("empresaVacacionesDiv").classList.add("oculto");
        document.getElementById("empleadoDiv").classList.add("oculto");

        document.getElementById("empresaDiv").classList.remove("oculto");

        document.getElementById("inicioDiv").classList.add("oculto");
        document.getElementById("finDiv").classList.add("oculto");

        document.getElementById("fechaFestivo").classList.remove("oculto");

    }

}

function cerrarDrawer(){

    document.getElementById("drawer").classList.remove("show");
    document.getElementById("overlay").classList.remove("show");

}

function cerrarDrawerDia(){

    document.getElementById("drawerDia")
        .classList.remove("show");

    document.getElementById("overlay")
        .classList.remove("show");

}

document.addEventListener("DOMContentLoaded",function(){

    calendar = new FullCalendar.Calendar(

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

            dateClick:function(info){

    fetch(
        "/app_fichaje/public/vacacionesDia.php?fecha=" +
        encodeURIComponent(info.dateStr)
    )

    .then(response => response.json())

    .then(function(datos){

        let html = "";

        if(datos.length === 0){

            html = "<p>No hay empleados de vacaciones este día.</p>";

        }else{

            datos.forEach(function(v){

                html += `

                    <div class="lista-evento">

                        <strong>${v.nombre}</strong><br>

                        <small>${v.empresa}</small>

                        <button
                            class="btn-delete"
                            onclick="eliminarVacaciones(${v.id}, '${info.dateStr}')">

                            Eliminar

                        </button>

                    </div>

                `;

            });

        }

        const titulo =
            document.getElementById("tituloDrawerDia");

        const contenido =
            document.getElementById("contenidoDrawerDia");

        const drawer =
            document.getElementById("drawerDia");

        const overlay =
            document.getElementById("overlay");

        if(!titulo || !contenido || !drawer){

            console.error(
                "No existe el drawer de vacaciones."
            );

            return;

        }

        titulo.innerHTML =
            "Vacaciones - " + info.dateStr;

        contenido.innerHTML = html;

        drawer.classList.add("show");

        if(overlay){

            overlay.classList.add("show");

        }

    })

    .catch(function(error){

        console.error(error);

        alert("Error al cargar las vacaciones.");

    });

},
            events:[

                <?php foreach($eventos as $evento): ?>

                {

                    id:"<?= $evento["id"] ?>",

                    title:"<?= addslashes($evento["title"]) ?>",

                    start:"<?= $evento["start"] ?>",

                    end:"<?= $evento["end"] ?>",

                    backgroundColor:"<?= $evento["color"] ?>",

                    borderColor:"<?= $evento["color"] ?>",

                    textColor:"#ffffff",

                    allDay:true,

                    extendedProps:{

    tipo:"<?= $evento["extendedProps"]["tipo"] ?? "" ?>",

    empresa:"<?= addslashes($evento["extendedProps"]["empresa"] ?? "") ?>",

    empleados: <?= json_encode($evento["extendedProps"]["empleados"] ?? []) ?>,

    usuarios: <?= json_encode($evento["extendedProps"]["usuarios"] ?? []) ?>,

    motivo:"<?= addslashes($evento["extendedProps"]["motivo"] ?? "") ?>"

}

                },

                    <?php endforeach; ?>

            ]

                }

    );

    calendar.render();

    /*
    =====================================
    FILTRAR EMPLEADOS POR EMPRESA
    =====================================
    */

    const empresaVacaciones =
        document.getElementById("empresaVacaciones");

    const usuario =
        document.getElementById("usuario_id");

    if(empresaVacaciones && usuario){

        const opcionesUsuarios = Array.from(usuario.options);

empresaVacaciones.addEventListener("change", function () {

    const empresa = this.value;

    usuario.innerHTML = '<option value="">Seleccione un empleado</option>';

    opcionesUsuarios.forEach(function(opcion){

        if(opcion.value == ""){
            return;
        }

        if(opcion.dataset.empresa == empresa){

            usuario.appendChild(opcion.cloneNode(true));

        }

    });

});

    }

    /*
    =====================================
    OCULTAR EVENTOS AL CARGAR
    =====================================
    */

    calendar.getEvents().forEach(function(evento){

        evento.setProp("display","none");

    });

    filtrarCalendario();

    const filtroEmpresaCalendario =
    document.getElementById("filtroEmpresaCalendario");

const filtroEmpleadoCalendario =
    document.getElementById("filtroEmpleadoCalendario");

const empleadosOriginales =
    Array.from(filtroEmpleadoCalendario.options);

filtroEmpresaCalendario.addEventListener("change", function(){

    const empresa = this.value;

    filtroEmpleadoCalendario.innerHTML =
        '<option value="">Todos</option>';

    empleadosOriginales.forEach(function(opcion){

        if(opcion.value==""){
            return;
        }

        if(
            empresa=="" ||
            opcion.dataset.empresa==empresa
        ){

            filtroEmpleadoCalendario.appendChild(
                opcion.cloneNode(true)
            );

        }

    });

    filtrarCalendario();

});

filtroEmpleadoCalendario.addEventListener(
    "change",
    filtrarCalendario
);

                });

/*
==========================================
FILTROS DEL CALENDARIO
==========================================
*/

function filtrarCalendario(){

    const empresa =
        document.getElementById("filtroEmpresaCalendario").value;

    const empleado =
        document.getElementById("filtroEmpleadoCalendario").value;

    calendar.getEvents().forEach(function(evento){

        let mostrar = true;

        // Filtro empresa
        if(
            empresa !== "" &&
            evento.extendedProps.empresa != empresa
        ){
            mostrar = false;
        }

        // Filtro empleado
        if(
            mostrar &&
            empleado !== "" &&
            evento.extendedProps.tipo === "vacaciones"
        ){

            const usuarios =
                (evento.extendedProps.usuarios || []).map(Number);

            if(!usuarios.includes(Number(empleado))){
                mostrar = false;
            }

        }

        evento.setProp(
            "display",
            mostrar ? "auto" : "none"
        );

    });

}



function editarVacacion(id){

    alert("Editar vacaciones " + id);

}

function eliminarVacaciones(id, fecha){

    if(!confirm("¿Eliminar este día de vacaciones?")){
        return;
    }

    window.location =
        "vacaciones.php?eliminar=" + id +
        "&fecha=" + fecha;

}
/*
==========================================
RESETEAR DRAWER
==========================================
*/

document
.querySelectorAll('[onclick^="abrirDrawer"]')
.forEach(function(boton){

    boton.addEventListener("click",function(){

        const empresa =
            document.getElementById("empresaVacaciones");

        const usuario =
            document.getElementById("usuario_id");

        if(empresa){

            empresa.value="";

        }

        if(usuario){

            usuario.value="";

            Array.from(usuario.options).forEach(function(opcion){

                opcion.hidden = false;

            });

        }

    });

});

</script>

<?php

include "../views/layouts/footer.php";

?>