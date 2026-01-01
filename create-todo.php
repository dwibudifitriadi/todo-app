<?php require_once __DIR__ . '/includes/session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php include_once __DIR__ . '/includes/heading.php'; ?>
<body class="page-body">
        <?php include_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="page-container-sm">
            <?php
            $breadcrumb = [
                'title' => 'Buat Todo',
                'backUrl' => 'index.php',
            ];
            require_once __DIR__ . '/includes/breadcrumb.php';
            ?>
            <div class="neo-box card-lg">
                <?php if (empty($_SESSION['user'])): ?>
                    <div class="neo-badge mb-4 text-sm" style="background: #fee2e2; border-color: #dc2626; color: #dc2626;">Silakan login terlebih dahulu.</div>
                <?php else: ?>
                    <form id="todoForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <label class="form-label mb-4x" style="color: var(--primary);"><span class="font-semibold">Judul</span>
                            <input name="title" id="title" required class="neo-input full-width mt-2x" />
                        </label>
                        <label class="form-label mb-6x" style="color: var(--primary);"><span class="font-semibold">Deskripsi</span>
                            <textarea name="description" id="description" rows="4" class="neo-textarea full-width mt-2x"></textarea>
                        </label>
                        <div class="grid-two mb-6x">
                            <label style="color: var(--primary);"><span class="font-semibold">Status</span>
                                <select name="status" id="status" class="neo-select full-width mt-2x">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">Sedang Berlangsung</option>
                                    <option value="completed">Selesai</option>
                                </select>
                            </label>
                            <label style="color: var(--primary);"><span class="font-semibold">Prioritas</span>
                                <select name="priority" id="priority" class="neo-select full-width mt-2x">
                                    <option value="">-- Pilih --</option>
                                    <option value="high-urgent">Penting & Mendesak</option>
                                    <option value="high-noturgent">Penting, Tidak Mendesak</option>
                                    <option value="low-urgent">Tidak Penting, Mendesak</option>
                                    <option value="low-noturgent">Tidak Penting, Tidak Mendesak</option>
                                </select>
                            </label>
                        </div>
                        <div class="grid-two mb-4x">
                            <label style="color: var(--primary);"><span class="font-semibold label-icon"><i class='bx bx-zap'></i> Tingkat Energi</span>
                                <select name="energy_level" id="energy" class="neo-select full-width mt-2x">
                                    <option value="">-- Pilih --</option>
                                    <option value="low">Rendah (Relaksasi)</option>
                                    <option value="medium">Sedang</option>
                                    <option value="high">Tinggi (Fokus Penuh)</option>
                                </select>
                            </label>
                            <label style="color: var(--primary);"><span class="font-semibold label-icon"><i class='bx bx-tag'></i> Tag</span>
                                <div id="tagContainer" class="tag-box">
                                    <div id="tagList" class="muted-box">Memuat tags...</div>
                                </div>
                            </label>
                        </div>
                        <input type="hidden" id="selectedTags" name="tags" value="" />
                        <div class="mb-6">
                            <label style="color: var(--primary);"><span class="font-semibold">Tambah Tag Baru</span></label>
                            <div class="btn-row mt-2x">
                                <input type="text" id="newTagName" placeholder="Nama tag" class="flex-fill neo-input" />
                                <input type="color" id="newTagColor" value="#6366f1" class="neo-input" style="width: 60px; height: 60px;" />
                                <button type="button" id="addTagBtn" class="neo-btn neo-btn-primary btn-md">+</button>
                            </div>
                        </div>
                        <div class="btn-row">
                            <button type="submit" class="neo-btn neo-btn-primary btn-lg text-lg">Buat Todo</button>
                        </div>
                    </form>
                    <div id="msg" class="mt-4x"></div>
                <?php endif; ?>
            </div>
        </main>

        <script>
        let allTags = [];
        let selectedTags = [];

        async function loadTags() {
            try {
                const res = await fetch('api/tags.php');
                const json = await res.json();
                // Handle both response formats (array or object with tags property)
                if (Array.isArray(json)) {
                    allTags = json;
                } else if (json.tags) {
                    allTags = json.tags;
                } else {
                    allTags = json;
                }
                renderTags();
            } catch (e) {
                console.error('Error loading tags:', e);
                document.getElementById('tagList').innerHTML = '<div class="text-danger text-sm-base">Error loading tags</div>';
            }
        }

        function renderTags() {
            const tagList = document.getElementById('tagList');
            if (!allTags || allTags.length === 0) {
                tagList.innerHTML = '<div class="muted-box">Belum ada tag</div>';
                return;
            }
            tagList.innerHTML = allTags.map(tag => `
                <label class="tag-option">
                    <input type="checkbox" value="${tag.id}" class="tag-checkbox" />
                    <span style="background: ${tag.color};" class="tag-dot"></span>
                    <span class="text-sm-base">${tag.name}</span>
                </label>
            `).join('');
            
            document.querySelectorAll('.tag-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    selectedTags = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(el => el.value);
                    document.getElementById('selectedTags').value = selectedTags.join(',');
                });
            });
        }

        // Add new tag
        document.getElementById('addTagBtn')?.addEventListener('click', async function() {
            const name = document.getElementById('newTagName').value.trim();
            const color = document.getElementById('newTagColor').value;
            
            if (!name) {
                alert('Nama tag tidak boleh kosong');
                return;
            }
            
            const fd = new FormData();
            fd.append('name', name);
            fd.append('color', color);
            fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
            
            try {
                const res = await fetch('api/tags.php', { method: 'POST', body: fd });
                const json = await res.json();
                if (json.success) {
                    const tagId = json.tag_id || json.id;
                    allTags.push({ id: tagId, name: json.name, color: json.color });
                    renderTags();
                    document.getElementById('newTagName').value = '';
                    document.getElementById('newTagColor').value = '#6366f1';
                } else {
                    alert(json.message || 'Gagal membuat tag');
                }
            } catch (e) {
                console.error('Error:', e);
            }
        });

        // Submit form
        document.getElementById('todoForm')?.addEventListener('submit', async function(e){
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            const res = await fetch('api/create.php', { method: 'POST', body: data });
            const json = await res.json();
            const msg = document.getElementById('msg');
            if (json.success) {
                msg.innerHTML = '<div class="text-success">To do dibuat.</div>';
                setTimeout(()=> location.href = 'index.php', 800);
            } else {
                msg.innerHTML = '<div class="text-error">'+(json.message||'Error')+'</div>';
            }
        });

        // Initialize
        loadTags();
        </script>
</body>
</html>