<?php
// API: Get, create, update, delete tags for user
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$pdo = require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if getting todos for a tag
    if (isset($_GET['action']) && $_GET['action'] === 'get_todos' && isset($_GET['tag_id'])) {
        $tag_id = (int)$_GET['tag_id'];
        
        // Verify tag belongs to user
        $verify = $pdo->prepare('SELECT id FROM tags WHERE id = ? AND user_id = ?');
        $verify->execute([$tag_id, $user_id]);
        if (!$verify->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Get todos with this tag
        $stmt = $pdo->prepare('
            SELECT t.id, t.title, t.description, t.status, t.priority
            FROM todos t
            JOIN todo_tags tt ON t.id = tt.todo_id
            WHERE tt.tag_id = ? AND t.user_id = ?
            ORDER BY t.created_at DESC
        ');
        $stmt->execute([$tag_id, $user_id]);
        $todos = $stmt->fetchAll();
        echo json_encode($todos);
    } else {
        // Get all tags for user
        $stmt = $pdo->prepare('SELECT id, name, color FROM tags WHERE user_id = ? ORDER BY name');
        $stmt->execute([$user_id]);
        $tags = $stmt->fetchAll();
        // Return as array for consistent format
        echo json_encode($tags);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    $action = $_POST['action'] ?? 'create';
    
    if ($action === 'create') {
        // Create new tag
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#6366f1';
        
        if ($name === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tag name required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('INSERT INTO tags (user_id, name, color) VALUES (?, ?, ?)');
            $stmt->execute([$user_id, $name, $color]);
            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'tag_id' => (int)$id, 'id' => (int)$id, 'name' => $name, 'color' => $color]);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tag sudah ada']);
            } else {
                http_response_code(500);
                error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
            }
        }
    } elseif ($action === 'update') {
        // Update tag
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#6366f1';
        
        if ($id <= 0 || $name === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        
        // Verify ownership
        $verify = $pdo->prepare('SELECT id FROM tags WHERE id = ? AND user_id = ?');
        $verify->execute([$id, $user_id]);
        if (!$verify->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('UPDATE tags SET name = ?, color = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$name, $color, $id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Tag updated']);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
        }
    } elseif ($action === 'delete') {
        // Delete tag
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid tag id']);
            exit;
        }
        
        // Verify ownership
        $verify = $pdo->prepare('SELECT id FROM tags WHERE id = ? AND user_id = ?');
        $verify->execute([$id, $user_id]);
        if (!$verify->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            // Delete tag and its associations (cascading delete in DB)
            $stmt = $pdo->prepare('DELETE FROM tags WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Tag deleted']);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
}
