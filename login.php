<?php
	//si existe una sesion activa se cierra
	session_start();
	session_unset();
	session_destroy();
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<!--<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">-->
	<!--<link rel="stylesheet" href="auth.css">-->
</head>

<body>
	<div class="auth-page">
		<div class="auth-card">
			<div class="auth-card-bar"></div>
			<main class="auth-form">
				<h2>Iniciar sesión</h2>
				<p class="subtitle">Ingresá con tu correo institucional.</p>

				<form id="form" method="post">
					<div class="field">
						<label for="usuario">Correo</label>
						<input type="text" id="usuario" name="usuario" placeholder="Ingrese su correo">
					</div>

					<div class="field">
						<label for="contrasena">Contraseña</label>
						<input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña">
					</div>

					<button type="submit" name="btn" class="btn-primary">Ingresar</button>

				</form>
			</main>
		</div>
	</div>

	<script>
		document.getElementById("form").addEventListener("submit", async function(e) {
			//Evitar comportamiento normal
			e.preventDefault();
			//Recoger datos
			const formulario = new FormData(this);
			try {
				//Enviar datos a la API
				const respuesta = await fetch("./api/login/end_login.php", {
					method: "POST",
					body: formulario
				});
				//Guardar resultado de la API
				const resultado = await respuesta.json();
				//Si el resultado es ok
				if (resultado.code === 200) {
					alert(resultado.message);
					//Limpiar formulario
					this.reset();
					if(resultado.rol === '3'){
						window.location.href = "./admin/adm_dashboard.php";
					}else if(resultado.rol === '2'){
						window.location.href = "./tutor/tut_dashboard.php";
					}else if(resultado.rol === '1'){
						window.location.href = "./estudiante/stu_dashboard.php";
					}
				} else {
					alert(resultado.message);
				}
			} catch (error) {
				console.error(error);
				alert("Ocurrió un error al comunicarse con el servidor.");
			}
		});
	</script>

</body>

</html>
