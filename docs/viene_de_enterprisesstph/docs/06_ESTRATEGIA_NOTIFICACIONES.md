# Estrategia de Notificaciones - Modulo de Inspecciones

> **Estado: PENDIENTE DE IMPLEMENTACION** — Este documento es un diseno a futuro. No se ha instalado `minishlink/web-push`, no existe la tabla `tbl_push_subscriptions`, ni los archivos JS/PHP descritos aqui. Actualmente solo se usa email via SendGrid (sin integracion con el modulo de inspecciones).

---

## 1. Estado Actual del Proyecto

| Canal | Estado | Implementacion |
|-------|--------|----------------|
| Email (SendGrid SDK) | ACTIVO | 7 controllers lo usan |
| Email (SendGrid cURL) | ACTIVO | 3 controllers lo usan |
| Web Push | NO EXISTE | Ni libreria, ni SW, ni tabla |
| In-app (campana/inbox) | NO EXISTE | Notificaciones solo por email |
| Cron jobs | 1 comando | `contratos:resumen-semanal` |

**Sender email:** `notificacion.cycloidtalent@cycloidtalent.com`
**API key:** `SENDGRID_API_KEY` en `.env`

---

## 2. Canales de Notificacion para Inspecciones

| Canal | Caso de uso | Cuando |
|-------|-------------|--------|
| **Web Push** | Sync completada, PDF listo, recordatorios | Tiempo real, app cerrada |
| **Email (SendGrid)** | PDF enviado al cliente, resumen semanal | Despues de generar PDF |
| **In-app (SweetAlert/Toast)** | Feedback inmediato en la PWA | App abierta |

### Eventos que generan notificacion

| Evento | Push | Email | In-app |
|--------|------|-------|--------|
| Acta sincronizada desde offline | Si | No | Si |
| PDF del acta generado | Si | Si (al admin del conjunto) | Si |
| Recordatorio de visita programada | Si | No | No |
| Pendientes vencidos del cliente | Si | Si (al consultor) | No |
| Mantenimiento proximo a vencer | Si | Si (al consultor) | No |
| Error de sincronizacion | Si | No | Si |

---

## 3. Plan de Implementacion Web Push

### Paso 1: Instalar libreria web-push PHP

```bash
composer require minishlink/web-push
```

Requisitos que ya se cumplen:
- PHP 8.1+ (el proyecto usa 8.2)
- Extension `gmp` (verificar que este habilitada en XAMPP y produccion)
- Extension `mbstring` (ya habilitada por CI4)
- HTTPS en produccion (requerido por Web Push API)

### Paso 2: Generar VAPID keys

```bash
# Generar par de claves VAPID (una sola vez)
php -r "
use Minishlink\WebPush\VAPID;
require 'vendor/autoload.php';
\$keys = VAPID::createVapidKeys();
echo 'VAPID_PUBLIC_KEY=' . \$keys['publicKey'] . PHP_EOL;
echo 'VAPID_PRIVATE_KEY=' . \$keys['privateKey'] . PHP_EOL;
"
```

Agregar al `.env`:

```env
# Web Push VAPID Keys
VAPID_PUBLIC_KEY=BEl62iUYgU...base64url...
VAPID_PRIVATE_KEY=Dt1CLgQl...base64url...
VAPID_SUBJECT=mailto:notificacion.cycloidtalent@cycloidtalent.com
```

### Paso 3: Tabla `tbl_push_subscriptions`

```sql
CREATE TABLE tbl_push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL COMMENT 'FK a tbl_usuarios.id_usuario',
    endpoint TEXT NOT NULL COMMENT 'URL del push service (Google FCM, Apple, etc.)',
    p256dh VARCHAR(255) NOT NULL COMMENT 'Clave publica del cliente',
    auth VARCHAR(255) NOT NULL COMMENT 'Token de autenticacion',
    user_agent VARCHAR(500) NULL COMMENT 'Navegador/dispositivo',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL COMMENT 'Ultima notificacion enviada exitosamente',
    is_active TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_push_usuario
        FOREIGN KEY (id_usuario) REFERENCES tbl_usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_push_usuario (id_usuario),
    INDEX idx_push_active (is_active),
    UNIQUE INDEX idx_push_endpoint (endpoint(500))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Notas:**
- Un usuario puede tener multiples suscripciones (celular + PC + tablet)
- `endpoint` es UNIQUE para evitar duplicados del mismo navegador
- `is_active` se pone 0 si el push falla con error 410 (suscripcion expirada)

### Paso 4: Modelo CI4

```php
// app/Models/PushSubscriptionModel.php

