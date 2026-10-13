
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

    $stmtProducciones = $cn->prepare(
        "SELECT
            id_produccion,
            nombre_vino
         FROM producciones
         ORDER BY id_produccion DESC"
    );
    $stmtProducciones->execute();
    $producciones = $stmtProducciones->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_GET["id_cata"])) {
        $id_cata = filter_var(
            $_GET["id_cata"],
            FILTER_VALIDATE_INT
        );

        if (!$id_cata) {
            Response::error("ID de cata no valido", -1002, 400);
        }

        $stmt = $cn->prepare(
            "SELECT
                c.id_cata,
                c.id_produccion,
                p.nombre_vino,
                c.fecha_cata,
                c.aroma,
                c.sabor,
                c.color,
                c.cuerpo,
                c.puntuacion,
                c.resultado,
                c.observaciones
             FROM catas c
             INNER JOIN producciones p
                ON c.id_produccion = p.id_produccion
             WHERE c.id_cata = ?"
        );

        $stmt->execute([$id_cata]);
        $cata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cata) {
            Response::error("La cata no existe", -1003, 404);
        }

        Response::success(
            "Cata consultada correctamente",
            200,
            [
                "cata" => $cata,
                "producciones" => $producciones
            ]
        );
    }

    $stmt = $cn->prepare(
        "SELECT
            c.id_cata,
            c.id_produccion,
            p.nombre_vino,
            c.fecha_cata,
            c.aroma,
            c.sabor,
            c.color,
            c.cuerpo,
            c.puntuacion,
            c.resultado,
            c.observaciones
         FROM catas c
         INNER JOIN producciones p
            ON c.id_produccion = p.id_produccion
         ORDER BY c.id_cata DESC"
    );

    $stmt->execute();
    $catas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    Response::success(
        "Catas consultadas correctamente",
        200,
        [
            "catas" => $catas,
            "producciones" => $producciones
        ]
    );

} catch (PDOException $e) {
    Response::error("Error al consultar las catas", -1004, 500);
}
?>
