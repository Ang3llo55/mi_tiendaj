<?php
/**
 * db.php - Conexión a la base de datos PostgreSQL
 * 
 * INSTRUCCIONES:
 * - Ajusta las constantes DB_* según tu configuración
 * - Asegúrate de tener instalada la extensión php-pgsql
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'tienda_db');
define('DB_USER', 'tienda_user');
define('DB_PASS', 'tienda_password');

// Variable global para la conexión
$db_connection = null;

/**
 * Obtiene la conexión a la base de datos
 * @return resource|false
 */
function get_db_connection() {
    global $db_connection;
    
    if ($db_connection !== null) {
        return $db_connection;
    }
    
    $conn_string = sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_USER,
        DB_PASS
    );
    
    $db_connection = @pg_connect($conn_string);
    
    if (!$db_connection) {
        error_log("Error de conexión a PostgreSQL: " . pg_last_error());
        return false;
    }
    
    // Configurar el cliente para usar UTF-8
    pg_set_client_encoding($db_connection, 'UTF8');
    
    return $db_connection;
}

/**
 * Cierra la conexión a la base de datos
 */
function close_db_connection() {
    global $db_connection;
    
    if ($db_connection !== null) {
        pg_close($db_connection);
        $db_connection = null;
    }
}

/**
 * Ejecuta una consulta preparada
 * @param string $stmt_name Nombre del statement
 * @param string $query Consulta SQL con placeholders $1, $2, etc.
 * @param array $params Parámetros para la consulta
 * @return resource|false
 */
function db_execute($stmt_name, $query, $params = []) {
    $conn = get_db_connection();
    if (!$conn) {
        return false;
    }
    
    // Preparar la consulta
    $prepare = @pg_prepare($conn, $stmt_name, $query);
    if (!$prepare) {
        error_log("Error preparando consulta '$stmt_name': " . pg_last_error($conn));
        return false;
    }
    
    // Ejecutar la consulta
    $result = @pg_execute($conn, $stmt_name, $params);
    if (!$result) {
        error_log("Error ejecutando consulta '$stmt_name': " . pg_last_error($conn));
        return false;
    }
    
    return $result;
}

/**
 * Obtiene una fila como array asociativo
 * @param resource $result
 * @return array|false
 */
function db_fetch($result) {
    return pg_fetch_assoc($result);
}

/**
 * Obtiene todas las filas como array asociativo
 * @param resource $result
 * @return array
 */
function db_fetch_all($result) {
    return pg_fetch_all($result) ?: [];
}

/**
 * Obtiene el número de filas afectadas
 * @param resource $result
 * @return int
 */
function db_affected_rows($result) {
    return pg_affected_rows($result);
}

/**
 * Escapa una cadena para usar en consultas (usar preferiblemente parámetros preparados)
 * @param string $string
 * @return string
 */
function db_escape($string) {
    $conn = get_db_connection();
    if (!$conn) {
        return addslashes($string);
    }
    return pg_escape_string($conn, $string);
}

// Inicializar conexión
get_db_connection();