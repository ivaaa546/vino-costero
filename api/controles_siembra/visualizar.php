<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_control = $_GET["id_control_siembra"] ?? null;

try {
    $cn = new Connection($_SESSION["rol"]);

    $sql = "SELECT
                cs.id_control_siembra,
                cs.id_siembra,
                s.variedad_uva,
                cs.fecha_control,
                cs.estado_general,
                cs.riego_realizado,
                cs.fertilizacion_realizada,
                cs.observaciones
            FROM controles_siembra cs
            INNER JOIN siembras s
                ON cs.id_siembra = s.id_siembra";

    if ($id_control !== null) {
        $id_control = filter_var($id_control, FILTER_VALIDATE_INT);

        if (!$id_control || $id_control <= 0) {
            Response::error("ID de control no valido", -1002, 400);
        }

        $stmt = $cn->prepare(
            $sql . " WHERE cs.id_control_siembra = ?"
        );

        $stmt->execute([$id_control]);
        $control = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$control) {
            Response::error("El control de siembra no existe", -1003, 404);
        }

        Response::success("Control de siembra obtenido correctamente", 200, [
            "control_siembra" => $control
        ]);

    } else {
        $stmt = $cn->prepare(
            $sql . " ORDER BY cs.fecha_control DESC,
                     cs.id_control_siembra DESC"
        );

        $stmt->execute();
        $controles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Controles de siembra obtenidos correctamente", 200, [
            "controles_siembra" => $controles
        ]);
    }

} catch (PDOException $error) {
    Response::error("No se pudieron obtener los controles de siembra", -1004, 500);
}
?>