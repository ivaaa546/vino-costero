
<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
        Response::error("No autorizado", -1001, 403);
    }

    $id_parcela = filter_var(
        $_POST["id_parcela"] ?? null,
        FILTER_VALIDATE_INT
    );

    $nombre = trim($_POST["nombre"] ?? '');
    $ubicacion = trim($_POST["ubicacion"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $estado = $_POST["estado"] ?? 'Activa';

    if (!$id_parcela || $id_parcela <= 0 || empty($nombre)) {
        Response::error("Datos obligatorios incompletos", -1002, 400);
    }

    if (!in_array($estado, ['Activa', 'Inactiva'], true)) {
        Response::error("Estado no valido", -1003, 400);
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare(
            "UPDATE parcelas
             SET nombre = ?, ubicacion = ?, descripcion = ?, estado = ?
             WHERE id_parcela = ?"
        );

        $stmt->execute([
            $nombre,
            $ubicacion !== '' ? $ubicacion : null,
            $descripcion !== '' ? $descripcion : null,
            $estado,
            $id_parcela
        ]);

        if ($stmt->rowCount() === 0) {
            $verificar = $cn->prepare(
                "SELECT id_parcela FROM parcelas WHERE id_parcela = ?"
            );
            $verificar->execute([$id_parcela]);

            if (!$verificar->fetch(PDO::FETCH_ASSOC)) {
                Response::error("La parcela no existe", -1004, 404);
            }
        }

        Response::success("Parcela actualizada correctamente", 200);

    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            Response::error("Ya existe una parcela con ese nombre", -1005, 409);
        }

        Response::error("No se pudo actualizar la parcela", -1006, 500);
    }
?>
