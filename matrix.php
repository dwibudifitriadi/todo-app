<?php
require_once __DIR__ . '/includes/session.php';
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$pdo = require __DIR__ . '/config/db.php';
$user_id = $_SESSION['user']['id'];

// Get todos grouped by priority (Eisenhower quadrants)
$quadrants = [
    'high-urgent' => ['label' => 'Penting & Mendesak', 'color' => 'red', 'todos' => []],
    'high-noturgent' => ['label' => 'Penting, Tidak Mendesak', 'color' => 'orange', 'todos' => []],
    'low-urgent' => ['label' => 'Tidak Penting, Mendesak', 'color' => 'yellow', 'todos' => []],
    'low-noturgent' => ['label' => 'Tidak Penting, Tidak Mendesak', 'color' => 'blue', 'todos' => []]
];

$stmt = $pdo->prepare('
    SELECT id, title, status, priority 
    FROM todos 
    WHERE user_id = ? AND status != "completed"
    ORDER BY priority, created_at DESC
');
$stmt->execute([$user_id]);
$todos = $stmt->fetchAll();

foreach ($todos as $todo) {
    $quad = $todo['priority'] ?? null;
    if (isset($quadrants[$quad])) {
        $quadrants[$quad]['todos'][] = $todo;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/heading.php'; ?>

<body class="min-h-screen" style="background-color: var(--soft-blue)">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <?php
            $breadcrumb = [
                'title' => 'Eisenhower Matrix',
                'useMarginBottom' => false
            ];
            require_once __DIR__ . '/includes/breadcrumb.php';
            ?>
            <p class="mt-4 text-lg font-semibold" style="color: var(--primary);">Prioritas tugas berdasarkan urgensi dan kepentingan</p>
        </div>

        <!-- Matrix Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Q1: High-Urgent (Red) -->
            <div class="neo-box p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-6 h-6 rounded-full" style="background: #dc2626;"></div>
                    <h2 class="text-2xl font-bold" style="color: var(--primary);">Penting & Mendesak</h2>
                </div>
                <p class="text-base font-semibold mb-6 flex items-center gap-2" style="color: var(--primary);">
                    <i class='bx bx-fire'></i> Lakukan segera - Prioritas tertinggi
                </p>
                <div class="space-y-3">
                    <?php if (empty($quadrants['high-urgent']['todos'])): ?>
                        <div class="neo-badge text-center py-3">
                            <i class='bx bx-box'></i> Tidak ada tugas
                        </div>
                    <?php else: ?>
                        <?php foreach ($quadrants['high-urgent']['todos'] as $t): ?>
                            <div class="neo-list-item">
                                <a href="todo-detail.php?id=<?= $t['id'] ?>" class="font-bold text-lg hover:underline flex items-center gap-2" style="color: var(--primary);">
                                    <i class='bx bx-circle-half'></i> <?= htmlspecialchars($t['title']) ?>
                                </a>
                                <div class="text-sm mt-2 neo-badge <?php echo $t['status'] === 'completed' ? 'neo-status-completed' : 'neo-status-pending'; ?>">
                                    <i class='bx bx-check'></i> Status: <?= htmlspecialchars($t['status']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Q2: High-Not Urgent (Orange) -->
            <div class="neo-box p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-6 h-6 rounded-full" style="background: #ea580c;"></div>
                    <h2 class="text-2xl font-bold" style="color: var(--primary);">Penting, Tidak Mendesak</h2>
                </div>
                <p class="text-base font-semibold mb-6 flex items-center gap-2" style="color: var(--primary);">
                    <i class='bx bx-calendar-event'></i> Jadwalkan - Investasi jangka panjang
                </p>
                <div class="space-y-3">
                    <?php if (empty($quadrants['high-noturgent']['todos'])): ?>
                        <div class="neo-badge text-center py-3">
                            <i class='bx bx-box'></i> Tidak ada tugas
                        </div>
                    <?php else: ?>
                        <?php foreach ($quadrants['high-noturgent']['todos'] as $t): ?>
                            <div class="neo-list-item">
                                <a href="todo-detail.php?id=<?= $t['id'] ?>" class="font-bold text-lg hover:underline flex items-center gap-2" style="color: var(--primary);">
                                    <i class='bx bx-circle-half'></i> <?= htmlspecialchars($t['title']) ?>
                                </a>
                                <div class="text-sm mt-2 neo-badge <?php echo $t['status'] === 'completed' ? 'neo-status-completed' : 'neo-status-pending'; ?>">
                                    <i class='bx bx-check'></i> Status: <?= htmlspecialchars($t['status']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Q3: Low-Urgent (Yellow) -->
            <div class="neo-box p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-6 h-6 rounded-full" style="background: #eab308;"></div>
                    <h2 class="text-2xl font-bold" style="color: var(--primary);">Tidak Penting, Mendesak</h2>
                </div>
                <p class="text-base font-semibold mb-6 flex items-center gap-2" style="color: var(--primary);">
                    <i class='bx bx-share-alt'></i> Delegasikan - Jangan sia-siakan waktu
                </p>
                <div class="space-y-3">
                    <?php if (empty($quadrants['low-urgent']['todos'])): ?>
                        <div class="neo-badge text-center py-3">
                            <i class='bx bx-box'></i> Tidak ada tugas
                        </div>
                    <?php else: ?>
                        <?php foreach ($quadrants['low-urgent']['todos'] as $t): ?>
                            <div class="neo-list-item">
                                <a href="todo-detail.php?id=<?= $t['id'] ?>" class="font-bold text-lg hover:underline flex items-center gap-2" style="color: var(--primary);">
                                    <i class='bx bx-circle-half'></i> <?= htmlspecialchars($t['title']) ?>
                                </a>
                                <div class="text-sm mt-2 neo-badge <?php echo $t['status'] === 'completed' ? 'neo-status-completed' : 'neo-status-pending'; ?>">
                                    <i class='bx bx-check'></i> Status: <?= htmlspecialchars($t['status']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Q4: Low-Not Urgent (Blue) -->
            <div class="neo-box p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-6 h-6 rounded-full" style="background: #3b82f6;"></div>
                    <h2 class="text-2xl font-bold" style="color: var(--primary);">Tidak Penting, Tidak Mendesak</h2>
                </div>
                <p class="text-base font-semibold mb-6 flex items-center gap-2" style="color: var(--primary);">
                    <i class='bx bx-trash'></i> Hapus - Pemborosan waktu
                </p>
                <div class="space-y-3">
                    <?php if (empty($quadrants['low-noturgent']['todos'])): ?>
                        <div class="neo-badge text-center py-3">
                            <i class='bx bx-box'></i> Tidak ada tugas
                        </div>
                    <?php else: ?>
                        <?php foreach ($quadrants['low-noturgent']['todos'] as $t): ?>
                            <div class="neo-list-item">
                                <a href="todo-detail.php?id=<?= $t['id'] ?>" class="font-bold text-lg hover:underline flex items-center gap-2" style="color: var(--primary);">
                                    <i class='bx bx-circle-half'></i> <?= htmlspecialchars($t['title']) ?>
                                </a>
                                <div class="text-sm mt-2 neo-badge <?php echo $t['status'] === 'completed' ? 'neo-status-completed' : 'neo-status-pending'; ?>">
                                    <i class='bx bx-check'></i> Status: <?= htmlspecialchars($t['status']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="neo-box p-8 mt-12">
            <h3 class="text-2xl font-bold mb-6" style="color: var(--primary);">📊 Ringkasan</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="neo-card text-center">
                    <div class="text-4xl font-bold" style="color: #dc2626;"><?= count($quadrants['high-urgent']['todos']) ?></div>
                    <div class="text-sm font-semibold mt-3" style="color: var(--primary);">Penting & Mendesak</div>
                </div>
                <div class="neo-card text-center">
                    <div class="text-4xl font-bold" style="color: #ea580c;"><?= count($quadrants['high-noturgent']['todos']) ?></div>
                    <div class="text-sm font-semibold mt-3" style="color: var(--primary);">Penting, Tidak Mendesak</div>
                </div>
                <div class="neo-card text-center">
                    <div class="text-4xl font-bold" style="color: #eab308;"><?= count($quadrants['low-urgent']['todos']) ?></div>
                    <div class="text-sm font-semibold mt-3" style="color: var(--primary);">Tidak Penting, Mendesak</div>
                </div>
                <div class="neo-card text-center">
                    <div class="text-4xl font-bold" style="color: #3b82f6;"><?= count($quadrants['low-noturgent']['todos']) ?></div>
                    <div class="text-sm font-semibold mt-3" style="color: var(--primary);">Tidak Penting, Tidak Mendesak</div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>