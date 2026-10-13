<?php
	//Verificar sesion
	session_start();

	//Si no hay sesion iniciada o si el rol no es 1 o 2
	if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3' && $_SESSION['rol'] !== '4')) {
	    header("Location: ./login.php");
	    exit();
	}
?>