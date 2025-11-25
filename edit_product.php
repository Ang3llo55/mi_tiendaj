<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$db = get_db_connection();

// Obtener producto actual
$res = pg_prepare($db, "get_one", "SELECT * FROM products WHERE id = $1");
$res = pg_execute($db, "get_one", [$id]);
$product = pg_fetch_assoc($res);

if (!$product) { die("Producto no encontrado"); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);
    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = trim($_POST['category']);
    $image_path = $product['image_path']; // Mantener antigua por defecto

    $errors = validate_product_input($_POST);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $image_path = handle_image_upload($_FILES['image']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE products SET name=$1, description=$2, price=$3, stock=$4, category=$5, image_path=$6, updated_at=NOW() WHERE id=$7";
        $stmt = pg_prepare($db, "update_product", $sql);
        $res = pg_execute($db, "update_product", [$name, $description, $price, $stock, $category, $image_path, $id]);

        if ($res) {
            $_SESSION['flash'] = "Producto actualizado.";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Error SQL: " . pg_last_error($db);
        }
    }
}

include 'includes/header.php';
?>

<h2>Editar Producto</h2>
<?php if(!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div class="mb-3">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? $product['name']) ?>" required>
    </div>
    <div class="mb-3">
        <label>Categoría</label>
        <input type="text" name="category" class="form-control" value="<?= e($_POST['category'] ?? $product['category']) ?>">
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Precio</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= e($_POST['price'] ?? $product['price']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" value="<?= e($_POST['stock'] ?? $product['stock']) ?>" required>
        </div>
    </div>
    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="description" class="form-control"><?= e($_POST['description'] ?? $product['description']) ?></textarea>
    </div>
    
    <div class="mb-3">
        <p>Imagen actual:</p>
        <?php if($product['image_path']): ?>
            <img src="<?= e($product['image_path']) ?>" class="product-img-thumb mb-2">
        <?php else: ?>
            <span class="text-muted">Sin imagen</span>
        <?php endif; ?>
        <input type="file" name="image" class="form-control">
        <small class="text-muted">Subir nueva para reemplazar.</small>
    </div>
    
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php include 'includes/footer.php'; ?>