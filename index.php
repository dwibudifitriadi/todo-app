<?php require_once __DIR__ . '/includes/session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/heading.php'; ?>

<body class="page-body">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="page-container-md">
        <?php if (!empty($_SESSION['user'])): ?>
            <?php
            $pdo = require __DIR__ . '/config/db.php';

            $search = trim($_GET['search'] ?? '');
            $status_filter = $_GET['status'] ?? '';

            $where = ['user_id = ?'];
            $params = [$_SESSION['user']['id']];

            if ($search !== '') {
                $where[] = "(title LIKE ? OR description LIKE ?)";
                $search_param = "%{$search}%";
                $params[] = $search_param;
                $params[] = $search_param;
            }
            if ($status_filter !== '' && in_array($status_filter, ['pending', 'in_progress', 'completed'])) {
                $where[] = "status = ?";
                $params[] = $status_filter;
            }

            $sql = 'SELECT * FROM todos WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $todos = $stmt->fetchAll();
            ?>

            <div class="section-header">
                <?php
                $breadcrumb = [
                    'title' => 'Daftar To Do',
                    'showBack' => false,
                    'useMarginBottom' => false
                ];
                require_once __DIR__ . '/includes/breadcrumb.php';
                ?>
                <a href="create-todo.php" class="neo-btn neo-btn-primary btn-lg">+ Buat To Do</a>
            </div>

            <div class="neo-box card section-margin">
                <form method="get" action="" class="stack-4">
                    <div class="form-row">
                        <div class="flex-fill">
                            <label class="field-label" style="color: var(--primary);">
                                <i class='bx bx-search'></i> Cari Todo
                            </label>
                            <input type="text" name="search" placeholder="Masukan judul atau deskripsi..." value="<?= htmlspecialchars($search) ?>" class="neo-input full-width" />
                        </div>
                        <div class="field-fixed">
                            <label class="field-label" style="color: var(--primary);">
                                <i class='bx bx-filter'></i> Filter Status
                            </label>
                            <select name="status" class="neo-select full-width">
                                <option value="">Semua Status</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>🔄 Sedang Berlangsung</option>
                                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>✓ Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="submit" class="neo-btn neo-btn-primary btn-md btn-with-icon">
                            <i class='bx bx-search-alt'></i> Cari
                        </button>
                        <a href="index.php" class="neo-btn neo-btn-secondary btn-md btn-with-icon">
                            <i class='bx bx-refresh'></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="stack-4">
                <?php if (empty($todos)): ?>
                    <div class="neo-box card text-center font-semibold" style="color: var(--primary);">Belum ada todo. Buat yang pertama!</div>
                <?php else: ?>
                    <?php foreach ($todos as $t): ?>
                        <div class="neo-list-item list-row">
                            <div class="flex-fill">
                                <div class="heading-row">
                                    <div class="title-sm-bold" style="color: var(--primary);"><?= htmlspecialchars($t['title']) ?></div>
                                    <span class="neo-badge text-xs <?php if ($t['status'] == 'completed') echo 'neo-status-completed';
                                                                                            elseif ($t['status'] == 'in_progress') echo 'neo-status-in-progress';
                                                                                            else echo 'neo-status-pending'; ?>"><?= htmlspecialchars($t['status']) ?></span>
                                </div>
                                <div class="text-sm-base" style="color: var(--primary);">Dibuat: <?= htmlspecialchars($t['created_at']) ?></div>
                                <div class="mt-3x text-sm-base" style="color: var(--primary);">
                                    <?php $snippet = mb_substr(trim($t['description'] ?? ''), 0, 120);
                                    echo htmlspecialchars($snippet); ?><?php if (strlen($t['description'] ?? '') > 120) echo '...'; ?>
                                </div>
                            </div>
                            <div class="inline-actions">
                                <a href="pomodoro.php?id=<?= $t['id'] ?>" class="neo-btn neo-btn-secondary btn-icon" title="Pomodoro Timer">
                                    <i class="bx bx-time icon-md"></i>
                                </a>
                                <a href="todo-detail.php?id=<?= $t['id'] ?>" class="neo-btn neo-btn-secondary btn-icon" title="Detail">
                                    <i class="bx bx-show icon-md"></i>
                                </a>
                                <a href="edit-todo.php?id=<?= $t['id'] ?>" class="neo-btn neo-btn-secondary btn-icon" title="Edit">
                                    <i class="bx bx-edit icon-md"></i>
                                </a>
                                <button onclick="deleteConfirm(<?= $t['id'] ?>)" class="neo-btn neo-btn-secondary btn-icon" title="Hapus" style="background: #ef4444; border-color: #991b1b;">
                                    <i class="bx bx-trash icon-md"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="neo-box card narrow-box">
                <h2 class="title-sm mb-2x">Selamat datang</h2>
                <p class="text-muted">Silakan login atau daftar untuk mulai membuat todo.</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
        async function deleteConfirm(id) {
            if (!confirm('Hapus todo ini?')) return;
            const fd = new FormData();
            fd.append('id', id);
            fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
            const res = await fetch('api/delete.php', {
                method: 'POST',
                body: fd
            });
            const json = await res.json();
            if (!json.success) alert(json.message || 'Gagal menghapus');
            else location.reload();
        }
    </script>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>