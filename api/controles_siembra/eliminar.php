<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_control = filter_var(
    $_POST["id_control_siembra"] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id_control || $id_control <= 0) {
    Response::error("ID de control no valido", -1002, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "DELETE FROM controles_siembra
         WHERE id_control_siembra = ?"
    );

    $stmt->execute([$id_control]);

    if ($stmt->rowCount() === 0) {
        Response::error("El control de siembra no existe", -1003, 404);
    }

    Response::success("Control de siembra eliminado correctamente", 200, [
        "id_control_siembra" => $id_control
    ]);

} catch (PDOException $error) {
    Response::error("No se pudo eliminar el control de siembra", -1004, 500);
}
?>