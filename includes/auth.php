<?php
/**
 * auth.php - Funciones de autenticación y sesiones
 */

/**
 * Inicia la sesión de forma segura
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_start();
    }
}

/**
 * Verifica si hay un usuario autenticado
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtiene el ID del usuario actual
 * @return int|null
 */
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtiene los datos del usuario actual
 * @return array|null
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $result = db_execute(
        'current_user_get',
        'SELECT id, name, email, created_at FROM users WHERE id = $1',
        [current_user_id()]
    );
    
    if (!$result) {
        return null;
    }
    
    return db_fetch($result);
}

/**
 * Requiere que el usuario esté autenticado
 * @param string $redirect_url URL a la que redirigir si no está autenticado
 */
function require_login($redirect_url = '/mi_tienda/login.php') {
    if (!is_logged_in()) {
        set_flash('error', 'Debes iniciar sesión para acceder a esta página');
        redirect($redirect_url);
    }
}

/**
 * Registra un nuevo usuario
 * @param string $name
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function register_user($name, $email, $password) {
    // Validar datos
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Todos los campos son obligatorios', 'user_id' => null];
    }
    
    if (!is_valid_email($email)) {
        return ['success' => false, 'message' => 'Email inválido', 'user_id' => null];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres', 'user_id' => null];
    }
    
    // Verificar si el email ya existe
    $result = db_execute(
        'user_check_email',
        'SELECT id FROM users WHERE email = $1',
        [$email]
    );
    
    if (!$result) {
        return ['success' => false, 'message' => 'Error al verificar el email', 'user_id' => null];
    }
    
    if (db_fetch($result)) {
        return ['success' => false, 'message' => 'El email ya está registrado', 'user_id' => null];
    }
    
    // Crear hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar usuario
    $result = db_execute(
        'user_insert',
        'INSERT INTO users (name, email, password_hash) VALUES ($1, $2, $3) RETURNING id',
        [$name, $email, $password_hash]
    );
    
    if (!$result) {
        return ['success' => false, 'message' => 'Error al crear el usuario', 'user_id' => null];
    }
    
    $row = db_fetch($result);
    
    return [
        'success' => true,
        'message' => 'Usuario registrado exitosamente',
        'user_id' => $row['id']
    ];
}

/**
 * Autentica un usuario
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function login_user($email, $password) {
    // Validar datos
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email y contraseña son obligatorios', 'user_id' => null];
    }
    
    // Buscar usuario por email
    $result = db_execute(
        'user_get_by_email',
        'SELECT id, name, email, password_hash FROM users WHERE email = $1',
        [$email]
    );
    
    if (!$result) {
        return ['success' => false, 'message' => 'Error al buscar el usuario', 'user_id' => null];
    }
    
    $user = db_fetch($result);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Email o contraseña incorrectos', 'user_id' => null];
    }
    
    // Verificar contraseña
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Email o contraseña incorrectos', 'user_id' => null];
    }
    
    // Regenerar ID de sesión para prevenir fijación de sesión
    session_regenerate_id(true);
    
    // Establecer sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    
    return [
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'user_id' => $user['id']
    ];
}

/**
 * Cierra la sesión del usuario
 */
function logout_user() {
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Verifica autenticación para API
 * @return bool
 */
function api_check_auth() {
    init_session();
    return is_logged_in();
}

/**
 * Verifica CSRF para API
 * @return bool
 */
function api_check_csrf() {
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? null;
    
    if (!$token) {
        return false;
    }
    
    return verify_csrf($token);
}

// Iniciar sesión automáticamente
init_session();