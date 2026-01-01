<?php
require_once __DIR__ . '/includes/session.php';
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$todo_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$todo_title = '';

if ($todo_id) {
    $pdo = require __DIR__ . '/config/db.php';
    $stmt = $pdo->prepare('SELECT title FROM todos WHERE id = ? AND user_id = ?');
    $stmt->execute([$todo_id, $_SESSION['user']['id']]);
    $todo = $stmt->fetch();
    if ($todo) {
        $todo_title = $todo['title'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/heading.php'; ?>

<style>
    .tab-btn { background: transparent; color: rgba(255,255,255,0.85); border: 2px solid rgba(255,255,255,0.18); }
    .tab-btn.active { background: white; color: #1f2937; }
    .tab-btn:hover { background: rgba(255,255,255,0.12); color: white; }
    
    #pomodoroWrapper {
        position: relative;
    }
    
    #pomodoroWrapper::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: var(--bg-image, linear-gradient(135deg, rgba(124,58,237,0.9), rgba(14,165,233,0.9)));
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        z-index: -1;
        pointer-events: none;
    }
    
    #pomodoroWrapper:-webkit-full-screen::before {
        position: fixed;
    }
    
    #pomodoroWrapper:-moz-full-screen::before {
        position: fixed;
    }
    
    #pomodoroWrapper:fullscreen::before {
        position: fixed;
    }
    
    .youtube-card {
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 250px;
        /* background: rgba(0, 0, 0, 0.8); */
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 40;
    }
    
    .youtube-card iframe {
        width: 100%;
        height: 128px;
        border-radius: 8px;
        border: none;
    }
    
    .youtube-card .label {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        text-align: center;
        margin-top: 8px;
        font-weight: 500;
    }
    
    #fullscreenBtn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 40;
        backdrop-filter: blur(10px);
    }
    
    #fullscreenBtn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }
    
    #fullscreenBtn:active {
        transform: scale(0.95);
    }
</style>

