<?php
/**
 * index.php - Página principal con listado de productos
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Productos - Mi Tienda';

// Obtener parámetros de búsqueda y filtrado
$search = isset($_GET['search']) ? validate_input($_GET['search']) : '';
$category = isset($_GET['category']) ? validate_input($_GET['category']) : '';

// Construir consulta
$query = 'SELECT p.*, u.name as owner_name FROM products p 
          INNER JOIN users u ON p.user_id = u.id 
          WHERE 1=1';
$params = [];
$param_count = 1;

if (!empty($search)) {
    $query .= " AND (p.name ILIKE $" . $param_count . " OR p.description ILIKE $" . $param_count . ")";
    $params[] = "%$search%";
    $param_count++;
}

if (!empty($category)) {
    $query .= " AND p.category = $" . $param_count;
    $params[] = $category;
    $param_count++;
}

$query .= ' ORDER BY p.created_at DESC';

// Ejecutar consulta
$result = db_execute('products_list', $query, $params);

if (!$result) {
    die('Error al obtener productos');
}

$products = db_fetch_all($result);

// Obtener categorías únicas para el filtro
$cat_result = db_execute(
    'categories_list',
    'SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category',
    []
);
$categories = $cat_result ? db_fetch_all($cat_result) : [];

require __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h1>Productos Disponibles</h1>
    </div>
    <?php if (is_logged_in()): ?>
    <div class="col-auto">
        <a href="add_product.php" class="btn btn-primary">+ Agregar Producto</a>
    </div>
    <?php endif; ?>
</div>

<!-- Filtros de búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" placeholder="Buscar producto..." 
                       value="<?php echo e($search); ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="category">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat['category']); ?>" 
                            <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['category']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        <p class="mb-0">No se encontraron productos.
        <?php if (is_logged_in()): ?>
            <a href="add_product.php">¿Quieres agregar uno?</a>
        <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
                <img src="<?php echo e($product['image_path']); ?>" class="card-img-top product-image" 
                     alt="<?php echo e($product['name']); ?>">
                <?php else: ?>
                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                     style="height: 200px;">
                    <span>Sin imagen</span>
                </div>
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo e($product['name']); ?></h5>
                    
                    <?php if (!empty($product['category'])): ?>
                    <span class="badge bg-secondary mb-2 align-self-start">
                        <?php echo e($product['category']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <p class="card-text flex-grow-1">
                        <?php echo e(substr($product['description'] ?? '', 0, 100)); ?>
                        <?php echo strlen($product['description'] ?? '') > 100 ? '...' : ''; ?>
                    </p>
                    
                    <div class="mt-auto">
                        <p class="h4 text-primary mb-2"><?php echo format_price($product['price']); ?></p>
                        <p class="text-muted small mb-3">
                            Stock: <?php echo e($product['stock']); ?> unidades<br>
                            Vendedor: <?php echo e($product['owner_name']); ?>
                        </p>
                        
                        <div class="d-flex gap-2">
                            <a href="product.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-outline-primary flex-grow-1">Ver Detalle</a>
                            
                            <?php if (is_owner($product['user_id'])): ?>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-warning">Editar</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>