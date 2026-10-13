
<?php
    require_once("./api/login/end_verificar_agronomo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Parcelas</title>
</head>
<body>

    <header>
        <nav>
            <ul>
                <li>
                    <button type="button" id="CerrarS">Cerrar Sesión</button>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Administración de parcelas</h1>
    </main>

    <section>
        <h2>Registrar nueva parcela</h2>

        <form id="formpar" method="POST">
            <label for="nombre">Nombre</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="80"
                placeholder="Ingrese nombre de la parcela"
                required
            >

            <label for="ubicacion">Ubicación</label>
            <input
                type="text"
                id="ubicacion"
                name="ubicacion"
                maxlength="150"
                placeholder="Ingrese ubicación de la parcela"
            >

            <label for="descripcion">Descripción</label>
            <textarea
                id="descripcion"
                name="descripcion"
                placeholder="Ingrese descripción de la parcela"
            ></textarea>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" disabled>
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
            </select>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de parcelas</h2>

        <label for="buscar">Buscar parcela</label>
        <input
            type="text"
            id="buscar"
            name="buscar"
            placeholder="Nombre de parcela"
        >

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todas</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Fecha de registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        // Variables para editar y eliminar
        let idparcelaSelect = null;
        let editando = false;
        let parcelasCargadas = [];

        const formulario = document.getElementById("formpar");
        const nombre = document.getElementById("nombre");
        const ubicacion = document.getElementById("ubicacion");
        const descripcion = document.getElementById("descripcion");
        const estado = document.getElementById("estado");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");
        const btnCancelar = document.getElementById("btnCancelar");

        // Habilitar o deshabilitar los campos
        function bloquearCampos(bloquear) {
            nombre.disabled = bloquear;
            ubicacion.disabled = bloquear;
            descripcion.disabled = bloquear;
            estado.disabled = bloquear;
            btnGuardar.disabled = bloquear;
        }

        // Restablecer el formulario
        function limpiarFormulario() {
            formulario.reset();
            idparcelaSelect = null;
            editando = false;
            nombre.disabled = false;
            ubicacion.disabled = false;
            descripcion.disabled = false;
            estado.disabled = true;
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Registrar";
        }

        // Cerrar sesión
        document.getElementById("CerrarS").addEventListener("click", async function() {
            try {
                const respuesta = await fetch(
                    "./api/login/end_cerrar_sesion.php",
                    { method: "POST" }
                );

                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    window.location.href = "./login.php";
                } else {
                    alert(resultado.message);
                }

            } catch (error) {
                console.error(error);
                alert("Ocurrió un error al cerrar sesión");
            }
        });

        // Registrar y editar parcelas
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            const datos = new FormData(formulario);

            // Si estamos editando, enviar el ID de la parcela
            if (editando && idparcelaSelect !== null) {
                datos.append("id_parcela", idparcelaSelect);
            }

            const endpoint = editando
                ? "./api/parcelas/editar.php"
                : "./api/parcelas/crear.php";

            try {
                const respuesta = await fetch(endpoint, {
                    method: "POST",
                    body: datos
                });

                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    alert(resultado.message);
                    limpiarFormulario();
                    await verParcelas();
                } else {
                    alert(resultado.message || "No se pudo guardar la parcela");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        // Visualizar todas las parcelas
        async function verParcelas(busqueda = "") {
            try {
                const respuesta = await fetch(
                    "./api/parcelas/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code === 200) {
                    // Se espera que el endpoint devuelva un arreglo
                    // en la propiedad resultado.parcelas.
                    parcelasCargadas = resultado.parcelas || [];

                    // Filtrar por nombre sin distinguir mayúsculas
                    const filtro = busqueda.toLowerCase();

                    const parcelasFiltradas = parcelasCargadas.filter(
                        function(parcela) {
                            return (parcela.nombre || "")
                                .toLowerCase()
                                .includes(filtro);
                        }
                    );

                    const tabla = document.querySelector("table tbody");
                    tabla.innerHTML = "";

                    if (parcelasFiltradas.length === 0) {
                        const fila = document.createElement("tr");
                        const celda = document.createElement("td");

                        celda.colSpan = 7;
                        celda.textContent = "No se encontraron parcelas";

                        fila.appendChild(celda);
                        tabla.appendChild(fila);
                        return;
                    }

                    parcelasFiltradas.forEach(function(parcela) {
                        const fila = document.createElement("tr");

                        // Crear celdas de forma segura
                        [
                            parcela.id_parcela,
                            parcela.nombre,
                            parcela.ubicacion || "",
                            parcela.descripcion || "",
                            parcela.estado,
                            parcela.fecha_registro || ""
                        ].forEach(function(valor) {
                            const celda = document.createElement("td");
                            celda.textContent = valor;
                            fila.appendChild(celda);
                        });

                        const acciones = document.createElement("td");

                        const btnSeleccionar = document.createElement("button");
                        btnSeleccionar.type = "button";
                        btnSeleccionar.textContent = "Seleccionar";
                        btnSeleccionar.className = "btnSeleccionar";

                        const btnEliminar = document.createElement("button");
                        btnEliminar.type = "button";
                        btnEliminar.textContent = "Eliminar";
                        btnEliminar.className = "btnEliminar";

                        acciones.appendChild(btnSeleccionar);
                        acciones.appendChild(btnEliminar);
                        fila.appendChild(acciones);
                        tabla.appendChild(fila);

                        // Seleccionar una parcela
                        btnSeleccionar.addEventListener("click", function() {
                            idparcelaSelect = parcela.id_parcela;
                            editando = false;

                            nombre.value = parcela.nombre || "";
                            ubicacion.value = parcela.ubicacion || "";
                            descripcion.value = parcela.descripcion || "";
                            estado.value = parcela.estado || "Activa";

                            bloquearCampos(true);
                            btnEditar.disabled = false;
                            btnGuardar.textContent = "Registrar";
                        });

                        // Eliminar una parcela
                        btnEliminar.addEventListener("click", async function() {
                            const confirmar = confirm(
                                "¿Estás seguro de que deseas eliminar la parcela '" +
                                parcela.nombre + "'?"
                            );

                            if (!confirmar) {
                                return;
                            }

                            const datos = new FormData();
                            datos.append("id_parcela", parcela.id_parcela);

                            try {
                                const respuestaEliminar = await fetch(
                                    "./api/parcelas/eliminar.php",
                                    {
                                        method: "POST",
                                        body: datos
                                    }
                                );

                                const resultadoEliminar =
                                    await respuestaEliminar.json();

                                if (resultadoEliminar.code === 200) {
                                    alert(resultadoEliminar.message);

                                    if (idparcelaSelect == parcela.id_parcela) {
                                        limpiarFormulario();
                                    }

                                    await verParcelas(
                                        document.getElementById("buscar").value.trim()
                                    );
                                } else {
                                    alert(resultadoEliminar.message);
                                }

                            } catch (error) {
                                console.error(error);
                                alert("Error al eliminar la parcela");
                            }
                        });
                    });

                } else {
                    alert(resultado.message || "No se pudieron cargar las parcelas");
                }

            } catch (error) {
                console.error(error);
                alert("Error al cargar las parcelas");
            }
        }

        // Buscar parcelas
        document.getElementById("btnBuscar").addEventListener("click", function() {
            const busqueda = document.getElementById("buscar").value.trim();
            verParcelas(busqueda);
        });

        // Buscar también al presionar Enter
        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verParcelas(this.value.trim());
            }
        });

        // Mostrar todas las parcelas
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verParcelas();
        });

        // Activar modo edición
        btnEditar.addEventListener("click", function() {
            if (idparcelaSelect === null) {
                alert("Primero debe seleccionar una parcela");
                return;
            }

            editando = true;

            bloquearCampos(false);
            btnGuardar.textContent = "Guardar cambios";
        });

        // Cancelar edición o selección
        btnCancelar.addEventListener("click", function() {
            limpiarFormulario();
        });

        // Cargar parcelas al abrir la página
        bloquearCampos(false);
        estado.disabled = true;
        btnEditar.disabled = false;

        verParcelas();
    </script>

</body>
</html>
