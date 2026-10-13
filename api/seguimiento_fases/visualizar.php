
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

    $stmtFases = $cn->prepare(
        "SELECT
            id_fase,
            nombre,
            descripcion,
            orden_fase,
            estado
        FROM fases_produccion
        ORDER BY orden_fase ASC"
    );
    $stmtFases->execute();
    $fases = $stmtFases->fetchAll(PDO::FETCH_ASSOC);

    $id_seguimiento = filter_input(
        INPUT_GET,
        "id_seguimiento",
        FILTER_VALIDATE_INT
    );

    if (isset($_GET["id_seguimiento"])) {
        if (!$id_seguimiento) {
            Response::error("ID de seguimiento no valido", -1002, 400);
        }

        $stmt = $cn->prepare(
            "SELECT
                sf.id_seguimiento,
                sf.id_produccion,
                p.nombre_vino,
                sf.id_fase,
                f.nombre AS nombre_fase,
                sf.fecha_inicio,
                sf.fecha_fin,
                sf.estado,
                sf.observaciones
            FROM seguimiento_fases sf
            INNER JOIN producciones p
                ON sf.id_produccion = p.id_produccion
            INNER JOIN fases_produccion f
                ON sf.id_fase = f.id_fase
            WHERE sf.id_seguimiento = ?"
        );
        $stmt->execute([$id_seguimiento]);

        $seguimiento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seguimiento) {
            Response::error("Seguimiento no encontrado", -1003, 404);
        }

        Response::success("Seguimiento encontrado", 200, [
            "seguimiento" => $seguimiento,
            "fases" => $fases
        ]);
    } else {
        $stmt = $cn->prepare(
            "SELECT
                sf.id_seguimiento,
                sf.id_produccion,
                p.nombre_vino,
                sf.id_fase,
                f.nombre AS nombre_fase,
                sf.fecha_inicio,
                sf.fecha_fin,
                sf.estado,
                sf.observaciones
            FROM seguimiento_fases sf
            INNER JOIN producciones p
                ON sf.id_produccion = p.id_produccion
            INNER JOIN fases_produccion f
                ON sf.id_fase = f.id_fase
            ORDER BY sf.id_seguimiento DESC"
        );
        $stmt->execute();

        $seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Seguimientos consultados correctamente", 200, [
            "seguimientos" => $seguimientos,
            "fases" => $fases
        ]);
    }
} catch (PDOException $e) {
    Response::error("No se pudieron consultar los seguimientos", -1004, 500);
}
?>
