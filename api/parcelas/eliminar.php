
<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
        Response::error("No autorizado", -1001, 403);
    }

    $id_parcela = filter_var(
        $_POST["id_parcela"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id_parcela || $id_parcela <= 0) {
        Response::error("ID de parcela no valido", -1002, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare(
            "DELETE FROM parcelas WHERE id_parcela = ?"
        );

        $stmt->execute([$id_parcela]);

        if ($stmt->rowCount() === 0) {
            Response::error("La parcela no existe", -1003, 404);
        }

        Response::success("Parcela eliminada correctamente", 200);

    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            Response::error(
                "No se puede eliminar la parcela porque tiene registros relacionados",
                -1004,
                409
            );
        }

        Response::error("No se pudo eliminar la parcela", -1005, 500);
    }
?>
