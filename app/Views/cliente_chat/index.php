<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1c2437">
    <title>Otto - Asistente Virtual SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --primary: #1c2437;
            --secondary: #2c3e50;
            --gold: #bd9751;
            --gold2: #d4af37;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --chat-user-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --chat-agent-bg: #ffffff;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ─── Navbar ─── */
        .navbar-custom {
            background: #fffafa;
            box-shadow: 0 2px 15px rgba(0,0,0,0.12);
            padding: 10px 0;
            border-bottom: 2px solid var(--gold);
            flex-shrink: 0;
            z-index: 1000;
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
        }

        .navbar-logos { display: flex; align-items: center; gap: 10px; }
        .navbar-logos img { max-height: 44px; }

        .navbar-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .navbar-title img {
            width: 32px; height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn-back {
            background: var(--primary);
            color: white;
            border: none;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover { background: var(--gold); color: white; }

        /* ─── Chat wrapper ─── */
        .chat-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            padding: 0 12px;
            overflow: hidden;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0 8px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .chat-messages::-webkit-scrollbar { width: 5px; }
        .chat-messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 3px; }

        /* Welcome */
        .welcome-message {
            text-align: center;
            padding: 30px 16px 10px;
            animation: fadeInUp 0.4s ease;
        }

        .welcome-avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 16px;
            display: block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .welcome-message h3 { color: var(--primary); font-weight: 600; margin-bottom: 8px; }

        .welcome-message p {
            font-size: 0.88rem;
            color: #555;
            max-width: 480px;
            margin: 0 auto 20px;
        }

        .suggestion-chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .suggestion-chip {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 7px 14px;
            font-size: 0.8rem;
            cursor: pointer;
            color: var(--secondary);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .suggestion-chip:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        /* Mensajes */
        .message {
            display: flex;
            margin-bottom: 14px;
            animation: fadeInUp 0.25s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user { justify-content: flex-end; }
        .message.agent, .message.error { justify-content: flex-start; }

        .msg-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .message.user .msg-avatar {
            background: var(--primary);
            color: white;
            margin-left: 8px;
            order: 2;
        }

        .message.agent .msg-avatar,
        .message.error .msg-avatar {
            background: white;
            overflow: hidden;
            margin-right: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .message.agent .msg-avatar img,
        .message.error .msg-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .msg-bubble {
            max-width: 78%;
            padding: 11px 15px;
            border-radius: 16px;
            font-size: 0.9rem;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .message.user .msg-bubble {
            background: var(--chat-user-bg);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.agent .msg-bubble {
            background: var(--chat-agent-bg);
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .message.error .msg-bubble {
            background: #fff5f5;
            border: 1px solid #f8d7da;
            color: #721c24;
            border-bottom-left-radius: 4px;
        }

        /* Markdown */
        .msg-bubble p { margin-bottom: 8px; }
        .msg-bubble p:last-child { margin-bottom: 0; }
        .msg-bubble ul, .msg-bubble ol { padding-left: 20px; margin-bottom: 8px; }
        .msg-bubble code { background: rgba(0,0,0,0.08); padding: 1px 5px; border-radius: 4px; font-size: 0.82rem; }
        .msg-bubble pre { background: #1e1e1e; color: #d4d4d4; padding: 10px 12px; border-radius: 8px; overflow-x: auto; margin: 8px 0; }
        .msg-bubble pre code { background: none; padding: 0; font-size: 0.78rem; }
        .msg-bubble table { width: 100%; border-collapse: collapse; font-size: 0.8rem; margin: 8px 0; }
        .msg-bubble th { background: var(--primary); color: white; padding: 5px 8px; }
        .msg-bubble td { padding: 4px 8px; border-bottom: 1px solid #eee; }
        .msg-bubble tr:nth-child(even) td { background: #f8f9fa; }
        .message.user .msg-bubble code { background: rgba(255,255,255,0.2); }

        .msg-time { font-size: 0.65rem; color: rgba(0,0,0,0.35); margin-top: 4px; text-align: right; }
        .message.user .msg-time { color: rgba(255,255,255,0.6); }

        /* Tabla de datos */
        .data-table-wrapper { overflow-x: auto; margin-top: 8px; -webkit-overflow-scrolling: touch; }
        .data-table { width: 100%; font-size: 0.78rem; border-collapse: collapse; }
        .data-table th { background: var(--primary); color: white; padding: 6px 8px; white-space: nowrap; }
        .data-table td { padding: 5px 8px; border-bottom: 1px solid #eee; white-space: nowrap; max-width: 180px; overflow: hidden; text-overflow: ellipsis; }
        .data-table tr:nth-child(even) td { background: #f8f9fa; }

        /* ─── Typing indicator ─── */
        .typing-indicator {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 6px 0 14px;
        }

        .typing-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: white;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .typing-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .typing-dots {
            background: white;
            padding: 10px 14px;
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .typing-dots span {
            display: inline-block;
            width: 7px; height: 7px;
            background: #aaa;
            border-radius: 50%;
            margin: 0 2px;
            animation: typeDot 1.4s infinite;
        }

        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typeDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-5px); opacity: 1; }
        }

        /* ─── Input area ─── */
        .chat-input-area {
            background: white;
            border-top: 1px solid #e0e0e0;
            padding: 10px 0;
            padding-bottom: calc(10px + var(--safe-bottom));
            flex-shrink: 0;
        }

        .input-container {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        .input-container textarea {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 10px 16px;
            font-size: 0.92rem;
            resize: none;
            max-height: 120px;
            outline: none;
            font-family: inherit;
            line-height: 1.4;
            background: #f8f9fa;
        }

        .input-container textarea:focus { border-color: var(--gold); background: white; }

        .btn-send {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold2));
            border: none;
            color: white;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            transition: transform 0.15s;
        }

        .btn-send:hover { transform: scale(1.05); }
        .btn-send:disabled { background: #ccc; cursor: not-allowed; transform: none; }

        /* Responsive */
        @media (max-width: 600px) {
            .navbar-logos img { max-height: 32px; }
            .msg-bubble { max-width: 88%; font-size: 0.86rem; }
            .btn-back span { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="navbar-inner">
            <div class="navbar-logos">
                <img src="<?= base_url('img/logoenterprisesstblancoslogan.png') ?>" alt="EnterpriseSST">
                <img src="<?= base_url('img/logocycloid.png') ?>" alt="Cycloid" style="max-height:36px;">
            </div>
            <div class="navbar-title">
                <img src="<?= base_url('img/otto/otto.png') ?>" alt="Otto">
                Otto
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button onclick="finalizarConversacion()" style="background:#c0392b;color:#fff;border:none;border-radius:20px;padding:7px 14px;font-size:0.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-stop-circle"></i>
                    <span>Finalizar</span>
                </button>
                <a href="<?= base_url('client/dashboard') ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Chat -->
    <div class="chat-wrapper">
        <div class="chat-messages" id="chatMessages">
            <!-- Welcome -->
            <div class="welcome-message" id="welcomeMessage">
                <img src="<?= base_url('img/otto/otto.png') ?>" alt="Otto" class="welcome-avatar">
                <h3>¡Hola! Soy Otto</h3>
                <p>Tu asistente virtual de SST para <b><?= esc($nombre_empresa) ?></b>. Puedo consultarte información sobre tus documentos, plan de trabajo, indicadores y más.</p>
                <div class="suggestion-chips">
                    <div class="suggestion-chip" onclick="usarSugerencia(this)">Mis documentos pendientes</div>
                    <div class="suggestion-chip" onclick="usarSugerencia(this)">Mi plan de trabajo actual</div>
                    <div class="suggestion-chip" onclick="usarSugerencia(this)">Indicadores de mi empresa</div>
                    <div class="suggestion-chip" onclick="usarSugerencia(this)">Próximas visitas</div>
                    <div class="suggestion-chip" onclick="usarSugerencia(this)">Estado de mis firmas</div>
                </div>
            </div>

            <!-- Typing -->
            <div class="typing-indicator" id="typingIndicator">
                <div class="typing-avatar">
                    <img src="<?= base_url('img/otto/otto.png') ?>" alt="Otto">
                </div>
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <div class="input-container">
                <textarea id="inputMsg" rows="1" placeholder="Pregúntale a Otto sobre tu empresa..."
                          onkeydown="handleKeyDown(event)"
                          oninput="autoResize(this)"></textarea>
                <button class="btn-send" id="btnSend" onclick="enviarMensaje()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="<?= base_url('js/cliente_chat.js') ?>"></script>
</body>
</html>
