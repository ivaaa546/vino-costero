<?php

    require_once("Env.php");
    Env::load(__DIR__ . "/.env");

    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    define('COOKIE_NAME', Env::get('COOKIE_NAME'));
    define('COOKIE_KEY', Env::get('COOKIE_KEY'));

    //base de datos
    define("HOST_DB", Env::get('DATABASE_URL'));
    define("DATABASE", Env::get('DATABASE_NAME'));
    define("CHARSET", Env::get('DATABASE_CHARSET'));

    //usuario admin
    define("USER_DB", Env::get('DB_USER'));
    define("PASSWORD_DB", Env::get('DB_PSSW'));


