<?php
// API: get todo by id (JSON)
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Waduh tidak dapat']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

try {
    $pdo = require __DIR__ . '/../config/db.php';
    $stmt = $pdo->prepare('SELECT id, user_id, title, description, status, created_at FROM todos WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user']['id']]);
    $todo = $stmt->fetch();
    if (!$todo) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found']);
        exit;
    }
    echo json_encode(['success' => true, 'todo' => $todo]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
}
