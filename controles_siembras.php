<?php
    require_once("./api/login/end_verificar_agronomo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Controles de Siembra</title>
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
        <h1>Administración de controles de siembra</h1>
    </main>

    <section>
        <h2>Registrar control de siembra</h2>

        <form id="formControl">
            <label for="id_siembra">Siembra</label>
            <select id="id_siembra" name="id_siembra" required>
                <option value="">Seleccione una siembra</option>
            </select>

            <label for="fecha_control">Fecha del control</label>
            <input type="date" id="fecha_control"
                   name="fecha_control" required>

            <label for="estado_general">Estado general</label>
            <select id="estado_general" name="estado_general" required>
                <option value="Excelente">Excelente</option>
                <option value="Bueno">Bueno</option>
                <option value="Regular">Regular</option>
                <option value="Malo">Malo</option>
            </select>

            <label for="riego_realizado">Riego realizado</label>
            <input type="checkbox" id="riego_realizado"
                   name="riego_realizado" value="1">

            <label for="fertilizacion_realizada">Fertilización realizada</label>
            <input type="checkbox" id="fertilizacion_realizada"
                   name="fertilizacion_realizada" value="1">

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones del control"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de controles de siembra</h2>

        <label for="buscar">Buscar por variedad de uva</label>
        <input type="text" id="buscar" placeholder="Variedad de uva">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todos</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Siembra</th>
                    <th>Fecha</th>
                    <th>Estado general</th>
                    <th>Riego realizado</th>
                    <th>Fertilización realizada</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        let idControlSelect = null;
        let editando = false;
        let controlesCargados = [];

        const formulario = document.getElementById("formControl");
        const siembra = document.getElementById("id_siembra");
        const fecha = document.getElementById("fecha_control");
        const estado = document.getElementById("estado_general");
        const riego = document.getElementById("riego_realizado");
        const fertilizacion = document.getElementById("fertilizacion_realizada");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            siembra, fecha, estado,
            riego, fertilizacion, observaciones
        ];

        // Habilitar o bloquear los campos
        function bloquearCampos(bloquear) {
            campos.forEach(campo => campo.disabled = bloquear);
            btnGuardar.disabled = bloquear;
        }

        // Limpiar el formulario
        function limpiarFormulario() {
            formulario.reset();
            idControlSelect = null;
            editando = false;

            bloquearCampos(false);
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

        // Registrar y editar controles
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            const datos = new FormData(formulario);

            // Las casillas desmarcadas no se envían en FormData.
            datos.set("riego_realizado", riego.checked ? "1" : "0");
            datos.set("fertilizacion_realizada", fertilizacion.checked ? "1" : "0");

            let endpoint = "./api/controles_siembra/crear.php";

            if (editando && idControlSelect !== null) {
                endpoint = "./api/controles_siembra/editar.php";
                datos.append("id_control_siembra", idControlSelect);
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
                    await verControles();
                } else {
                    alert(resultado.message || "No se pudo guardar el control");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        // Visualizar controles
        async function verControles(busqueda = "") {
            try {
                const respuesta = await fetch(
                    "./api/controles_siembra/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar los controles");
                    return;
                }

                controlesCargados = resultado.controles_siembra || [];

                const filtro = busqueda.toLowerCase();

                const filtrados = controlesCargados.filter(function(c) {
                    return (c.variedad_uva || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 8;
                    celda.textContent = "No se encontraron controles";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(c) {
                    const fila = document.createElement("tr");

                    [
                        c.id_control_siembra,
                        "#" + c.id_siembra + " - " + c.variedad_uva,
                        c.fecha_control,
                        c.estado_general,
                        Number(c.riego_realizado) === 1 ? "Sí" : "No",
                        Number(c.fertilizacion_realizada) === 1 ? "Sí" : "No",
                        c.observaciones ?? ""
                    ].forEach(function(valor) {
                        const celda = document.createElement("td");
                        celda.textContent = valor;
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

                    // Seleccionar un control
                    btnSeleccionar.addEventListener("click", function() {
                        idControlSelect = c.id_control_siembra;
                        editando = false;

                        siembra.value = c.id_siembra;
                        fecha.value = c.fecha_control;
                        estado.value = c.estado_general;
                        riego.checked = Number(c.riego_realizado) === 1;
                        fertilizacion.checked =
                            Number(c.fertilizacion_realizada) === 1;
                        observaciones.value = c.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar un control
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar el control de siembra #" +
                            c.id_control_siembra + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append(
                            "id_control_siembra",
                            c.id_control_siembra
                        );

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/controles_siembra/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar =
                                await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idControlSelect == c.id_control_siembra) {
                                    limpiarFormulario();
                                }

                                await verControles(
                                    document.getElementById("buscar").value.trim()
                                );
                            } else {
                                alert(resultadoEliminar.message);
                            }

                        } catch (error) {
                            console.error(error);
                            alert("Error al eliminar el control");
                        }
                    });
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar los controles de siembra");
            }
        }

        // Buscar por variedad de uva
        document.getElementById("btnBuscar").addEventListener("click", function() {
            verControles(document.getElementById("buscar").value.trim());
        });

        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verControles(this.value.trim());
            }
        });

        // Mostrar todos los controles
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verControles();
        });

        // Activar edición
        btnEditar.addEventListener("click", function() {
            if (idControlSelect === null) {
                alert("Primero debe seleccionar un control");
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
            await verControles();
        }

        iniciar();
    </script>

</body>
</html>