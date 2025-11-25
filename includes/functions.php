<?php
// includes/functions.php

session_start();

// Escapar HTML para prevenir XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Generar Token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar Token CSRF
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("Error de seguridad: Token CSRF inválido.");
    }
    return true;
}

// Validar Inputs
function validate_product_input($data) {
    $errors = [];
    if (empty($data['name'])) $errors[] = "El nombre es obligatorio.";
    if (!is_numeric($data['price']) || $data['price'] < 0) $errors[] = "El precio debe ser un número positivo.";
    if (!is_numeric($data['stock']) || $data['stock'] < 0) $errors[] = "El stock debe ser un entero positivo.";
    return $errors;
}

// Manejo de subida de imágenes
function handle_image_upload($file_array) {
    if ($file_array['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Validar tipo MIME y tamaño
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file_array['tmp_name']);

    if (!in_array($mime, $allowed_types)) {
        throw new Exception("Tipo de archivo no permitido. Solo JPG/PNG.");
    }

    if ($file_array['size'] > $max_size) {
        throw new Exception("El archivo es demasiado grande (Máx 2MB).");
    }

    // Generar nombre único
    $ext = ($mime === 'image/jpeg') ? 'jpg' : 'png';
    $filename = uniqid('prod_', true) . '.' . $ext;
    $target_dir = __DIR__ . '/../uploads/';
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file_array['tmp_name'], $target_file)) {
        return 'uploads/' . $filename;
    }

    throw new Exception("Error al mover el archivo subido.");
}

// Respuesta JSON para API
function json_response($data, $status = 200) {
    header("Content-Type: application/json");
    http_response_code($status);
    echo json_encode($data);
    exit;
}
?>