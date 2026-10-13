<?php
    require_once(__DIR__ . "/../../config/Connection.php");
    require_once(__DIR__ . "/../../config/Response.php");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        Response::error("Metodo no autorizado", -1000, 405);
    }

    if (!isset($_SESSION["rol"]) || ($_SESSION['rol'] !== '1' && $_SESSION['rol'] !== '3' && $_SESSION['rol'] !== '4')) {
        Response::error("No autorizado", -1001, 403);
    }

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

    if (!$id_parcela || $id_parcela <= 0 || empty($fecha_control)) {
        Response::error("La parcela y la fecha son obligatorias", -1002, 400);
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
            "INSERT INTO controles_tierra
            (id_parcela, fecha_control, ph, humedad, nitrogeno,
             fosforo, potasio, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $id_parcela,
            $fecha_control,
            $ph !== "" ? $ph : null,
            $humedad !== "" ? $humedad : null,
            $nitrogeno !== "" ? $nitrogeno : null,
            $fosforo !== "" ? $fosforo : null,
            $potasio !== "" ? $potasio : null,
            $observaciones !== "" ? $observaciones : null
        ]);

        Response::success("Control de tierra registrado correctamente", 200);

    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            Response::error("La parcela seleccionada no existe", -1006, 400);
        }

        Response::error("No se pudo registrar el control de tierra", -1007, 500);
    }
?>