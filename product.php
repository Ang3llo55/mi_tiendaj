<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
$db = get_db_connection();

$res = pg_prepare($db, "get_detail", "SELECT * FROM products WHERE id = $1");
$res = pg_execute($db, "get_detail", [$id]);
$p = pg_fetch_assoc($res);

if (!$p) die("Producto no existe.");

include 'includes/header.php';
?>

<div class="card mb-3">
  <div class="row g-0">
    <div class="col-md-4 text-center p-3">
      <?php if($p['image_path']): ?>
        <img src="<?= e($p['image_path']) ?>" class="product-img-detail rounded" alt="...">
      <?php else: ?>
        <div class="p-5 bg-light border">Sin imagen</div>
      <?php endif; ?>
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h2 class="card-title"><?= e($p['name']) ?></h2>
        <span class="badge bg-secondary"><?= e($p['category']) ?></span>
        <h4 class="mt-3 text-success">$<?= e($p['price']) ?></h4>
        <p class="card-text mt-3"><?= nl2br(e($p['description'])) ?></p>
        <p class="card-text"><small class="text-muted">Stock disponible: <?= e($p['stock']) ?></small></p>
        
        <div class="mt-4">
            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-warning">Editar</a>
            <a href="index.php" class="btn btn-outline-primary">Volver al listado</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>