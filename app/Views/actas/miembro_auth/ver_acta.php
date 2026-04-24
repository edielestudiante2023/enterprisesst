<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/dashboard') ?>">Mis Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/comite/' . $comite['id_comite']) ?>"><?= esc($comite['tipo_nombre']) ?></a></li>
                    <li class="breadcrumb-item active">Acta <?= esc($acta['numero_acta']) ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Acta <?= esc($acta['numero_acta']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($comite['tipo_nombre']) ?> - <?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if (in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
                    <a href="<?= base_url('miembro/actas/editar/' . $acta['id_acta']) ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($miembroEnComite['puede_cerrar_actas']) && in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCerrarActa">
                        <i class="bi bi-send me-1"></i>Enviar a Firmas
                    </button>
                    <?php endif; ?>
                    <?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada', 'cerrada'])): ?>
                    <a href="<?= base_url('miembro/acta/' . $acta['id_acta'] . '/firmas') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pen me-1"></i>Estado de Firmas
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url('miembro/acta/' . $acta['id_acta'] . '/pdf') ?>" class="btn btn-outline-danger" target="_blank">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('miembro/acta/' . $acta['id_acta'] . '/word') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-file-word me-1"></i>Word
                    </a>
                    <?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada', 'cerrada'])): ?>
                    <button type="button" class="btn btn-outline-warning" onclick="solicitarReapertura()">
                        <i class="bi bi-unlock me-1"></i>Solicitar Reabrir
                    </button>
                    <?php endif; ?>
                    <a href="<?= base_url('miembro/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información de la reunión -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion de la Reunion</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Fecha:</td>
                            <td><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Hora:</td>
                            <td><?= date('H:i', strtotime($acta['hora_inicio'])) ?> - <?= date('H:i', strtotime($acta['hora_fin'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lugar:</td>
                            <td><?= esc($acta['lugar'] ?? 'No especificado') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Modalidad:</td>
                            <td><?= ucfirst($acta['modalidad']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tipo:</td>
                            <td><span class="badge bg-secondary"><?= ucfirst($acta['tipo_acta']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Estado:</td>
                            <td>
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
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Asistentes -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i>Asistentes</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cargo</th>
                                    <th>Rol</th>
                                    <th>Asistio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asistentes as $asist): ?>
                                <tr>
                                    <td><?= esc($asist['nombre_completo']) ?></td>
                                    <td><?= esc($asist['cargo'] ?? '-') ?></td>
                                    <td>
                                        <?php if (($asist['tipo_asistente'] ?? '') === 'asesor'): ?>
                                            <span class="badge bg-info">Consultor SST</span>
                                        <?php elseif (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                                            <span class="badge bg-warning text-dark"><?= ucfirst($asist['rol_comite']) ?></span>
                                        <?php else: ?>
                                            Miembro
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($asist['asistio']): ?>
                                            <span class="badge bg-success">Si</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orden del día y desarrollo -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-list-ol me-2"></i>Orden del Dia y Desarrollo</h6>
                </div>
                <div class="card-body">
                    <?php
                    $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
                    $desarrollo = is_string($acta['desarrollo']) ? json_decode($acta['desarrollo'], true) : $acta['desarrollo'];

                    if (!empty($ordenDia)):
                        foreach ($ordenDia as $punto):
                            $numPunto = $punto['punto'] ?? '';
                            $contenido = $desarrollo[$numPunto] ?? '';
                    ?>
                    <div class="mb-3 p-3 bg-light rounded">
                        <h6 class="text-primary mb-2"><?= $numPunto ?>. <?= esc($punto['tema'] ?? '') ?></h6>
                        <?php if (!empty($contenido)): ?>
                            <p class="mb-0" style="white-space: pre-line;"><?= esc($contenido) ?></p>
                        <?php else: ?>
                            <p class="mb-0 text-muted fst-italic">Sin desarrollo registrado</p>
                        <?php endif; ?>
                    </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <p class="text-muted">No hay orden del dia registrado</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Compromisos -->
    <?php if (!empty($compromisos)): ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Compromiso</th>
                                    <th>Responsable</th>
                                    <th>Fecha Limite</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compromisos as $i => $comp): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($comp['descripcion']) ?></td>
                                    <td><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                                    <td><?= !empty($comp['fecha_limite']) ? date('d/m/Y', strtotime($comp['fecha_limite'])) : '-' ?></td>
                                    <td>
                                        <?php
                                        $estadoBadges = [
                                            'pendiente' => 'bg-secondary',
                                            'en_proceso' => 'bg-info',
                                            'cumplido' => 'bg-success',
                                            'vencido' => 'bg-danger',
                                            'cancelado' => 'bg-dark'
                                        ];
                                        ?>
                                        <span class="badge <?= $estadoBadges[$comp['estado']] ?? 'bg-secondary' ?>">
                                            <?= ucfirst(str_replace('_', ' ', $comp['estado'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($miembroEnComite['puede_cerrar_actas']) && in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
<!-- Modal Cerrar Acta -->
<div class="modal fade" id="modalCerrarActa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Cerrar Acta y Enviar a Firmas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Al cerrar el acta:
                    <ul class="mb-0 mt-2">
                        <li>Se verificara que haya quorum</li>
                        <li>Se enviaran solicitudes de firma a todos los asistentes</li>
                        <li>El acta no podra ser editada</li>
                    </ul>
                </div>
                <p>¿Esta seguro de cerrar el acta <strong><?= esc($acta['numero_acta']) ?></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= base_url('miembro/acta/' . $acta['id_acta'] . '/enviar-firmas') ?>" method="POST" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-1"></i>Enviar a Firmas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada', 'cerrada'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function solicitarReapertura() {
    Swal.fire({
        title: 'Solicitar Reapertura de Acta',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" id="swal_nombre" class="form-control" placeholder="Su nombre completo" value="<?= esc($miembro['nombre_completo'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" id="swal_email" class="form-control" placeholder="su@email.com" value="<?= esc($miembro['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Cargo</label>
                    <input type="text" id="swal_cargo" class="form-control" placeholder="Su cargo" value="<?= esc($miembro['cargo'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Justificacion <span class="text-danger">*</span></label>
                    <textarea id="swal_justificacion" class="form-control" rows="4" placeholder="Explique por que debe reabrirse esta acta..."></textarea>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Al aprobar la reapertura, las firmas existentes seran invalidadas y el acta volvera a estado de edicion.
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i> Enviar Solicitud',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        width: '550px',
        preConfirm: () => {
            const nombre = document.getElementById('swal_nombre').value.trim();
            const email = document.getElementById('swal_email').value.trim();
            const justificacion = document.getElementById('swal_justificacion').value.trim();

            if (!nombre || !email || !justificacion) {
                Swal.showValidationMessage('Nombre, email y justificacion son obligatorios');
                return false;
            }

            return {
                solicitante_nombre: nombre,
                solicitante_email: email,
                solicitante_cargo: document.getElementById('swal_cargo').value.trim(),
                justificacion: justificacion
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('solicitante_nombre', result.value.solicitante_nombre);
            formData.append('solicitante_email', result.value.solicitante_email);
            formData.append('solicitante_cargo', result.value.solicitante_cargo);
            formData.append('justificacion', result.value.justificacion);

            fetch('<?= base_url("miembro/acta/" . $acta["id_acta"] . "/solicitar-reapertura") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Solicitud Enviada',
                        text: data.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexion. Intente nuevamente.' });
            });
        }
    });
}
</script>
<?php endif; ?>

<?= $this->endSection() ?>
