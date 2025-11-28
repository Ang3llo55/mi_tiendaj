# Tienda Web PHP + PostgreSQL

Proyecto completo de tienda web desarrollado con PHP nativo (sin frameworks) y PostgreSQL.

## 📋 Requisitos

- Apache 2.4+
- PHP 8.x
- PostgreSQL 14+
- Extensión PHP: `php-pgsql`

## 🚀 Instalación

### 1. Instalar dependencias

#### Ubuntu/Debian:
```bash
sudo apt update
sudo apt install apache2 php php-pgsql postgresql postgresql-contrib
```

#### CentOS/RHEL:
```bash
sudo yum install httpd php php-pgsql postgresql-server postgresql-contrib
```

### 2. Configurar PostgreSQL

```bash
# Cambiar al usuario postgres
sudo -u postgres psql

# Crear base de datos y usuario
CREATE DATABASE tienda_db ENCODING 'UTF8';
CREATE USER tienda_user WITH PASSWORD 'tienda_password';
GRANT ALL PRIVILEGES ON DATABASE tienda_db TO tienda_user;
\q
```

### 3. Importar el esquema

```bash
# Importar schema.sql
sudo -u postgres psql tienda_db < /var/www/html/mi_tienda/sql/schema.sql

# O desde psql:
sudo -u postgres psql tienda_db
\i /var/www/html/mi_tienda/sql/schema.sql
\q
```

### 4. Configurar el proyecto

```bash
# Copiar archivos al directorio web
sudo cp -r mi_tienda /var/www/html/

# Crear directorio de uploads
sudo mkdir -p /var/www/html/mi_tienda/uploads

# Ajustar permisos
sudo chown -R www-data:www-data /var/www/html/mi_tienda
sudo chmod -R 755 /var/www/html/mi_tienda
sudo chmod -R 775 /var/www/html/mi_tienda/uploads
```

### 5. Configurar credenciales de base de datos

Editar el archivo `/var/www/html/mi_tienda/includes/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'tienda_db');
define('DB_USER', 'tienda_user');
define('DB_PASS', 'tienda_password');
```

### 6. Configurar VirtualHost de Apache (Opcional)

Crear archivo `/etc/apache2/sites-available/tienda.conf`:

```apache
<VirtualHost *:80>
    ServerName tienda.local
    DocumentRoot /var/www/html/mi_tienda

    <Directory /var/www/html/mi_tienda>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tienda_error.log
    CustomLog ${APACHE_LOG_DIR}/tienda_access.log combined
</VirtualHost>
```

Activar el sitio:
```bash
sudo a2ensite tienda.conf
sudo systemctl reload apache2
```

### 7. Habilitar mod_rewrite (si es necesario)

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 🔐 Usuarios de Prueba

El archivo `schema.sql` incluye 2 usuarios de ejemplo:

- **Email:** `juan@example.com`  
  **Contraseña:** `password123`

- **Email:** `maria@example.com`  
  **Contraseña:** `password123`

## 📁 Estructura del Proyecto

```
mi_tienda/
├── index.php              # Listado de productos
├── product.php            # Detalle de producto
├── add_product.php        # Agregar producto
├── edit_product.php       # Editar producto
├── delete_product.php     # Eliminar producto
├── register.php           # Registro de usuarios
├── login.php              # Inicio de sesión
├── logout.php             # Cerrar sesión
├── api.php                # API REST JSON
├── includes/
│   ├── db.php            # Conexión PostgreSQL
│   ├── functions.php     # Funciones auxiliares
│   ├── auth.php          # Autenticación
│   ├── header.php        # Encabezado HTML
│   └── footer.php        # Pie de página HTML
├── sql/
│   └── schema.sql        # Esquema de base de datos
├── assets/
│   └── css/
│       └── style.css     # Estilos CSS
└── uploads/              # Imágenes de productos
    └── .htaccess         # Seguridad
```

## 🔌 API REST

La API está disponible en `/mi_tienda/api.php` y soporta los siguientes endpoints:

### Endpoints Públicos

**Listar productos:**
```bash
curl http://localhost/mi_tienda/api.php?action=list
```

**Obtener producto:**
```bash
curl http://localhost/mi_tienda/api.php?action=get&id=1
```

**Registrar usuario:**
```bash
curl -X POST http://localhost/mi_tienda/api.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'
```

**Iniciar sesión:**
```bash
curl -X POST http://localhost/mi_tienda/api.php?action=login \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"email":"juan@example.com","password":"password123"}'
```

### Endpoints Protegidos (requieren autenticación)

**Crear producto:**
```bash
curl -X POST http://localhost/mi_tienda/api.php?action=create \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"name":"Nuevo Producto","description":"Descripción","price":99.99,"stock":10,"category":"Test"}'
```

**Actualizar producto:**
```bash
curl -X PUT http://localhost/mi_tienda/api.php?action=update&id=1 \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"name":"Producto Actualizado","description":"Nueva descripción","price":149.99,"stock":5,"category":"Actualizado"}'
```

**Eliminar producto:**
```bash
curl -X DELETE http://localhost/mi_tienda/api.php?action=delete&id=1 \
  -b cookies.txt
```

## 🔒 Seguridad Implementada

- ✅ Prepared statements (pg_prepare/pg_execute) para prevenir SQL Injection
- ✅ Tokens CSRF en todos los formularios
- ✅ Validación de inputs en servidor
- ✅ Escapado de salidas HTML (htmlspecialchars)
- ✅ Hash de contraseñas con password_hash()
- ✅ Verificación de propiedad para editar/eliminar
- ✅ Validación de tipos de archivo y tamaño
- ✅ .htaccess para prevenir ejecución de scripts en uploads
- ✅ Sesiones seguras con httponly

## 🛠️ Troubleshooting

### Error: "could not connect to server"
Verifica que PostgreSQL esté corriendo:
```bash
sudo systemctl status postgresql
sudo systemctl start postgresql
```

### Error: "pg_connect() function not found"
Instala la extensión php-pgsql:
```bash
sudo apt install php-pgsql
sudo systemctl restart apache2
```

### Error de permisos en uploads
```bash
sudo chown -R www-data:www-data /var/www/html/mi_tienda/uploads
sudo chmod -R 775 /var/www/html/mi_tienda/uploads
```

### Ver logs de errores
```bash
# Apache
tail -f /var/log/apache2/error.log

# PostgreSQL
tail -f /var/log/postgresql/postgresql-14-main.log
```

## 📝 Generar Hash de Contraseña

Para generar un hash de contraseña para usuarios:

```bash
php -r "echo password_hash('tu_contraseña', PASSWORD_DEFAULT);"
```

## 🎯 Características

- ✅ Registro y autenticación de usuarios
- ✅ CRUD completo de productos
- ✅ Control de propiedad (solo el dueño puede editar/eliminar)
- ✅ Subida de imágenes
- ✅ Búsqueda y filtrado por categoría
- ✅ API REST JSON completa
- ✅ Responsive design con Bootstrap
- ✅ Mensajes flash para feedback
- ✅ Validaciones de servidor

## 📄 Licencia

Este proyecto es de código abierto y está disponible para uso educativo.