<?php
	//Verificar sesion
	session_start();

	//Si no hay sesion iniciada o si el rol no es 1
	if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '1') {
	    header("Location: ./login.php");
	    exit();
	}
?>