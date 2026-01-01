<!-- Pomodoro Timer Modal -->
<div id="pomodoroModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 id="pomodoroTitle" class="text-xl font-bold">Pomodoro Timer</h3>
      <button onclick="closePomodoroModal()" class="text-gray-500 hover:text-gray-700">
        <i class="bx bx-x text-2xl"></i>
      </button>
    </div>

    <div class="text-center mb-6">
      <div id="timerDisplay" class="text-6xl font-bold text-indigo-600 font-mono">25:00</div>
      <div id="sessionInfo" class="text-sm text-gray-600 mt-2">Sesi Pomodoro</div>
    </div>

    <div class="flex gap-3 mb-6">
      <button onclick="startPomodoro()" id="startBtn" class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        <i class="bx bx-play"></i> Mulai
      </button>
      <button onclick="pausePomodoro()" id="pauseBtn" class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 hidden">
        <i class="bx bx-pause"></i> Pause
      </button>
      <button onclick="resetPomodoro()" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
        <i class="bx bx-reset"></i> Reset
      </button>
    </div>

    <div class="mb-4">
      <label class="text-sm text-gray-600">Durasi (menit)</label>
      <input type="number" id="durationInput" value="25" min="1" max="60" class="w-full p-2 border rounded mt-1">
    </div>

    <div id="statsDisplay" class="text-sm text-gray-600 p-3 bg-gray-50 rounded">
      <div>Total sesi: <span id="totalSessions">0</span></div>
      <div>Total waktu: <span id="totalTime">0</span> menit</div>
    </div>

    <input type="hidden" id="currentTodoId" value="">
    <input type="hidden" id="currentSessionId" value="">
  </div>
</div>

<script>
let pomodoroInterval = null;
let pomodoroRunning = false;
let pomodoroSeconds = 0;
let totalSeconds = 0;

function openPomodoroModal(todoId, todoTitle) {
    document.getElementById('currentTodoId').value = todoId;
    document.getElementById('pomodoroTitle').textContent = `Pomodoro: ${todoTitle}`;
    document.getElementById('pomodoroModal').classList.remove('hidden');
    loadSessionStats(todoId);
}

function closePomodoroModal() {
    document.getElementById('pomodoroModal').classList.add('hidden');
    if (pomodoroInterval) clearInterval(pomodoroInterval);
    pomodoroRunning = false;
}

function startPomodoro() {
    if (pomodoroRunning) return;
    pomodoroRunning = true;
    document.getElementById('startBtn').classList.add('hidden');
    document.getElementById('pauseBtn').classList.remove('hidden');
    document.getElementById('durationInput').disabled = true;

    const todoId = document.getElementById('currentTodoId').value;
    const duration = parseInt(document.getElementById('durationInput').value) * 60;
    
    pomodoroSeconds = duration;
    totalSeconds = 0;

    // Start session in DB
    startSession(todoId, Math.ceil(duration / 60));

    pomodoroInterval = setInterval(() => {
        pomodoroSeconds--;
        totalSeconds++;
        updateTimerDisplay();
        
        if (pomodoroSeconds <= 0) {
            completePomodoro();
        }
    }, 1000);
}

function pausePomodoro() {
    pomodoroRunning = false;
    clearInterval(pomodoroInterval);
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('pauseBtn').classList.add('hidden');
}

function resetPomodoro() {
    clearInterval(pomodoroInterval);
    pomodoroRunning = false;
    pomodoroSeconds = 0;
    totalSeconds = 0;
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('pauseBtn').classList.add('hidden');
    document.getElementById('durationInput').disabled = false;
    updateTimerDisplay();
}

function updateTimerDisplay() {
    const mins = Math.floor(pomodoroSeconds / 60);
    const secs = pomodoroSeconds % 60;
    document.getElementById('timerDisplay').textContent = 
        `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

async function startSession(todoId, duration) {
    const fd = new FormData();
    fd.append('action', 'start');
    fd.append('todo_id', todoId);
    fd.append('duration', duration);

    try {
        const res = await fetch('api/work-sessions.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            document.getElementById('currentSessionId').value = json.session_id;
        }
    } catch (e) {
        console.error('Error starting session:', e);
    }
}

async function completePomodoro() {
    clearInterval(pomodoroInterval);
    pomodoroRunning = false;
    
    const todoId = document.getElementById('currentTodoId').value;
    const sessionId = document.getElementById('currentSessionId').value;

    const fd = new FormData();
    fd.append('action', 'complete');
    fd.append('todo_id', todoId);
    fd.append('session_id', sessionId);
    fd.append('actual_duration', totalSeconds);

    try {
        const res = await fetch('api/work-sessions.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            alert('Pomodoro selesai! Istirahat sebentar');
            loadSessionStats(todoId);
            resetPomodoro();
        }
    } catch (e) {
        console.error('Error completing session:', e);
    }
}

async function loadSessionStats(todoId) {
    const fd = new FormData();
    fd.append('action', 'get_stats');
    fd.append('todo_id', todoId);

    try {
        const res = await fetch('api/work-sessions.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            document.getElementById('totalSessions').textContent = json.total_sessions;
            document.getElementById('totalTime').textContent = json.total_minutes;
        }
    } catch (e) {
        console.error('Error loading stats:', e);
    }
}
</script>
