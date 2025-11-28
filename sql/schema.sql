-- ============================================
-- SCHEMA SQL PARA TIENDA WEB
-- PostgreSQL 14+
-- ============================================

-- Crear la base de datos (ejecutar como superusuario)
-- CREATE DATABASE tienda_db ENCODING 'UTF8';

-- Conectarse a la base de datos
-- \c tienda_db

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Índice en email para búsquedas rápidas
CREATE INDEX idx_users_email ON users(email);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10,2) NOT NULL DEFAULT 0.00 CHECK (price >= 0),
    stock INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
    category VARCHAR(100),
    image_path VARCHAR(512),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE
);

-- Índices para mejorar búsquedas
CREATE INDEX idx_products_user_id ON products(user_id);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_name ON products(name);

-- ============================================
-- DATOS DE PRUEBA
-- ============================================

-- Usuarios de ejemplo
-- Contraseña para ambos: "password123"
-- Hash generado con: password_hash('password123', PASSWORD_DEFAULT)
-- Nota: Estos hashes son de ejemplo, en producción genera nuevos con PHP

INSERT INTO users (name, email, password_hash) VALUES 
('Juan Pérez', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('María García', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Productos de ejemplo
INSERT INTO products (user_id, name, description, price, stock, category) VALUES 
(1, 'Laptop HP Pavilion', 'Laptop HP Pavilion 15, Intel Core i5, 8GB RAM, 256GB SSD. Ideal para trabajo y estudio.', 899.99, 5, 'Electrónica'),
(1, 'Mouse Inalámbrico Logitech', 'Mouse inalámbrico ergonómico Logitech M330, batería de larga duración.', 24.99, 50, 'Accesorios'),
(2, 'Teclado Mecánico RGB', 'Teclado mecánico gaming con iluminación RGB personalizable, switches azules.', 79.99, 15, 'Accesorios'),
(2, 'Monitor Samsung 24"', 'Monitor LED Full HD 24 pulgadas, 75Hz, ideal para gaming y productividad.', 179.99, 8, 'Electrónica'),
(1, 'Auriculares Bluetooth Sony', 'Auriculares inalámbricos con cancelación de ruido, 30 horas de batería.', 149.99, 12, 'Audio');

-- ============================================
-- COMANDOS PARA GENERAR HASH DE CONTRASEÑA
-- ============================================
-- En PHP, ejecuta esto para generar un hash:
-- <?php echo password_hash('tu_contraseña', PASSWORD_DEFAULT); ?>
-- O desde línea de comandos:
-- php -r "echo password_hash('password123', PASSWORD_DEFAULT);"