<?php
    require_once("./api/login/end_verificar_agronomo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Siembras</title>
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
        <h1>Administración de siembras</h1>
    </main>

    <section>
        <h2>Registrar siembra</h2>

        <form id="formSiembra">
            <label for="id_parcela">Parcela</label>
            <select id="id_parcela" name="id_parcela" required>
                <option value="">Seleccione una parcela</option>
            </select>

            <label for="variedad_uva">Variedad de uva</label>
            <input type="text" id="variedad_uva"
                   name="variedad_uva" maxlength="80" required>

            <label for="fecha_siembra">Fecha de siembra</label>
            <input type="date" id="fecha_siembra"
                   name="fecha_siembra" required>

            <label for="cantidad_plantas">Cantidad de plantas</label>
            <input type="number" id="cantidad_plantas"
                   name="cantidad_plantas" min="1" step="1" required>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <option value="En crecimiento">En crecimiento</option>
                <option value="En producción">En producción</option>
                <option value="Finalizada">Finalizada</option>
                <option value="Suspendida">Suspendida</option>
            </select>

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones de la siembra"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de siembras</h2>

        <label for="buscar">Buscar por parcela</label>
        <input type="text" id="buscar" placeholder="Nombre de parcela">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todas</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Parcela</th>
                    <th>Variedad de uva</th>
                    <th>Fecha de siembra</th>
                    <th>Cantidad de plantas</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        let idSiembraSelect = null;
        let editando = false;
        let siembrasCargadas = [];

        const formulario = document.getElementById("formSiembra");
        const parcela = document.getElementById("id_parcela");
        const variedad = document.getElementById("variedad_uva");
        const fecha = document.getElementById("fecha_siembra");
        const cantidad = document.getElementById("cantidad_plantas");
        const estado = document.getElementById("estado");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            parcela, variedad, fecha,
            cantidad, estado, observaciones
        ];

        // Habilitar o bloquear los campos
        function bloquearCampos(bloquear) {
            campos.forEach(campo => campo.disabled = bloquear);
            btnGuardar.disabled = bloquear;
        }

        // Limpiar el formulario
        function limpiarFormulario() {
            formulario.reset();
            idSiembraSelect = null;
            editando = false;

            bloquearCampos(false);
            btnGuardar.textContent = "Registrar";
            estado.value = "En crecimiento";
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
                    opcion.textContent = p.nombre;

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

        // Registrar y editar siembras
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            const datos = new FormData(formulario);

            let endpoint = "./api/siembras/crear.php";

            if (editando && idSiembraSelect !== null) {
                endpoint = "./api/siembras/editar.php";
                datos.append("id_siembra", idSiembraSelect);
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
                    await verSiembras(
                        document.getElementById("buscar").value.trim()
                    );
                } else {
                    alert(resultado.message || "No se pudo guardar la siembra");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        // Visualizar siembras
        async function verSiembras(busqueda = "") {
            try {
                const respuesta = await fetch(
                    "./api/siembras/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las siembras");
                    return;
                }

                siembrasCargadas = resultado.siembras || [];

                const filtro = busqueda.toLowerCase();

                const filtrados = siembrasCargadas.filter(function(s) {
                    return (s.nombre_parcela || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 8;
                    celda.textContent = "No se encontraron siembras";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(s) {
                    const fila = document.createElement("tr");

                    [
                        s.id_siembra,
                        s.nombre_parcela,
                        s.variedad_uva,
                        s.fecha_siembra,
                        s.cantidad_plantas,
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

                    // Seleccionar una siembra
                    btnSeleccionar.addEventListener("click", function() {
                        idSiembraSelect = s.id_siembra;
                        editando = false;

                        parcela.value = s.id_parcela;
                        variedad.value = s.variedad_uva;
                        fecha.value = s.fecha_siembra;
                        cantidad.value = s.cantidad_plantas;
                        estado.value = s.estado;
                        observaciones.value = s.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar una siembra
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar la siembra #" +
                            s.id_siembra + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append("id_siembra", s.id_siembra);

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/siembras/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar =
                                await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idSiembraSelect == s.id_siembra) {
                                    limpiarFormulario();
                                }

                                await verSiembras(
                                    document.getElementById("buscar").value.trim()
                                );
                            } else {
                                alert(resultadoEliminar.message);
                            }

                        } catch (error) {
                            console.error(error);
                            alert("Error al eliminar la siembra");
                        }
                    });
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las siembras");
            }
        }

        // Buscar por nombre de parcela
        document.getElementById("btnBuscar").addEventListener("click", function() {
            verSiembras(document.getElementById("buscar").value.trim());
        });

        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verSiembras(this.value.trim());
            }
        });

        // Mostrar todas las siembras
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verSiembras();
        });

        // Activar edición
        btnEditar.addEventListener("click", function() {
            if (idSiembraSelect === null) {
                alert("Primero debe seleccionar una siembra");
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
            await verSiembras();
        }

        iniciar();
    </script>

</body>
</html>