<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1c2437">
    <title>Otto - Asistente Virtual SST</title>
    <link rel="manifest" href="<?= base_url('agente-chat/manifest.json') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1c2437;
            --secondary: #bd9751;
            --bg-chat: #f0f2f5;
            --bg-msg-user: #d4edda;
            --bg-msg-agent: #ffffff;
            --bg-msg-error: #f8d7da;
            --bg-msg-confirm: #fff3cd;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-chat);
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            max-width: 768px;
            margin: 0 auto;
        }

        /* ─── Header ─── */
        .chat-header {
            background: var(--primary);
            color: white;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            z-index: 10;
        }
        .chat-header .avatar {
            width: 44px; height: 44px;
            background: white;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }
        .chat-header .avatar img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .chat-header .info { flex: 1; }
        .chat-header .info h6 { margin: 0; font-size: 1rem; }
        .chat-header .info small { opacity: 0.8; font-size: 0.75rem; }
        .chat-header .actions { display: flex; gap: 8px; }
        .chat-header .actions button {
            background: none; border: none; color: white;
            font-size: 1.2rem; padding: 4px 8px; cursor: pointer;
            border-radius: 8px;
        }
        .chat-header .actions button:hover { background: rgba(255,255,255,0.15); }

        /* ─── Messages area ─── */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px 12px 8px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .msg {
            max-width: 88%;
            margin-bottom: 8px;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 0.92rem;
            line-height: 1.45;
            word-wrap: break-word;
            position: relative;
            animation: msgIn 0.2s ease-out;
        }
        @keyframes msgIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .msg-user {
            background: var(--bg-msg-user);
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        .msg-agent {
            background: var(--bg-msg-agent);
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }
        .msg-error {
            background: var(--bg-msg-error);
            border-bottom-left-radius: 4px;
        }
        .msg-confirm {
            background: var(--bg-msg-confirm);
            border-bottom-left-radius: 4px;
            border: 1px solid #ffc107;
        }
        .msg-system {
            text-align: center;
            font-size: 0.78rem;
            color: #6c757d;
            max-width: 100%;
            background: transparent;
            padding: 4px;
        }

        .msg .timestamp {
            font-size: 0.68rem;
            color: #999;
            margin-top: 4px;
            text-align: right;
        }

        /* Data table inside messages */
        .msg-table-wrapper {
            overflow-x: auto;
            margin-top: 8px;
            -webkit-overflow-scrolling: touch;
        }
        .msg-table {
            width: 100%;
            font-size: 0.78rem;
            border-collapse: collapse;
        }
        .msg-table th {
            background: var(--primary);
            color: white;
            padding: 6px 8px;
            white-space: nowrap;
            position: sticky;
            top: 0;
        }
        .msg-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msg-table tr:nth-child(even) { background: #f8f9fa; }

        /* SQL block */
        .sql-block {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 8px 12px;
            border-radius: 8px;
            font-family: 'Fira Code', 'Consolas', monospace;
            font-size: 0.78rem;
            overflow-x: auto;
            margin: 8px 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Confirm box */
        .confirm-box {
            margin-top: 10px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.7);
        }
        .confirm-box .btn { font-size: 0.85rem; padding: 6px 16px; }
        .confirm-box input {
            width: 80px;
            text-align: center;
            font-size: 1rem;
            font-weight: bold;
        }

        /* ─── Typing indicator ─── */
        .typing-indicator {
            display: none;
            padding: 10px 14px;
            background: var(--bg-msg-agent);
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            max-width: 80px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }
        .typing-indicator span {
            display: inline-block;
            width: 8px; height: 8px;
            background: #999;
            border-radius: 50%;
            margin: 0 2px;
            animation: typingDot 1.4s infinite;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-6px); opacity: 1; }
        }

        /* ─── Input area ─── */
        .chat-input-area {
            background: white;
            padding: 10px 12px;
            padding-bottom: calc(10px + var(--safe-bottom));
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
            align-items: flex-end;
            flex-shrink: 0;
        }
        .chat-input-area textarea {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 10px 16px;
            font-size: 0.95rem;
            resize: none;
            max-height: 120px;
            outline: none;
            font-family: inherit;
            line-height: 1.4;
        }
        .chat-input-area textarea:focus { border-color: var(--secondary); }
        .chat-input-area .btn-send {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: var(--secondary);
            border: none;
            color: white;
            font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .chat-input-area .btn-send:hover { background: #a8832e; }
        .chat-input-area .btn-send:disabled { background: #ccc; cursor: not-allowed; }

        /* ─── Side panel (tables) ─── */
        .tables-panel {
            position: fixed;
            top: 0; right: -300px;
            width: 280px; height: 100%;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.15);
            z-index: 100;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .tables-panel.open { right: 0; }
        .tables-panel .panel-header {
            background: var(--primary);
            color: white;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tables-panel .panel-header button { background: none; border: none; color: white; font-size: 1.3rem; cursor: pointer; }
        .tables-panel .panel-search { padding: 8px 12px; }
        .tables-panel .panel-search input {
            width: 100%; padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        .tables-panel .panel-list {
            flex: 1;
            overflow-y: auto;
            padding: 4px 0;
        }
        .tables-panel .panel-list .table-item {
            padding: 8px 16px;
            font-size: 0.82rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-family: monospace;
        }
        .tables-panel .panel-list .table-item:hover { background: #f8f9fa; }
        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 99;
        }
        .overlay.active { display: block; }

        /* Markdown-like bold */
        .msg b, .msg strong { font-weight: 600; }
    </style>
</head>
<body>
    <div class="chat-container" id="chatContainer">
        <!-- Header -->
        <div class="chat-header">
            <a href="<?= base_url(session()->get('role') === 'consultant' ? 'consultor/dashboard' : 'admin/dashboard') ?>" style="color:white;text-decoration:none;">
                <i class="bi bi-arrow-left" style="font-size:1.3rem;"></i>
            </a>
            <div class="avatar"><img src="<?= base_url('img/otto/otto.png') ?>" alt="Otto"></div>
            <div class="info">
                <h6>Otto</h6>
                <small id="statusText">En línea</small>
            </div>
            <div class="actions">
                <button onclick="toggleTablesPanel()" title="Ver tablas"><i class="bi bi-database"></i></button>
                <button onclick="nuevaSesion()" title="Nueva sesión"><i class="bi bi-plus-circle"></i></button>
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="msg msg-agent">
                ¡Hola! Soy <b>Otto</b>, tu asistente virtual de SST. ¿Cómo puedo ayudarte?
                <div class="timestamp"><?= date('h:i a') ?></div>
            </div>
            <div class="typing-indicator" id="typingIndicator">
                <span></span><span></span><span></span>
            </div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <textarea id="inputMsg" rows="1" placeholder="Pregúntale a Otto..."
                      onkeydown="handleKeyDown(event)"
                      oninput="autoResize(this)"></textarea>
            <button class="btn-send" id="btnSend" onclick="enviarMensaje()">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

    <!-- Side panel: Tables -->
    <div class="overlay" id="overlay" onclick="toggleTablesPanel()"></div>
    <div class="tables-panel" id="tablesPanel">
        <div class="panel-header">
            <span><i class="bi bi-database me-2"></i>Tablas (<?= count($tablas) ?>)</span>
            <button onclick="toggleTablesPanel()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="panel-search">
            <input type="text" id="searchTabla" placeholder="Buscar tabla..." oninput="filtrarTablas()">
        </div>
        <div class="panel-list" id="tablesList">
            <?php foreach ($tablas as $t): ?>
            <div class="table-item" onclick="insertarTabla('<?= esc($t) ?>')"><?= esc($t) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="<?= base_url('js/agente_chat.js') ?>"></script>
    <script>
        // Registrar Service Worker para PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= base_url("agente-chat/sw.js") ?>').catch(() => {});
        }
    </script>
</body>
</html>
