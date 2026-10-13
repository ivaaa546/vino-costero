
<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
        Response::error("No autorizado", -1001, 403);
    }

    $id_parcela = $_GET["id_parcela"] ?? null;

    try {
        $cn = new Connection($_SESSION["rol"]);

        if ($id_parcela !== null) {
            $id_parcela = filter_var(
                $id_parcela,
                FILTER_VALIDATE_INT
            );

            if (!$id_parcela || $id_parcela <= 0) {
                Response::error("ID de parcela no valido", -1002, 400);
            }

            $stmt = $cn->prepare(
                "SELECT * FROM parcelas WHERE id_parcela = ?"
            );

            $stmt->execute([$id_parcela]);
            $parcelas = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$parcelas) {
                Response::error("La parcela no existe", -1003, 404);
            }
            Response::success("Parcela encontrada", 200, ["parcelas"=>$parcelas]);

        } else {
            $stmt = $cn->prepare(
                "SELECT * FROM parcelas ORDER BY id_parcela DESC"
            );

            $stmt->execute();
            $parcelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Response::success("Parcelas obtenidas correctamente", 200, ["parcelas"=>$parcelas]);
        }

    } catch (PDOException $error) {
        Response::error("No se pudieron obtener las parcelas", -1004, 500);
    }
?>
