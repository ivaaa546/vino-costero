
<?php
require_once(__DIR__ . "/../../config/Connection.php");
require_once(__DIR__ . "/../../config/Response.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Metodo no autorizado", -1000, 405);
}

if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '2')) {
    Response::error("No autorizado", -1001, 403);
}

$id_produccion = filter_input(INPUT_POST, "id_produccion", FILTER_VALIDATE_INT);
$fecha_cata = trim($_POST["fecha_cata"] ?? "");
$aroma = trim($_POST["aroma"] ?? "");
$sabor = trim($_POST["sabor"] ?? "");
$color = trim($_POST["color"] ?? "");
$cuerpo = trim($_POST["cuerpo"] ?? "");
$puntuacion = trim($_POST["puntuacion"] ?? "");
$resultado = trim($_POST["resultado"] ?? "");
$observaciones = trim($_POST["observaciones"] ?? "");

$resultadosValidos = [
    "Aprobado",
    "Requiere mejoras",
    "Rechazado"
];

if (
    !$id_produccion ||
    $fecha_cata === "" ||
    $puntuacion === "" ||
    !is_numeric($puntuacion) ||
    !in_array($resultado, $resultadosValidos, true)
) {
    Response::error("Complete correctamente los campos obligatorios", -1002, 400);
}

if ((float)$puntuacion < 0 || (float)$puntuacion > 100) {
    Response::error("La puntuacion debe estar entre 0 y 100", -1003, 400);
}

$fecha = DateTime::createFromFormat("!Y-m-d", $fecha_cata);

if (!$fecha || $fecha->format("Y-m-d") !== $fecha_cata) {
    Response::error("La fecha de la cata no es valida", -1004, 400);
}

try {
    $cn = new Connection($_SESSION["rol"]);

    $stmtProduccion = $cn->prepare(
        "SELECT id_produccion
         FROM producciones
         WHERE id_produccion = ?"
    );
    $stmtProduccion->execute([$id_produccion]);

    if (!$stmtProduccion->fetch(PDO::FETCH_ASSOC)) {
        Response::error("La produccion seleccionada no existe", -1005, 400);
    }

    $stmt = $cn->prepare(
        "INSERT INTO catas (
            id_produccion,
            fecha_cata,
            aroma,
            sabor,
            color,
            cuerpo,
            puntuacion,
            resultado,
            observaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $id_produccion,
        $fecha_cata,
        $aroma !== "" ? $aroma : null,
        $sabor !== "" ? $sabor : null,
        $color !== "" ? $color : null,
        $cuerpo !== "" ? $cuerpo : null,
        (float)$puntuacion,
        $resultado,
        $observaciones !== "" ? $observaciones : null
    ]);

    Response::success(
        "Cata registrada correctamente",
        201,
        ["id_cata" => $cn->lastInsertId()]
    );

} catch (PDOException $e) {
    Response::error("Error al registrar la cata", -1006, 500);
}
?>
