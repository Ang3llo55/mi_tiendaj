<?php
/**
 * delete_product.php - Eliminar producto
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Eliminar Producto - Mi Tienda';

// Requiere autenticación
require_login();

// Obtener ID del producto
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    set_flash('error', 'Producto no encontrado');
    redirect('index.php');
}

// Obtener producto
$result = db_execute(
    'product_get_for_delete',
    'SELECT * FROM products WHERE id = $1',
    [$product_id]
);

if (!$result) {
    set_flash('error', 'Error al obtener el producto');
    redirect('index.php');
}

$product = db_fetch($result);

if (!$product) {
    set_flash('error', 'Producto no encontrado');
    redirect('index.php');
}

// Verificar que el usuario actual es el propietario
if (!is_owner($product['user_id'])) {
    set_flash('error', 'No tienes permiso para eliminar este producto');
    redirect('product.php?id=' . $product_id);
}

$errors = [];

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    } else {
        // Eliminar producto
        $result = db_execute(
            'product_delete',
            'DELETE FROM products WHERE id = $1 AND user_id = $2',
            [$product_id, current_user_id()]
        );
        
        if ($result && db_affected_rows($result) > 0) {
            // Eliminar imagen si existe
            if (!empty($product['image_path'])) {
                delete_image($product['image_path']);
            }
            
            set_flash('success', 'Producto eliminado exitosamente');
            redirect('index.php');
        } else {
            $errors[] = 'Error al eliminar el producto';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h3 class="mb-0">Confirmar Eliminación</h3>
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
                
                <div class="alert alert-warning">
                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                </div>
                
                <div class="mb-3">
                    <h5>¿Estás seguro de que deseas eliminar este producto?</h5>
                    
                    <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
                    <div class="text-center my-3">
                        <img src="<?php echo e($product['image_path']); ?>" 
                             alt="<?php echo e($product['name']); ?>" 
                             style="max-width: 200px;" class="img-thumbnail">
                    </div>
                    <?php endif; ?>
                    
                    <ul class="list-unstyled">
                        <li><strong>Nombre:</strong> <?php echo e($product['name']); ?></li>
                        <li><strong>Precio:</strong> <?php echo format_price($product['price']); ?></li>
                        <li><strong>Stock:</strong> <?php echo e($product['stock']); ?> unidades</li>
                        <?php if (!empty($product['category'])): ?>
                        <li><strong>Categoría:</strong> <?php echo e($product['category']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <form method="POST" action="delete_product.php?id=<?php echo $product_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">Sí, Eliminar Producto</button>
                        <a href="product.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>