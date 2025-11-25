<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$db = get_db_connection();

// Buscar nombre para confirmación
$res = pg_prepare($db, "find_name", "SELECT name FROM products WHERE id = $1");
$res = pg_execute($db, "find_name", [$id]);
$product = pg_fetch_assoc($res);

if (!$product) { die("Producto no encontrado"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);
    
    $del = pg_prepare($db, "delete_prod", "DELETE FROM products WHERE id = $1");
    $del = pg_execute($db, "delete_prod", [$id]);
    
    if ($del) {
        $_SESSION['flash'] = "Producto eliminado.";
        header("Location: index.php");
        exit;
    } else {
        echo "Error al eliminar.";
    }
}

include 'includes/header.php';
?>

<div class="alert alert-warning">
    <h3>¿Estás seguro?</h3>
    <p>Vas a eliminar el producto: <strong><?= e($product['name']) ?></strong></p>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>