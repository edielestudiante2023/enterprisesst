<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>">Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>"><?= esc($comite['codigo']) ?></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta']) ?>">Acta <?= esc($acta['numero_acta']) ?></a></li>
                    <li class="breadcrumb-item active">Estado de Firmas</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">Estado de Firmas - Acta <?= esc($acta['numero_acta']) ?></h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= esc($comite['tipo_nombre']) ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Resumen -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?php
                    $firmados = count(array_filter($asistentes, fn($a) => !empty($a['firma_fecha'])));
                    $totalAsist = count($asistentes);
                    $porcentaje = $totalAsist > 0 ? ($firmados / $totalAsist) * 100 : 0;
                    ?>

                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h2 class="text-primary mb-0"><?= $totalAsist ?></h2>
                                <small class="text-muted">Total Asistentes</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h2 class="text-success mb-0"><?= $firmados ?></h2>
                                <small class="text-muted">Han Firmado</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h2 class="text-warning mb-0"><?= $totalAsist - $firmados ?></h2>
                                <small class="text-muted">Pendientes</small>
                            </div>
                        </div>
                    </div>

                    <div class="progress mt-4" style="height: 30px;">
                        <div class="progress-bar <?= $porcentaje >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $porcentaje ?>%">
                            <?= number_format($porcentaje, 0) ?>% completado
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de firmantes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Detalle de Firmantes</h5>
                    <?php if ($acta['estado'] === 'pendiente_firma' && ($totalAsist - $firmados) > 0): ?>
                    <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/reenviar-todos') ?>" method="post" class="d-inline">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-envelope me-1"></i> Reenviar a pendientes
                        </button>
                    </form>
                    <?php elseif (in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
                    <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/enviar-firmas') ?>" method="post" class="d-inline">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-send me-1"></i> Enviar a Firmas
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
                    <div class="alert alert-info m-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Acta en edición:</strong> Para enviar solicitudes de firma, primero debe cerrar el acta usando el botón "Enviar a Firmas".
                    </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cargo</th>
                                    <th>Estado</th>
                                    <th>Fecha Firma</th>
                                    <th>IP</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asistentes as $asist): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($asist['nombre_completo']) ?></strong>
                                        <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                                            <br><span class="badge bg-warning text-dark"><?= ucfirst($asist['rol_comite']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($asist['cargo'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($asist['firma_fecha'])): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Firmado</span>
                                        <?php elseif (!empty($asist['notificacion_enviada_at']) || !empty($asist['recordatorio_enviado_at'])): ?>
                                            <span class="badge bg-info"><i class="bi bi-envelope me-1"></i>Enviado</span>
                                        <?php elseif (!$asist['asistio']): ?>
                                            <span class="badge bg-secondary"><i class="bi bi-x-lg me-1"></i>No asistió</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pendiente envío</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($asist['firma_fecha'])): ?>
                                            <?= date('d/m/Y H:i', strtotime($asist['firma_fecha'])) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($asist['firma_ip'])): ?>
                                            <small class="text-muted"><?= esc($asist['firma_ip']) ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($asist['firma_fecha']) && !empty($asist['firma_imagen'])): ?>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="verFirma('<?= $asist['id_asistente'] ?>', '<?= esc($asist['nombre_completo']) ?>')" title="Ver firma">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php elseif ($asist['asistio'] && empty($asist['firma_fecha']) && $acta['estado'] === 'pendiente_firma'): ?>
                                        <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/reenviar/' . $asist['id_asistente']) ?>" method="post" class="d-inline">
                                            <button type="submit" class="btn btn-outline-primary btn-sm" title="Enviar/Reenviar email de firma">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        </form>
                                        <?php elseif (!$asist['asistio']): ?>
                                        <small class="text-muted">N/A</small>
                                        <?php elseif (in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
                                        <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Historial de notificaciones -->
            <?php if (!empty($notificaciones)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Notificaciones</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Destinatario</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notificaciones as $notif): ?>
                                <tr>
                                    <td><small><?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?></small></td>
                                    <td><small><?= esc($notif['email_destino']) ?></small></td>
                                    <td><small><?= esc($notif['tipo']) ?></small></td>
                                    <td>
                                        <?php
                                        $estadoNotif = [
                                            'pendiente' => '<span class="badge bg-secondary">Pendiente</span>',
                                            'enviada' => '<span class="badge bg-success">Enviada</span>',
                                            'error' => '<span class="badge bg-danger">Error</span>'
                                        ];
                                        echo $estadoNotif[$notif['estado']] ?? '<span class="badge bg-secondary">-</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info del acta -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Informacion del Acta</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Numero</small>
                        <strong><?= esc($acta['numero_acta']) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Fecha de reunion</small>
                        <strong><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Estado</small>
                        <?php
                        $estadoClases = [
                            'borrador' => 'bg-secondary',
                            'pendiente_firma' => 'bg-warning text-dark',
                            'firmada' => 'bg-success',
                            'cerrada' => 'bg-primary'
                        ];
                        ?>
                        <span class="badge <?= $estadoClases[$acta['estado']] ?? 'bg-secondary' ?>">
                            <?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?>
                        </span>
                    </div>
                    <?php if (!empty($acta['enviada_firma_at'])): ?>
                    <div class="mb-0">
                        <small class="text-muted d-block">Enviada a firma</small>
                        <strong><?= date('d/m/Y H:i', strtotime($acta['enviada_firma_at'])) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones -->
            <div class="d-grid gap-2">
                <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta']) ?>" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> Ver Acta
                </a>
                <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Comite
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal ver firma -->
<div class="modal fade" id="modalVerFirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Firma de <span id="nombreFirmante"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagenFirma" src="" alt="Firma" class="img-fluid border rounded" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>

<script>
function verFirma(idAsistente, nombre) {
    document.getElementById('nombreFirmante').textContent = nombre;
    document.getElementById('imagenFirma').src = '<?= base_url('actas/firma-imagen/') ?>' + idAsistente;
    new bootstrap.Modal(document.getElementById('modalVerFirma')).show();
}
</script>

<?= $this->endSection() ?>
