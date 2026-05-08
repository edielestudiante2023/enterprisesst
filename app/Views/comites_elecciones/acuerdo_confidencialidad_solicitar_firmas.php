<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-shield-lock-fill me-2 text-danger"></i>Acuerdo de Confidencialidad COCOLAB</h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; Periodo <?= (int) ($proceso['anio'] ?? date('Y')) ?> &middot; Codigo FT-SST-018</p>
        </div>
        <div>
            <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acuerdo-confidencialidad/generar') ?>"
               target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye me-1"></i>Vista previa PDF
            </a>
            <?php if (!empty($documento['id_documento'])): ?>
            <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>"
               target="_blank" class="btn btn-outline-info btn-sm">
                <i class="bi bi-clipboard-check me-1"></i>Ver detalle de firmas
            </a>
            <?php endif; ?>
            <a href="<?= base_url('comites-elecciones/' . $proceso['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al proceso
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i><?= session()->getFlashdata('warning') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="alert alert-light border d-flex">
        <i class="bi bi-info-circle text-primary fs-4 me-2"></i>
        <div>
            Cada miembro recibira un correo con un enlace privado para leer el acuerdo y firmarlo electronicamente.
            El documento se considera "completo" cuando todos los miembros activos hayan firmado.
            Base legal: Ley 1010/2006, Resolucion 652/2012 (modificada por Res. 3461/2025).
        </div>
    </div>

    <form method="post" action="<?= base_url('comites-elecciones/acuerdo-confidencialidad/crear-solicitudes') ?>" id="formAcuerdo">
        <?= csrf_field() ?>
        <input type="hidden" name="id_proceso" value="<?= (int) $proceso['id_proceso'] ?>">

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill me-2"></i>Miembros del Comite y Estado de Firma</span>
                <span class="badge bg-light text-primary"><?= count($miembros) ?> miembro(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($miembros)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        No hay miembros activos en este proceso.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="checkAll" title="Seleccionar todos">
                                    </th>
                                    <th>Miembro</th>
                                    <th>Cedula</th>
                                    <th>Email</th>
                                    <th>Representacion</th>
                                    <th>Estado de firma</th>
                                    <th style="width:140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($miembros as $m):
                                    $tipoFirma = 'miembro_' . ($m['id_miembro'] ?? '');
                                    $sol = $solicitudesPorTipo[$tipoFirma] ?? null;
                                    $estado = $sol['estado'] ?? null;
                                    $firmado = ($estado === 'firmado');
                                    $pendiente = ($estado === 'pendiente');
                                    $tieneEmail = !empty($m['email']) && filter_var($m['email'], FILTER_VALIDATE_EMAIL);
                                    $disabled = $firmado || !$tieneEmail;
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input checkMiembro"
                                               name="miembros[]" value="<?= esc($m['id_miembro'] ?? '') ?>"
                                               <?= $disabled ? 'disabled' : '' ?>>
                                    </td>
                                    <td>
                                        <strong><?= esc($m['nombre']) ?></strong>
                                        <?php if (!empty($m['cargo'])): ?>
                                            <div class="small text-muted"><?= esc($m['cargo']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= esc($m['cedula']) ?: '<span class="text-muted">-</span>' ?></td>
                                    <td class="small">
                                        <?php if ($tieneEmail): ?>
                                            <?= esc($m['email']) ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Sin email valido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($m['representacion'] ?? '') === 'empleador' ? 'primary' : 'success' ?>">
                                            <?= esc(ucfirst($m['representacion'] ?? '-')) ?>
                                        </span>
                                        <span class="badge bg-info-subtle text-info-emphasis"><?= esc(ucfirst($m['tipo_plaza'] ?? '')) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($firmado): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Firmado</span>
                                            <?php if (!empty($sol['fecha_firma'])): ?>
                                                <div class="small text-muted"><?= esc(date('d/m/Y H:i', strtotime($sol['fecha_firma']))) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($pendiente): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pendiente</span>
                                            <?php if (!empty($sol['fecha_expiracion'])): ?>
                                                <div class="small text-muted">Expira: <?= esc(date('d/m/Y', strtotime($sol['fecha_expiracion']))) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Sin solicitud</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sol && !empty($sol['id_solicitud'])): ?>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($pendiente): ?>
                                                <form method="post" action="<?= base_url('firma/reenviar/' . (int) $sol['id_solicitud']) ?>"
                                                      class="d-inline" onsubmit="return confirm('Reenviar correo de firma a <?= esc($m['email']) ?>?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-outline-primary" title="Reenviar email">
                                                        <i class="bi bi-envelope-arrow-up"></i>
                                                    </button>
                                                </form>
                                                <form method="post" action="<?= base_url('firma/cancelar/' . (int) $sol['id_solicitud']) ?>"
                                                      class="d-inline" onsubmit="return confirm('Cancelar esta solicitud? El miembro NO podra firmar con el enlace actual.');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-outline-danger" title="Cancelar solicitud">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <a href="<?= base_url('firma/audit-log/' . (int) $sol['id_solicitud']) ?>"
                                                   target="_blank" class="btn btn-outline-info" title="Ver bitacora">
                                                    <i class="bi bi-list-task"></i>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="small text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-success" <?= empty($miembros) ? 'disabled' : '' ?>>
                <i class="bi bi-envelope-check me-1"></i>Enviar enlaces de firma a seleccionados
            </button>

            <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acuerdo-confidencialidad/subir-reportlist') ?>"
               class="btn btn-outline-secondary"
               onclick="return confirm('Esto generara el PDF actual y lo subira al reportList del cliente. Continuar?');">
                <i class="bi bi-cloud-upload me-1"></i>Subir PDF actual al reportList
            </a>

            <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acuerdo-confidencialidad/descargar') ?>"
               class="btn btn-outline-primary">
                <i class="bi bi-download me-1"></i>Descargar PDF
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.checkMiembro:not(:disabled)').forEach(cb => cb.checked = this.checked);
});
document.getElementById('formAcuerdo')?.addEventListener('submit', function(e) {
    const seleccionados = document.querySelectorAll('.checkMiembro:checked').length;
    if (seleccionados === 0) {
        e.preventDefault();
        alert('Selecciona al menos un miembro.');
    }
});
</script>

<?= $this->endSection() ?>
