<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
    Response::error("No autorizado", -1001, 403);
}

$id_enfermedad = filter_var(
    $_POST["id_siembra_enferma"] ?? null,
    FILTER_VALIDATE_INT
);

$id_siembra = filter_var(
    $_POST["id_siembra"] ?? null,
    FILTER_VALIDATE_INT
);

$nombre_enfermedad = trim($_POST["nombre_enfermedad"] ?? "");
$fecha_deteccion = $_POST["fecha_deteccion"] ?? "";

$cantidad_plantas_afectadas = filter_var(
    $_POST["cantidad_plantas_afectadas"] ?? null,
    FILTER_VALIDATE_INT
);

$nivel_gravedad = $_POST["nivel_gravedad"] ?? "";
$tratamiento = trim($_POST["tratamiento"] ?? "");
$estado = $_POST["estado"] ?? "";
$observaciones = trim($_POST["observaciones"] ?? "");

if (
    !$id_enfermedad || $id_enfermedad <= 0 ||
    !$id_siembra || $id_siembra <= 0 ||
    $nombre_enfermedad === "" ||
    $fecha_deteccion === "" ||
    $cantidad_plantas_afectadas === false ||
    $cantidad_plantas_afectadas === null ||
    $nivel_gravedad === "" ||
    $estado === ""
) {
    Response::error("Complete los campos obligatorios", -1002, 400);
}

if ($cantidad_plantas_afectadas <= 0) {
    Response::error("La cantidad de plantas afectadas debe ser mayor a cero", -1003, 400);
}

$gravedadesPermitidas = ["Leve", "Moderada", "Grave"];

if (!in_array($nivel_gravedad, $gravedadesPermitidas, true)) {
    Response::error("Nivel de gravedad no valido", -1004, 400);
}

$estadosPermitidos = [
    "Detectada",
    "En tratamiento",
    "Controlada",
    "Sin recuperacion"
];

if (!in_array($estado, $estadosPermitidos, true)) {
    Response::error("Estado de enfermedad no valido", -1005, 400);
}

$fecha = DateTime::createFromFormat("!Y-m-d", $fecha_deteccion);

if (!$fecha || $fecha->format("Y-m-d") !== $fecha_deteccion) {
    Response::error("Fecha de deteccion no valida", -1006, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $sql = "UPDATE siembras_enfermas
            SET id_siembra = ?,
                nombre_enfermedad = ?,
                fecha_deteccion = ?,
                cantidad_plantas_afectadas = ?,
                nivel_gravedad = ?,
                tratamiento = ?,
                estado = ?,
                observaciones = ?
            WHERE id_siembra_enferma = ?";

    $stmt = $cn->prepare($sql);
    $stmt->execute([
        $id_siembra,
        $nombre_enfermedad,
        $fecha_deteccion,
        $cantidad_plantas_afectadas,
        $nivel_gravedad,
        $tratamiento !== "" ? $tratamiento : null,
        $estado,
        $observaciones !== "" ? $observaciones : null,
        $id_enfermedad
    ]);

    if ($stmt->rowCount() === 0) {
        $verificar = $cn->prepare(
            "SELECT id_siembra_enferma
             FROM siembras_enfermas
             WHERE id_siembra_enferma = ?"
        );

        $verificar->execute([$id_enfermedad]);

        if (!$verificar->fetch(PDO::FETCH_ASSOC)) {
            Response::error("El registro de enfermedad no existe", -1007, 404);
        }
    }

    Response::success("Enfermedad actualizada correctamente", 200, [
        "id_siembra_enferma" => $id_enfermedad
    ]);

} catch (PDOException $error) {
    Response::error("No se pudo actualizar la enfermedad", -1008, 500);
}
?>