
<?php
    require_once("./api/login/end_verificar_agronomo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Controles de Tierra</title>
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
        <h1>Administración de controles de tierra</h1>
    </main>

    <section>
        <h2>Registrar control de tierra</h2>

        <form id="formControl">
            <label for="id_parcela">Parcela</label>
            <select id="id_parcela" name="id_parcela" required>
                <option value="">Seleccione una parcela</option>
            </select>

            <label for="fecha_control">Fecha del control</label>
            <input type="date" id="fecha_control"
                   name="fecha_control" required>

            <label for="ph">pH</label>
            <input type="number" id="ph" name="ph"
                   min="-99.99" max="99.99" step="0.01"
                   placeholder="Ej. 6.50">

            <label for="humedad">Humedad (%)</label>
            <input type="number" id="humedad" name="humedad"
                   min="-999.99" max="999.99" step="0.01"
                   placeholder="Ej. 45.50">

            <label for="nitrogeno">Nitrógeno</label>
            <input type="number" id="nitrogeno" name="nitrogeno"
                   min="-999999.99" max="999999.99" step="0.01">

            <label for="fosforo">Fósforo</label>
            <input type="number" id="fosforo" name="fosforo"
                   min="-999999.99" max="999999.99" step="0.01">

            <label for="potasio">Potasio</label>
            <input type="number" id="potasio" name="potasio"
                   min="-999999.99" max="999999.99" step="0.01">

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones del control"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de controles de tierra</h2>

        <label for="buscar">Buscar por parcela</label>
        <input type="text" id="buscar" placeholder="Nombre de parcela">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todos</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Parcela</th>
                    <th>Fecha</th>
                    <th>pH</th>
                    <th>Humedad</th>
                    <th>Nitrógeno</th>
                    <th>Fósforo</th>
                    <th>Potasio</th>
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
        const parcela = document.getElementById("id_parcela");
        const fecha = document.getElementById("fecha_control");
        const ph = document.getElementById("ph");
        const humedad = document.getElementById("humedad");
        const nitrogeno = document.getElementById("nitrogeno");
        const fosforo = document.getElementById("fosforo");
        const potasio = document.getElementById("potasio");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            parcela, fecha, ph, humedad,
            nitrogeno, fosforo, potasio, observaciones
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

                parcela.innerHTML = '<option value="">Seleccione una parcela</option>';

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

        // Registrar y editar controles
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            const datos = new FormData(formulario);

            let endpoint = "./api/controles_tierra/crear.php";

            if (editando && idControlSelect !== null) {
                endpoint = "./api/controles_tierra/editar.php";
                datos.append("id_control_tierra", idControlSelect);
            }

            try {
                const respuesta = await fetch(endpoint, {
                    method: "POST",
                    body: datos
                });

                const resultado = await respuesta.json();

                if (resultado.code === 200) {
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
                    "./api/controles_tierra/visualizar.php"
                );

                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar los controles");
                    return;
                }

                controlesCargados = resultado.controles || [];

                const filtro = busqueda.toLowerCase();
                const filtrados = controlesCargados.filter(function(c) {
                    return (c.nombre_parcela || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 10;
                    celda.textContent = "No se encontraron controles";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(c) {
                    const fila = document.createElement("tr");

                    [
                        c.id_control_tierra,
                        c.nombre_parcela,
                        c.fecha_control,
                        c.ph ?? "",
                        c.humedad ?? "",
                        c.nitrogeno ?? "",
                        c.fosforo ?? "",
                        c.potasio ?? "",
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
                        idControlSelect = c.id_control_tierra;
                        editando = false;

                        parcela.value = c.id_parcela;
                        fecha.value = c.fecha_control;
                        ph.value = c.ph ?? "";
                        humedad.value = c.humedad ?? "";
                        nitrogeno.value = c.nitrogeno ?? "";
                        fosforo.value = c.fosforo ?? "";
                        potasio.value = c.potasio ?? "";
                        observaciones.value = c.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar un control
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar el control de tierra #" +
                            c.id_control_tierra + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append("id_control_tierra", c.id_control_tierra);

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/controles_tierra/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar = await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idControlSelect == c.id_control_tierra) {
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
                alert("Error al cargar los controles de tierra");
            }
        }

        // Buscar por nombre de parcela
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
            await cargarParcelas();
            await verControles();
        }

        iniciar();
    </script>

</body>
</html>
