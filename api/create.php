<?php
// API: create todo
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

$data = $_POST;
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$status = $data['status'] ?? 'pending';
$priority = $data['priority'] ?? null;
$energy_level = $data['energy_level'] ?? null;
$tags = isset($data['tags']) ? array_filter(array_map('intval', explode(',', $data['tags']))) : [];

if ($title === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

try {
    $pdo = require __DIR__ . '/../config/db.php';
    $stmt = $pdo->prepare('INSERT INTO todos (user_id, title, description, status, priority, energy_level) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$_SESSION['user']['id'], $title, $description, $status, $priority ?: null, $energy_level ?: null]);
    $id = $pdo->lastInsertId();
    
    // Add tags
    if (!empty($tags)) {
        $tagStmt = $pdo->prepare('INSERT INTO todo_tags (todo_id, tag_id) VALUES (?, ?)');
        foreach ($tags as $tag_id) {
            $tagStmt->execute([$id, $tag_id]);
        }
    }
    
    echo json_encode(['success' => true, 'id' => (int)$id]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
}
