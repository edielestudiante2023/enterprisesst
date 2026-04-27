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
                    <li class="breadcrumb-item active">Acta <?= esc($acta['numero_acta']) ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">Acta <?= esc($acta['numero_acta']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= esc($comite['tipo_nombre']) ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php
                    $estadoClases = [
                        'borrador' => 'bg-secondary',
                        'pendiente_firma' => 'bg-warning text-dark',
                        'firmada' => 'bg-success',
                        'cerrada' => 'bg-primary'
                    ];
                    ?>
                    <span class="badge <?= $estadoClases[$acta['estado']] ?? 'bg-secondary' ?> fs-6">
                        <?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?>
                    </span>

                    <?php
                        $ctxPrefix = $ctxPrefix ?? 'actas';
                    ?>
                    <?php if ($acta['estado'] === 'borrador' || $acta['estado'] === 'pendiente_firma'): ?>
                    <a href="<?= base_url($ctxPrefix . '/editar/' . $acta['id_acta']) ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                    <?php endif; ?>

                    <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/pdf') ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="bi bi-file-pdf me-1"></i> PDF
                    </a>

                    <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/word') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-word me-1"></i> Word
                    </a>

                    <?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada', 'cerrada'])): ?>
                        <?php if (!empty($solicitudReaperturaPendiente)): ?>
                        <button type="button" class="btn btn-warning btn-sm" disabled>
                            <i class="bi bi-hourglass-split me-1"></i> Reapertura Pendiente
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="solicitarReapertura()">
                            <i class="bi bi-unlock me-1"></i> Solicitar Reabrir
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Encabezado Documento SG-SST según AA_WEB.md -->
    <style>
        .encabezado-formal { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .encabezado-formal td { border: 1px solid #333; vertical-align: middle; }
        .encabezado-logo { width: 150px; padding: 10px; text-align: center; }
        .encabezado-logo img { max-width: 130px; max-height: 70px; object-fit: contain; }
        .encabezado-titulo-central { text-align: center; padding: 0; }
        .encabezado-titulo-central .sistema { font-size: 0.85rem; font-weight: bold; color: #333; padding: 8px 15px; border-bottom: 1px solid #333; }
        .encabezado-titulo-central .nombre-doc { font-size: 0.85rem; font-weight: bold; color: #333; padding: 8px 15px; }
        .encabezado-info { width: 170px; padding: 0; }
        .encabezado-info-table { width: 100%; border-collapse: collapse; }
        .encabezado-info-table td { border: none; border-bottom: 1px solid #333; padding: 3px 8px; font-size: 0.75rem; }
        .encabezado-info-table tr:last-child td { border-bottom: none; }
        .encabezado-info-table .label { font-weight: bold; }
        .encabezado-info-table .valor { color: #333; }
    </style>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <table class="encabezado-formal">
                <tr>
                    <td class="encabezado-logo" rowspan="2">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo">
                        <?php else: ?>
                            <strong style="font-size: 0.7rem;"><?= esc($cliente['nombre_cliente']) ?></strong>
                        <?php endif; ?>
                    </td>
                    <td class="encabezado-titulo-central">
                        <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                    </td>
                    <td class="encabezado-info" rowspan="2">
                        <table class="encabezado-info-table">
                            <tr>
                                <td class="label">Codigo:</td>
                                <td class="valor"><?= esc($acta['codigo_documento'] ?? 'ACT-GEN') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td class="valor"><?= esc($acta['version_documento'] ?? '001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Fecha:</td>
                                <td class="valor"><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <?php
                        $tipoNombre = $comite['tipo_nombre'] ?? 'Reunion SST';
                        $tipoNombreSinAcentos = str_replace(
                            ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
                            ['A','E','I','O','U','A','E','I','O','U','N','N'],
                            $tipoNombre
                        );
                        ?>
                        <div class="nombre-doc">ACTA DE <?= strtoupper($tipoNombreSinAcentos) ?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información de la reunión -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion de la Reunion</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Tipo de Acta</small>
                            <strong><?= ucfirst($acta['tipo_acta']) ?></strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Fecha</small>
                            <strong><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Modalidad</small>
                            <strong><?= ucfirst($acta['modalidad']) ?></strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Horario</small>
                            <strong><?= $acta['hora_inicio'] ?> - <?= $acta['hora_fin'] ?></strong>
                        </div>
                        <?php if (!empty($acta['lugar'])): ?>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Lugar</small>
                            <strong><?= esc($acta['lugar']) ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($acta['enlace_virtual'])): ?>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Enlace Virtual</small>
                            <a href="<?= esc($acta['enlace_virtual']) ?>" target="_blank"><?= esc($acta['enlace_virtual']) ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Orden del día -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Orden del Dia</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <?php
                        $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
                        if (!empty($ordenDia)):
                            foreach ($ordenDia as $punto):
                        ?>
                        <li class="mb-2"><?= esc($punto['tema'] ?? '') ?></li>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </ol>
                </div>
            </div>

            <!-- Desarrollo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Desarrollo de la Reunion</h5>
                </div>
                <div class="card-body">
                    <?php
                    $desarrollo = is_string($acta['desarrollo']) ? json_decode($acta['desarrollo'], true) : $acta['desarrollo'];
                    if (!empty($ordenDia)):
                        foreach ($ordenDia as $punto):
                            $numPunto = $punto['punto'] ?? '';
                            $contenido = $desarrollo[$numPunto] ?? '';
                    ?>
                    <div class="mb-4">
                        <h6 class="fw-bold"><?= $numPunto ?>. <?= esc($punto['tema'] ?? '') ?></h6>
                        <?php if (!empty($contenido)): ?>
                            <p class="mb-0" style="white-space: pre-line;"><?= esc($contenido) ?></p>
                        <?php else: ?>
                            <p class="text-muted mb-0 fst-italic">Sin desarrollo registrado</p>
                        <?php endif; ?>
                    </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>

            <!-- Compromisos -->
            <?php if (!empty($compromisos)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripcion</th>
                                    <th>Responsable</th>
                                    <th>Fecha Limite</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compromisos as $comp): ?>
                                <tr>
                                    <td><?= esc($comp['descripcion']) ?></td>
                                    <td><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                                    <td><?= date('d/m/Y', strtotime($comp['fecha_limite'])) ?></td>
                                    <td>
                                        <?php
                                        $estadoComp = [
                                            'pendiente' => '<span class="badge bg-secondary">Pendiente</span>',
                                            'en_proceso' => '<span class="badge bg-info">En progreso</span>',
                                            'cumplido' => '<span class="badge bg-success">Completado</span>',
                                            'vencido' => '<span class="badge bg-danger">Vencido</span>',
                                            'cancelado' => '<span class="badge bg-dark">Cancelado</span>'
                                        ];
                                        echo $estadoComp[$comp['estado']] ?? '<span class="badge bg-secondary">-</span>';
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

            <!-- Próxima reunión -->
            <?php if (!empty($acta['proxima_reunion_fecha'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Proxima Reunion</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Fecha</small>
                            <strong><?= date('d/m/Y', strtotime($acta['proxima_reunion_fecha'])) ?></strong>
                        </div>
                        <?php if (!empty($acta['proxima_reunion_hora'])): ?>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Hora</small>
                            <strong><?= $acta['proxima_reunion_hora'] ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($acta['proxima_reunion_lugar'])): ?>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Lugar</small>
                            <strong><?= esc($acta['proxima_reunion_lugar']) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Asistentes -->
            <?php
            $asistentesPresentes = array_filter($asistentes, fn($a) => !empty($a['asistio']));
            $totalPresentes = count($asistentesPresentes);
            ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Asistentes</h5>
                    <span class="badge bg-primary" title="Presentes / Total registrados"><?= $totalPresentes ?> / <?= count($asistentes) ?></span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($asistentes as $asist): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($asist['nombre_completo']) ?></strong>
                                <?php if (($asist['tipo_asistente'] ?? '') === 'asesor'): ?>
                                    <span class="badge bg-info">Consultor SST</span>
                                <?php elseif (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                                    <span class="badge bg-warning text-dark"><?= ucfirst($asist['rol_comite']) ?></span>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted"><?= esc($asist['cargo'] ?? '') ?></small>
                            </div>
                            <?php if (!empty($asist['firma_fecha'])): ?>
                                <span class="badge bg-success" title="Firmado: <?= date('d/m/Y H:i', strtotime($asist['firma_fecha'])) ?>">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                            <?php elseif (empty($asist['asistio'])): ?>
                                <span class="badge bg-danger" title="Ausente - no requiere firma">
                                    <i class="bi bi-x-lg"></i> Ausente
                                </span>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-secondary" title="Pendiente de firma">
                                        <i class="bi bi-clock"></i>
                                    </span>
                                    <?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada']) && ($asist['tipo_asistente'] ?? '') !== 'asesor'): ?>
                                        <?php if (!empty($solicitudesMarcarAusente[$asist['id_asistente']] ?? null)): ?>
                                            <span class="badge bg-warning text-dark" title="Solicitud de marcar ausente pendiente de aprobacion">
                                                <i class="bi bi-hourglass-split"></i>
                                            </span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-danger" title="Solicitar marcar como ausente"
                                                    onclick="solicitarMarcarAusente(<?= (int)$asist['id_asistente'] ?>, <?= json_encode($asist['nombre_completo']) ?>)">
                                                <i class="bi bi-person-x-fill"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Estado de firmas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Estado de Firmas</h5>
                </div>
                <div class="card-body">
                    <?php
                    $firmados = count(array_filter($asistentesPresentes, fn($a) => !empty($a['firma_fecha'])));
                    $totalAsist = $totalPresentes;
                    $porcentaje = $totalAsist > 0 ? ($firmados / $totalAsist) * 100 : 0;
                    ?>

                    <div class="text-center mb-3">
                        <h2 class="mb-0"><?= $firmados ?> / <?= $totalAsist ?></h2>
                        <small class="text-muted">firmas completadas</small>
                    </div>

                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar <?= $porcentaje >= 100 ? 'bg-success' : 'bg-warning' ?>" style="width: <?= $porcentaje ?>%">
                            <?= number_format($porcentaje, 0) ?>%
                        </div>
                    </div>

                    <?php if ($porcentaje >= 100): ?>
                    <div class="alert alert-success py-2 mb-0">
                        <i class="bi bi-check-circle me-1"></i> Todas las firmas completadas
                    </div>
                    <?php elseif ($acta['estado'] === 'pendiente_firma'): ?>
                    <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/firmas') ?>" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-bell me-1"></i> Ver estado y reenviar
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información de auditoría -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Auditoria</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Creada por</small>
                        <strong><?= esc($acta['creador_nombre'] ?? 'Sistema') ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Fecha de creacion</small>
                        <strong><?= date('d/m/Y H:i', strtotime($acta['created_at'])) ?></strong>
                    </div>
                    <?php if (!empty($acta['updated_at'])): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Ultima modificacion</small>
                        <strong><?= date('d/m/Y H:i', strtotime($acta['updated_at'])) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($acta['fecha_cierre'])): ?>
                    <div class="mb-0">
                        <small class="text-muted d-block">Fecha de cierre</small>
                        <strong><?= date('d/m/Y H:i', strtotime($acta['fecha_cierre'])) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones -->
            <div class="d-grid gap-2">
                <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Comite
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada', 'cerrada']) && empty($solicitudReaperturaPendiente)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function solicitarReapertura() {
    Swal.fire({
        title: 'Solicitar Reapertura de Acta',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" id="swal_nombre" class="form-control" placeholder="Su nombre completo">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" id="swal_email" class="form-control" placeholder="su@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Cargo</label>
                    <input type="text" id="swal_cargo" class="form-control" placeholder="Su cargo">
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

            fetch('<?= base_url("actas/solicitar-reapertura/" . $acta["id_acta"]) ?>', {
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

<?php if (in_array($acta['estado'], ['pendiente_firma', 'firmada'])): ?>
<?php if (empty($solicitudReaperturaPendiente)): ?><?php /* sweetalert ya cargado arriba */ ?>
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php endif; ?>
<script>
function solicitarMarcarAusente(idAsistente, nombreAsistente) {
    Swal.fire({
        title: 'Marcar como Ausente',
        html: `
            <div class="text-start">
                <div class="alert alert-warning small">
                    <strong>Persona:</strong> ${nombreAsistente}
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Su nombre completo <span class="text-danger">*</span></label>
                    <input type="text" id="swal_ma_nombre" class="form-control" placeholder="Quien solicita el cambio">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Su email <span class="text-danger">*</span></label>
                    <input type="email" id="swal_ma_email" class="form-control" placeholder="su@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Su cargo</label>
                    <input type="text" id="swal_ma_cargo" class="form-control" placeholder="Su cargo">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Justificacion <span class="text-danger">*</span></label>
                    <textarea id="swal_ma_justificacion" class="form-control" rows="4" placeholder="Explique por que esta persona debe figurar como ausente..."></textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    El consultor recibira un email para aprobar. Las firmas existentes de otras personas NO se afectan.
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i> Enviar Solicitud',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f97316',
        width: '550px',
        preConfirm: () => {
            const nombre = document.getElementById('swal_ma_nombre').value.trim();
            const email = document.getElementById('swal_ma_email').value.trim();
            const just = document.getElementById('swal_ma_justificacion').value.trim();
            if (!nombre || !email || !just) {
                Swal.showValidationMessage('Nombre, email y justificacion son obligatorios');
                return false;
            }
            return {
                solicitante_nombre: nombre,
                solicitante_email: email,
                solicitante_cargo: document.getElementById('swal_ma_cargo').value.trim(),
                justificacion: just
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('solicitante_nombre', result.value.solicitante_nombre);
            formData.append('solicitante_email', result.value.solicitante_email);
            formData.append('solicitante_cargo', result.value.solicitante_cargo);
            formData.append('justificacion', result.value.justificacion);

            fetch('<?= base_url("actas/solicitar-marcar-ausente/" . $acta["id_acta"]) ?>/' + idAsistente, {
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
