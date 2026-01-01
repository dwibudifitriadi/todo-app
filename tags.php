<?php
require_once __DIR__ . '/includes/session.php';
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$pdo = require __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/heading.php'; ?>
<body class="min-h-screen" style="background-color: var(--soft-blue)">
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<main class="max-w-4xl mx-auto p-6">
  <?php
  $breadcrumb = [
      'title' => 'Manajemen Kategori',
      'backUrl' => 'index.php',
  ];
  require_once __DIR__ . '/includes/breadcrumb.php';
  ?>
  <div class="mb-8">
    <button id="showCreateForm" class="neo-btn neo-btn-primary px-6 py-3 font-bold text-lg flex items-center gap-2">
      <i class='bx bx-plus'></i> Tambah Kategori
    </button>
  </div>

  <!-- Form Tambah Kategori Baru -->
  <div id="createForm" class="hidden mb-8 neo-box p-8">
    <h3 class="text-2xl font-bold mb-6" style="color: var(--primary);">
      <i class='bx bx-plus-circle'></i> Buat Kategori Baru
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-6">
      <div>
        <label class="block text-sm font-semibold mb-2" style="color: var(--primary);">
          <i class='bx bx-tag'></i> Nama Kategori
        </label>
        <input type="text" id="newTagName" placeholder="Contoh: Pekerjaan" class="neo-input w-full" />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2" style="color: var(--primary);">
          <i class='bx bx-palette'></i> Pilih Warna
        </label>
        <input type="color" id="newTagColor" value="#3b82f6" class="neo-input cursor-pointer" style="width: 100%; height: 50px;" />
      </div>
      <div class="flex gap-2">
        <button id="createTagBtn" type="button" class="neo-btn neo-btn-primary px-6 py-2 font-bold flex-1 flex items-center justify-center gap-2">
          <i class='bx bx-check'></i> Buat
        </button>
        <button id="cancelCreate" type="button" class="neo-btn neo-btn-secondary px-6 py-2 font-bold flex items-center justify-center gap-2">
          <i class='bx bx-x'></i>
        </button>
      </div>
    </div>
    <div id="createMsg" class="mt-4 text-sm"></div>
  </div>

  <!-- List Kategori -->
  <div id="tagsList" class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <!-- Tags akan dimuat di sini -->
  </div>

  <!-- Modal Edit -->
  <div id="editModal" class="modal-overlay is-hidden">
    <div class="neo-box w-full max-w-md max-h-96 flex flex-col">
      <!-- Header -->
      <div class="p-6 border-b-2" style="border-color: var(--primary);">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold flex items-center gap-2" style="color: var(--primary);">
            <i class='bx bx-edit-alt'></i> Edit Kategori
          </h3>
          <button id="closeEditModal" type="button" class="neo-btn neo-btn-secondary p-2 flex items-center justify-center">
            <i class="bx bx-x text-lg"></i>
          </button>
        </div>
      </div>
      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-6 space-y-4">
        <div>
          <label class="block text-sm font-semibold mb-2" style="color: var(--primary);">
            <i class='bx bx-tag'></i> Nama Kategori
          </label>
          <input type="text" id="editTagName" class="neo-input w-full" />
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2" style="color: var(--primary);">
            <i class='bx bx-palette'></i> Warna
          </label>
          <input type="color" id="editTagColor" class="neo-input cursor-pointer" style="width: 100%; height: 50px;" />
        </div>
        <div id="editMsg" class="mt-3 text-sm"></div>
      </div>
      <!-- Footer -->
      <div class="p-4 border-t-2 flex gap-2 justify-end" style="border-color: var(--primary);">
        <button id="cancelEditModal" type="button" class="neo-btn neo-btn-secondary px-4 py-2 font-bold flex items-center gap-2">
          <i class='bx bx-x'></i> Batal
        </button>
        <button id="saveEditTag" type="button" class="neo-btn neo-btn-primary px-4 py-2 font-bold flex items-center gap-2">
          <i class='bx bx-check'></i> Simpan
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Relasi Todos -->
  <div id="todosModal" class="modal-overlay is-hidden">
    <div class="neo-box w-full max-w-2xl max-h-96 flex flex-col">
      <!-- Header -->
      <div class="p-6 border-b-2" style="border-color: var(--primary);">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold flex items-center gap-2" style="color: var(--primary);">
            <i class='bx bx-list-check'></i> Todos - <span id="tagNameInModal" class="font-bold"></span>
          </h3>
          <button id="closeTodosModal" type="button" class="neo-btn neo-btn-secondary p-2 flex items-center justify-center">
            <i class="bx bx-x text-lg"></i>
          </button>
        </div>
      </div>
      <!-- Content -->
      <div id="todosListModal" class="flex-1 overflow-y-auto p-6 space-y-3">
        <!-- Todos akan dimuat di sini -->
      </div>
    </div>
  </div>

