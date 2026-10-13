
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2')) {
    Response::error("No autorizado", -1001, 403);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $id_produccion = filter_input(INPUT_GET, "id_produccion", FILTER_VALIDATE_INT);

    if (isset($_GET["id_produccion"])) {
        if (!$id_produccion) {
            Response::error("ID de produccion no valido", -1002, 400);
        }

        $stmt = $cn->prepare(
            "SELECT
                pr.id_produccion,
                pr.id_parcela,
                pa.nombre AS nombre_parcela,
                pr.nombre_vino,
                pr.variedad_uva,
                pr.fecha_inicio,
                pr.fecha_fin,
                pr.cantidad_uva_kg,
                pr.cantidad_vino_litros,
                pr.estado,
                pr.observaciones
            FROM producciones pr
            INNER JOIN parcelas pa ON pr.id_parcela = pa.id_parcela
            WHERE pr.id_produccion = ?"
        );
        $stmt->execute([$id_produccion]);

        $produccion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produccion) {
            Response::error("Produccion no encontrada", -1003, 404);
        }

        Response::success("Produccion encontrada", 200, [
            "produccion" => $produccion
        ]);
    } else {
        $stmt = $cn->prepare(
            "SELECT
                pr.id_produccion,
                pr.id_parcela,
                pa.nombre AS nombre_parcela,
                pr.nombre_vino,
                pr.variedad_uva,
                pr.fecha_inicio,
                pr.fecha_fin,
                pr.cantidad_uva_kg,
                pr.cantidad_vino_litros,
                pr.estado,
                pr.observaciones
            FROM producciones pr
            INNER JOIN parcelas pa ON pr.id_parcela = pa.id_parcela
            ORDER BY pr.id_produccion DESC"
        );
        $stmt->execute();

        $producciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Producciones consultadas correctamente", 200, [
            "producciones" => $producciones
        ]);
    }
} catch (PDOException $e) {
    Response::error("No se pudieron consultar las producciones", -1004, 500);
}
?>
