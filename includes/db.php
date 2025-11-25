<?php
// includes/db.php

define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'mi_tienda_db');
define('DB_USER', 'tienda_user');
define('DB_PASS', 'tienda_password'); // Cambiar por contraseña real

function get_db_connection() {
    $conn_string = "host=".DB_HOST." port=".DB_PORT." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASS;
    $db = pg_connect($conn_string);

    if (!$db) {
        die("Error: No se pudo conectar a la base de datos.");
    }
    return $db;
}
?>