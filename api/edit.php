<?php
// API: edit todo (update status or title)
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
$status = $_POST['status'] ?? null;
$title = isset($_POST['title']) ? trim($_POST['title']) : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : null;
$priority = $_POST['priority'] ?? null;
$energy_level = $_POST['energy_level'] ?? null;
$tags = isset($_POST['tags']) ? $_POST['tags'] : [];

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

try {
    $pdo = require __DIR__ . '/../config/db.php';

    // Verify ownership
    $verify = $pdo->prepare('SELECT user_id FROM todos WHERE id = ?');
    $verify->execute([$id]);
    $todo = $verify->fetch();
    if (!$todo || $todo['user_id'] != $_SESSION['user']['id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $fields = [];
    $params = [];
    if ($status !== null) {
        $fields[] = '`status` = ?';
        $params[] = $status;
    }
    if ($title !== null) {
        $fields[] = '`title` = ?';
        $params[] = $title;
    }
    if ($description !== null) {
        $fields[] = '`description` = ?';
        $params[] = $description;
    }
    if ($priority !== null) {
        $fields[] = '`priority` = ?';
        $params[] = $priority;
    }
    if ($energy_level !== null) {
        $fields[] = '`energy_level` = ?';
        $params[] = $energy_level;
    }
    if (empty($fields) && empty($tags)) {
        echo json_encode(['success' => false, 'message' => 'Nothing to update']);
        exit;
    }

    // Update todo fields
    if (!empty($fields)) {
        $params[] = $id;
        $params[] = $_SESSION['user']['id'];
        $sql = 'UPDATE todos SET ' . implode(', ', $fields) . ' WHERE id = ? AND user_id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    // Update tags
    if (!empty($tags)) {
        // Delete existing associations
        $del = $pdo->prepare('DELETE FROM todo_tags WHERE todo_id = ?');
        $del->execute([$id]);
        
        // Add new associations
        if (!empty($tags)) {
            $ins = $pdo->prepare('INSERT INTO todo_tags (todo_id, tag_id) VALUES (?, ?)');
            foreach ($tags as $tag_id) {
                $ins->execute([$id, (int)$tag_id]);
            }
        }
    }

    echo json_encode(['success' => true, 'affected' => 1]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
}
