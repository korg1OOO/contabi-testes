<?php

define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT === true) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Contabi');
define('URL_BASE', 'http://localhost:8080');
define('URL_BASE_CSS', URL_BASE . '/assets/css');
define('UPLOAD_PATH', __DIR__ . '/../../public/assets/uploads');
define('STORAGE_PATH', __DIR__ . '/../../public/assets/uploads');

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_contabi');
define('DB_USER', 'root');
define('DB_PASS', '');