namespace App\Models;
use CodeIgniter\Model;

class PushSubscriptionModel extends Model
{
    protected $table = 'tbl_push_subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_usuario', 'endpoint', 'p256dh', 'auth',
        'user_agent', 'last_used_at', 'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Obtener suscripciones activas de un usuario
     */
    public function getActiveByUser(int $idUsuario): array
    {
        return $this->where('id_usuario', $idUsuario)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Guardar o actualizar suscripcion
     */
    public function saveSubscription(int $idUsuario, array $subscription, string $userAgent = ''): bool
    {
        $existing = $this->where('endpoint', $subscription['endpoint'])->first();

        $data = [
            'id_usuario' => $idUsuario,
            'endpoint'   => $subscription['endpoint'],
            'p256dh'     => $subscription['keys']['p256dh'],
            'auth'       => $subscription['keys']['auth'],
            'user_agent' => $userAgent,
            'is_active'  => 1
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }

    /**
     * Desactivar suscripcion (endpoint expirado/invalido)
     */
    public function deactivate(string $endpoint): bool
    {
        return $this->where('endpoint', $endpoint)
                    ->set(['is_active' => 0])
                    ->update();
    }
}
```

### Paso 5: Controller para Push

```php
// app/Controllers/PushNotificationController.php

namespace App\Controllers;
use App\Models\PushSubscriptionModel;

class PushNotificationController extends BaseController
{
    /**
     * POST /push/subscribe
     * Guarda la suscripcion push del navegador
     */
    public function subscribe()
    {
        $idUsuario = session()->get('id_usuario');
        if (!$idUsuario) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'No autenticado']);
        }

        $subscription = $this->request->getJSON(true);

        if (empty($subscription['endpoint']) || empty($subscription['keys'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Datos incompletos']);
        }

        $model = new PushSubscriptionModel();
        $result = $model->saveSubscription(
            $idUsuario,
            $subscription,
            $this->request->getUserAgent()->getAgentString()
        );

        return $this->response->setJSON(['success' => $result]);
    }

    /**
     * POST /push/unsubscribe
     * Desactiva la suscripcion
     */
    public function unsubscribe()
    {
        $endpoint = $this->request->getJSON(true)['endpoint'] ?? '';
        if (empty($endpoint)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Endpoint requerido']);
        }

        $model = new PushSubscriptionModel();
        $model->deactivate($endpoint);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * GET /push/vapid-key
     * Retorna la clave publica VAPID para el cliente JS
     */
    public function vapidKey()
    {
        return $this->response->setJSON([
            'publicKey' => getenv('VAPID_PUBLIC_KEY')
        ]);
    }
}
```

### Paso 6: Libreria para enviar Push

```php
// app/Libraries/PushNotificationService.php

namespace App\Libraries;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscriptionModel;

class PushNotificationService
{
    private WebPush $webPush;
    private PushSubscriptionModel $model;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => getenv('VAPID_SUBJECT'),
                'publicKey'  => getenv('VAPID_PUBLIC_KEY'),
                'privateKey' => getenv('VAPID_PRIVATE_KEY'),
            ]
        ]);

        // No reintentar automaticamente (lo manejamos nosotros)
        $this->webPush->setReuseVAPIDHeaders(true);
        $this->model = new PushSubscriptionModel();
    }

    /**
     * Enviar notificacion a un usuario especifico
     */
    public function sendToUser(int $idUsuario, string $title, string $body, array $data = []): array
    {
        $subscriptions = $this->model->getActiveByUser($idUsuario);
        $results = [];

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);

            $payload = json_encode([
                'title'   => $title,
                'body'    => $body,
                'icon'    => '/assets/icons/icon-192.png',
                'badge'   => '/assets/icons/badge-72.png',
                'data'    => $data,
                'tag'     => $data['tag'] ?? 'default',
                'requireInteraction' => $data['requireInteraction'] ?? false
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        // Enviar todas las notificaciones encoladas
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                // Actualizar last_used_at
                $this->model->where('endpoint', $endpoint)
                    ->set(['last_used_at' => date('Y-m-d H:i:s')])
                    ->update();
                $results[] = ['endpoint' => $endpoint, 'success' => true];

            } else {
                $statusCode = $report->getResponse()?->getStatusCode();

                // 410 Gone = suscripcion expirada, desactivar
                if ($statusCode === 410 || $statusCode === 404) {
                    $this->model->deactivate($endpoint);
                }

                $results[] = [
                    'endpoint' => $endpoint,
                    'success'  => false,
                    'reason'   => $report->getReason(),
                    'status'   => $statusCode
                ];
            }
        }

        return $results;
    }

    /**
     * Enviar a todos los consultores (para alertas generales)
     */
    public function sendToAllConsultants(string $title, string $body, array $data = []): void
    {
        $db = \Config\Database::connect();
        $consultores = $db->table('tbl_usuarios')
            ->where('tipo_usuario', 'consultant')
            ->where('estado', 'activo')
            ->get()->getResultArray();

        foreach ($consultores as $consultor) {
            $this->sendToUser($consultor['id_usuario'], $title, $body, $data);
        }
    }
}
```

### Paso 7: JavaScript cliente (suscripcion push)

```javascript
// public/js/inspecciones/push-manager.js

