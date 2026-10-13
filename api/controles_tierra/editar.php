<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3')) {
        Response::error("No autorizado", -1001, 403);
    }

    $id_control = filter_var(
        $_POST["id_control_tierra"] ?? null,
        FILTER_VALIDATE_INT
    );

    $id_parcela = filter_var(
        $_POST["id_parcela"] ?? null,
        FILTER_VALIDATE_INT
    );

    $fecha_control = $_POST["fecha_control"] ?? "";
    $ph = $_POST["ph"] ?? "";
    $humedad = $_POST["humedad"] ?? "";
    $nitrogeno = $_POST["nitrogeno"] ?? "";
    $fosforo = $_POST["fosforo"] ?? "";
    $potasio = $_POST["potasio"] ?? "";
    $observaciones = trim($_POST["observaciones"] ?? "");

    if (!$id_control || $id_control <= 0 ||
        !$id_parcela || $id_parcela <= 0 ||
        empty($fecha_control)) {
        Response::error("Los datos obligatorios estan incompletos", -1002, 400);
    }

    $fecha = DateTime::createFromFormat("!Y-m-d", $fecha_control);

    if (!$fecha || $fecha->format("Y-m-d") !== $fecha_control) {
        Response::error("La fecha del control no es valida", -1003, 400);
    }

    $mediciones = [
        "ph" => [$ph, 4, 2],
        "humedad" => [$humedad, 5, 2],
        "nitrogeno" => [$nitrogeno, 8, 2],
        "fosforo" => [$fosforo, 8, 2],
        "potasio" => [$potasio, 8, 2]
    ];

    foreach ($mediciones as $campo => $datos) {
        [$valor, $precision, $decimales] = $datos;

        if ($valor !== "" && !is_numeric($valor)) {
            Response::error("El valor de " . $campo . " debe ser numerico", -1004, 400);
        }

        if ($valor !== "") {
            $maximo = pow(10, $precision - $decimales) -
                      pow(10, -$decimales);

            if (abs((float)$valor) > $maximo) {
                Response::error("El valor de " . $campo . " excede el limite permitido", -1005, 400);
            }
        }
    }

    try {
        $cn = new Connection($_SESSION["rol"]);

        $stmt = $cn->prepare(
            "UPDATE controles_tierra
             SET id_parcela = ?, fecha_control = ?, ph = ?,
                 humedad = ?, nitrogeno = ?, fosforo = ?,
                 potasio = ?, observaciones = ?
             WHERE id_control_tierra = ?"
        );

        $stmt->execute([
            $id_parcela,
            $fecha_control,
            $ph !== "" ? $ph : null,
            $humedad !== "" ? $humedad : null,
            $nitrogeno !== "" ? $nitrogeno : null,
            $fosforo !== "" ? $fosforo : null,
            $potasio !== "" ? $potasio : null,
            $observaciones !== "" ? $observaciones : null,
            $id_control
        ]);

        if ($stmt->rowCount() === 0) {
            $verificar = $cn->prepare(
                "SELECT id_control_tierra
                 FROM controles_tierra
                 WHERE id_control_tierra = ?"
            );
            $verificar->execute([$id_control]);

            if (!$verificar->fetch(PDO::FETCH_ASSOC)) {
                Response::error("El control de tierra no existe", -1006, 404);
            }
        }

        Response::success("Control de tierra actualizado correctamente", 200);

    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            Response::error("La parcela seleccionada no existe", -1007, 400);
        }

        Response::error("No se pudo actualizar el control de tierra", -1008, 500);
    }
?>