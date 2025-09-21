<?php
// mini_todo_app.php - Enhanced Professional Version (Fixed)
// Description: A professional To‑Do app with modern UI, animations, and additional features
// Features: Edit tasks, clear completed, filter tasks, dark mode, animations, and better UX
// How to run: Place in server folder (e.g., htdocs in XAMPP) and open: http://localhost/mini_todo_app.php

header('X-Frame-Options: SAMEORIGIN');
$DATA_FILE = __DIR__ . '/tasks.json';

// Create data file if it doesn't exist
if (!file_exists($DATA_FILE)) {
    file_put_contents($DATA_FILE, json_encode([]));
}

// ----- Backend PHP (simple API inside the same file) -----
function read_tasks() {
    global $DATA_FILE;
    if (!file_exists($DATA_FILE)) return [];
    $json = file_get_contents($DATA_FILE);
    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}

function write_tasks($tasks) {
    global $DATA_FILE;
    file_put_contents($DATA_FILE, json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// API endpoints via query param action
$action = $_REQUEST['action'] ?? null;
if ($action) {
    header('Content-Type: application/json; charset=utf-8');
    $tasks = read_tasks();

    if ($action === 'list') {
        echo json_encode(['ok' => true, 'tasks' => $tasks]);
        exit;
    }

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['ok' => false, 'error' => 'Title is empty']);
            exit;
        }
        $id = time() . rand(100,999);
        $task = ['id' => $id, 'title' => $title, 'done' => false, 'created_at' => date('c')];
        array_unshift($tasks, $task);
        write_tasks($tasks);
        echo json_encode(['ok' => true, 'task' => $task]);
        exit;
    }

    if ($action === 'toggle') {
        $id = $_POST['id'] ?? null;
        foreach ($tasks as &$t) {
            if ($t['id'] == $id) {
                $t['done'] = !$t['done'];
                write_tasks($tasks);
                echo json_encode(['ok' => true, 'task' => $t]);
                exit;
            }
        }
        echo json_encode(['ok' => false, 'error' => 'Task not found']);
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        $new = array_values(array_filter($tasks, function($t) use($id){ return $t['id'] != $id; }));
        write_tasks($new);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['ok' => false, 'error' => 'Title is empty']);
            exit;
        }
        foreach ($tasks as &$t) {
            if ($t['id'] == $id) {
                $t['title'] = $title;
                write_tasks($tasks);
                echo json_encode(['ok' => true, 'task' => $t]);
                exit;
            }
        }
        echo json_encode(['ok' => false, 'error' => 'Task not found']);
        exit;
    }

    if ($action === 'clear_completed') {
        $new = array_values(array_filter($tasks, function($t){ return !$t['done']; }));
        write_tasks($new);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}

