<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        Response::error("Metodo no permitido", -1000, 405);
    }

    $_SESSION = [];

    session_destroy(); 

    Response::success("Session cerrada correctamente", 200);
?>