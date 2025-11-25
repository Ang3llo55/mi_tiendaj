<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Tienda PHP</title>
    <!-- Bootstrap CDN opcional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">Mi Tienda</a>
    <div class="navbar-nav">
      <a class="nav-link" href="index.php">Productos</a>
      <a class="nav-link" href="add_product.php">Agregar Producto</a>
    </div>
  </div>
</nav>
<div class="container">
    <?php if(isset($_SESSION['flash'])): ?>
        <div class="alert alert-info">
            <?= e($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>