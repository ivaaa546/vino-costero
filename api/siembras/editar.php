<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_siembra = $_POST["id_siembra"] ?? null;
$id_parcela = $_POST["id_parcela"] ?? null;
$variedad_uva = trim($_POST["variedad_uva"] ?? "");
$fecha_siembra = $_POST["fecha_siembra"] ?? "";
$cantidad_plantas = $_POST["cantidad_plantas"] ?? null;
$estado = $_POST["estado"] ?? "";
$observaciones = trim($_POST["observaciones"] ?? "");

if (
    !$id_siembra ||
    !$id_parcela ||
    $variedad_uva === "" ||
    $fecha_siembra === "" ||
    $cantidad_plantas === null ||
    $cantidad_plantas === "" ||
    $estado === ""
) {
    Response::error("Complete los campos obligatorios", -1002, 400);
}

$id_siembra = filter_var($id_siembra, FILTER_VALIDATE_INT);
$id_parcela = filter_var($id_parcela, FILTER_VALIDATE_INT);
$cantidad_plantas = filter_var($cantidad_plantas, FILTER_VALIDATE_INT);

if (!$id_siembra || $id_siembra <= 0 || !$id_parcela || $id_parcela <= 0) {
    Response::error("Identificador no valido", -1003, 400);
}

if ($cantidad_plantas === false || $cantidad_plantas <= 0) {
    Response::error("La cantidad de plantas debe ser mayor a cero", -1004, 400);
}

$estadosPermitidos = [
    "En crecimiento",
    "En producción",
    "Finalizada",
    "Suspendida"
];

if (!in_array($estado, $estadosPermitidos, true)) {
    Response::error("Estado de siembra no valido", -1005, 400);
}

$fecha = DateTime::createFromFormat("!Y-m-d", $fecha_siembra);

if (!$fecha || $fecha->format("Y-m-d") !== $fecha_siembra) {
    Response::error("Fecha de siembra no valida", -1006, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $sql = "UPDATE siembras
            SET id_parcela = ?,
                variedad_uva = ?,
                fecha_siembra = ?,
                cantidad_plantas = ?,
                estado = ?,
                observaciones = ?
            WHERE id_siembra = ?";

    $stmt = $cn->prepare($sql);
    $stmt->execute([
        $id_parcela,
        $variedad_uva,
        $fecha_siembra,
        $cantidad_plantas,
        $estado,
        $observaciones !== "" ? $observaciones : null,
        $id_siembra
    ]);

    if ($stmt->rowCount() === 0) {
        $verificar = $cn->prepare(
            "SELECT id_siembra FROM siembras WHERE id_siembra = ?"
        );
        $verificar->execute([$id_siembra]);

        if (!$verificar->fetch(PDO::FETCH_ASSOC)) {
            Response::error("La siembra no existe", -1007, 404);
        }
    }

    Response::success("Siembra actualizada correctamente", 200, [
        "id_siembra" => $id_siembra
    ]);

} catch (PDOException $error) {
    Response::error("No se pudo actualizar la siembra", -1008, 500);
}
?>