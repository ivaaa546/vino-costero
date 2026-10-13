<?php
    require_once("./api/login/end_verificar_enologo.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Catas</title>
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
        <h1>Administración de catas</h1>
    </main>

    <section>
        <h2>Registrar cata</h2>

        <form id="formCata">
            <label for="id_produccion">Producción</label>
            <select id="id_produccion" name="id_produccion" required>
                <option value="">Seleccione una producción</option>
            </select>

            <label for="fecha_cata">Fecha de cata</label>
            <input type="date" id="fecha_cata"
                   name="fecha_cata" required>

            <label for="aroma">Aroma</label>
            <input type="text" id="aroma"
                   name="aroma" maxlength="200">

            <label for="sabor">Sabor</label>
            <input type="text" id="sabor"
                   name="sabor" maxlength="200">

            <label for="color">Color</label>
            <input type="text" id="color"
                   name="color" maxlength="100">

            <label for="cuerpo">Cuerpo</label>
            <input type="text" id="cuerpo"
                   name="cuerpo" maxlength="100">

            <label for="puntuacion">Puntuación</label>
            <input type="number" id="puntuacion"
                   name="puntuacion" min="0" max="100"
                   step="0.01" required>

            <label for="resultado">Resultado</label>
            <select id="resultado" name="resultado" required>
                <option value="">Seleccione un resultado</option>
                <option value="Aprobado">Aprobado</option>
                <option value="Requiere mejoras">Requiere mejoras</option>
                <option value="Rechazado">Rechazado</option>
            </select>

            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones"
                      placeholder="Observaciones de la cata"></textarea>

            <button type="submit" id="btnGuardar">Registrar</button>
            <button type="button" id="btnEditar">Editar</button>
            <button type="button" id="btnCancelar">Cancelar</button>
        </form>
    </section>

    <section>
        <h2>Lista de catas</h2>

        <label for="buscar">Buscar por nombre del vino</label>
        <input type="text" id="buscar" placeholder="Nombre del vino">

        <button type="button" id="btnBuscar">Buscar</button>
        <button type="button" id="btnMostrarTodas">Mostrar todas</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producción</th>
                    <th>Fecha de cata</th>
                    <th>Aroma</th>
                    <th>Sabor</th>
                    <th>Color</th>
                    <th>Cuerpo</th>
                    <th>Puntuación</th>
                    <th>Resultado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <script>
        let idCataSelect = null;
        let editando = false;
        let catasCargadas = [];

        const formulario = document.getElementById("formCata");
        const produccion = document.getElementById("id_produccion");
        const fecha = document.getElementById("fecha_cata");
        const aroma = document.getElementById("aroma");
        const sabor = document.getElementById("sabor");
        const color = document.getElementById("color");
        const cuerpo = document.getElementById("cuerpo");
        const puntuacion = document.getElementById("puntuacion");
        const resultadoCata = document.getElementById("resultado");
        const observaciones = document.getElementById("observaciones");
        const btnGuardar = document.getElementById("btnGuardar");
        const btnEditar = document.getElementById("btnEditar");

        const campos = [
            produccion, fecha, aroma, sabor, color,
            cuerpo, puntuacion, resultadoCata, observaciones
        ];

        // Habilitar o bloquear los campos
        function bloquearCampos(bloquear) {
            campos.forEach(campo => campo.disabled = bloquear);
            btnGuardar.disabled = bloquear;
        }

        // Limpiar el formulario
        function limpiarFormulario() {
            formulario.reset();
            idCataSelect = null;
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

        // Cargar producciones en el selector
        async function cargarProducciones() {
            try {
                const respuesta = await fetch("./api/producciones/visualizar.php");
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las producciones");
                    return;
                }

                const datos = resultado.resultado?.producciones
                    || resultado.producciones
                    || [];

                produccion.innerHTML =
                    '<option value="">Seleccione una producción</option>';

                datos.forEach(function(p) {
                    const opcion = document.createElement("option");
                    opcion.value = p.id_produccion;
                    opcion.textContent = p.nombre_vino;
                    produccion.appendChild(opcion);
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las producciones");
            }
        }

        // Registrar y editar catas
        formulario.addEventListener("submit", async function(e) {
            e.preventDefault();

            if (
                puntuacion.value === "" ||
                Number(puntuacion.value) < 0 ||
                Number(puntuacion.value) > 100
            ) {
                alert("La puntuación debe estar entre 0 y 100");
                return;
            }

            const datos = new FormData(formulario);

            let endpoint = "./api/catas/crear.php";

            if (editando && idCataSelect !== null) {
                endpoint = "./api/catas/editar.php";
                datos.append("id_cata", idCataSelect);
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

                    await verCatas(
                        document.getElementById("buscar").value.trim()
                    );
                } else {
                    alert(resultado.message || "No se pudo guardar la cata");
                }

            } catch (error) {
                console.error(error);
                alert("Error al comunicarse con el servidor");
            }
        });

        // Visualizar catas
        async function verCatas(busqueda = "") {
            try {
                const respuesta = await fetch("./api/catas/visualizar.php");
                const resultado = await respuesta.json();

                if (resultado.code !== 200) {
                    alert(resultado.message || "No se pudieron cargar las catas");
                    return;
                }

                catasCargadas = resultado.resultado?.catas
                    || resultado.catas
                    || [];

                const filtro = busqueda.toLowerCase();

                const filtrados = catasCargadas.filter(function(c) {
                    return (c.nombre_vino || "")
                        .toLowerCase().includes(filtro);
                });

                const tabla = document.querySelector("table tbody");
                tabla.innerHTML = "";

                if (filtrados.length === 0) {
                    const fila = document.createElement("tr");
                    const celda = document.createElement("td");
                    celda.colSpan = 11;
                    celda.textContent = "No se encontraron catas";
                    fila.appendChild(celda);
                    tabla.appendChild(fila);
                    return;
                }

                filtrados.forEach(function(c) {
                    const fila = document.createElement("tr");

                    [
                        c.id_cata,
                        c.nombre_vino,
                        c.fecha_cata,
                        c.aroma ?? "",
                        c.sabor ?? "",
                        c.color ?? "",
                        c.cuerpo ?? "",
                        c.puntuacion,
                        c.resultado,
                        c.observaciones ?? ""
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

                    // Seleccionar una cata
                    btnSeleccionar.addEventListener("click", function() {
                        idCataSelect = c.id_cata;
                        editando = false;

                        produccion.value = c.id_produccion;
                        fecha.value = c.fecha_cata;
                        aroma.value = c.aroma ?? "";
                        sabor.value = c.sabor ?? "";
                        color.value = c.color ?? "";
                        cuerpo.value = c.cuerpo ?? "";
                        puntuacion.value = c.puntuacion;
                        resultadoCata.value = c.resultado;
                        observaciones.value = c.observaciones ?? "";

                        bloquearCampos(true);
                        btnGuardar.textContent = "Registrar";
                    });

                    // Eliminar una cata
                    btnEliminar.addEventListener("click", async function() {
                        if (!confirm(
                            "¿Deseas eliminar la cata #" + c.id_cata + "?"
                        )) {
                            return;
                        }

                        const datos = new FormData();
                        datos.append("id_cata", c.id_cata);

                        try {
                            const respuestaEliminar = await fetch(
                                "./api/catas/eliminar.php",
                                {
                                    method: "POST",
                                    body: datos
                                }
                            );

                            const resultadoEliminar =
                                await respuestaEliminar.json();

                            if (resultadoEliminar.code === 200) {
                                alert(resultadoEliminar.message);

                                if (idCataSelect == c.id_cata) {
                                    limpiarFormulario();
                                }

                                await verCatas(
                                    document.getElementById("buscar").value.trim()
                                );
                            } else {
                                alert(resultadoEliminar.message);
                            }

                        } catch (error) {
                            console.error(error);
                            alert("Error al eliminar la cata");
                        }
                    });
                });

            } catch (error) {
                console.error(error);
                alert("Error al cargar las catas");
            }
        }

        // Buscar por nombre del vino
        document.getElementById("btnBuscar").addEventListener("click", function() {
            verCatas(document.getElementById("buscar").value.trim());
        });

        document.getElementById("buscar").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                verCatas(this.value.trim());
            }
        });

        // Mostrar todas las catas
        document.getElementById("btnMostrarTodas").addEventListener("click", function() {
            document.getElementById("buscar").value = "";
            verCatas();
        });

        // Activar edición
        btnEditar.addEventListener("click", function() {
            if (idCataSelect === null) {
                alert("Primero debe seleccionar una cata");
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
            await verCatas();
        }

        iniciar();
    </script>

</body>
</html>
