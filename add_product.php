<?php
/**
 * add_product.php - Formulario para agregar productos
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Agregar Producto - Mi Tienda';

// Requiere autenticación
require_login();

$errors = [];
$name = '';
$description = '';
$price = '';
$stock = '';
$category = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    } else {
        $name = validate_input($_POST['name'] ?? '');
        $description = validate_input($_POST['description'] ?? '');
        $price = validate_input($_POST['price'] ?? '');
        $stock = validate_input($_POST['stock'] ?? '');
        $category = validate_input($_POST['category'] ?? '');
        
        // Validaciones
        if (empty($name)) {
            $errors[] = 'El nombre es obligatorio';
        }
        
        if (empty($price)) {
            $errors[] = 'El precio es obligatorio';
        } elseif (!is_valid_price($price)) {
            $errors[] = 'El precio debe ser un número válido mayor o igual a 0';
        }
        
        if (empty($stock)) {
            $errors[] = 'El stock es obligatorio';
        } elseif (!is_valid_stock($stock)) {
            $errors[] = 'El stock debe ser un número entero mayor o igual a 0';
        }
        
        // Procesar imagen si se subió
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_path = save_image($_FILES['image']);
                if (!$image_path) {
                    $errors[] = 'Error al subir la imagen. Asegúrate de que sea JPG o PNG y menor a 5MB';
                }
            } else {
                $errors[] = 'Error al subir el archivo';
            }
        }
        
        // Si no hay errores, insertar producto
        if (empty($errors)) {
            $result = db_execute(
                'product_insert',
                'INSERT INTO products (user_id, name, description, price, stock, category, image_path) 
                 VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id',
                [
                    current_user_id(),
                    $name,
                    $description,
                    $price,
                    (int)$stock,
                    !empty($category) ? $category : null,
                    $image_path
                ]
            );
            
            if ($result) {
                $row = db_fetch($result);
                set_flash('success', 'Producto agregado exitosamente');
                redirect('product.php?id=' . $row['id']);
            } else {
                $errors[] = 'Error al crear el producto';
                // Eliminar imagen si se subió
                if ($image_path) {
                    delete_image($image_path);
                }
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Agregar Nuevo Producto</h3>
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
                
                <form method="POST" action="add_product.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del producto *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo e($name); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="4"><?php echo e($description); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" value="<?php echo e($price); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label">Stock *</label>
                            <input type="number" class="form-control" id="stock" name="stock" 
                                   min="0" value="<?php echo e($stock); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Categoría</label>
                            <input type="text" class="form-control" id="category" name="category" 
                                   value="<?php echo e($category); ?>" 
                                   placeholder="Ej: Electrónica">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Imagen del producto</label>
                        <input type="file" class="form-control" id="image" name="image" 
                               accept="image/jpeg,image/png,image/jpg">
                        <small class="form-text text-muted">
                            Formatos permitidos: JPG, PNG. Tamaño máximo: 5MB
                        </small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Agregar Producto</button>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>