<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis de Causa Raiz - Accion #<?= $accion['id_accion'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .chat-container { max-height: 500px; overflow-y: auto; }
        .chat-message { max-width: 85%; }
        .chat-ia { background-color: #e3f2fd; border-radius: 15px 15px 15px 0; }
        .chat-usuario { background-color: #f5f5f5; border-radius: 15px 15px 0 15px; }
        .typing-indicator { display: none; }
        .typing-indicator.show { display: block; }
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #6c757d;
            border-radius: 50%;
            animation: typing 1s infinite;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-shield-check me-2"></i>EnterpriseSST
            </a>
            <div class="d-flex align-items-center">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}") ?>"
                   class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver a la Accion
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">
                            <i class="bi bi-robot text-primary me-2"></i>
                            Analisis de Causa Raiz con IA
                        </h4>
                        <p class="text-muted mb-2">
                            Utilizando la metodologia de <strong>Indagacion Socratica Estructurada</strong>,
                            la IA te guiara mediante preguntas para identificar la causa raiz del hallazgo.
                        </p>
                        <div class="alert alert-light border mb-0">
                            <strong>Hallazgo:</strong> <?= esc($hallazgo['titulo']) ?>
                            <br>
                            <small class="text-muted"><?= esc(substr($hallazgo['descripcion'], 0, 200)) ?>...</small>
                        </div>
                    </div>
                </div>

                <!-- Chat Container -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-dots me-2"></i>Dialogo de Analisis
                        </h6>
                    </div>
                    <div class="card-body chat-container" id="chat_container">
                        <?php if (empty($historial_dialogo)): ?>
                        <!-- Mensaje inicial de bienvenida -->
                        <div class="chat-message chat-ia p-3 mb-3">
                            <small class="text-primary d-block mb-2">
                                <i class="bi bi-robot me-1"></i>Asistente IA
                            </small>
                            <p class="mb-0">
                                Hola! Voy a ayudarte a identificar la causa raiz del hallazgo mediante un proceso de indagacion.
                                <br><br>
                                Haz clic en <strong>"Iniciar Analisis"</strong> para comenzar con la primera pregunta.
                            </p>
                        </div>
                        <?php else: ?>
                        <!-- Mostrar historial existente -->
                        <?php foreach ($historial_dialogo as $mensaje): ?>
                        <div class="chat-message <?= $mensaje['rol'] === 'ia' ? 'chat-ia' : 'chat-usuario ms-auto' ?> p-3 mb-3">
                            <small class="<?= $mensaje['rol'] === 'ia' ? 'text-primary' : 'text-secondary' ?> d-block mb-2">
                                <i class="bi <?= $mensaje['rol'] === 'ia' ? 'bi-robot' : 'bi-person' ?> me-1"></i>
                                <?= $mensaje['rol'] === 'ia' ? 'Asistente IA' : 'Tu respuesta' ?>
                                <span class="float-end small"><?= date('H:i', strtotime($mensaje['timestamp'])) ?></span>
                            </small>
                            <p class="mb-0"><?= nl2br(esc($mensaje['mensaje'])) ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Indicador de escritura -->
                        <div class="typing-indicator chat-message chat-ia p-3 mb-3" id="typing_indicator">
                            <small class="text-primary d-block mb-2">
                                <i class="bi bi-robot me-1"></i>Asistente IA esta escribiendo
                            </small>
                            <span></span><span></span><span></span>
                        </div>
                    </div>

                    <!-- Input de respuesta -->
                    <div class="card-footer bg-white">
                        <?php if (!empty($causa_identificada)): ?>
                        <div class="alert alert-success mb-3">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb-fill me-2"></i>Causa Raiz Identificada
                            </h6>
                            <p class="mb-0"><?= nl2br(esc($causa_identificada)) ?></p>
                        </div>
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}") ?>"
                           class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i>Volver a la Accion
                        </a>
                        <?php else: ?>
                        <form id="form_respuesta" class="d-flex gap-2">
                            <input type="hidden" id="id_accion" value="<?= $accion['id_accion'] ?>">
                            <input type="hidden" id="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                            <textarea class="form-control" id="respuesta_usuario" rows="2"
                                      placeholder="Escribe tu respuesta aqui..."
                                      <?= empty($historial_dialogo) ? 'disabled' : '' ?>></textarea>
                            <div class="d-flex flex-column gap-2">
                                <?php if (empty($historial_dialogo)): ?>
                                <button type="button" class="btn btn-primary" id="btn_iniciar" onclick="iniciarAnalisis(); return false;">
                                    <i class="bi bi-play-fill"></i> Iniciar
                                </button>
                                <?php else: ?>
                                <button type="submit" class="btn btn-primary" id="btn_enviar">
                                    <i class="bi bi-send"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Guardar Causa Raiz Manualmente -->
                <?php if (empty($causa_identificada)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-pencil me-2"></i>Definir Causa Raiz Manualmente
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/causa-raiz") ?>"
                              method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <textarea class="form-control" name="causa_raiz_identificada" rows="3"
                                          placeholder="Si ya conoces la causa raiz, escribela aqui..."
                                          required><?= esc($accion['causa_raiz_identificada'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="bi bi-check2 me-1"></i>Guardar Causa Raiz
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Variables globales - usar valores PHP directamente para evitar problemas de timing
    const baseUrl = '<?= base_url() ?>';
    const idCliente = '<?= $cliente['id_cliente'] ?>';
    const idAccion = '<?= $accion['id_accion'] ?>';

    function scrollToBottom() {
        const container = document.getElementById('chat_container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function agregarMensaje(rol, mensaje) {
        const clase = rol === 'ia' ? 'chat-ia' : 'chat-usuario ms-auto';
        const icono = rol === 'ia' ? 'bi-robot' : 'bi-person';
        const titulo = rol === 'ia' ? 'Asistente IA' : 'Tu respuesta';
        const colorClase = rol === 'ia' ? 'text-primary' : 'text-secondary';
        const hora = new Date().toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit'});

        const html = `
            <div class="chat-message ${clase} p-3 mb-3">
                <small class="${colorClase} d-block mb-2">
                    <i class="bi ${icono} me-1"></i>${titulo}
                    <span class="float-end small">${hora}</span>
                </small>
                <p class="mb-0">${mensaje.replace(/\n/g, '<br>')}</p>
            </div>
        `;

        const typingIndicator = document.getElementById('typing_indicator');
        if (typingIndicator) {
            typingIndicator.insertAdjacentHTML('beforebegin', html);
        }
        scrollToBottom();
    }

    function habilitarTextarea() {
        const textarea = document.getElementById('respuesta_usuario');
        if (textarea) {
            textarea.disabled = false;
            textarea.removeAttribute('disabled');
            console.log('Textarea habilitado');
        }
    }

    function enviarRespuesta(respuesta) {
        console.log('Enviando respuesta:', respuesta);
        const url = baseUrl + 'acciones-correctivas/' + idCliente + '/accion/' + idAccion + '/analisis-ia';
        console.log('URL:', url);

        // Habilitar textarea inmediatamente
        habilitarTextarea();

        // Mostrar mensaje del usuario
        if (respuesta) {
            agregarMensaje('usuario', respuesta);
        }

        // Mostrar indicador de escritura
        const typingIndicator = document.getElementById('typing_indicator');
        if (typingIndicator) typingIndicator.classList.add('show');

        const btnEnviar = document.getElementById('btn_enviar');
        const btnIniciar = document.getElementById('btn_iniciar');
        if (btnEnviar) btnEnviar.disabled = true;
        if (btnIniciar) btnIniciar.disabled = true;

        scrollToBottom();

        // Enviar al servidor usando fetch (mas moderno y confiable)
        const formData = new FormData();
        formData.append('respuesta_usuario', respuesta);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Status HTTP:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Respuesta recibida:', data);
            if (typingIndicator) typingIndicator.classList.remove('show');

            if (data.success) {
                console.log('Respuesta IA:', data.respuesta_ia);
                agregarMensaje('ia', data.respuesta_ia);

                if (data.causa_identificada) {
                    // Mostrar alerta de exito
                    const alertHtml = `
                        <div class="alert alert-success mt-3">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb-fill me-2"></i>Causa Raiz Identificada!
                            </h6>
                            <p class="mb-2">El analisis ha concluido. La causa raiz ha sido guardada.</p>
                            <a href="${baseUrl}acciones-correctivas/${idCliente}/accion/${idAccion}"
                               class="btn btn-success btn-sm">
                                Volver a la Accion
                            </a>
                        </div>
                    `;
                    const chatContainer = document.getElementById('chat_container');
                    if (chatContainer) chatContainer.insertAdjacentHTML('beforeend', alertHtml);
                    const formRespuesta = document.getElementById('form_respuesta');
                    if (formRespuesta) formRespuesta.style.display = 'none';
                } else {
                    // Reemplazar boton Iniciar por Enviar si es necesario
                    const currentBtnIniciar = document.getElementById('btn_iniciar');
                    if (currentBtnIniciar && currentBtnIniciar.parentNode) {
                        const newBtn = document.createElement('button');
                        newBtn.type = 'submit';
                        newBtn.className = 'btn btn-primary';
                        newBtn.id = 'btn_enviar';
                        newBtn.innerHTML = '<i class="bi bi-send"></i>';
                        currentBtnIniciar.parentNode.replaceChild(newBtn, currentBtnIniciar);
                    }
                    const newBtnEnviar = document.getElementById('btn_enviar');
                    if (newBtnEnviar) newBtnEnviar.disabled = false;

                    const textarea = document.getElementById('respuesta_usuario');
                    if (textarea) {
                        textarea.value = '';
                        textarea.focus();
                    }
                }
            } else {
                console.error('Error en respuesta:', data);
                alert('Error: ' + (data.error || 'Error desconocido'));
                const errBtnEnviar = document.getElementById('btn_enviar');
                const errBtnIniciar = document.getElementById('btn_iniciar');
                if (errBtnEnviar) errBtnEnviar.disabled = false;
                if (errBtnIniciar) {
                    errBtnIniciar.innerHTML = '<i class="bi bi-play-fill"></i> Reintentar';
                    errBtnIniciar.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            if (typingIndicator) typingIndicator.classList.remove('show');
            alert('Error de conexion: ' + error.message);
            const catchBtnEnviar = document.getElementById('btn_enviar');
            const catchBtnIniciar = document.getElementById('btn_iniciar');
            if (catchBtnEnviar) catchBtnEnviar.disabled = false;
            if (catchBtnIniciar) {
                catchBtnIniciar.innerHTML = '<i class="bi bi-play-fill"></i> Reintentar';
                catchBtnIniciar.disabled = false;
            }
        });
    }

    // Funcion global para iniciar analisis (llamada desde onclick)
    function iniciarAnalisis() {
        console.log('Funcion iniciarAnalisis() ejecutada');
        habilitarTextarea();

        const btnIniciar = document.getElementById('btn_iniciar');
        if (btnIniciar) {
            btnIniciar.innerHTML = '<i class="bi bi-hourglass-split"></i> Cargando...';
            btnIniciar.disabled = true;
        }

        enviarRespuesta('');
    }

    // Inicializacion cuando el DOM este listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Cargado. idCliente:', idCliente, 'idAccion:', idAccion);
        scrollToBottom();

        // Iniciar analisis - binding con jQuery como backup
        const btnIniciar = document.getElementById('btn_iniciar');
        if (btnIniciar) {
            btnIniciar.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Boton Iniciar clickeado (event listener)');
                iniciarAnalisis();
            });
        }

        // Enviar respuesta
        const formRespuesta = document.getElementById('form_respuesta');
        if (formRespuesta) {
            formRespuesta.addEventListener('submit', function(e) {
                e.preventDefault();
                const textarea = document.getElementById('respuesta_usuario');
                const respuesta = textarea ? textarea.value.trim() : '';
                if (respuesta) {
                    enviarRespuesta(respuesta);
                }
            });
        }

        // Enviar con Enter (Shift+Enter para nueva linea)
        const textarea = document.getElementById('respuesta_usuario');
        if (textarea) {
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (formRespuesta) {
                        formRespuesta.dispatchEvent(new Event('submit'));
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
