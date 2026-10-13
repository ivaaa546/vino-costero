
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2')) {
    Response::error("No autorizado", -1001, 403);
}

$id_cata = filter_input(INPUT_POST, "id_cata", FILTER_VALIDATE_INT);

if (!$id_cata) {
    Response::error("ID de cata no valido", -1002, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "DELETE FROM catas WHERE id_cata = ?"
    );
    $stmt->execute([$id_cata]);

    if ($stmt->rowCount() === 0) {
        Response::error("La cata no existe", -1003, 404);
    }

    Response::success("Cata eliminada correctamente", 200);

} catch (PDOException $e) {
    Response::error("Error al eliminar la cata", -1004, 500);
}
?>
