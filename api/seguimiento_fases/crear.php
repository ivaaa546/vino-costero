
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2' && $_SESSION['rol'] !== '4')) {
    Response::error("No autorizado", -1001, 403);
}

$id_produccion = filter_input(INPUT_POST, "id_produccion", FILTER_VALIDATE_INT);
$id_fase = filter_input(INPUT_POST, "id_fase", FILTER_VALIDATE_INT);
$fecha_inicio = trim($_POST["fecha_inicio"] ?? "");
$fecha_fin = trim($_POST["fecha_fin"] ?? "");
$estado = trim($_POST["estado"] ?? "Pendiente");
$observaciones = trim($_POST["observaciones"] ?? "");

$estados = ["Pendiente", "En proceso", "Completada", "Cancelada"];

if (!$id_produccion || !$id_fase || $fecha_inicio === "") {
    Response::error("Complete los campos obligatorios", -1002, 400);
}

if (!in_array($estado, $estados, true)) {
    Response::error("Estado no valido", -1003, 400);
}

$inicio = DateTime::createFromFormat("!Y-m-d\TH:i", $fecha_inicio);
$fin = $fecha_fin !== ""
    ? DateTime::createFromFormat("!Y-m-d\TH:i", $fecha_fin)
    : null;

if (!$inicio || $inicio->format("Y-m-d\TH:i") !== $fecha_inicio) {
    Response::error("Fecha de inicio no valida", -1004, 400);
}

if ($fecha_fin !== "" && (!$fin || $fin->format("Y-m-d\TH:i") !== $fecha_fin)) {
    Response::error("Fecha de finalizacion no valida", -1005, 400);
}

if ($fin && $fin < $inicio) {
    Response::error("La fecha de finalizacion no puede ser anterior a la fecha de inicio", -1006, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "INSERT INTO seguimiento_fases
        (id_produccion, id_fase, fecha_inicio, fecha_fin, estado, observaciones)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $id_produccion,
        $id_fase,
        $inicio->format("Y-m-d H:i:s"),
        $fin ? $fin->format("Y-m-d H:i:s") : null,
        $estado,
        $observaciones !== "" ? $observaciones : null
    ]);

    Response::success("Seguimiento de fase registrado correctamente", 201, [
        "id_seguimiento" => $cn->lastInsertId()
    ]);
} catch (PDOException $e) {
    Response::error("No se pudo registrar el seguimiento. Verifique la produccion y la fase", -1007, 500);
}
?>
