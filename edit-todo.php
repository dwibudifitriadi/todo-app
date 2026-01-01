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
<main class="page-container-sm">
  <?php
  $breadcrumb = [
      'title' => 'Edit Todo',
      'backUrl' => 'index.php',
  ];
  require_once __DIR__ . '/includes/breadcrumb.php';
  ?>
  <div class="neo-box card-lg">
    <form id="editForm">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <!-- Full width fields -->
      <label class="form-label mb-4x" style="color: var(--primary);"><span class="font-semibold">Judul</span>
        <input name="title" id="title" required class="neo-input full-width mt-2x" value="<?=htmlspecialchars($todo['title'])?>" />
      </label>
      <label class="form-label mb-6x" style="color: var(--primary);"><span class="font-semibold">Deskripsi</span>
        <textarea name="description" id="description" rows="6" class="neo-textarea full-width mt-2x"><?=htmlspecialchars($todo['description'])?></textarea>
      </label>

      <!-- Grid layout: Status, Priority, Energy Level -->
      <div class="grid-three-responsive mb-6x">
        <label class="form-label" style="color: var(--primary);"><span class="font-semibold label-icon"><i class='bx bx-check-circle'></i> Status</span>
          <select name="status" id="status" class="neo-select full-width mt-2x">
            <option value="pending" <?= $todo['status']=='pending'?'selected':'' ?>>Pending</option>
            <option value="in_progress" <?= $todo['status']=='in_progress'?'selected':'' ?>>Sedang Berlangsung</option>
            <option value="completed" <?= $todo['status']=='completed'?'selected':'' ?>>Selesai</option>
          </select>
        </label>
        <label class="form-label" style="color: var(--primary);"><span class="font-semibold label-icon"><i class='bx bx-arrow-to-top'></i> Prioritas</span>
          <select name="priority" id="priority" class="neo-select full-width mt-2x">
            <option value="high-urgent" <?= $todo['priority']=='high-urgent'?'selected':'' ?>>Penting & Mendesak</option>
            <option value="high-noturgent" <?= $todo['priority']=='high-noturgent'?'selected':'' ?>>Penting, Tidak Mendesak</option>
            <option value="low-urgent" <?= $todo['priority']=='low-urgent'?'selected':'' ?>>Tidak Penting, Mendesak</option>
            <option value="low-noturgent" <?= $todo['priority']=='low-noturgent'?'selected':'' ?>>Tidak Penting, Tidak Mendesak</option>
          </select>
        </label>
        <label class="form-label" style="color: var(--primary);"><span class="font-semibold label-icon"><i class='bx bx-zap'></i> Tingkat Energi</span>
          <select name="energy_level" id="energy_level" class="neo-select full-width mt-2x">
            <option value="low" <?= $todo['energy_level']=='low'?'selected':'' ?>>Rendah</option>
            <option value="medium" <?= $todo['energy_level']=='medium'?'selected':'' ?>>Sedang</option>
            <option value="high" <?= $todo['energy_level']=='high'?'selected':'' ?>>Tinggi</option>
          </select>
        </label>
      </div>

      <!-- Full width tags field -->
      <label class="form-label mb-4x" style="color: var(--primary);"><span class="font-semibold">Kategori / Tag</span>
        <div id="tagContainer" class="tag-box" style="max-height: 10rem;">
          <!-- Tags akan dimuat di sini -->
        </div>
        <button type="button" id="showNewTagForm" class="mt-2x neo-btn neo-btn-secondary btn-md">+ Tambah Kategori Baru</button>
        <div id="newTagForm" class="neo-box card-compact mt-3x is-hidden">
          <label class="form-label mb-2x" style="color: var(--primary);"><span class="font-semibold">Nama Kategori Baru</span>
            <input type="text" id="newTagName" placeholder="Contoh: Pekerjaan" class="neo-input full-width mt-1x" />
          </label>
          <label class="form-label mb-3x" style="color: var(--primary);"><span class="font-semibold">Warna</span>
            <input type="color" id="newTagColor" value="#3b82f6" class="neo-input mt-1x" style="height: 45px; width: 100%; cursor: pointer;" />
          </label>
          <div class="btn-row">
            <button type="button" id="createNewTag" class="neo-btn neo-btn-primary btn-md">Buat</button>
            <button type="button" id="cancelNewTag" class="neo-btn neo-btn-secondary btn-md">Batal</button>
          </div>
        </div>
      </label>

      <!-- Buttons -->
      <div class="btn-row">
        <button type="submit" class="neo-btn neo-btn-primary btn-lg text-lg">Simpan</button>
      </div>
    </form>
    <div id="msg" class="mt-4x"></div>
  </div>
