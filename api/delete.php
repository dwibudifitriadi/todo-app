<?php
// API: delete todo
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

try {
    $pdo = require __DIR__ . '/../config/db.php';
    $stmt = $pdo->prepare('DELETE FROM todos WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user']['id']]);
    echo json_encode(['success' => true, 'affected' => $stmt->rowCount()]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
}
