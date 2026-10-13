<?php
    require_once("./api/login/end_verificar_agronomo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Siembras Enfermas</title>
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
        <h1>Administración de siembras enfermas</h1>
    </main>

    <section>
        <h2>Registrar enfermedad</h2>

        <form id="formEnfermedad">
            <label for="id_siembra">Siembra</label>
            <select id="id_siembra" name="id_siembra" required>
                <option value="">Seleccione una siembra</option>
            </select>

            <label for="nombre_enfermedad">Nombre de la enfermedad</label>
            <input type="text" id="nombre_enfermedad"
                   name="nombre_enfermedad" maxlength="120" required>

            <label for="fecha_deteccion">Fecha de detección</label>
            <input type="date" id="fecha_deteccion"
                   name="fecha_deteccion" required>

            <label for="cantidad_plantas_afectadas">Cantidad de plantas afectadas</label>
            <input type="number" id="cantidad_plantas_afectadas"
                   name="cantidad_plantas_afectadas"
                   min="1" step="1" required>

            <label for="nivel_gravedad">Nivel de gravedad</label>
            <select id="nivel_gravedad" name="nivel_gravedad" required>
                <option value="Leve">Leve</option>
                <option value="Moderada">Moderada</option>
                <option value="Grave">Grave</option>
            </select>

            <label for="tratamiento">Tratamiento</label>
            <textarea id="tratamiento" name="tratamiento"
                      placeholder="Tratamiento aplicado"></textarea>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <option value="Detectada">Detectada</option>
                <option value="En tratamiento">En tratamiento</option>
                <option value="Controlada">Controlada</option>
                <option value="Sin recuperacion">Sin recuperación</option>
            </select>

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones de la enfermedad"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de siembras enfermas</h2>

        <label for="buscar">Buscar por enfermedad</label>
        <input type="text" id="buscar" placeholder="Nombre de la enfermedad">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todas</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Siembra</th>
                    <th>Enfermedad</th>
                    <th>Fecha de detección</th>
                    <th>Plantas afectadas</th>
                    <th>Gravedad</th>
                    <th>Tratamiento</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        let idEnfermedadSelect = null;
        let editando = false;
        let enfermedadesCargadas = [];

        const formulario = document.getElementById("formEnfermedad");
        const siembra = document.getElementById("id_siembra");
        const nombreEnfermedad = document.getElementById("nombre_enfermedad");
        const fechaDeteccion = document.getElementById("fecha_deteccion");
        const cantidadAfectadas = document.getElementById("cantidad_plantas_afectadas");
        const gravedad = document.getElementById("nivel_gravedad");
        const tratamiento = document.getElementById("tratamiento");
        const estado = document.getElementById("estado");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            siembra, nombreEnfermedad, fechaDeteccion,
            cantidadAfectadas, gravedad, tratamiento,
            estado, observaciones
        ];

        // Habilitar o bloquear los campos
        function bloquearCampos(bloquear) {
            campos.forEach(campo => campo.disabled = bloquear);
            btnGuardar.disabled = bloquear;
        }

        // Limpiar el formulario
        function limpiarFormulario() {
            formulario.reset();
            idEnfermedadSelect = null;
            editando = false;

            bloquearCampos(false);
            btnGuardar.textContent = "Registrar";
            estado.value = "Detectada";
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
                    window.location.href = "../login.php";
                } else {
                    alert(resultado.message);
                }
            } catch (error) {
                console.error(error);
                alert("Error al cerrar sesión");
            }
        });

        // Cargar siembras en el selector
        async function cargarSiembras() {
            try {
                const respuesta = await fetch("./api/siembras/visualizar.php");
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las siembras");
                    return;
                }

                const siembras = resultado.siembras || [];

                siembra.innerHTML =
                    '<option value="">Seleccione una siembra</option>';

                siembras.forEach(function(s) {
                    const opcion = document.createElement("option");
                    opcion.value = s.id_siembra;
                    opcion.textContent =
                        "#" + s.id_siembra + " - " + s.variedad_uva;

                    siembra.appendChild(opcion);
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las siembras");
            }
        }

        // Registrar y editar enfermedades
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            const datos = new FormData(formulario);
            let endpoint = "./api/siembras_enfermas/crear.php";

            if (editando && idEnfermedadSelect !== null) {
                endpoint = "./api/siembras_enfermas/editar.php";
                datos.append("id_siembra_enferma", idEnfermedadSelect);
            }

            try {
                const respuesta = await fetch(endpoint, {
                    method: "POST",
                    body: datos
                });

                const resultado = await respuesta.json();

                if (resultado.code === 200 || resultado.code === 201) {
                    alert(resultado.message);
                    limpiarFormulario();

                    await verEnfermedades(
                        document.getElementById("buscar").value.trim()
                    );
                } else {
                    alert(resultado.message || "No se pudo guardar la enfermedad");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        // Visualizar enfermedades
        async function verEnfermedades(busqueda = "") {
            try {
                const respuesta = await fetch(
                    "./api/siembras_enfermas/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las enfermedades");
                    return;
                }

                enfermedadesCargadas = resultado.siembras_enfermas || [];

                const filtro = busqueda.toLowerCase();

                const filtrados = enfermedadesCargadas.filter(function(e) {
                    return (e.nombre_enfermedad || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 10;
                    celda.textContent = "No se encontraron registros";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(e) {
                    const fila = document.createElement("tr");

                    [
                        e.id_siembra_enferma,
                        "#" + e.id_siembra + " - " + e.variedad_uva,
                        e.nombre_enfermedad,
                        e.fecha_deteccion,
                        e.cantidad_plantas_afectadas,
                        e.nivel_gravedad,
                        e.tratamiento ?? "",
                        e.estado,
                        e.observaciones ?? ""
                    ].forEach(function(valor) {
                        const celda = document.createElement("td");
                        celda.textContent = valor ?? "";
                        fila.appendChild(celda);
                    });

                    const acciones = document.createElement("td");

                    const btnSeleccionar = document.createElement("button");
                    btnSeleccionar.type = "button";
                    btnSeleccionar.textContent = "Seleccionar";

                    const btnEliminar = document.createElement("button");
                    btnEliminar.type = "button";
                    btnEliminar.textContent = "Eliminar";

                    acciones.appendChild(btnSeleccionar);
                    acciones.appendChild(btnEliminar);
                    fila.appendChild(acciones);
                    tabla.appendChild(fila);

                    // Seleccionar una enfermedad
                    btnSeleccionar.addEventListener("click", function() {
                        idEnfermedadSelect = e.id_siembra_enferma;
                        editando = false;

                        siembra.value = e.id_siembra;
                        nombreEnfermedad.value = e.nombre_enfermedad;
                        fechaDeteccion.value = e.fecha_deteccion;
                        cantidadAfectadas.value = e.cantidad_plantas_afectadas;
                        gravedad.value = e.nivel_gravedad;
                        tratamiento.value = e.tratamiento ?? "";
                        estado.value = e.estado;
                        observaciones.value = e.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar una enfermedad
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar el registro de enfermedad #" +
                            e.id_siembra_enferma + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append(
                            "id_siembra_enferma",
                            e.id_siembra_enferma
                        );

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/siembras_enfermas/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar =
                                await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idEnfermedadSelect == e.id_siembra_enferma) {
                                    limpiarFormulario();
                                }

                                await verEnfermedades(
                                    document.getElementById("buscar").value.trim()
                                );
                            } else {
                                alert(resultadoEliminar.message);
                            }

                        } catch (error) {
                            console.error(error);
                            alert("Error al eliminar el registro");
                        }
                    });
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar los registros de enfermedades");
            }
        }

        // Buscar por nombre de enfermedad
        document.getElementById("btnBuscar").addEventListener("click", function() {
            verEnfermedades(
                document.getElementById("buscar").value.trim()
            );
        });

        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verEnfermedades(this.value.trim());
            }
        });

        // Mostrar todos los registros
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verEnfermedades();
        });

        // Activar edición
        btnEditar.addEventListener("click", function() {
            if (idEnfermedadSelect === null) {
                alert("Primero debe seleccionar un registro");
                return;
            }

            editando = true;
            bloquearCampos(false);
            btnGuardar.textContent = "Guardar cambios";
        });

        // Cancelar edición
        document.getElementById("btnCancelar").addEventListener("click", function() {
            limpiarFormulario();
        });

        // Inicializar página
        async function iniciar() {
            await cargarSiembras();
            await verEnfermedades();
        }

        iniciar();
    </script>

</body>
</html>
