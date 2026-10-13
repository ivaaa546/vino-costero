<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error("Metodo no permitido", -1000, 405);
    }

    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        Response::error("El correo y la contraseña son obligatorios", -1001, 400);
    }

    if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
        Response::error("Correo invalido", -1002, 400);
    }

    try {
        $cn = new Connection("");
        $stmt = $cn->prepare("SELECT id_usuario, correo, password_hash, id_rol FROM usuarios WHERE correo = ? && estado = 'Activo'");
        $stmt->execute([$usuario]);
        $usuarioBD = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuarioBD === false || !password_verify($contrasena, $usuarioBD['password_hash'])) {
            Response::error("Correo o contraseña incorrectos", -1003, 401);
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['rol'] = (string) $usuarioBD['id_rol'];
        $_SESSION['id'] = (string) $usuarioBD['id_usuario'];
        Response::success("Inicio de sesion correcto", 200, ["rol"=>$_SESSION["rol"]]);
    } catch (PDOException $error) {
        Response::error("No se pudo iniciar sesion, intenta de nuevo", -1004, 500);
        //Response::debug($error->getMessage(), -1004, 500);
    }
