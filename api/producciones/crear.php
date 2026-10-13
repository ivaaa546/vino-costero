
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2' && $_SESSION['rol'] !== '4')) {
    Response::error("No autorizado", -1001, 403);
}

$id_parcela = filter_input(INPUT_POST, "id_parcela", FILTER_VALIDATE_INT);
$nombre_vino = trim($_POST["nombre_vino"] ?? "");
$variedad_uva = trim($_POST["variedad_uva"] ?? "");
$fecha_inicio = trim($_POST["fecha_inicio"] ?? "");
$fecha_fin = trim($_POST["fecha_fin"] ?? "");
$cantidad_uva_kg = $_POST["cantidad_uva_kg"] ?? "0";
$cantidad_vino_litros = $_POST["cantidad_vino_litros"] ?? "0";
$estado = trim($_POST["estado"] ?? "Planificada");
$observaciones = trim($_POST["observaciones"] ?? "");

$estados = ["Planificada", "En proceso", "Finalizada", "Cancelada"];

if (!$id_parcela || $nombre_vino === "" || $variedad_uva === "" || $fecha_inicio === "") {
    Response::error("Complete los campos obligatorios", -1002, 400);
}

if (!is_numeric($cantidad_uva_kg) || !is_numeric($cantidad_vino_litros) ||
    (float)$cantidad_uva_kg < 0 || (float)$cantidad_vino_litros < 0) {
    Response::error("Las cantidades deben ser numericas y no negativas", -1003, 400);
}

if (!in_array($estado, $estados, true)) {
    Response::error("Estado no valido", -1004, 400);
}

$inicio = DateTime::createFromFormat("!Y-m-d", $fecha_inicio);
$fin = $fecha_fin !== "" ? DateTime::createFromFormat("!Y-m-d", $fecha_fin) : null;

if (!$inicio || $inicio->format("Y-m-d") !== $fecha_inicio) {
    Response::error("Fecha de inicio no valida", -1005, 400);
}

if ($fecha_fin !== "" && (!$fin || $fin->format("Y-m-d") !== $fecha_fin)) {
    Response::error("Fecha de finalizacion no valida", -1006, 400);
}

if ($fin && $fin < $inicio) {
    Response::error("La fecha de finalizacion no puede ser anterior a la fecha de inicio", -1007, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmt = $cn->prepare(
        "INSERT INTO producciones
        (id_parcela, nombre_vino, variedad_uva, fecha_inicio, fecha_fin,
         cantidad_uva_kg, cantidad_vino_litros, estado, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $id_parcela,
        $nombre_vino,
        $variedad_uva,
        $fecha_inicio,
        $fecha_fin !== "" ? $fecha_fin : null,
        $cantidad_uva_kg,
        $cantidad_vino_litros,
        $estado,
        $observaciones !== "" ? $observaciones : null
    ]);

    Response::success("Produccion registrada correctamente", 201, [
        "id_produccion" => $cn->lastInsertId()
    ]);
} catch (PDOException $e) {
    Response::error("No se pudo registrar la produccion. Verifique los datos", -1008, 500);
}
?>