class PushNotificationManager {

    constructor() {
        this.swRegistration = null;
    }

    /**
     * Inicializar: registrar SW y suscribir a push
     */
    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push notifications no soportadas en este navegador');
            return false;
        }

        try {
            // Obtener registro del Service Worker
            this.swRegistration = await navigator.serviceWorker.ready;

            // Verificar si ya esta suscrito
            const subscription = await this.swRegistration.pushManager.getSubscription();

            if (subscription) {
                console.log('Ya suscrito a push notifications');
                await this.syncSubscriptionWithServer(subscription);
                return true;
            }

            // Pedir permiso
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.log('Permiso de notificaciones denegado');
                return false;
            }

            // Suscribirse
            await this.subscribe();
            return true;

        } catch (err) {
            console.error('Error inicializando push:', err);
            return false;
        }
    }

    /**
     * Suscribirse a push notifications
     */
    async subscribe() {
        try {
            // Obtener clave VAPID publica del servidor
            const response = await fetch('/push/vapid-key');
            const { publicKey } = await response.json();

            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(publicKey)
            });

            // Enviar suscripcion al servidor
            await this.syncSubscriptionWithServer(subscription);

            console.log('Suscrito a push notifications');

        } catch (err) {
            console.error('Error al suscribirse:', err);
        }
    }

    /**
     * Guardar suscripcion en el servidor
     */
    async syncSubscriptionWithServer(subscription) {
        await fetch('/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(subscription.toJSON())
        });
    }

    /**
     * Desuscribirse
     */
    async unsubscribe() {
        const subscription = await this.swRegistration.pushManager.getSubscription();
        if (subscription) {
            await subscription.unsubscribe();
            await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });
        }
    }

    /**
     * Convertir base64url a Uint8Array (requerido por PushManager.subscribe)
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}

// Inicializar al cargar la PWA
const pushManager = new PushNotificationManager();
pushManager.init();
```

---

## 4. Service Worker: Manejo de Push

Esto se agrega al `sw_inspecciones.js` (ya planificado en doc 05):

```javascript
// Recibir notificacion push
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const payload = event.data.json();

    const options = {
        body: payload.body,
        icon: payload.icon || '/assets/icons/icon-192.png',
        badge: payload.badge || '/assets/icons/badge-72.png',
        tag: payload.tag || 'inspecciones',
        data: payload.data || {},
        requireInteraction: payload.requireInteraction || false,
        vibrate: [200, 100, 200],
        actions: payload.actions || []
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// Click en la notificacion
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data;
    let url = '/inspecciones';

    // Navegar segun el tipo de notificacion
    if (data.type === 'sync_complete' && data.id_acta) {
        url = '/inspecciones/acta-visita/view/' + data.id_acta;
    } else if (data.type === 'pdf_ready' && data.url_pdf) {
        url = data.url_pdf;
    } else if (data.type === 'reminder') {
        url = '/inspecciones/acta-visita/create/' + data.id_cliente;
    } else if (data.type === 'pendientes_vencidos') {
        url = '/inspecciones'; // dashboard con alerta
    }

    // Abrir o enfocar la ventana
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // Si ya hay una ventana abierta, enfocarla
                for (const client of windowClients) {
                    if (client.url.includes('/inspecciones') && 'focus' in client) {
                        client.navigate(url);
                        return client.focus();
                    }
                }
                // Si no, abrir una nueva
                return clients.openWindow(url);
            })
    );
});
```

---

## 5. Integracion con el Flujo de Inspecciones

### 5.1 Despues de sincronizar acta offline

En `ActaVisitaController::sync()` (del doc 05), al final:

```php
// Despues de crear acta + generar PDF exitosamente:
$pushService = new \App\Libraries\PushNotificationService();

// Notificar al consultor que creo el acta
$pushService->sendToUser($idUsuario,
    'Acta sincronizada',
    "Acta de {$cliente['nombre_cliente']} lista. PDF generado.",
    [
        'type'     => 'sync_complete',
        'id_acta'  => $idActa,
        'url_pdf'  => $urlPdf,
        'tag'      => 'sync_' . $idActa
    ]
);
```

### 5.2 Enviar PDF al administrador del conjunto (Email)

```php
// Despues de generar el PDF:
$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
$email = new \SendGrid\Mail\Mail();
$email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'Cycloid Talent');
$email->setSubject("Acta de Visita - {$fecha_visita}");
$email->addTo($correoCliente);

$htmlEmail = view('emails/acta_visita_notificacion', [
    'nombre_cliente' => $cliente['nombre_cliente'],
    'fecha_visita'   => $acta['fecha_visita'],
    'motivo'         => $acta['motivo'],
    'url_pdf'        => base_url($urlPdf)
]);

$email->addContent('text/html', $htmlEmail);

// Adjuntar PDF
$pdfPath = FCPATH . $urlPdf;
$email->addAttachment(
    base64_encode(file_get_contents($pdfPath)),
    'application/pdf',
    "Acta_Visita_{$fecha_visita}.pdf"
);

$sendgrid->send($email);
```

### 5.3 Recordatorio de visita programada (Cron)

Nuevo comando CI4 para ejecutar via cron diariamente:

```php
// app/Commands/RecordatorioVisitas.php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use App\Libraries\PushNotificationService;

class RecordatorioVisitas extends BaseCommand
{
    protected $group       = 'Inspecciones';
    protected $name        = 'inspecciones:recordatorio-visitas';
    protected $description = 'Envia push a consultores con visitas programadas para hoy';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Buscar actas que tienen proxima_reunion_fecha = hoy
        $hoy = date('Y-m-d');
        $visitas = $db->table('tbl_acta_visita a')
            ->select('a.proxima_reunion_fecha, a.proxima_reunion_hora, a.id_consultor,
                      c.nombre_cliente, c.id_cliente, u.id_usuario')
            ->join('tbl_clientes c', 'c.id_cliente = a.id_cliente')
            ->join('tbl_consultor co', 'co.id_consultor = a.id_consultor')
            ->join('tbl_usuarios u', 'u.id_entidad = co.id_consultor AND u.tipo_usuario = "consultant"')
            ->where('a.proxima_reunion_fecha', $hoy)
            ->get()->getResultArray();

        if (empty($visitas)) {
            CLI::write('No hay visitas programadas para hoy.', 'green');
            return;
        }

        $pushService = new PushNotificationService();

        foreach ($visitas as $v) {
            $hora = $v['proxima_reunion_hora'] ?? 'sin hora definida';

            $pushService->sendToUser($v['id_usuario'],
                'Visita programada hoy',
                "{$v['nombre_cliente']} — {$hora}",
                [
                    'type'       => 'reminder',
                    'id_cliente' => $v['id_cliente'],
                    'tag'        => 'reminder_' . $v['id_cliente']
                ]
            );

            CLI::write("Push enviado: {$v['nombre_cliente']} ({$hora})", 'yellow');
        }

        CLI::write("Total: " . count($visitas) . " recordatorios enviados.", 'green');
    }
}
```

Cron (en servidor de produccion):

```bash
# Recordatorio de visitas diario a las 7:00 AM
0 7 * * * cd /www/wwwroot/phorizontal/enterprisesstph && php spark inspecciones:recordatorio-visitas

# Resumen semanal de contratos (ya existente)
0 8 * * 1 cd /www/wwwroot/phorizontal/enterprisesstph && php spark contratos:resumen-semanal
```

---

## 6. Momento de Pedir Permiso Push

**NO pedir permiso al primer acceso.** Hacerlo al momento correcto:

```javascript
// En layout_pwa.php, DESPUES de que el usuario complete su primera acta exitosamente:

async function pedirPermisoNotificaciones() {
    // Solo si no se ha pedido antes
    if (Notification.permission !== 'default') return;

    // Mostrar contexto antes del prompt del navegador
    const result = await Swal.fire({
        title: 'Activar notificaciones',
        html: `
            <p>Recibe alertas cuando:</p>
            <ul style="text-align:left;">
                <li>Tus actas se sincronicen</li>
                <li>Tengas visitas programadas</li>
                <li>Haya pendientes vencidos</li>
            </ul>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Activar',
        cancelButtonText: 'Ahora no',
        confirmButtonColor: '#bd9751'
    });

    if (result.isConfirmed) {
        await pushManager.init();
    }
}
```

---

## 7. Compatibilidad Push Notifications

| Navegador | Push API | Background notifications |
|-----------|----------|--------------------------|
| Chrome Android | Si | Si (incluso app cerrada) |
| Samsung Internet | Si | Si |
| Firefox Android | Si | Si |
| Safari iOS 16.4+ | Si (parcial) | Solo con app abierta |
| Safari iOS <16.4 | NO | NO |

**Target principal:** Chrome Android — cobertura completa.
**iOS:** Funciona desde iOS 16.4 pero con limitaciones. Fallback: notificaciones in-app cuando abra la PWA.

---

## 8. Rutas Nuevas

```php
// En Routes.php
$routes->group('push', ['filter' => 'auth'], function($routes) {
    $routes->post('subscribe', 'PushNotificationController::subscribe');
    $routes->post('unsubscribe', 'PushNotificationController::unsubscribe');
    $routes->get('vapid-key', 'PushNotificationController::vapidKey');
});
```

---

## 9. Archivos Nuevos

| Archivo | Descripcion |
|---------|-------------|
| `app/Models/PushSubscriptionModel.php` | CRUD de suscripciones push |
| `app/Controllers/PushNotificationController.php` | Endpoints subscribe/unsubscribe/vapid-key |
| `app/Libraries/PushNotificationService.php` | Logica de envio con minishlink/web-push |
| `app/Commands/RecordatorioVisitas.php` | Cron diario: push de visitas programadas |
| `app/Views/emails/acta_visita_notificacion.php` | Template email con PDF adjunto |
| `public/js/inspecciones/push-manager.js` | JS cliente: suscripcion push |
| `app/SQL/migrate_push_subscriptions.php` | Script crear tabla |

### Archivos a modificar

| Archivo | Cambio |
|---------|--------|
| `composer.json` | Agregar `minishlink/web-push` |
| `.env` | Agregar `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` |
| `app/Config/Routes.php` | Agregar grupo `/push/*` |
| `public/sw_inspecciones.js` | Agregar handlers `push` y `notificationclick` |

---

## 10. Resumen del Flujo Completo

```
OFFLINE (campo):
  Consultor llena acta → IndexedDB
      |
      v
ONLINE (automatico):
  Background Sync → POST /acta-visita/sync
      |
      v
SERVIDOR:
  Guarda datos + firmas + fotos → Genera PDF (DOMPDF)
      |
      +→ Web Push al consultor: "Acta sincronizada, PDF listo"
      +→ Email al admin del conjunto: PDF adjunto
      +→ (Si hay proxima reunion) → Se programa recordatorio
      |
      v
CRON DIARIO (7am):
  php spark inspecciones:recordatorio-visitas
      |
      v
  Push: "Visita hoy: Los Tucanes a las 2pm"
```
