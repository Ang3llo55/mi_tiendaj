<?php
/**
 * functions.php - Funciones auxiliares reutilizables
 */

/**
 * Renderiza HTML escapado
 * @param mixed $value
 * @return string
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Valida y sanea entrada
 * @param string $input
 * @return string
 */
function validate_input($input) {
    return trim(strip_tags($input));
}

/**
 * Genera un token CSRF
 * @return string
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica el token CSRF
 * @param string $token
 * @return bool
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifica si un archivo es una imagen válida
 * @param array $file Array $_FILES
 * @return bool
 */
function is_valid_image($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        return false;
    }
    
    // Validar tamaño (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    return true;
}

/**
 * Guarda una imagen subida
 * @param array $file Array $_FILES
 * @return string|false Ruta relativa o false en caso de error
 */
function save_image($file) {
    if (!is_valid_image($file)) {
        return false;
    }
    
    $upload_dir = __DIR__ . '/../uploads/';
    
    // Crear directorio si no existe
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $destination = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/' . $filename;
    }
    
    return false;
}

/**
 * Elimina una imagen
 * @param string $image_path
 * @return bool
 */
function delete_image($image_path) {
    if (empty($image_path)) {
        return true;
    }
    
    $full_path = __DIR__ . '/../' . $image_path;
    
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    
    return true;
}

/**
 * Redirecciona a una URL
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Establece un mensaje flash en la sesión
 * @param string $type success, error, info, warning
 * @param string $message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtiene y limpia el mensaje flash
 * @return array|null
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Renderiza el mensaje flash en HTML
 * @return string
 */
function render_flash() {
    $flash = get_flash();
    if (!$flash) {
        return '';
    }
    
    $type_class = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'info' => 'alert-info',
        'warning' => 'alert-warning'
    ];
    
    $class = $type_class[$flash['type']] ?? 'alert-info';
    
    return sprintf(
        '<div class="alert %s alert-dismissible fade show" role="alert">%s<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>',
        $class,
        e($flash['message'])
    );
}

/**
 * Valida un email
 * @param string $email
 * @return bool
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida un precio
 * @param mixed $price
 * @return bool
 */
function is_valid_price($price) {
    return is_numeric($price) && $price >= 0;
}

/**
 * Valida un stock
 * @param mixed $stock
 * @return bool
 */
function is_valid_stock($stock) {
    return is_numeric($stock) && $stock >= 0 && $stock == (int)$stock;
}

/**
 * Formatea un precio para mostrar
 * @param float $price
 * @return string
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Verifica si el usuario actual es el propietario de un recurso
 * @param int $owner_id
 * @return bool
 */
function is_owner($owner_id) {
    return is_logged_in() && current_user_id() == $owner_id;
}

/**
 * Responde con JSON
 * @param mixed $data
 * @param int $status_code
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Responde con error JSON
 * @param string $message
 * @param int $status_code
 */
function json_error($message, $status_code = 400) {
    json_response(['error' => $message], $status_code);
}