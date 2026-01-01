<?php
require_once __DIR__ . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';

		if (strlen($username) < 3 || strlen($password) < 6) {
				$error = 'Username minimal 3 karakter dan password minimal 6 karakter.';
		} elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
				$error = 'Invalid CSRF token.';
		} else {
				$pdo = require __DIR__ . '/config/db.php';

				// cek apakah username sudah ada
				$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
				$stmt->execute([$username]);
				if ($stmt->fetch()) {
						$error = 'Username sudah dipakai.';
				} else {
						$hash = password_hash($password, PASSWORD_DEFAULT);
						$ins = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
						$ins->execute([$username, $hash]);
						header('Location: login.php?registered=1');
						exit;
				}
		}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Register - Todo App</title>
	<link rel="stylesheet" href="./assets/css/style.css">
	<style>/* small fallback */</style>
</head>
<body class="auth-shell">
	<div class="neo-box auth-card">
		<h2 class="title-md mb-6x" style="color: var(--primary);">Register</h2>
		<?php if (!empty($error)): ?>
		<div class="neo-badge mb-4 text-sm" style="background: #fee2e2; border-color: #dc2626; color: #dc2626;">✗ <?=htmlspecialchars($error)?></div>
		<?php endif; ?>
		<form method="post" action="">
		<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
		<label class="form-label mb-4x" style="color: var(--primary);"><span class="font-semibold">Username</span>
			<input name="username" required class="neo-input full-width mt-2x" value="<?=htmlspecialchars($username ?? '')?>">
		</label>
		<label class="form-label mb-6x" style="color: var(--primary);"><span class="font-semibold">Password</span>
				<input name="password" type="password" required class="neo-input full-width mt-2x">
			</label>
		<div class="btn-stack">
			<button class="neo-btn neo-btn-primary full-width py-3 font-bold text-lg">Register</button>
			<a href="login.php" class="neo-btn neo-btn-secondary full-width py-3 font-bold text-center text-lg">Sudah punya akun? Login</a>
			</div>
		</form>
	</div>
</body>
</html>