// ----- If no action, render the HTML UI -----
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Professional To‑Do App</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --bg: #f8fafc;
      --card: #ffffff;
      --accent: #3b82f6;
      --accent-hover: #2563eb;
      --muted: #64748b;
      --border: #e2e8f0;
      --success: #10b981;
      --danger: #ef4444;
      --warning: #f59e0b;
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    [data-theme="dark"] {
      --bg: #1e293b;
      --card: #334155;
      --accent: #60a5fa;
      --accent-hover: #3b82f6;
      --muted: #cbd5e1;
      --border: #475569;
    }
    
    * {
      box-sizing: border-box;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    body {
      background: var(--bg);
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: #334155;
      transition: background-color 0.3s ease;
    }
    
    [data-theme="dark"] body {
      color: #e2e8f0;
    }
    
    .app {
      width: 100%;
      max-width: 720px;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .card {
      background: var(--card);
      border-radius: 16px;
      box-shadow: var(--shadow-lg);
      padding: 24px;
      transition: all 0.3s ease;
    }
    
    [data-theme="dark"] .card {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    }
    
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 12px;
    }
    
    .header-content {
      display: flex;
      flex-direction: column;
    }
    
    h1 {
      font-size: 28px;
      margin: 0;
      font-weight: 700;
      color: var(--accent);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .meta {
      color: var(--muted);
      font-size: 14px;
      margin-top: 4px;
    }
    
    .theme-toggle {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 20px;
      cursor: pointer;
      padding: 8px;
      border-radius: 8px;
      transition: all 0.2s ease;
    }
    
    .theme-toggle:hover {
      background: var(--border);
      color: var(--accent);
    }
    
    .task-form {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
    }
    
    .task-input {
      flex: 1;
      padding: 12px 16px;
      border-radius: 10px;
      border: 1px solid var(--border);
      outline: none;
      font-size: 16px;
      transition: all 0.2s ease;
      background: var(--card);
      color: inherit;
    }
    
    .task-input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn {
      background: var(--accent);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn:hover {
      background: var(--accent-hover);
      transform: translateY(-1px);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .btn-secondary {
      background: var(--border);
      color: var(--muted);
    }
    
    .btn-secondary:hover {
      background: #cbd5e1;
    }
    
    [data-theme="dark"] .btn-secondary:hover {
      background: #475569;
    }
    
    .btn-danger {
      background: var(--danger);
    }
    
    .btn-danger:hover {
      background: #dc2626;
    }
    
    .btn-sm {
      padding: 8px 12px;
      font-size: 14px;
    }
    
    .filter-bar {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
    
    .filter-btn {
      padding: 8px 16px;
      border-radius: 20px;
      border: 1px solid var(--border);
      background: var(--card);
      color: var(--muted);
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 14px;
    }
    
    .filter-btn:hover {
      background: var(--border);
    }
    
    .filter-btn.active {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
    }
    
    .tasks-container {
      max-height: 500px;
      overflow-y: auto;
      padding-right: 8px;
    }
    
    .tasks-container::-webkit-scrollbar {
      width: 6px;
    }
    
    .tasks-container::-webkit-scrollbar-track {
      background: var(--border);
      border-radius: 3px;
    }
    
    .tasks-container::-webkit-scrollbar-thumb {
      background: var(--accent);
      border-radius: 3px;
    }
    
    ul.tasks {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    
    li.task {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px;
      border-radius: 12px;
      border: 1px solid var(--border);
      background: var(--card);
      transition: all 0.2s ease;
      animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(-10px); }
      to { opacity: 1; transform: translateX(0); }
    }
    
    li.task:hover {
      box-shadow: var(--shadow);
      transform: translateY(-2px);
    }
    
    .task-checkbox {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      border: 2px solid var(--border);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    
    .task-checkbox.checked {
      background: var(--success);
      border-color: var(--success);
      color: white;
    }
    
    .title {
      flex: 1;
      word-break: break-word;
      font-size: 16px;
      transition: all 0.2s ease;
    }
    
    .done .title {
      text-decoration: line-through;
      color: var(--muted);
    }
    
    .task-actions {
      display: flex;
      gap: 8px;
      flex-shrink: 0;
    }
    
    .task-btn {
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 8px;
      border-radius: 6px;
      transition: all 0.2s ease;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
    }
    
    .task-btn:hover {
      background: var(--border);
      color: var(--accent);
    }
    
    .task-btn.edit:hover {
      color: var(--warning);
    }
    
    .task-btn.delete:hover {
      color: var(--danger);
    }
    
    .empty {
      padding: 40px 20px;
      text-align: center;
      color: var(--muted);
      font-size: 16px;
    }
    
    .empty-icon {
      font-size: 48px;
      margin-bottom: 16px;
      color: var(--border);
    }
    
    .footer {
      margin-top: 24px;
      padding-top: 16px;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }
    
    .stats {
      color: var(--muted);
      font-size: 14px;
    }
    
    .clear-btn {
      background: none;
      border: 1px solid var(--danger);
      color: var(--danger);
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .clear-btn:hover {
      background: var(--danger);
      color: white;
    }
    
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: var(--card);
      color: var(--muted);
      padding: 16px 20px;
      border-radius: 8px;
      box-shadow: var(--shadow-lg);
      display: flex;
      align-items: center;
      gap: 10px;
      z-index: 1000;
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s ease;
      max-width: 350px;
    }
    
    .toast.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .toast.success {
      border-left: 4px solid var(--success);
    }
    
    .toast.error {
      border-left: 4px solid var(--danger);
    }
    
    .edit-form {
      display: none;
      flex: 1;
      gap: 8px;
    }
    
    .edit-form.active {
      display: flex;
    }
    
    .edit-input {
      flex: 1;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid var(--border);
      outline: none;
      font-size: 14px;
      background: var(--card);
      color: inherit;
    }
    
    .edit-input:focus {
      border-color: var(--accent);
    }
    
    .clock {
      color: var(--muted);
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    @media (max-width: 640px) {
      .card {
        padding: 16px;
      }
      
      h1 {
        font-size: 24px;
      }
      
      .task-form {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
      
      .footer {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .toast {
        left: 20px;
        right: 20px;
        max-width: none;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <div class="card">
      <header>
        <div class="header-content">
          <h1><i class="fas fa-check-circle"></i> To‑Do List</h1>
          <div class="meta">Professional task management app</div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
          <div class="clock" id="clock"><i class="far fa-clock"></i> --:--</div>
          <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
            <i class="fas fa-moon"></i>
          </button>
        </div>
      </header>

      <form class="task-form" id="addForm">
        <input type="text" class="task-input" id="titleInput" placeholder="Add a new task..." autocomplete="off">
        <button type="submit" class="btn"><i class="fas fa-plus"></i> Add Task</button>
      </form>

      <div class="filter-bar">
        <button class="filter-btn active" data-filter="all">All Tasks</button>
        <button class="filter-btn" data-filter="active">Active</button>
        <button class="filter-btn" data-filter="completed">Completed</button>
      </div>

      <div class="tasks-container">
        <ul class="tasks" id="tasksList">
          <!-- Tasks will appear here dynamically -->
        </ul>
        
        <div class="empty" id="emptyNotice" style="display:none">
          <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
          <div>No tasks yet — add one!</div>
        </div>
      </div>

      <div class="footer">
        <div class="stats" id="taskStats">0 tasks</div>
        <button class="clear-btn" id="clearCompleted">
          <i class="fas fa-trash-alt"></i> Clear Completed
        </button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Notification</span>
  </div>

  <script>
    // API helper
    async function api(action, data = {}){
      const opts = { method: 'POST' };
      let url = '?action=' + encodeURIComponent(action);
      
      if (data instanceof FormData) {
        opts.body = data;
      } else {
        const fd = new FormData();
        for (const k in data) fd.append(k, data[k]);
        opts.body = fd;
      }
      
      try {
        const res = await fetch(url, opts);
        return await res.json();
      } catch (error) {
        console.error('API error:', error);
        return { ok: false, error: 'Network error' };
      }
    }

    // DOM elements
    const tasksList = document.getElementById('tasksList');
    const emptyNotice = document.getElementById('emptyNotice');
    const addForm = document.getElementById('addForm');
    const titleInput = document.getElementById('titleInput');
    const taskStats = document.getElementById('taskStats');
    const clearCompletedBtn = document.getElementById('clearCompleted');
    const themeToggle = document.getElementById('themeToggle');
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    let currentFilter = 'all';
    let tasks = [];

    // Initialize theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // Theme toggle
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
      const icon = themeToggle.querySelector('i');
      icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }

    // Show toast notification
    function showToast(message, type = 'success') {
      toastMessage.textContent = message;
      toast.className = `toast ${type}`;
      
      // Update icon based on type
      const icon = toast.querySelector('i');
      icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
      
      toast.classList.add('show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    // Escape HTML to prevent XSS
    function escapeHtml(s){
      return s.replace(/[&<>"']/g, function(c){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];
      });
    }

    // Render tasks based on current filter
    function renderTasks(){
      tasksList.innerHTML = '';
      
      const filteredTasks = tasks.filter(task => {
        if (currentFilter === 'active') return !task.done;
        if (currentFilter === 'completed') return task.done;
        return true; // 'all'
      });
      
      if (filteredTasks.length === 0){
        emptyNotice.style.display = 'block';
        updateStats();
        return;
      }
      
      emptyNotice.style.display = 'none';
      
      filteredTasks.forEach(task => {
        const li = document.createElement('li');
        li.className = 'task' + (task.done ? ' done' : '');
        li.dataset.id = task.id;
        
        li.innerHTML = `
          <div class="task-checkbox ${task.done ? 'checked' : ''}" data-id="${task.id}">
            ${task.done ? '<i class="fas fa-check"></i>' : ''}
          </div>
          <div class="title">${escapeHtml(task.title)}</div>
          <div class="edit-form" id="edit-${task.id}">
            <input type="text" class="edit-input" value="${escapeHtml(task.title)}">
            <button class="btn btn-sm btn-secondary save-btn"><i class="fas fa-save"></i></button>
            <button class="btn btn-sm btn-secondary cancel-btn"><i class="fas fa-times"></i></button>
          </div>
          <div class="task-actions">
            <button class="task-btn edit" data-id="${task.id}" title="Edit task">
              <i class="fas fa-edit"></i>
            </button>
            <button class="task-btn delete" data-id="${task.id}" title="Delete task">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        `;
        
        tasksList.appendChild(li);
      });
      
      updateStats();
    }

    // Update task statistics
    function updateStats() {
      const totalTasks = tasks.length;
      const completedTasks = tasks.filter(t => t.done).length;
      const activeTasks = totalTasks - completedTasks;
      
      if (totalTasks === 0) {
        taskStats.textContent = 'No tasks';
      } else if (activeTasks === 0) {
        taskStats.textContent = `${totalTasks} task${totalTasks > 1 ? 's' : ''} (all completed)`;
      } else {
        taskStats.textContent = `${totalTasks} task${totalTasks > 1 ? 's' : ''}: ${activeTasks} active, ${completedTasks} completed`;
      }
    }

    // Load tasks from server
    async function load(){
      const res = await api('list');
      if (res.ok) {
        tasks = res.tasks;
        renderTasks();
      } else {
        showToast('Failed to load tasks', 'error');
      }
    }

    // Add new task
    addForm.addEventListener('submit', async function(e){
      e.preventDefault();
      const title = titleInput.value.trim();
      if (!title) return;
      
      const res = await api('add', { title });
      if (res.ok) {
        tasks.unshift(res.task);
        renderTasks();
        titleInput.value = '';
        showToast('Task added successfully');
      } else {
        showToast(res.error || 'Error adding task', 'error');
      }
    });

    // Handle task actions (toggle, edit, delete)
    tasksList.addEventListener('click', async function(e){
      // Handle checkbox click
      const checkbox = e.target.closest('.task-checkbox');
      if (checkbox) {
        const taskId = checkbox.dataset.id;
        const res = await api('toggle', { id: taskId });
        if (res.ok) {
          const task = tasks.find(t => t.id == taskId);
          if (task) {
            task.done = res.task.done;
            renderTasks();
          }
        } else {
          showToast(res.error || 'Error updating task', 'error');
        }
        return;
      }
      
      // Handle button clicks
      const target = e.target.closest('button');
      if (!target) return;
      
      const taskId = target.dataset.id;
      if (!taskId) return;
      
      // Edit task
      if (target.classList.contains('edit')) {
        const editForm = document.getElementById(`edit-${taskId}`);
        const titleElement = target.closest('.task').querySelector('.title');
        
        // Hide title and show edit form
        titleElement.style.display = 'none';
        target.closest('.task-actions').style.display = 'none';
        editForm.classList.add('active');
        
        // Focus on input
        const input = editForm.querySelector('.edit-input');
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
        
        // Handle save
        editForm.querySelector('.save-btn').onclick = async function() {
          const newTitle = input.value.trim();
          if (!newTitle) {
            showToast('Task title cannot be empty', 'error');
            return;
          }
          
          const res = await api('update', { id: taskId, title: newTitle });
          if (res.ok) {
            const task = tasks.find(t => t.id == taskId);
            if (task) {
              task.title = newTitle;
              renderTasks();
              showToast('Task updated successfully');
            }
          } else {
            showToast(res.error || 'Error updating task', 'error');
          }
        };
        
        // Handle cancel
        editForm.querySelector('.cancel-btn').onclick = function() {
          titleElement.style.display = '';
          target.closest('.task-actions').style.display = '';
          editForm.classList.remove('active');
        };
        
        return;
      }
      
      // Delete task
      if (target.classList.contains('delete')) {
        if (confirm('Are you sure you want to delete this task?')) {
          const res = await api('delete', { id: taskId });
          if (res.ok) {
            tasks = tasks.filter(t => t.id != taskId);
            renderTasks();
            showToast('Task deleted successfully');
          } else {
            showToast(res.error || 'Error deleting task', 'error');
          }
        }
        return;
      }
    });

    // Clear completed tasks
    clearCompletedBtn.addEventListener('click', async function() {
      const completedCount = tasks.filter(t => t.done).length;
      if (completedCount === 0) {
        showToast('No completed tasks to clear', 'error');
        return;
      }
      
      if (confirm(`Are you sure you want to delete ${completedCount} completed task${completedCount > 1 ? 's' : ''}?`)) {
        const res = await api('clear_completed');
        if (res.ok) {
          tasks = tasks.filter(t => !t.done);
          renderTasks();
          showToast(`Cleared ${completedCount} completed task${completedCount > 1 ? 's' : ''}`);
        } else {
          showToast(res.error || 'Error clearing tasks', 'error');
        }
      }
    });

    // Filter tasks
    filterBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        // Update active filter button
        filterBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Set current filter
        currentFilter = this.dataset.filter;
        
        // Re-render tasks
        renderTasks();
      });
    });

    // Update clock
    function tick(){
      const d = new Date();
      const hours = d.getHours().toString().padStart(2, '0');
      const minutes = d.getMinutes().toString().padStart(2, '0');
      document.getElementById('clock').innerHTML = `<i class="far fa-clock"></i> ${hours}:${minutes}`;
    }
    
    setInterval(tick, 1000);
    tick();

    // Initial load
    load();
  </script>
</body>
</html>