<?php
/**
 * Vista Base para Carpetas de Acciones Correctivas
 * Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4
 *
 * Variables requeridas:
 * - $carpeta: datos de la carpeta
 * - $cliente: datos del cliente
 * - $numeral: código del numeral (7.1.1, 7.1.2, etc.)
 * - $titulo_numeral: título descriptivo
 * - $descripcion_numeral: descripción del numeral
 * - $icono: icono Bootstrap Icons
 * - $color: color del tema (danger, warning, info, success)
 * - $hallazgos: array de hallazgos del numeral
 * - $acciones: array de acciones del numeral
 * - $estadisticas: estadísticas del numeral
 */

use App\Models\AccHallazgosModel;
use App\Models\AccAccionesModel;
use App\Services\AccionesCorrectivasService;

// Obtener datos del módulo de acciones correctivas
$hallazgosModel = new AccHallazgosModel();
$accionesModel = new AccAccionesModel();
$service = new AccionesCorrectivasService();

$idCliente = $cliente['id_cliente'];
$numeral = $numeral ?? $carpeta['codigo'] ?? '7.1.1';

// Obtener datos específicos del numeral
$hallazgos = $hallazgosModel->getByCliente($idCliente, $numeral);
$acciones = $accionesModel->getByNumeral($idCliente, $numeral);

// Calcular estadísticas
$totalHallazgos = count($hallazgos);
$totalAcciones = count($acciones);
$accionesAbiertas = count(array_filter($acciones, fn($a) =>
    !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])
));
$accionesVencidas = count(array_filter($acciones, fn($a) =>
    !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
    isset($a['fecha_compromiso']) && $a['fecha_compromiso'] < date('Y-m-d')
));

// Determinar si hay alertas
$tieneAlerta = $accionesVencidas > 0;
?>

<!-- Card Principal del Numeral -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi <?= esc($icono ?? 'bi-exclamation-triangle') ?> text-<?= esc($color ?? 'primary') ?> me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <span class="badge bg-<?= esc($color ?? 'primary') ?> me-2"><?= esc($numeral) ?></span>
                <?php if ($tieneAlerta): ?>
                    <span class="badge bg-danger">
                        <i class="bi bi-exclamation-circle me-1"></i><?= $accionesVencidas ?> vencida<?= $accionesVencidas > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
                <p class="text-muted mb-0 mt-2">
                    <?= esc($descripcion_numeral ?? 'Gestión de acciones correctivas, preventivas y de mejora.') ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url("acciones-correctivas/{$idCliente}/hallazgo/crear/{$numeral}") ?>"
                   class="btn btn-success mb-2">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Hallazgo
                </a>
                <br>
                <a href="<?= base_url("acciones-correctivas/{$idCliente}") ?>"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-grid-3x3-gap me-1"></i>Ver Módulo Completo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- KPIs del Numeral -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-<?= esc($color ?? 'primary') ?>"><?= $totalHallazgos ?></div>
                <small class="text-muted">Hallazgos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-primary"><?= $totalAcciones ?></div>
                <small class="text-muted">Acciones</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-warning"><?= $accionesAbiertas ?></div>
                <small class="text-muted">En Proceso</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 <?= $accionesVencidas > 0 ? 'text-danger' : 'text-success' ?>"><?= $accionesVencidas ?></div>
                <small class="text-muted">Vencidas</small>
            </div>
        </div>
    </div>
</div>

<?php if (!defined('TABLA_SOPORTES_STYLES_LOADED')): ?>
    <?php define('TABLA_SOPORTES_STYLES_LOADED', true); ?>
    <?= view('documentacion/_components/tabla_soportes_styles') ?>
<?php endif; ?>

