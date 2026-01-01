<?php
require_once __DIR__ . '/includes/session.php';
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$pdo = require __DIR__ . '/config/db.php';
$user_id = $_SESSION['user']['id'];

// Get statistics
$stmt = $pdo->prepare('SELECT status, COUNT(*) as count FROM todos WHERE user_id = ? GROUP BY status');
$stmt->execute([$user_id]);
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}

$pending = $status_counts['pending'] ?? 0;
$in_progress = $status_counts['in_progress'] ?? 0;
$completed = $status_counts['completed'] ?? 0;
$total = $pending + $in_progress + $completed;

// Get todos by date (last 7 days)
$stmt = $pdo->prepare('
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM todos 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
');
$stmt->execute([$user_id]);
$daily_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_data[$date] = 0;
}
while ($row = $stmt->fetch()) {
    $daily_data[$row['date']] = $row['count'];
}

// Get work-sessions statistics
$stmt = $pdo->prepare('
    SELECT 
        COUNT(*) as total_sessions,
        SUM(duration_minutes) as total_focus_time,
        AVG(duration_minutes) as avg_duration
    FROM work_sessions
    WHERE user_id = ?
');
$stmt->execute([$user_id]);
$session_stats = $stmt->fetch();
$total_sessions = $session_stats['total_sessions'] ?? 0;
$total_focus_time = (int)($session_stats['total_focus_time'] ?? 0);
$avg_duration = round($session_stats['avg_duration'] ?? 0, 1);

// Get daily focus time (last 7 days)
$stmt = $pdo->prepare('
    SELECT DATE(created_at) as date, SUM(duration_minutes) as focus_time
    FROM work_sessions
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
');
$stmt->execute([$user_id]);
$daily_focus = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_focus[$date] = 0;
}
while ($row = $stmt->fetch()) {
    $daily_focus[$row['date']] = (int)$row['focus_time'];
}

// Get top todos by session count
$stmt = $pdo->prepare('
    SELECT t.title, COUNT(ws.id) as session_count, SUM(ws.duration_minutes) as total_time
    FROM work_sessions ws
    JOIN todos t ON ws.todo_id = t.id
    WHERE ws.user_id = ?
    GROUP BY t.id
    ORDER BY session_count DESC
    LIMIT 5
');
$stmt->execute([$user_id]);
$top_todos = $stmt->fetchAll();
$top_todos_labels = [];
$top_todos_sessions = [];
$top_todos_time = [];
foreach ($top_todos as $todo) {
    $top_todos_labels[] = $todo['title'];
    $top_todos_sessions[] = $todo['session_count'];
    $top_todos_time[] = $todo['total_time'];
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/heading.php'; ?>
<head>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
</head>
<body class="page-body">
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main class="page-container">
  <?php
  $breadcrumb = [
      'title' => 'Dashboard',
      'backUrl' => 'index.php',
  ];
  require_once __DIR__ . '/includes/breadcrumb.php';
  ?>

  <!-- Statistics Cards -->
  <div class="grid-stats">
    <div class="neo-box card">
      <div class="text-muted-sm">Total Todo</div>
      <div class="stat-number"><?= $total ?></div>
    </div>
    <div class="neo-box card">
      <div class="text-muted-sm">Pending</div>
      <div class="stat-number-soft"><?= $pending ?></div>
    </div>
    <div class="neo-box card">
      <div class="text-muted-sm">Sedang Berlangsung</div>
      <div class="stat-number" style="color: #eab308;">
        <?= $in_progress ?>
      </div>
    </div>
    <div class="neo-box card">
      <div class="text-muted-sm">Selesai</div>
      <div class="stat-number" style="color: #22c55e;">
        <?= $completed ?>
      </div>
    </div>
  </div>

  <!-- Work Sessions Statistics Cards -->
  <h2 class="title-md mb-6x mt-8x">Statistik Work Sessions</h2>
  <div class="grid-stats">
    <div class="neo-box card">
      <div class="text-muted-sm">Total Sesi Fokus</div>
      <div class="stat-number" style="color: #3b82f6;">
        <?= $total_sessions ?>
      </div>
    </div>
    <div class="neo-box card">
      <div class="text-muted-sm">Total Waktu Fokus</div>
      <div class="stat-number" style="color: #a855f7;">
        <?= $total_focus_time ?> min
      </div>
      <div class="text-tiny text-muted mt-1x"><?= round($total_focus_time / 60, 1) ?> jam</div>
    </div>
    <div class="neo-box card">
      <div class="text-muted-sm">Rata-rata Durasi</div>
      <div class="stat-number" style="color: #6366f1;">
        <?= $avg_duration ?> min
      </div>
    </div>
    <div class="neo-box card">
      <div class="text-muted-sm">Produktivitas Hari Ini</div>
      <div class="stat-number" style="color: #22c55e;">
        <?= $daily_focus[date('Y-m-d')] ?? 0 ?> min
      </div>
    </div>
  </div>
  <div class="chart-layout">
    <!-- Pie Chart -->
    <div class="neo-box card chart-panel">
      <h2 class="title-sm mb-4x">Distribusi Status</h2>
      <canvas id="statusChart"></canvas>
    </div>

    <!-- Bar Chart -->
    <div class="neo-box card chart-panel">
      <h2 class="title-sm mb-4x">Todo Dibuat (7 Hari Terakhir)</h2>
      <canvas id="dailyChart"></canvas>
    </div>
  </div>

  <!-- Work Sessions Charts -->
  <div class="grid-double">
    <!-- Daily Focus Time Chart -->
    <div class="neo-box card">
      <h2 class="title-sm mb-4x">Waktu Fokus (7 Hari Terakhir)</h2>
      <canvas id="focusTimeChart"></canvas>
    </div>

    <!-- Top Todos by Session Count -->
    <div class="neo-box card">
      <h2 class="title-sm mb-4x">Top 5 Todo Paling Difokuskan</h2>
      <canvas id="topTodosChart"></canvas>
    </div>
  </div>
</main>

<script>
// Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
  type: 'doughnut',
  data: {
    labels: ['Pending', 'Sedang Berlangsung', 'Selesai'],
    datasets: [{
      data: [<?= (int)$pending ?>, <?= (int)$in_progress ?>, <?= (int)$completed ?>],
      backgroundColor: ['#d1d5db', '#fbbf24', '#10b981'],
      borderColor: '#ffffff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// Bar Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($daily_data)) ?>,
    datasets: [{
      label: 'Todo Dibuat',
      data: <?= json_encode(array_values($daily_data)) ?>,
      backgroundColor: '#6366f1',
      borderColor: '#4f46e5',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, max: Math.max(...<?= json_encode(array_values($daily_data)) ?>, 5) }
    },
    plugins: {
      legend: { display: false }
    }
  }
});

// Focus Time Line Chart
const focusTimeCtx = document.getElementById('focusTimeChart').getContext('2d');
new Chart(focusTimeCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_keys($daily_focus)) ?>,
    datasets: [{
      label: 'Waktu Fokus (menit)',
      data: <?= json_encode(array_values($daily_focus)) ?>,
      borderColor: '#8b5cf6',
      backgroundColor: 'rgba(139, 92, 246, 0.1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#8b5cf6',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 5
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    },
    plugins: {
      legend: { display: true }
    }
  }
});

// Top Todos Bar Chart
const topTodosCtx = document.getElementById('topTodosChart').getContext('2d');
new Chart(topTodosCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($top_todos_labels) ?>,
    datasets: [
      {
        label: 'Jumlah Sesi',
        data: <?= json_encode($top_todos_sessions) ?>,
        backgroundColor: '#06b6d4',
        borderColor: '#0891b2',
        borderWidth: 1
      },
      {
        label: 'Total Waktu (menit)',
        data: <?= json_encode($top_todos_time) ?>,
        backgroundColor: '#ec4899',
        borderColor: '#be185d',
        borderWidth: 1
      }
    ]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    },
    plugins: {
      legend: { position: 'top' }
    }
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
