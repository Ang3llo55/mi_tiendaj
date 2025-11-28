<?php
/**
 * logout.php - Cierre de sesión
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Cerrar sesión
logout_user();

// Redirigir con mensaje
init_session();
set_flash('success', 'Has cerrado sesión correctamente');
redirect('index.php');