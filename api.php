<?php
// api.php
require_once 'includes/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

$db = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// Helper para leer body JSON
function get_json_input() {
    return json_decode(file_get_contents('php://input'), true);
}

// Router básico
try {
    switch ($method) {
        case 'GET':
            if ($action === 'list' || !$id) {
                // Listar
                $result = pg_query($db, "SELECT * FROM products ORDER BY id DESC");
                $data = pg_fetch_all($result);
                echo json_encode(['products' => $data ?: []]);
            } elseif ($id) {
                // Ver uno
                $res = pg_prepare($db, "api_get", "SELECT * FROM products WHERE id = $1");
                $res = pg_execute($db, "api_get", [$id]);
                $data = pg_fetch_assoc($res);
                if ($data) echo json_encode($data);
                else { http_response_code(404); echo json_encode(['error' => 'Not found']); }
            }
            break;

        case 'POST':
            // Crear producto (Body JSON esperado)
            $input = get_json_input();
            if (!$input) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }
            
            // Validaciones mínimas
            if (empty($input['name']) || !isset($input['price'])) {
                http_response_code(422); echo json_encode(['error' => 'Missing fields']); exit;
            }

            $sql = "INSERT INTO products (name, description, price, stock, category) VALUES ($1, $2, $3, $4, $5) RETURNING id";
            $res = pg_prepare($db, "api_ins", $sql);
            $res = pg_execute($db, "api_ins", [
                $input['name'], 
                $input['description'] ?? '', 
                $input['price'], 
                $input['stock'] ?? 0, 
                $input['category'] ?? ''
            ]);

            if ($res) {
                $row = pg_fetch_assoc($res);
                http_response_code(201);
                echo json_encode(['message' => 'Created', 'id' => $row['id']]);
            } else {
                http_response_code(500); echo json_encode(['error' => pg_last_error($db)]);
            }
            break;

        case 'PUT':
            // Actualizar (requiere ID en URL y Body JSON)
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); exit; }
            $input = get_json_input();

            $sql = "UPDATE products SET name=$1, description=$2, price=$3, stock=$4, category=$5, updated_at=NOW() WHERE id=$6";
            $res = pg_prepare($db, "api_upd", $sql);
            $res = pg_execute($db, "api_upd", [
                $input['name'], 
                $input['description'], 
                $input['price'], 
                $input['stock'], 
                $input['category'],
                $id
            ]);
            echo json_encode(['message' => 'Updated']);
            break;

        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); exit; }
            $res = pg_prepare($db, "api_del", "DELETE FROM products WHERE id=$1");
            pg_execute($db, "api_del", [$id]);
            echo json_encode(['message' => 'Deleted']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>