</main>

<script>
let selectedTags = new Set();

// Load tags on page load
loadTags();

async function loadTags() {
  try {
    const res = await fetch('api/tags.php');
    const tagsResponse = await res.json();
    
    // Handle both array and object response formats
    let tags = Array.isArray(tagsResponse) ? tagsResponse : (tagsResponse.tags || []);
    
    const container = document.getElementById('tagContainer');
    container.innerHTML = '';
    
    // Load current tags for this todo
    const todoId = <?= $todo['id'] ?>;
    const res2 = await fetch('api/get.php?id=' + todoId);
    const todoData = await res2.json();
    const currentTags = todoData.tags || [];
    currentTags.forEach(t => selectedTags.add(t.id));
    
    if (!tags || tags.length === 0) {
      container.innerHTML = '<div class="muted-box">Belum ada kategori</div>';
      return;
    }
    
    tags.forEach(tag => {
      const label = document.createElement('label');
      label.className = 'tag-checkbox-row';
      label.innerHTML = `
        <input type="checkbox" class="tag-checkbox" value="${tag.id}" ${selectedTags.has(tag.id) ? 'checked' : ''} />
        <span class="tag-pill" style="background-color: ${tag.color}">${tag.name}</span>
      `;
      label.querySelector('input').addEventListener('change', function() {
        if (this.checked) {
          selectedTags.add(parseInt(this.value));
        } else {
          selectedTags.delete(parseInt(this.value));
        }
      });
      container.appendChild(label);
    });
  } catch (e) {
    console.error('Error loading tags:', e);
    document.getElementById('tagContainer').innerHTML = '<div class="text-error text-sm-base">Error loading tags</div>';
  }
}

// New tag form toggle
document.getElementById('showNewTagForm').addEventListener('click', function() {
  const form = document.getElementById('newTagForm');
  form.classList.toggle('is-hidden');
});

document.getElementById('cancelNewTag').addEventListener('click', function() {
  document.getElementById('newTagForm').classList.add('is-hidden');
  document.getElementById('newTagName').value = '';
});

// Create new tag
document.getElementById('createNewTag').addEventListener('click', async function() {
  const name = document.getElementById('newTagName').value.trim();
  const color = document.getElementById('newTagColor').value;
  
  if (!name) {
    alert('Nama kategori tidak boleh kosong');
    return;
  }
  
  try {
    const fd = new FormData();
    fd.append('name', name);
    fd.append('color', color);
    fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    const res = await fetch('api/tags.php', { method: 'POST', body: fd });
    const json = await res.json();
    
    if (json.success) {
      document.getElementById('newTagForm').classList.add('is-hidden');
      document.getElementById('newTagName').value = '';
      selectedTags.add(json.tag_id);
      loadTags();
    } else {
      alert(json.message || 'Error membuat kategori');
    }
  } catch (e) {
    console.error('Error creating tag:', e);
  }
});

document.getElementById('editForm')?.addEventListener('submit', async function(e){
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('id', <?= $todo['id'] ?>);
  
  // Add selected tags
  selectedTags.forEach(tagId => {
    fd.append('tags[]', tagId);
  });
  
  const res = await fetch('api/edit.php', { method: 'POST', body: fd });
  const json = await res.json();
  const msg = document.getElementById('msg');
  if (json.success) {
    msg.innerHTML = '<div class="text-green-600">Berhasil disimpan.</div>';
    setTimeout(()=> location.href = 'index.php', 700);
  } else {
    msg.innerHTML = '<div class="text-red-600">'+(json.message||'Error')+'</div>';
  }
});
</script>

</body>
</html>
