<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3' && $_SESSION['rol'] !== '4')) {
    Response::error("No autorizado", -1001, 403);
}

$id_siembra = filter_var(
    $_POST["id_siembra"] ?? null,
    FILTER_VALIDATE_INT
);

$fecha_control = $_POST["fecha_control"] ?? "";
$estado_general = $_POST["estado_general"] ?? "";
$riego_realizado = ($_POST["riego_realizado"] ?? "0") === "1" ? 1 : 0;
$fertilizacion_realizada = ($_POST["fertilizacion_realizada"] ?? "0") === "1" ? 1 : 0;
$observaciones = trim($_POST["observaciones"] ?? "");

if (!$id_siembra || $id_siembra <= 0 || $fecha_control === "" || $estado_general === "") {
    Response::error("Complete los campos obligatorios", -1002, 400);
}

$estadosPermitidos = ["Excelente", "Bueno", "Regular", "Malo"];

if (!in_array($estado_general, $estadosPermitidos, true)) {
    Response::error("Estado general no valido", -1003, 400);
}

$fecha = DateTime::createFromFormat("!Y-m-d", $fecha_control);

if (!$fecha || $fecha->format("Y-m-d") !== $fecha_control) {
    Response::error("Fecha de control no valida", -1004, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $sql = "INSERT INTO controles_siembra
            (id_siembra, fecha_control, estado_general,
             riego_realizado, fertilizacion_realizada, observaciones)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $cn->prepare($sql);
    $stmt->execute([
        $id_siembra,
        $fecha_control,
        $estado_general,
        $riego_realizado,
        $fertilizacion_realizada,
        $observaciones !== "" ? $observaciones : null
    ]);

    Response::success("Control de siembra registrado correctamente", 201, [
        "id_control_siembra" => $cn->lastInsertId()
    ]);

} catch (PDOException $error) {
    Response::error("No se pudo registrar el control de siembra", -1005, 500);
}
?>