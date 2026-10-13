<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
        Response::error("No autorizado", -1001, 403);
    }

    $id_control = $_GET["id_control_tierra"] ?? null;

    try {
        $cn = new Connection($_SESSION["rol"]);

        $sql = "SELECT
                    ct.id_control_tierra,
                    ct.id_parcela,
                    p.nombre AS nombre_parcela,
                    ct.fecha_control,
                    ct.ph,
                    ct.humedad,
                    ct.nitrogeno,
                    ct.fosforo,
                    ct.potasio,
                    ct.observaciones
                FROM controles_tierra ct
                INNER JOIN parcelas p
                    ON ct.id_parcela = p.id_parcela";

        if ($id_control !== null) {
            $id_control = filter_var($id_control, FILTER_VALIDATE_INT);

            if (!$id_control || $id_control <= 0) {
                Response::error("ID de control no valido", -1002, 400);
            }

            $stmt = $cn->prepare(
                $sql . " WHERE ct.id_control_tierra = ?"
            );

            $stmt->execute([$id_control]);
            $control = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$control) {
                Response::error("El control de tierra no existe", -1003, 404);
            }

            Response::success("Control obtenido correctamente", 200, ["control"=>$control]);

        } else {
            $stmt = $cn->prepare(
                $sql . " ORDER BY ct.fecha_control DESC,
                         ct.id_control_tierra DESC"
            );

            $stmt->execute();
            $controles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Response::success(
                "Controles de tierra obtenidos correctamente",
                200,
                ["controles"=>$controles]
            );
        }

    } catch (PDOException $error) {
        Response::error("No se pudieron obtener los controles de tierra", -1004, 500);
    }
?>