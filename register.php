<?php
/**
 * register.php - Formulario de registro de usuarios
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Registro - Mi Tienda';

// Redirigir si ya está autenticado
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$name = '';
$email = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    } else {
        $name = validate_input($_POST['name'] ?? '');
        $email = validate_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Validaciones
        if (empty($name)) {
            $errors[] = 'El nombre es obligatorio';
        }
        
        if (empty($email)) {
            $errors[] = 'El email es obligatorio';
        } elseif (!is_valid_email($email)) {
            $errors[] = 'El email no es válido';
        }
        
        if (empty($password)) {
            $errors[] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($password !== $password_confirm) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        // Si no hay errores, registrar usuario
        if (empty($errors)) {
            $result = register_user($name, $email, $password);
            
            if ($result['success']) {
                set_flash('success', $result['message']);
                redirect('login.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Crear Cuenta</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre completo *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo e($name); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo e($email); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               minlength="6" required>
                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar contraseña *</label>
                        <input type="password" class="form-control" id="password_confirm" 
                               name="password_confirm" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>