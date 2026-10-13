
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2')) {
    Response::error("No autorizado", -1001, 403);
}

$id_produccion = filter_input(INPUT_POST, "id_produccion", FILTER_VALIDATE_INT);

if (!$id_produccion) {
    Response::error("ID de produccion no valido", -1002, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "DELETE FROM producciones WHERE id_produccion = ?"
    );
    $stmt->execute([$id_produccion]);

    if ($stmt->rowCount() === 0) {
        Response::error("Produccion no encontrada", -1003, 404);
    }

    Response::success("Produccion eliminada correctamente", 200);
} catch (PDOException $e) {
    Response::error("No se pudo eliminar la produccion", -1004, 500);
}
?>
