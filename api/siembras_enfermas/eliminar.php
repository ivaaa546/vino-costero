<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_enfermedad = filter_var(
    $_POST["id_siembra_enferma"] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id_enfermedad || $id_enfermedad <= 0) {
    Response::error("ID de enfermedad no valido", -1002, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "DELETE FROM siembras_enfermas
         WHERE id_siembra_enferma = ?"
    );

    $stmt->execute([$id_enfermedad]);

    if ($stmt->rowCount() === 0) {
        Response::error("El registro de enfermedad no existe", -1003, 404);
    }

    Response::success("Registro de enfermedad eliminado correctamente", 200, [
        "id_siembra_enferma" => $id_enfermedad
    ]);

} catch (PDOException $error) {
    Response::error("No se pudo eliminar el registro de enfermedad", -1004, 500);
}
?>