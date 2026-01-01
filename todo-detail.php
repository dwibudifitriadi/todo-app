<?php
require_once __DIR__ . '/includes/session.php';
if (empty($_GET['id'])) {
    die('ID required');
}
$id = (int)$_GET['id'];
$pdo = require __DIR__ . '/config/db.php';
$stmt = $pdo->prepare('SELECT * FROM todos WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user']['id'] ?? 0]);
$todo = $stmt->fetch();
if (!$todo) {
    die('Todo tidak ditemukan atau Anda tidak memiliki akses.');
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/heading.php'; ?>
<body class="page-body">
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<main class="page-container-sm">  <?php
  $breadcrumb = [
      'title' => 'Detail Todo',
      'backUrl' => 'index.php',
  ];
  require_once __DIR__ . '/includes/breadcrumb.php';
  ?>  <div class="neo-box card-lg">
    <div class="flex-between mb-6x">
      <div>
        <h1 class="title-xl" style="color: var(--primary);"><?=htmlspecialchars($todo['title'])?></h1>
        <div class="text-sm-base mt-2x" style="color: var(--primary);">Dibuat: <?=htmlspecialchars($todo['created_at'])?></div>
        <div class="mt-4x">
          <span class="neo-badge <?php if($todo['status']=='completed') echo 'neo-status-completed'; elseif($todo['status']=='in_progress') echo 'neo-status-in-progress'; else echo 'neo-status-pending'; ?>"><?=htmlspecialchars($todo['status'])?></span>
        </div>
      </div>
    </div>

    <div class="neo-divider"></div>

    <div class="mt-6x text-lg-base" style="color: var(--primary);"><?=nl2br(htmlspecialchars($todo['description']))?></div>
  </div>
</main>

<script>
document.getElementById('deleteBtn').addEventListener('click', async function(){
  if (!confirm('Hapus todo ini?')) return;
  const fd = new FormData(); 
  fd.append('id', <?= $todo['id'] ?>);
  fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
  const res = await fetch('api/delete.php', { method: 'POST', body: fd });
  const json = await res.json();
  if (!json.success) alert(json.message || 'Gagal menghapus');
  else location.href = 'index.php';
});
</script>

</body>
</html>
