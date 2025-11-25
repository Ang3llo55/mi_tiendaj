<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$name = $description = $category = "";
$price = $stock = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = trim($_POST['category']);
    
    $errors = validate_product_input($_POST);
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $image_path = handle_image_upload($_FILES['image']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $db = get_db_connection();
        $sql = "INSERT INTO products (name, description, price, stock, category, image_path) VALUES ($1, $2, $3, $4, $5, $6)";
        $stmt = pg_prepare($db, "insert_product", $sql);
        $res = pg_execute($db, "insert_product", [$name, $description, $price, $stock, $category, $image_path]);

        if ($res) {
            $_SESSION['flash'] = "Producto creado exitosamente.";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Error al guardar en base de datos: " . pg_last_error($db);
        }
    }
}

include 'includes/header.php';
?>

<h2>Agregar Producto</h2>
<?php if(!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div class="mb-3">
        <label>Nombre *</label>
        <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
    </div>
    <div class="mb-3">
        <label>Categoría</label>
        <input type="text" name="category" class="form-control" value="<?= e($category) ?>">
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Precio *</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= e($price) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label>Stock *</label>
            <input type="number" name="stock" class="form-control" value="<?= e($stock) ?>" required>
        </div>
    </div>
    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="description" class="form-control"><?= e($description) ?></textarea>
    </div>
    <div class="mb-3">
        <label>Imagen (Opcional, jpg/png, max 2MB)</label>
        <input type="file" name="image" class="form-control">
    </div>
    
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php include 'includes/footer.php'; ?>