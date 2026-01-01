<?php
require_once __DIR__ . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';

		if ($username === '' || $password === '') {
				$error = 'Isi username dan password.';
		} elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
				$error = 'Invalid CSRF token.';
		} else {
				$pdo = require __DIR__ . '/config/db.php';
				$stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
				$stmt->execute([$username]);
				$user = $stmt->fetch();
				if ($user && password_verify($password, $user['password'])) {
						// sukses login
						$_SESSION['user'] = ['id' => $user['id'], 'username' => $username];
						header('Location: index.php');
						exit;
				} else {
						$error = 'Username atau password salah.';
				}
		}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Login - Todo App</title>
	<link rel="stylesheet" href="./assets/css/style.css">
</head>
<body class="auth-shell">
	<div class="neo-box auth-card">
		<h2 class="title-md mb-6x" style="color: var(--primary);">Login</h2>
		<?php if (!empty($_GET['registered'])): ?>
		<div class="neo-badge neo-badge-accent mb-4 text-sm">✓ Registrasi berhasil. Silakan login.</div>
	<?php endif; ?>
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
			<button class="neo-btn neo-btn-primary full-width py-3 font-bold text-lg">Login</button>
			<a href="register.php" class="neo-btn neo-btn-secondary full-width py-3 font-bold text-center text-lg">Belum punya akun? Register</a>
			</div>
		</form>
	</div>
</body>
</html>
