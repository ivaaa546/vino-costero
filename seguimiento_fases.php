
<?php
    require_once("./api/login/end_verificar_enologo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Seguimiento de Fases</title>
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
        <h1>Administración del seguimiento de fases</h1>
    </main>

    <section>
        <h2>Registrar seguimiento de fase</h2>

        <form id="formSeguimiento">
            <label for="id_produccion">Producción</label>
            <select id="id_produccion" name="id_produccion" required>
                <option value="">Seleccione una producción</option>
            </select>

            <label for="id_fase">Fase de producción</label>
            <select id="id_fase" name="id_fase" required>
                <option value="">Seleccione una fase</option>
            </select>

            <label for="fecha_inicio">Fecha y hora de inicio</label>
            <input type="datetime-local" id="fecha_inicio"
                   name="fecha_inicio" required>

            <label for="fecha_fin">Fecha y hora de finalización</label>
            <input type="datetime-local" id="fecha_fin" name="fecha_fin">

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <option value="Pendiente">Pendiente</option>
                <option value="En proceso">En proceso</option>
                <option value="Completada">Completada</option>
                <option value="Cancelada">Cancelada</option>
            </select>

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones del seguimiento"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de seguimientos de fases</h2>

        <label for="buscar">Buscar por producción</label>
        <input type="text" id="buscar" placeholder="Nombre del vino">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todas</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producción</th>
                    <th>Fase</th>
                    <th>Fecha de inicio</th>
                    <th>Fecha de finalización</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        let idSeguimientoSelect = null;
        let editando = false;
        let seguimientosCargados = [];
        let fasesCargadas = [];

        const formulario = document.getElementById("formSeguimiento");
        const produccion = document.getElementById("id_produccion");
        const fase = document.getElementById("id_fase");
        const fechaInicio = document.getElementById("fecha_inicio");
        const fechaFin = document.getElementById("fecha_fin");
        const estado = document.getElementById("estado");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            produccion, fase, fechaInicio, fechaFin,
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
            idSeguimientoSelect = null;
            editando = false;

            bloquearCampos(false);
            btnGuardar.textContent = "Registrar";
            estado.value = "Pendiente";
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

        // Cargar producciones en el selector
        async function cargarProducciones() {
            try {
                const respuesta = await fetch("./api/producciones/visualizar.php");
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las producciones");
                    return;
                }

                const producciones = resultado.producciones || [];

                produccion.innerHTML =
                    '<option value="">Seleccione una producción</option>';

                producciones.forEach(function(p) {
                    const opcion = document.createElement("option");
                    opcion.value = p.id_produccion;
                    opcion.textContent =
                        "#" + p.id_produccion + " - " + p.nombre_vino;

                    produccion.appendChild(opcion);
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las producciones");
            }
        }

        // Cargar las fases existentes en la base de datos
        async function cargarFases() {
            try {
                const respuesta = await fetch(
                    "./api/seguimiento_fases/visualizar.php"
                );
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las fases");
                    return;
                }

                fasesCargadas = resultado.fases || [];

                fase.innerHTML =
                    '<option value="">Seleccione una fase</option>';

                fasesCargadas.forEach(function(f) {
                    const opcion = document.createElement("option");
                    opcion.value = f.id_fase;

                    opcion.textContent =
                        f.orden_fase + ". " + f.nombre;

                    if (f.estado !== "Activa") {
                        opcion.disabled = true;
                    }

                    if (f.descripcion) {
                        opcion.title = f.descripcion;
                    }

                    fase.appendChild(opcion);
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las fases");
            }
        }


        // Registrar y editar seguimientos
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            if (fechaFin.value && fechaFin.value < fechaInicio.value) {
                alert("La fecha de finalización no puede ser anterior a la fecha de inicio");
                return;
            }

            const datos = new FormData(formulario);
            let endpoint = "./api/seguimiento_fases/crear.php";

            if (editando && idSeguimientoSelect !== null) {
                endpoint = "./api/seguimiento_fases/editar.php";
                datos.append("id_seguimiento", idSeguimientoSelect);
            }

            try {
                btnGuardar.disabled = true;

                const respuesta = await fetch(endpoint, {
                    method: "POST",
                    body: datos
                });

                const resultado = await respuesta.json();

                if (resultado.code === 200 || resultado.code === 201) {
                    alert(resultado.message);
                    limpiarFormulario();

                    await verSeguimientos(
                        document.getElementById("buscar").value.trim()
                    );
                } else {
                    alert(resultado.message || "No se pudo guardar el seguimiento");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            } finally {
                btnGuardar.disabled = false;
            }
        });

        // Visualizar seguimientos
        async function verSeguimientos(busqueda = "") {
            try {
                const respuesta = await fetch(
                    "./api/seguimiento_fases/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar los seguimientos");
                    return;
                }

                seguimientosCargados = resultado.seguimientos || [];

                const filtro = busqueda.toLowerCase();

                const filtrados = seguimientosCargados.filter(function(s) {
                    return (s.nombre_vino || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 8;
                    celda.textContent = "No se encontraron registros";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(s) {
                    const fila = document.createElement("tr");

                    [
                        s.id_seguimiento,
                        "#" + s.id_produccion + " - " + s.nombre_vino,
                        "Fase #" + s.nombre_fase,,
                        s.fecha_inicio,
                        s.fecha_fin ?? "",
                        s.estado,
                        s.observaciones ?? ""
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

                    // Seleccionar un seguimiento
                    btnSeleccionar.addEventListener("click", function() {
                        idSeguimientoSelect = s.id_seguimiento;
                        editando = false;

                        produccion.value = s.id_produccion;
                        fase.value = s.id_fase;
                        fechaInicio.value = s.fecha_inicio.replace(" ", "T").substring(0, 16);
                        fechaFin.value = s.fecha_fin
                            ? s.fecha_fin.replace(" ", "T").substring(0, 16)
                            : "";
                        estado.value = s.estado;
                        observaciones.value = s.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar un seguimiento
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar el seguimiento #" +
                            s.id_seguimiento + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append("id_seguimiento", s.id_seguimiento);

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/seguimiento_fases/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar =
                                await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idSeguimientoSelect == s.id_seguimiento) {
                                    limpiarFormulario();
                                }

                                await verSeguimientos(
                                    document.getElementById("buscar").value.trim()
                                );
                            } else {
                                alert(resultadoEliminar.message);
                            }

                        } catch (error) {
                            console.error(error);
                            alert("Error al eliminar el seguimiento");
                        }
                    });
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar los registros de seguimiento");
            }
        }

        // Buscar por nombre del vino
        document.getElementById("btnBuscar").addEventListener("click", function() {
            verSeguimientos(
                document.getElementById("buscar").value.trim()
            );
        });

        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verSeguimientos(this.value.trim());
            }
        });

        // Mostrar todos los registros
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verSeguimientos();
        });

        // Activar edición
        btnEditar.addEventListener("click", function() {
            if (idSeguimientoSelect === null) {
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
            await cargarProducciones();
            await cargarFases();
            await verSeguimientos();
            limpiarFormulario();
        }

        iniciar();
    </script>

</body>
</html>