<body  id="pomodoroWrapper" class="min-h-screen bg-gradient-to-br from-purple-600 to-blue-800 flex items-center justify-center p-4">

    <main class="w-full max-w-md" style="background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="text-center">
            <!-- Todo Title -->
            <?php if ($todo_title): ?>
                <div class="mb-8 text-white text-opacity-80">
                    <p class="text-sm uppercase tracking-wide opacity-60">Todo</p>
                    <p class="text-lg font-semibold truncate"><?= htmlspecialchars($todo_title) ?></p>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="flex gap-3 justify-center mb-12">
                <button class="tab-btn active px-6 py-2 rounded-full font-semibold transition" data-type="pomodoro">
                    pomodoro
                </button>
                <button class="tab-btn px-6 py-2 rounded-full font-semibold transition" data-type="short">
                    short break
                </button>
                <button class="tab-btn px-6 py-2 rounded-full font-semibold transition" data-type="long">
                    long break
                </button>
            </div>

            <!-- Timer Display -->
            <div class="mb-16">
                <div class="text-white text-9xl font-bold font-mono tracking-wider" id="timerDisplay">
                    25:00
                </div>
            </div>

            <!-- Controls -->
            <div class="flex gap-6 justify-center items-center">
                <button id="startBtn" class="bg-white text-gray-800 px-8 py-3 rounded-full font-bold text-lg hover:bg-gray-100 transition shadow-lg">
                    start
                </button>
                <button id="settingsBtn" class="text-white text-4xl hover:text-gray-200 transition">
                    <i class='bx bxs-cog'></i>
                </button>
            </div>

            <!-- Settings Modal -->
            <div id="settingsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-8 max-w-sm w-full shadow-xl">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Pengaturan</h2>

                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durasi Pomodoro (menit)</label>
                            <input type="number" id="pomodoroMinutes" min="1" max="60" value="25" class="w-full p-2 border rounded-lg text-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durasi Short Break (menit)</label>
                            <input type="number" id="shortBreakMinutes" min="1" max="30" value="5" class="w-full p-2 border rounded-lg text-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durasi Long Break (menit)</label>
                            <input type="number" id="longBreakMinutes" min="1" max="60" value="15" class="w-full p-2 border rounded-lg text-lg" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Latar Belakang</label>
                            <select id="bgChoice" class="w-full p-2 border rounded-lg text-lg">
                                <option value="gradient">Gradient Purple</option>
                                <option value="city">City Night</option>
                                <option value="mountain">Mountain</option>
                                <option value="dark">Solid Dark</option>
                                <option value="custom">Custom URL / Upload</option>
                            </select>
                            <input type="text" id="bgCustomUrl" placeholder="https://example.com/image.jpg" class="w-full p-2 border rounded-lg text-sm mt-2 hidden" />
                            <input type="file" id="bgFile" accept="image/*" class="w-full mt-2" />
                            <p class="text-xs text-gray-500 mt-2">Background akan diterapkan dengan <code>background-size: cover</code> untuk menghindari stretch. Upload akan menggunakan gambar lokal (data URL).</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button id="closeSettings" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg font-semibold hover:bg-gray-400 transition">
                            Tutup
                        </button>
                        <button id="saveSettings" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-12">
                <?php if ($todo_id): ?>
                    <a href="index.php" class="text-white text-opacity-70 hover:text-opacity-100 text-sm transition">
                        ← Kembali
                    </a>
                <?php else: ?>
                    <a href="index.php" class="text-white text-opacity-70 hover:text-opacity-100 text-sm transition">
                        ← Kembali ke Todo List
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- YouTube Card (Bottom Left) -->
    <div class="youtube-card">
        <iframe src="https://www.youtube.com/embed/jfKfPfyJRdk" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>

    <!-- Fullscreen Button (Bottom Right) -->
    <button id="fullscreenBtn" title="Fullscreen">
        <i class='bx bx-fullscreen'></i>
    </button>

    <script>
        let currentType = 'pomodoro';
        let timeLeft = 25 * 60;
        let isRunning = false;
        let timerInterval = null;

        const todoId = <?= json_encode($todo_id) ?>;
        let uploadedDataUrl = null;

        const durations = {
            pomodoro: 25 * 60,
            short: 5 * 60,
            long: 15 * 60
        };

        updateDisplay();

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (isRunning) return;

                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                currentType = this.dataset.type;
                timeLeft = durations[currentType];
                updateDisplay();
            });
        });

        // File upload for background (apply as data URL)
        document.getElementById('bgFile')?.addEventListener('change', function(e) {
            const f = this.files && this.files[0];
            if (!f) return;
            if (!f.type.startsWith('image/')) {
                alert('Pilih file gambar (jpg, png, dll)');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(ev) {
                const dataUrl = ev.target.result;
                // store uploaded data URL so Save can persist the selection
                uploadedDataUrl = dataUrl;
                // also set the custom text input so users see something (optional)
                const customInput = document.getElementById('bgCustomUrl');
                if (customInput) customInput.value = dataUrl;
                // apply and set choice to custom
                document.getElementById('bgChoice').value = 'custom';
                toggleCustomInput('custom');
                applyBackground('custom', dataUrl);
            };
            reader.readAsDataURL(f);
        });

        // Tab hover and active styling handled via CSS .tab-btn and .tab-btn.active

        // Start button
        document.getElementById('startBtn').addEventListener('click', startTimer);

        function startTimer() {
            if (isRunning) return;
            isRunning = true;
            document.getElementById('startBtn').textContent = 'running...';
            document.getElementById('startBtn').disabled = true;

            timerInterval = setInterval(() => {
                timeLeft--;
                updateDisplay();

                if (timeLeft <= 0) {
                    completeSession();
                }
            }, 1000);
        }

        function updateDisplay() {
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            document.getElementById('timerDisplay').textContent =
                String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

            document.title = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')} - Pomodoro`;
        }

        function completeSession() {
            clearInterval(timerInterval);
            isRunning = false;

            playNotification();

            if (currentType === 'pomodoro' && todoId) {
                saveWorkSession(todoId, durations.pomodoro / 60);
            }

            document.getElementById('startBtn').textContent = 'start';
            document.getElementById('startBtn').disabled = false;

            alert('Sesi selesai!');

            // Reset to pomodoro
            if (currentType !== 'pomodoro') {
                document.querySelector('[data-type="pomodoro"]').click();
            }
        }

        function playNotification() {
            try {
                const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                console.log('Audio not available');
            }
        }

        // Settings
        document.getElementById('settingsBtn').addEventListener('click', () => {
            document.getElementById('settingsModal').classList.remove('hidden');
            document.getElementById('pomodoroMinutes').value = durations.pomodoro / 60;
            document.getElementById('shortBreakMinutes').value = durations.short / 60;
            document.getElementById('longBreakMinutes').value = durations.long / 60;
            // Ensure bgChoice visibility state
            const bgChoiceEl = document.getElementById('bgChoice');
            if (bgChoiceEl) {
                toggleCustomInput(bgChoiceEl.value || 'gradient');
            }
        });

        document.getElementById('closeSettings').addEventListener('click', () => {
            document.getElementById('settingsModal').classList.add('hidden');
        });

        function toggleCustomInput(val) {
            const input = document.getElementById('bgCustomUrl');
            if (!input) return;
            if (val === 'custom') input.classList.remove('hidden');
            else input.classList.add('hidden');
        }

        document.getElementById('bgChoice')?.addEventListener('change', function() {
            toggleCustomInput(this.value);
        });

        document.getElementById('saveSettings').addEventListener('click', () => {
            const pomMinutes = parseInt(document.getElementById('pomodoroMinutes').value);
            const shortMinutes = parseInt(document.getElementById('shortBreakMinutes').value);
            const longMinutes = parseInt(document.getElementById('longBreakMinutes').value);
            const bgChoice = document.getElementById('bgChoice') ? document.getElementById('bgChoice').value : 'gradient';
            const bgCustom = document.getElementById('bgCustomUrl') ? document.getElementById('bgCustomUrl').value.trim() : '';

            if (pomMinutes > 0 && shortMinutes > 0 && longMinutes > 0) {
                durations.pomodoro = pomMinutes * 60;
                durations.short = shortMinutes * 60;
                durations.long = longMinutes * 60;

                timeLeft = durations[currentType];
                updateDisplay();

                // If the user selected custom but didn't type a URL (because they uploaded),
                // prefer the uploadedDataUrl stored earlier.
                let bgToUse = bgCustom;
                if (bgChoice === 'custom' && !bgToUse && typeof uploadedDataUrl !== 'undefined' && uploadedDataUrl) {
                    bgToUse = uploadedDataUrl;
                }

                applyBackground(bgChoice, bgToUse);

                document.getElementById('settingsModal').classList.add('hidden');
            }
        });

        function applyBackground(choice, customUrl) {
            const el = document.getElementById('pomodoroWrapper') || document.body;
            const darkOverlay = 'linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4))';
            let bgImage = '';
            
            if (choice === 'gradient') {
                bgImage = 'linear-gradient(135deg, rgba(124,58,237,0.9), rgba(14,165,233,0.9))';
            } else if (choice === 'city') {
                bgImage = `${darkOverlay}, url('https://images.unsplash.com/photo-1505765051173-6c8f6d2d6b2f?q=80&w=1600&auto=format&fit=crop')`;
            } else if (choice === 'mountain') {
                bgImage = `${darkOverlay}, url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?q=80&w=1600&auto=format&fit=crop')`;
            } else if (choice === 'dark') {
                bgImage = 'linear-gradient(180deg, #0f172a, #020617)';
            } else if (choice === 'custom' && customUrl) {
                bgImage = `${darkOverlay}, url('${customUrl}')`;
            }
            
            // Apply to ::before pseudo-element via CSS variable
            el.style.setProperty('--bg-image', bgImage);
        }
        // Apply default background on load
        applyBackground('gradient');

        async function saveWorkSession(todoId, minutes) {
            try {
                const fd = new FormData();
                fd.append('action', 'start');
                fd.append('todo_id', todoId);
                fd.append('duration_minutes', minutes);
                fd.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

                const res = await fetch('api/work-sessions.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await res.json();
            } catch (e) {
                console.error('Error saving session:', e);
            }
        }

        // Fullscreen functionality
        document.getElementById('fullscreenBtn').addEventListener('click', () => {
            const el = document.getElementById('pomodoroWrapper');
            if (!document.fullscreenElement) {
                el.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        });
    </script>

</body>

</html>