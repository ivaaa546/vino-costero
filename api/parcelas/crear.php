
<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3' && $_SESSION['rol'] !== '4')) {
        Response::error("No autorizado", -1001, 403);
    }

    $nombre = trim($_POST["nombre"] ?? '');
    $ubicacion = trim($_POST["ubicacion"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');

    if (empty($nombre)) {
        Response::error("El nombre de la parcela es obligatorio", -1002, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare(
            "INSERT INTO parcelas (nombre, ubicacion, descripcion)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $nombre,
            $ubicacion !== '' ? $ubicacion : null,
            $descripcion !== '' ? $descripcion : null
        ]);

        Response::success("Parcela creada correctamente", 200);

    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            Response::error("Ya existe una parcela con ese nombre", -1003, 409);
        }

        Response::error("No se pudo crear la parcela", -1004, 500);
    }
?>
