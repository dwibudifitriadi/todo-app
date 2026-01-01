<?php
// API: Manage todo tags association
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

$user_id = $_SESSION['user']['id'];
$pdo = require __DIR__ . '/../config/db.php';

$todo_id = isset($_POST['todo_id']) ? (int)$_POST['todo_id'] : 0;
$action = $_POST['action'] ?? '';

if ($todo_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid todo_id']);
    exit;
}

// Verify user owns this todo
$verify = $pdo->prepare('SELECT id FROM todos WHERE id = ? AND user_id = ?');
$verify->execute([$todo_id, $user_id]);
if (!$verify->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if ($action === 'add') {
    $tag_id = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0;
    if ($tag_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid tag_id']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare('INSERT INTO todo_tags (todo_id, tag_id) VALUES (?, ?)');
        $stmt->execute([$todo_id, $tag_id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate')) {
            echo json_encode(['success' => false, 'message' => 'Tag sudah ditambahkan']);
        } else {
            http_response_code(500);
            error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
        }
    }
} elseif ($action === 'remove') {
    $tag_id = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0;
    if ($tag_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid tag_id']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare('DELETE FROM todo_tags WHERE todo_id = ? AND tag_id = ?');
        $stmt->execute([$todo_id, $tag_id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
    }
} elseif ($action === 'get') {
    try {
        $stmt = $pdo->prepare('
            SELECT t.id, t.name, t.color
            FROM tags t
            JOIN todo_tags tt ON t.id = tt.tag_id
            WHERE tt.todo_id = ?
        ');
        $stmt->execute([$todo_id]);
        $tags = $stmt->fetchAll();
        echo json_encode(['success' => true, 'tags' => $tags]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