<?php if (!empty($acciones)): ?>
<!-- Tabla de Acciones del Numeral -->
<div class="card border-0 shadow-lg mb-4 tabla-soportes-card">
    <div class="card-header header-soportes-primary">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="bi bi-list-task"></i>
                </div>
                <div>
                    <span class="header-title">Acciones del Numeral <?= esc($numeral) ?></span>
                    <small class="d-block header-subtitle">Acciones correctivas, preventivas y de mejora</small>
                </div>
            </h6>
            <div class="header-stats">
                <span class="stat-badge">
                    <i class="bi bi-lightning me-1"></i>
                    <span><?= count($acciones) ?></span> accion<?= count($acciones) > 1 ? 'es' : '' ?>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 tabla-soportes-moderna" style="width:100%">
                <thead>
                    <tr>
                        <th>Hallazgo</th>
                        <th>Accion</th>
                        <th style="width:100px">Tipo</th>
                        <th>Responsable</th>
                        <th style="width:130px">Fecha Compromiso</th>
                        <th style="width:120px">Estado</th>
                        <th style="width:80px" class="text-center">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acciones as $accion): ?>
                    <?php
                        $estaVencida = !in_array($accion['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                                       isset($accion['fecha_compromiso']) && $accion['fecha_compromiso'] < date('Y-m-d');

                        $estadoClase = match($accion['estado']) {
                            'borrador' => 'secondary',
                            'asignada' => 'info',
                            'en_ejecucion' => 'primary',
                            'en_revision' => 'warning',
                            'en_verificacion' => 'purple',
                            'cerrada_efectiva' => 'success',
                            'cerrada_no_efectiva' => 'danger',
                            'reabierta' => 'warning',
                            'cancelada' => 'dark',
                            default => 'secondary'
                        };

                        $estadoTexto = match($accion['estado']) {
                            'borrador' => 'Borrador',
                            'asignada' => 'Asignada',
                            'en_ejecucion' => 'En Ejecucion',
                            'en_revision' => 'En Revision',
                            'en_verificacion' => 'Verificando',
                            'cerrada_efectiva' => 'Cerrada',
                            'cerrada_no_efectiva' => 'No Efectiva',
                            'reabierta' => 'Reabierta',
                            'cancelada' => 'Cancelada',
                            default => ucfirst($accion['estado'])
                        };

                        $tipoClase = match($accion['tipo_accion']) {
                            'correctiva' => 'danger',
                            'preventiva' => 'warning',
                            'mejora' => 'success',
                            default => 'secondary'
                        };
                    ?>
                    <tr class="<?= $estaVencida ? 'table-danger' : '' ?>">
                        <td>
                            <div class="nombre-soporte text-truncate" style="max-width: 200px;" title="<?= esc($accion['hallazgo_titulo'] ?? '') ?>">
                                <?= esc($accion['hallazgo_titulo'] ?? 'Sin hallazgo') ?>
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="<?= esc($accion['descripcion_accion']) ?>">
                                <?= esc(substr($accion['descripcion_accion'], 0, 50)) ?><?= strlen($accion['descripcion_accion']) > 50 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $tipoClase ?>">
                                <?= ucfirst($accion['tipo_accion']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="fecha-cell"><?= esc($accion['responsable_usuario_nombre'] ?? $accion['responsable_nombre'] ?? '-') ?></span>
                        </td>
                        <td>
                            <?php if (!empty($accion['fecha_compromiso'])): ?>
                                <span class="fecha-cell <?= $estaVencida ? 'text-danger fw-bold' : '' ?>">
                                    <?= date('d/m/Y', strtotime($accion['fecha_compromiso'])) ?>
                                    <?php if ($estaVencida): ?>
                                        <i class="bi bi-exclamation-triangle-fill ms-1"></i>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $estadoClase ?>"><?= $estadoTexto ?></span>
                        </td>
                        <td class="text-center acciones-cell">
                            <a href="<?= base_url("acciones-correctivas/{$idCliente}/accion/{$accion['id_accion']}") ?>"
                               class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Estado vacio -->
<div class="card border-0 shadow-lg mb-4 tabla-soportes-card">
    <div class="card-header header-soportes-primary">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="bi bi-list-task"></i>
                </div>
                <div>
                    <span class="header-title">Acciones del Numeral <?= esc($numeral) ?></span>
                    <small class="d-block header-subtitle">Acciones correctivas, preventivas y de mejora</small>
                </div>
            </h6>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <h5>No hay acciones registradas</h5>
            <p>
                <a href="<?= base_url("acciones-correctivas/{$idCliente}/hallazgo/crear/{$numeral}") ?>"
                   class="btn btn-outline-success btn-sm mt-2">
                    <i class="bi bi-plus-circle me-1"></i>Registrar Primer Hallazgo
                </a>
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hallazgos Abiertos -->
<?php
$hallazgosAbiertos = array_filter($hallazgos, fn($h) => $h['estado'] !== 'cerrado');
if (!empty($hallazgosAbiertos)):
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0">
        <h6 class="mb-0">
            <i class="bi bi-exclamation-diamond me-2 text-warning"></i>Hallazgos Abiertos
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach (array_slice($hallazgosAbiertos, 0, 5) as $hallazgo): ?>
            <?php
                $severidadClase = match($hallazgo['severidad']) {
                    'critica' => 'danger',
                    'alta' => 'warning',
                    'media' => 'info',
                    'baja' => 'secondary',
                    default => 'secondary'
                };
            ?>
            <a href="<?= base_url("acciones-correctivas/{$idCliente}/hallazgo/{$hallazgo['id_hallazgo']}") ?>"
               class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-<?= $severidadClase ?> me-2"><?= ucfirst($hallazgo['severidad']) ?></span>
                        <strong><?= esc($hallazgo['titulo']) ?></strong>
                        <br>
                        <small class="text-muted">
                            <?= date('d/m/Y', strtotime($hallazgo['fecha_deteccion'])) ?>
                            <?php if ($hallazgo['area_proceso']): ?>
                                • <?= esc($hallazgo['area_proceso']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if (count($hallazgosAbiertos) > 5): ?>
    <div class="card-footer bg-white text-center">
        <a href="<?= base_url("acciones-correctivas/{$idCliente}?numeral={$numeral}") ?>" class="text-decoration-none">
            Ver todos (<?= count($hallazgosAbiertos) ?>)
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>
