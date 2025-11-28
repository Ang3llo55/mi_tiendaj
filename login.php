<?php
/**
 * login.php - Formulario de inicio de sesión
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Iniciar Sesión - Mi Tienda';

// Redirigir si ya está autenticado
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$email = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    } else {
        $email = validate_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validaciones básicas
        if (empty($email) || empty($password)) {
            $errors[] = 'Email y contraseña son obligatorios';
        } else {
            $result = login_user($email, $password);
            
            if ($result['success']) {
                set_flash('success', '¡Bienvenido de nuevo!');
                redirect('index.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Iniciar Sesión</h3>
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
                
                <form method="POST" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo e($email); ?>" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
                </p>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <strong>Usuarios de prueba:</strong><br>
            Email: <code>juan@example.com</code> o <code>maria@example.com</code><br>
            Contraseña: <code>password123</code>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>