</main>

<script>
let currentEditTagId = null;
const editModalEl = document.getElementById('editModal');
const todosModalEl = document.getElementById('todosModal');

function openModal(el) {
  el.classList.remove('is-hidden');
  el.classList.add('is-open');
}

function closeModal(el) {
  el.classList.add('is-hidden');
  el.classList.remove('is-open');
}

// Load tags on page load
loadTags();

async function loadTags() {
  try {
    const res = await fetch('api/tags.php');
    const tags = await res.json();
    const container = document.getElementById('tagsList');
    
    if (!tags || tags.length === 0) {
      container.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">Belum ada kategori. Buat kategori baru untuk memulai.</div>';
      return;
    }

    container.innerHTML = tags.map(tag => `
      <div class="neo-box p-4 bg-white">
        <div class="flex items-center gap-3 mb-4 pb-4 border-b-2" style="border-color: ${tag.color};">
          <div class="w-12 h-12 rounded-lg flex-shrink-0" style="background-color: ${tag.color}; opacity: 0.8;"></div>
          <div class="flex-1 min-w-0">
            <h4 class="font-bold text-lg" style="color: var(--primary);">${tag.name}</h4>
            <div class="text-xs font-mono" style="color: var(--secondary);">${tag.color}</div>
          </div>
        </div>
        <div class="flex gap-2 flex-wrap">
          <button class="neo-btn neo-btn-secondary flex-1 px-3 py-2 font-semibold text-sm flex items-center justify-center gap-2" onclick="viewTodos(${tag.id}, '${tag.name}')">
            <i class="bx bx-list-check"></i> Todos
          </button>
          <button class="neo-btn neo-btn-secondary px-3 py-2 font-semibold text-sm flex items-center justify-center gap-2" onclick="editTag(${tag.id}, '${tag.name}', '${tag.color}')">
            <i class="bx bx-edit"></i>
          </button>
          <button class="neo-btn neo-btn-secondary px-3 py-2 font-semibold text-sm flex items-center justify-center gap-2" style="background: #ef4444; border-color: #991b1b;" onclick="deleteTag(${tag.id}, '${tag.name}')">
            <i class="bx bx-trash"></i>
          </button>
        </div>
      </div>
    `).join('');
  } catch (e) {
    console.error('Error loading tags:', e);
    document.getElementById('tagsList').innerHTML = '<div class="text-red-600">Error loading tags</div>';
  }
}

// Show create form
document.getElementById('showCreateForm').addEventListener('click', function() {
  document.getElementById('createForm').classList.toggle('hidden');
  document.getElementById('newTagName').focus();
});

document.getElementById('cancelCreate').addEventListener('click', function() {
  document.getElementById('createForm').classList.add('hidden');
  document.getElementById('newTagName').value = '';
});

// Create new tag
document.getElementById('createTagBtn').addEventListener('click', async function() {
  const name = document.getElementById('newTagName').value.trim();
  const color = document.getElementById('newTagColor').value;
  const msg = document.getElementById('createMsg');

  if (!name) {
    msg.innerHTML = '<div class="text-red-600">Nama kategori tidak boleh kosong</div>';
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
      msg.innerHTML = '<div class="text-green-600">Kategori berhasil dibuat!</div>';
      document.getElementById('newTagName').value = '';
      setTimeout(() => {
        document.getElementById('createForm').classList.add('hidden');
        loadTags();
      }, 500);
    } else {
      msg.innerHTML = '<div class="text-red-600">' + (json.message || 'Error') + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="text-red-600">Error: ' + e.message + '</div>';
  }
});

// Edit tag
function editTag(tagId, tagName, tagColor) {
  currentEditTagId = tagId;
  document.getElementById('editTagName').value = tagName;
  document.getElementById('editTagColor').value = tagColor;
  document.getElementById('editMsg').innerHTML = '';
  openModal(editModalEl);
}

