<?php
/**
 * edit_product.php - Formulario para editar productos
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Editar Producto - Mi Tienda';

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
    'product_get_for_edit',
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
    set_flash('error', 'No tienes permiso para editar este producto');
    redirect('product.php?id=' . $product_id);
}

$errors = [];

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
        
        // Procesar nueva imagen si se subió
        $image_path = $product['image_path'];
        $delete_old_image = false;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $new_image_path = save_image($_FILES['image']);
                if ($new_image_path) {
                    $delete_old_image = true;
                    $image_path = $new_image_path;
                } else {
                    $errors[] = 'Error al subir la imagen. Asegúrate de que sea JPG o PNG y menor a 5MB';
                }
            } else {
                $errors[] = 'Error al subir el archivo';
            }
        }
        
        // Si no hay errores, actualizar producto
        if (empty($errors)) {
            $result = db_execute(
                'product_update',
                'UPDATE products 
                 SET name = $1, description = $2, price = $3, stock = $4, 
                     category = $5, image_path = $6, updated_at = now() 
                 WHERE id = $7 AND user_id = $8',
                [
                    $name,
                    $description,
                    $price,
                    (int)$stock,
                    !empty($category) ? $category : null,
                    $image_path,
                    $product_id,
                    current_user_id()
                ]
            );
            
            if ($result && db_affected_rows($result) > 0) {
                // Eliminar imagen antigua si se subió una nueva
                if ($delete_old_image && $product['image_path']) {
                    delete_image($product['image_path']);
                }
                
                set_flash('success', 'Producto actualizado exitosamente');
                redirect('product.php?id=' . $product_id);
            } else {
                $errors[] = 'Error al actualizar el producto';
                // Eliminar nueva imagen si hubo error
                if ($delete_old_image) {
                    delete_image($image_path);
                }
            }
        }
        
        // Actualizar datos del producto para mostrar en el formulario
        $product['name'] = $name;
        $product['description'] = $description;
        $product['price'] = $price;
        $product['stock'] = $stock;
        $product['category'] = $category;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Editar Producto</h3>
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
                
                <form method="POST" action="edit_product.php?id=<?php echo $product_id; ?>" 
                      enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del producto *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo e($product['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="4"><?php echo e($product['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" value="<?php echo e($product['price']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label">Stock *</label>
                            <input type="number" class="form-control" id="stock" name="stock" 
                                   min="0" value="<?php echo e($product['stock']); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Categoría</label>
                            <input type="text" class="form-control" id="category" name="category" 
                                   value="<?php echo e($product['category']); ?>" 
                                   placeholder="Ej: Electrónica">
                        </div>
                    </div>
                    
                    <?php if (!empty($product['image_path'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Imagen actual</label>
                        <div>
                            <img src="<?php echo e($product['image_path']); ?>" 
                                 alt="Imagen actual" style="max-width: 200px;" class="img-thumbnail">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">
                            <?php echo !empty($product['image_path']) ? 'Cambiar imagen' : 'Subir imagen'; ?>
                        </label>
                        <input type="file" class="form-control" id="image" name="image" 
                               accept="image/jpeg,image/png,image/jpg">
                        <small class="form-text text-muted">
                            Formatos permitidos: JPG, PNG. Tamaño máximo: 5MB
                        </small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="product.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>