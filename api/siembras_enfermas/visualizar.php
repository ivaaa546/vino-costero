<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_enfermedad = $_GET["id_siembra_enferma"] ?? null;

try {
    $cn = new Connection($_SESSION["rol"]);

    $sql = "SELECT
                se.id_siembra_enferma,
                se.id_siembra,
                s.variedad_uva,
                se.nombre_enfermedad,
                se.fecha_deteccion,
                se.cantidad_plantas_afectadas,
                se.nivel_gravedad,
                se.tratamiento,
                se.estado,
                se.observaciones
            FROM siembras_enfermas se
            INNER JOIN siembras s
                ON se.id_siembra = s.id_siembra";

    if ($id_enfermedad !== null) {
        $id_enfermedad = filter_var(
            $id_enfermedad,
            FILTER_VALIDATE_INT
        );

        if (!$id_enfermedad || $id_enfermedad <= 0) {
            Response::error("ID de enfermedad no valido", -1002, 400);
        }

        $stmt = $cn->prepare(
            $sql . " WHERE se.id_siembra_enferma = ?"
        );

        $stmt->execute([$id_enfermedad]);
        $enfermedad = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$enfermedad) {
            Response::error("El registro de enfermedad no existe", -1003, 404);
        }

        Response::success("Registro de enfermedad obtenido correctamente", 200, [
            "siembra_enferma" => $enfermedad
        ]);

    } else {
        $stmt = $cn->prepare(
            $sql . " ORDER BY se.fecha_deteccion DESC,
                     se.id_siembra_enferma DESC"
        );

        $stmt->execute();
        $enfermedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success("Registros de enfermedades obtenidos correctamente", 200, [
            "siembras_enfermas" => $enfermedades
        ]);
    }

} catch (PDOException $error) {
    Response::error("No se pudieron obtener los registros de enfermedades", -1004, 500);
}
?>