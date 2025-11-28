<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Tienda Web'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/mi_tienda/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/mi_tienda/index.php">🛒 Mi Tienda</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/mi_tienda/index.php">Productos</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mi_tienda/add_product.php">Agregar Producto</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">
                            👤 <?php echo e($_SESSION['user_name']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/mi_tienda/logout.php">Cerrar Sesión</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mi_tienda/login.php">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/mi_tienda/register.php">Registrarse</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php echo render_flash(); ?>