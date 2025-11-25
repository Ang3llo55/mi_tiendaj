<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = get_db_connection();

// Preparar consulta para listar
$query = "SELECT * FROM products ORDER BY id DESC";
$result = pg_prepare($db, "list_products", $query);
$result = pg_execute($db, "list_products", []);

$products = pg_fetch_all($result);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Listado de Productos</h1>
    <a href="add_product.php" class="btn btn-primary">Nuevo Producto</a>
</div>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Img</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if($products): ?>
            <?php foreach($products as $p): ?>
            <tr>
                <td>
                    <?php if($p['image_path']): ?>
                        <img src="<?= e($p['image_path']) ?>" class="product-img-thumb" alt="img">
                    <?php else: ?>
                        <span class="text-muted">N/A</span>
                    <?php endif; ?>
                </td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category']) ?></td>
                <td>$<?= e($p['price']) ?></td>
                <td><?= e($p['stock']) ?></td>
                <td>
                    <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                    <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger">Borrar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">No hay productos registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>