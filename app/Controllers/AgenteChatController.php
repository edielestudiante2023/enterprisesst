<?php

namespace App\Controllers;

use App\Services\AgenteChatService;

class AgenteChatController extends BaseController
{
    protected AgenteChatService $service;

    public function __construct()
    {
        $this->service = new AgenteChatService();
    }

    /**
     * Vista principal del chat (PWA)
     */
    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado');
        }

        return view('agente_chat/index', [
            'title' => 'Otto - Asistente Virtual',
            'tablas' => $this->service->getListaTablas(),
        ]);
    }

    /**
     * PWA Manifest
     */
    public function manifest()
    {
        $manifest = [
            'name' => 'Otto - Asistente SST',
            'short_name' => 'Otto SST',
            'description' => 'Otto, tu asistente virtual de Seguridad y Salud en el Trabajo',
            'start_url' => base_url('agente-chat'),
            'display' => 'standalone',
            'background_color' => '#1c2437',
            'theme_color' => '#1c2437',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => base_url('img/otto/otto.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => base_url('img/otto/otto.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ]
            ]
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/manifest+json')
            ->setBody(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * API: Enviar mensaje al agente
     * POST /agente-chat/api/mensaje
     */
    public function enviarMensaje()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'No autorizado'])->setStatusCode(403);
        }

        $input = $this->request->getJSON(true);
        $mensaje   = trim($input['mensaje'] ?? '');
        $historial = $input['historial'] ?? [];
        $sesionChat = $input['sesion_chat'] ?? '';

        if (empty($mensaje)) {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'Mensaje vacío']);
        }

        $usuario = [
            'id'          => session()->get('id_usuario') ?? session()->get('user_id'),
            'rol'         => $role,
            'sesion_chat' => $sesionChat,
        ];

        $resultado = $this->service->procesarMensaje($mensaje, $historial, $usuario);

        // Verificar que datos serializa correctamente — si falla, limpiar y reintentar
        if (!empty($resultado['datos'])) {
            $test = json_encode($resultado['datos']);
            if ($test === false) {
                // json_encode falló — forzar limpieza agresiva y reintentar
                array_walk_recursive($resultado['datos'], function (&$v) {
                    if (is_string($v)) {
                        $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
                        $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
                        if (mb_strlen($v) > 500) $v = mb_substr($v, 0, 500) . '…';
                    }
                });
                if (json_encode($resultado['datos']) === false) {
                    // Si sigue fallando, descartar datos y notificar
                    $resultado['datos'] = [];
                    $resultado['mensaje'] .= "\n\n_(Los datos tienen caracteres especiales que no se pudieron mostrar)_";
                }
            }
        }

        return $this->response->setJSON($resultado);
    }

    /**
     * API: Confirmar operación de escritura (INSERT/UPDATE)
     * POST /agente-chat/api/confirmar
     */
    public function confirmarOperacion()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'No autorizado'])->setStatusCode(403);
        }

        $input = $this->request->getJSON(true);
        $sql            = $input['sql'] ?? '';
        $tipoOp         = $input['tipo_operacion'] ?? '';
        $mensajeOriginal = $input['mensaje_original'] ?? '';

        // Para DELETE, verificar respuesta aritmética
        if ($tipoOp === 'DELETE') {
            $respuestaUsuario  = intval($input['respuesta_aritmetica'] ?? -1);
            $respuestaCorrecta = intval($input['respuesta_correcta'] ?? -2);

            if ($respuestaUsuario !== $respuestaCorrecta) {
                return $this->response->setJSON([
                    'success' => false,
                    'mensaje' => 'Verificación aritmética incorrecta. Operación cancelada.',
                    'tipo' => 'rechazado'
                ]);
            }
        }

        if (empty($sql) || !in_array($tipoOp, ['INSERT', 'UPDATE', 'DELETE'])) {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'Datos inválidos']);
        }

        $usuario = [
            'id'          => session()->get('id_usuario') ?? session()->get('user_id'),
            'rol'         => $role,
            'sesion_chat' => $input['sesion_chat'] ?? '',
        ];

        $resultado = $this->service->ejecutarOperacionConfirmada($sql, $tipoOp, $usuario, $mensajeOriginal);

        return $this->response->setJSON($resultado);
    }

    /**
     * API: Obtener columnas de una tabla
     * GET /agente-chat/api/tabla/(:segment)
     */
    public function infoTabla(string $tabla)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return $this->response->setJSON(['success' => false])->setStatusCode(403);
        }

        $columnas = $this->service->getColumnasTabla($tabla);
        return $this->response->setJSON([
            'success' => true,
            'tabla' => $tabla,
            'columnas' => $columnas
        ]);
    }

    /**
     * Service Worker para PWA
     */
    public function serviceWorker()
    {
        $sw = <<<'JS'
const CACHE_NAME = 'agente-sst-v1';
const urlsToCache = ['/agente-chat'];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
JS;

        return $this->response
            ->setHeader('Content-Type', 'application/javascript')
            ->setBody($sw);
    }
}