document.getElementById('closeEditModal').addEventListener('click', function() {
  closeModal(editModalEl);
  currentEditTagId = null;
});

document.getElementById('cancelEditModal').addEventListener('click', function() {
  closeModal(editModalEl);
  currentEditTagId = null;
});

document.getElementById('saveEditTag').addEventListener('click', async function() {
  const name = document.getElementById('editTagName').value.trim();
  const color = document.getElementById('editTagColor').value;
  const msg = document.getElementById('editMsg');

  if (!name) {
    msg.innerHTML = '<div class="text-red-600">Nama kategori tidak boleh kosong</div>';
    return;
  }

  try {
    const fd = new FormData();
    fd.append('id', currentEditTagId);
    fd.append('name', name);
    fd.append('color', color);
    fd.append('action', 'update');
    fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    const res = await fetch('api/tags.php', { method: 'POST', body: fd });
    const json = await res.json();

    if (json.success) {
      msg.innerHTML = '<div class="text-green-600">Kategori berhasil diupdate!</div>';
      setTimeout(() => {
        closeModal(editModalEl);
        loadTags();
      }, 500);
    } else {
      msg.innerHTML = '<div class="text-red-600">' + (json.message || 'Error') + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="text-red-600">Error: ' + e.message + '</div>';
  }
});

// Delete tag
async function deleteTag(tagId, tagName) {
  if (!confirm(`Hapus kategori "${tagName}"? Todos tidak akan dihapus.`)) {
    return;
  }

  try {
    const fd = new FormData();
    fd.append('id', tagId);
    fd.append('action', 'delete');
    fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    const res = await fetch('api/tags.php', { method: 'POST', body: fd });
    const json = await res.json();

    if (json.success) {
      alert('Kategori berhasil dihapus!');
      loadTags();
    } else {
      alert(json.message || 'Error menghapus kategori');
    }
  } catch (e) {
    alert('Error: ' + e.message);
  }
}

// View todos with this tag
async function viewTodos(tagId, tagName) {
  try {
    const res = await fetch(`api/tags.php?action=get_todos&tag_id=${tagId}`);
    const todos = await res.json();

    document.getElementById('tagNameInModal').textContent = tagName;
    const todosContainer = document.getElementById('todosListModal');

    if (!todos || todos.length === 0) {
      todosContainer.innerHTML = '<div class="text-gray-500">Belum ada todos dengan kategori ini</div>';
    } else {
      todosContainer.innerHTML = todos.map(todo => `
        <div class="p-3 bg-gray-50  rounded border-l-4" style="border-color: ${getColorForStatus(todo.status)}">
          <div class="font-semibold">${todo.title}</div>
          <div class="text-sm text-gray-600 mt-1">${todo.description || '(tidak ada deskripsi)'}</div>
          <div class="flex gap-2 mt-2">
            <span class="text-xs px-2 py-1 rounded" style="background-color: ${getColorForStatus(todo.status)}; color: white">
              ${getStatusLabel(todo.status)}
            </span>
            <span class="text-xs px-2 py-1 bg-blue-200 text-blue-800 rounded">${getPriorityLabel(todo.priority)}</span>
          </div>
        </div>
      `).join('');
    }

    openModal(todosModalEl);
  } catch (e) {
    alert('Error loading todos: ' + e.message);
  }
}

document.getElementById('closeTodosModal').addEventListener('click', function() {
  closeModal(todosModalEl);
});

function getStatusLabel(status) {
  const labels = {
    'pending': 'Pending',
    'in_progress': 'Sedang Berlangsung',
    'completed': 'Selesai'
  };
  return labels[status] || status;
}

function getColorForStatus(status) {
  const colors = {
    'pending': '#fbbf24',
    'in_progress': '#3b82f6',
    'completed': '#10b981'
  };
  return colors[status] || '#999';
}

function getPriorityLabel(priority) {
  const labels = {
    'high-urgent': 'Penting & Mendesak',
    'high-noturgent': 'Penting, Tidak Mendesak',
    'low-urgent': 'Tidak Penting, Mendesak',
    'low-noturgent': 'Tidak Penting, Tidak Mendesak'
  };
  return labels[priority] || priority;
}
</script>

</body>
</html>
