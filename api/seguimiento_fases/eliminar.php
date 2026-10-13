
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2')) {
    Response::error("No autorizado", -1001, 403);
}

$id_seguimiento = filter_input(INPUT_POST, "id_seguimiento", FILTER_VALIDATE_INT);

if (!$id_seguimiento) {
    Response::error("ID de seguimiento no valido", -1002, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "DELETE FROM seguimiento_fases WHERE id_seguimiento = ?"
    );
    $stmt->execute([$id_seguimiento]);

    if ($stmt->rowCount() === 0) {
        Response::error("Seguimiento no encontrado", -1003, 404);
    }

    Response::success("Seguimiento eliminado correctamente", 200);
} catch (PDOException $e) {
    Response::error("No se pudo eliminar el seguimiento", -1004, 500);
}
?>
