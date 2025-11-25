-- Crear esquema si no existe (por defecto public)
CREATE SCHEMA IF NOT EXISTS public;

-- Tabla de productos
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    stock INTEGER NOT NULL DEFAULT 0,
    category VARCHAR(100),
    image_path VARCHAR(512),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE
);

-- Índices
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category);
CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);

-- Datos de prueba
INSERT INTO products (name, description, price, stock, category, image_path) VALUES
('Camiseta Retro', 'Camiseta de algodón estilo 80s', 19.99, 100, 'Ropa', NULL),
('Taza de Café', 'Taza cerámica color negro', 5.50, 50, 'Hogar', NULL),
('Auriculares', 'Auriculares con cancelación de ruido', 59.90, 25, 'Electrónica', NULL);