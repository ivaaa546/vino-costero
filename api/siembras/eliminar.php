<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_siembra = $_POST["id_siembra"] ?? null;

$id_siembra = filter_var($id_siembra, FILTER_VALIDATE_INT);

if (!$id_siembra || $id_siembra <= 0) {
    Response::error("ID de siembra no valido", -1002, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "DELETE FROM siembras WHERE id_siembra = ?"
    );

    $stmt->execute([$id_siembra]);

    if ($stmt->rowCount() === 0) {
        Response::error("La siembra no existe", -1003, 404);
    }

    Response::success("Siembra eliminada correctamente", 200, [
        "id_siembra" => $id_siembra
    ]);

} catch (PDOException $error) {
    Response::error("No se pudo eliminar la siembra", -1004, 500);
}
?>