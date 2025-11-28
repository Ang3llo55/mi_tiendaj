<?php
/**
 * product.php - Detalle de un producto
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Obtener ID del producto
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    set_flash('error', 'Producto no encontrado');
    redirect('index.php');
}

// Obtener producto
$result = db_execute(
    'product_get_by_id',
    'SELECT p.*, u.name as owner_name, u.email as owner_email 
     FROM products p 
     INNER JOIN users u ON p.user_id = u.id 
     WHERE p.id = $1',
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

$page_title = e($product['name']) . ' - Mi Tienda';

require __DIR__ . '/includes/header.php';
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-secondary">&larr; Volver al listado</a>
</div>

<div class="row">
    <div class="col-md-5">
        <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
        <img src="<?php echo e($product['image_path']); ?>" class="img-fluid rounded" 
             alt="<?php echo e($product['name']); ?>">
        <?php else: ?>
        <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" 
             style="height: 400px;">
            <span class="fs-1">Sin imagen</span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-7">
        <h1><?php echo e($product['name']); ?></h1>
        
        <?php if (!empty($product['category'])): ?>
        <span class="badge bg-secondary mb-3"><?php echo e($product['category']); ?></span>
        <?php endif; ?>
        
        <p class="h2 text-primary mb-4"><?php echo format_price($product['price']); ?></p>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Disponibilidad</h5>
                <p class="card-text mb-0">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success">En stock: <?php echo e($product['stock']); ?> unidades</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Agotado</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Descripción</h5>
                <p class="card-text"><?php echo nl2br(e($product['description'] ?? 'Sin descripción')); ?></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Información del vendedor</h5>
                <p class="card-text">
                    <strong>Nombre:</strong> <?php echo e($product['owner_name']); ?><br>
                    <strong>Registrado:</strong> <?php echo date('d/m/Y', strtotime($product['created_at'])); ?>
                </p>
            </div>
        </div>
        
        <?php if (is_owner($product['user_id'])): ?>
        <div class="d-flex gap-2">
            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning">
                Editar Producto
            </a>
            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger"
               onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                Eliminar Producto
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>