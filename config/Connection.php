<?php
    require_once(__DIR__  .  "/Configuration.php");
    require_once(__DIR__  .  "/Response.php");

    date_default_timezone_set("America/Guatemala");

    class Connection extends PDO
    {
        public function __construct(string $rol)
        {
            try {
                //Crear la conexion con los parametros de configuration.php
                $dsn = "mysql:host=" . HOST_DB . ";dbname=" . DATABASE . ";charset=" . CHARSET;
                parent::__construct($dsn, USER_DB, PASSWORD_DB);
                //Detectar si ocurre algun error en sql
                $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                //Informacion de erorr de conexion
                Response::error("Ocurrio un error", -1001, 400);
                //Response::debug($e->getMessage(), -1001, 400);
            }
        }
    }
