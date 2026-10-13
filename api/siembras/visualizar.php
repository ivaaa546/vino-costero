<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_siembra = $_GET["id_siembra"] ?? null;

try {
    $cn = new Connection($_SESSION["rol"]);

    $sql = "SELECT
                s.id_siembra,
                s.id_parcela,
                p.nombre AS nombre_parcela,
                s.variedad_uva,
                s.fecha_siembra,
                s.cantidad_plantas,
                s.estado,
                s.observaciones
            FROM siembras s
            INNER JOIN parcelas p
                ON s.id_parcela = p.id_parcela";

    if ($id_siembra !== null) {
        $id_siembra = filter_var($id_siembra, FILTER_VALIDATE_INT);

        if (!$id_siembra || $id_siembra <= 0) {
            Response::error("ID de siembra no valido", -1002, 400);
        }

        $stmt = $cn->prepare(
            $sql . " WHERE s.id_siembra = ?"
        );

        $stmt->execute([$id_siembra]);
        $siembra = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$siembra) {
            Response::error("La siembra no existe", -1003, 404);
        }

        Response::success("Siembra obtenida correctamente", 200, ["siembra" => $siembra]);

    } else {
        $stmt = $cn->prepare(
            $sql . " ORDER BY s.fecha_siembra DESC,
                     s.id_siembra DESC"
        );

        $stmt->execute();
        $siembras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Siembras obtenidas correctamente", 200, ["siembras" => $siembras]);
    }

} catch (PDOException $error) {
    Response::error("No se pudieron obtener las siembras", -1004, 500);
}
?>