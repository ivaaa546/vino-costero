<?php
    require_once("./api/login/end_verificar_enologo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Producciones</title>
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
        <h1>Administración de producciones</h1>
    </main>

    <section>
        <h2>Registrar producción</h2>

        <form id="formProduccion">
            <label for="id_parcela">Parcela</label>
            <select id="id_parcela" name="id_parcela" required>
                <option value="">Seleccione una parcela</option>
            </select>

            <label for="nombre_vino">Nombre del vino</label>
            <input type="text" id="nombre_vino"
                   name="nombre_vino" maxlength="100" required>

            <label for="variedad_uva">Variedad de uva</label>
            <input type="text" id="variedad_uva"
                   name="variedad_uva" maxlength="80" required>

            <label for="fecha_inicio">Fecha de inicio</label>
            <input type="date" id="fecha_inicio"
                   name="fecha_inicio" required>

            <label for="fecha_fin">Fecha de finalización</label>
            <input type="date" id="fecha_fin" name="fecha_fin">

            <label for="cantidad_uva_kg">Cantidad de uva (kg)</label>
            <input type="number" id="cantidad_uva_kg"
                   name="cantidad_uva_kg" min="0" step="0.01"
                   value="0" required>

            <label for="cantidad_vino_litros">Cantidad de vino (litros)</label>
            <input type="number" id="cantidad_vino_litros"
                   name="cantidad_vino_litros" min="0" step="0.01"
                   value="0" required>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <option value="Planificada">Planificada</option>
                <option value="En proceso">En proceso</option>
                <option value="Finalizada">Finalizada</option>
                <option value="Cancelada">Cancelada</option>
            </select>

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones de la producción"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de producciones</h2>

        <label for="buscar">Buscar por vino</label>
        <input type="text" id="buscar" placeholder="Nombre del vino">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todas</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Parcela</th>
                    <th>Nombre del vino</th>
                    <th>Variedad de uva</th>
                    <th>Fecha de inicio</th>
                    <th>Fecha de finalización</th>
                    <th>Uva (kg)</th>
                    <th>Vino (litros)</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        let idProduccionSelect = null;
        let editando = false;
        let produccionesCargadas = [];

        const formulario = document.getElementById("formProduccion");
        const parcela = document.getElementById("id_parcela");
        const nombreVino = document.getElementById("nombre_vino");
        const variedadUva = document.getElementById("variedad_uva");
        const fechaInicio = document.getElementById("fecha_inicio");
        const fechaFin = document.getElementById("fecha_fin");
        const cantidadUva = document.getElementById("cantidad_uva_kg");
        const cantidadVino = document.getElementById("cantidad_vino_litros");
        const estado = document.getElementById("estado");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            parcela, nombreVino, variedadUva, fechaInicio,
            fechaFin, cantidadUva, cantidadVino, estado,
            observaciones
        ];

        // Habilitar o bloquear los campos
        function bloquearCampos(bloquear) {
            campos.forEach(campo => campo.disabled = bloquear);
            btnGuardar.disabled = bloquear;
        }

        // Limpiar el formulario
        function limpiarFormulario() {
            formulario.reset();
            idProduccionSelect = null;
            editando = false;

            bloquearCampos(false);
            btnGuardar.textContent = "Registrar";
            estado.value = "Planificada";
            cantidadUva.value = "0";
            cantidadVino.value = "0";
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

        // Cargar parcelas en el selector
        async function cargarParcelas() {
            try {
                const respuesta = await fetch("./api/parcelas/visualizar.php");
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las parcelas");
                    return;
                }

                const parcelas = resultado.parcelas || [];

                parcela.innerHTML =
                    '<option value="">Seleccione una parcela</option>';

                parcelas.forEach(function(p) {
                    const opcion = document.createElement("option");
                    opcion.value = p.id_parcela;
                    opcion.textContent = "#" + p.id_parcela + " - " + p.nombre;

                    if (p.estado !== "Activa") {
                        opcion.disabled = true;
                    }

                    parcela.appendChild(opcion);
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las parcelas");
            }
        }

        // Registrar y editar producciones
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            if (fechaFin.value && fechaFin.value < fechaInicio.value) {
                alert("La fecha de finalización no puede ser anterior a la fecha de inicio");
                return;
            }

            if (Number(cantidadUva.value) < 0 ||
                Number(cantidadVino.value) < 0) {
                alert("Las cantidades no pueden ser negativas");
                return;
            }

            const datos = new FormData(formulario);
            let endpoint = "./api/producciones/crear.php";

            if (editando && idProduccionSelect !== null) {
                endpoint = "./api/producciones/editar.php";
                datos.append("id_produccion", idProduccionSelect);
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

                    await verProducciones(
                        document.getElementById("buscar").value.trim()
                    );
                } else {
                    alert(resultado.message || "No se pudo guardar la producción");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            } finally {
                btnGuardar.disabled = false;
            }
        });

        // Visualizar producciones
        async function verProducciones(busqueda = "") {
            try {
                const respuesta = await fetch(
                    "./api/producciones/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las producciones");
                    return;
                }

                produccionesCargadas = resultado.producciones || [];

                const filtro = busqueda.toLowerCase();

                const filtrados = produccionesCargadas.filter(function(p) {
                    return (p.nombre_vino || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 11;
                    celda.textContent = "No se encontraron registros";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(p) {
                    const fila = document.createElement("tr");

                    [
                        p.id_produccion,
                        "#" + p.id_parcela + " - " + p.nombre_parcela,
                        p.nombre_vino,
                        p.variedad_uva,
                        p.fecha_inicio,
                        p.fecha_fin ?? "",
                        p.cantidad_uva_kg,
                        p.cantidad_vino_litros,
                        p.estado,
                        p.observaciones ?? ""
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

                    // Seleccionar una producción
                    btnSeleccionar.addEventListener("click", function() {
                        idProduccionSelect = p.id_produccion;
                        editando = false;

                        parcela.value = p.id_parcela;
                        nombreVino.value = p.nombre_vino;
                        variedadUva.value = p.variedad_uva;
                        fechaInicio.value = p.fecha_inicio;
                        fechaFin.value = p.fecha_fin ?? "";
                        cantidadUva.value = p.cantidad_uva_kg;
                        cantidadVino.value = p.cantidad_vino_litros;
                        estado.value = p.estado;
                        observaciones.value = p.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar una producción
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar la producción #" +
                            p.id_produccion + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append("id_produccion", p.id_produccion);

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/producciones/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar =
                                await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idProduccionSelect == p.id_produccion) {
                                    limpiarFormulario();
                                }

                                await verProducciones(
                                    document.getElementById("buscar").value.trim()
                                );
                            } else {
                                alert(resultadoEliminar.message);
                            }

                        } catch (error) {
                            console.error(error);
                            alert("Error al eliminar la producción");
                        }
                    });
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar los registros de producciones");
            }
        }

        // Buscar por nombre del vino
        document.getElementById("btnBuscar").addEventListener("click", function() {
            verProducciones(
                document.getElementById("buscar").value.trim()
            );
        });

        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verProducciones(this.value.trim());
            }
        });

        // Mostrar todos los registros
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verProducciones();
        });

        // Activar edición
        btnEditar.addEventListener("click", function() {
            if (idProduccionSelect === null) {
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
            await cargarParcelas();
            await verProducciones();
            limpiarFormulario();
        }

        iniciar();
    </script>

</body>
</html>
