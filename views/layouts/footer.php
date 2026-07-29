    </div>

</div>

<script>

/* ==========================================================================
   BOTÓN DARK MODE
========================================================================== */

const botonDark = document.getElementById("toggle-darkmode");

/* ==========================================================================
   CARGAR ESTADO GUARDADO
========================================================================== */

if(localStorage.getItem("darkmode") === "activo") {

    document.body.classList.add("dark-mode");

}

/* ==========================================================================
   CAMBIAR MODO
========================================================================== */

if(botonDark) {

    botonDark.addEventListener("click", () => {

        document.body.classList.toggle("dark-mode");

        if(document.body.classList.contains("dark-mode")) {

            localStorage.setItem("darkmode", "activo");

            botonDark.innerHTML = "☀️ Modo claro";

        } else {

            localStorage.removeItem("darkmode");

            botonDark.innerHTML = "🌙 Modo oscuro";

        }

    });

}

/* ==========================================================================
   TEXTO INICIAL DEL BOTÓN
========================================================================== */

if(document.body.classList.contains("dark-mode")) {

    botonDark.innerHTML = "☀️ Modo claro";

}

</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select-buscador').select2({

            placeholder: "Buscar empleado...",
            width: '100%'
        });
    });

</script>
</body>
</html>