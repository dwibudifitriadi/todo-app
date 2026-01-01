<?php
// API: Work session management (Pomodoro tracking)
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$pdo = require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    $action = $_POST['action'] ?? '';
    $todo_id = isset($_POST['todo_id']) ? (int)$_POST['todo_id'] : 0;
    
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
    
    if ($action === 'start') {
        // Start new session
        $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 25;
        try {
            $stmt = $pdo->prepare('INSERT INTO work_sessions (todo_id, user_id, duration_minutes, completed) VALUES (?, ?, ?, 0)');
            $stmt->execute([$todo_id, $user_id, $duration]);
            $id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'session_id' => (int)$id]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
        }
    } elseif ($action === 'complete') {
        // Complete session
        $session_id = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;
        $actual_duration = isset($_POST['actual_duration']) ? (int)$_POST['actual_duration'] : 0;
        
        if ($session_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid session_id']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('UPDATE work_sessions SET completed = 1, actual_duration_seconds = ? WHERE id = ? AND todo_id = ?');
            $stmt->execute([$actual_duration, $session_id, $todo_id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
        }
    } elseif ($action === 'get_stats') {
        // Get work session stats for a todo
        try {
            $stmt = $pdo->prepare('
                SELECT COUNT(*) as total_sessions, SUM(actual_duration_seconds) as total_seconds
                FROM work_sessions
                WHERE todo_id = ? AND user_id = ? AND completed = 1
            ');
            $stmt->execute([$todo_id, $user_id]);
            $stats = $stmt->fetch();
            $total_minutes = $stats['total_seconds'] ? round($stats['total_seconds'] / 60) : 0;
            echo json_encode([
                'success' => true,
                'total_sessions' => (int)$stats['total_sessions'],
                'total_minutes' => $total_minutes
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get work stats for user
    try {
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as total_sessions, SUM(actual_duration_seconds) as total_seconds
            FROM work_sessions
            WHERE user_id = ? AND completed = 1
        ');
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch();
        $total_minutes = $stats['total_seconds'] ? round($stats['total_seconds'] / 60) : 0;
        echo json_encode([
            'success' => true,
            'total_sessions' => (int)$stats['total_sessions'],
            'total_minutes' => $total_minutes
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
