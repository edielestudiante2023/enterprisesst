<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Etapas de Inducción - <?= esc($cliente['nombre_cliente']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultant/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('documentacion/dashboard/' . $cliente['id_cliente']) ?>">Documentación</a></li>
            <li class="breadcrumb-item active">Etapas de Inducción</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-person-badge text-primary me-2"></i>
                Etapas del Proceso de Inducción
            </h2>
            <p class="text-muted mb-0">
                <?= esc($cliente['nombre_cliente']) ?> - Año <?= $anio ?>
            </p>
        </div>
        <div>
            <?php if (empty($etapas)): ?>
                <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/generar") ?>"
                   class="btn btn-success">
                    <i class="bi bi-magic me-1"></i>Generar Etapas con IA
                </a>
            <?php elseif (!$todasAprobadas): ?>
                <form action="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/aprobar") ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="anio" value="<?= $anio ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-all me-1"></i>Aprobar Todas
                    </button>
                </form>
                <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/generar") ?>"
                   class="btn btn-outline-warning">
                    <i class="bi bi-arrow-repeat me-1"></i>Regenerar
                </a>
            <?php else: ?>
                <span class="badge bg-success fs-6 py-2 px-3">
                    <i class="bi bi-check-circle me-1"></i>Todas las etapas aprobadas
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-primary"><?= $stats['total'] ?></div>
                    <div class="text-muted">Etapas Definidas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-success"><?= $stats['aprobadas'] ?></div>
                    <div class="text-muted">Etapas Aprobadas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-info"><?= $totalTemas ?></div>
                    <div class="text-muted">Temas Totales</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <?php
                    $duracionTotal = 0;
                    foreach ($etapas as $e) {
                        $duracionTotal += $e['duracion_estimada_minutos'] ?? 0;
                    }
                    ?>
                    <div class="fs-1 fw-bold text-warning"><?= round($duracionTotal / 60, 1) ?>h</div>
                    <div class="text-muted">Duración Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Etapas -->
    <?php if (!empty($etapas)): ?>
    <div class="row g-4">
        <?php foreach ($etapas as $etapa): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-3" style="width: 40px; height: 40px; line-height: 32px; font-size: 1.2rem;">
                            <?= $etapa['numero_etapa'] ?>
                        </span>
                        <div>
                            <h5 class="mb-0"><?= esc($etapa['nombre_etapa']) ?></h5>
                            <small class="text-muted">
                                <?= $etapa['cantidad_temas'] ?> temas |
                                <?= $etapa['duracion_estimada_minutos'] ?> min |
                                Responsable: <?= esc($etapa['responsable_sugerido']) ?>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($etapa['es_personalizado']): ?>
                            <span class="badge bg-info">
                                <i class="bi bi-magic"></i> Personalizado
                            </span>
                        <?php endif; ?>
                        <?php if ($etapa['estado'] === 'aprobado'): ?>
                            <span class="badge bg-success me-2">
                                <i class="bi bi-check-circle"></i> Aprobado
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-desaprobar-etapa"
                                    data-id="<?= $etapa['id_etapa'] ?>"
                                    title="Desaprobar para editar">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark me-2">
                                <i class="bi bi-clock"></i> Borrador
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-success btn-aprobar-etapa"
                                    data-id="<?= $etapa['id_etapa'] ?>"
                                    title="Aprobar esta etapa">
                                <i class="bi bi-check-lg"></i> Aprobar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($etapa['descripcion_etapa'])): ?>
                        <p class="text-muted mb-3"><?= esc($etapa['descripcion_etapa']) ?></p>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Tema</th>
                                    <th>Descripción</th>
                                    <th style="width: 100px;">Origen</th>
                                    <?php if ($etapa['estado'] !== 'aprobado'): ?>
                                    <th style="width: 80px;" class="text-center">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($etapa['temas_decodificados'] as $idx => $tema): ?>
                                <tr data-tema-idx="<?= $idx ?>">
                                    <td class="text-muted"><?= $idx + 1 ?></td>
                                    <td>
                                        <strong><?= esc($tema['nombre']) ?></strong>
                                        <?php if (!empty($tema['es_personalizado']) && $tema['es_personalizado']): ?>
                                            <i class="bi bi-stars text-warning" title="Tema personalizado"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= esc($tema['descripcion'] ?? '') ?></td>
                                    <td>
                                        <?php
                                        $origen = $tema['origen'] ?? 'base';
                                        $badgeClass = match($origen) {
                                            'peligro_identificado' => 'bg-danger',
                                            'organo_participacion' => 'bg-info',
                                            'estructura_empresa' => 'bg-warning text-dark',
                                            'alto_riesgo' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $origenLabel = match($origen) {
                                            'peligro_identificado' => 'Peligro',
                                            'organo_participacion' => 'Órgano',
                                            'estructura_empresa' => 'Estructura',
                                            'alto_riesgo' => 'Alto Riesgo',
                                            default => 'Base'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $origenLabel ?></span>
                                    </td>
                                    <?php if ($etapa['estado'] !== 'aprobado'): ?>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-eliminar-tema"
                                                data-etapa-id="<?= $etapa['id_etapa'] ?>"
                                                data-tema-idx="<?= $idx ?>"
                                                data-tema-nombre="<?= esc($tema['nombre']) ?>"
                                                title="Eliminar tema">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($etapa['estado'] !== 'aprobado'): ?>
                    <div class="mt-3 pt-3 border-top">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary btn-agregar-tema"
                                data-etapa-id="<?= $etapa['id_etapa'] ?>"
                                data-etapa-nombre="<?= esc($etapa['nombre_etapa']) ?>">
                            <i class="bi bi-plus-lg me-1"></i>Agregar Tema
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Acciones después de aprobar -->
    <?php if ($todasAprobadas): ?>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5 class="mb-3">
                <i class="bi bi-arrow-right-circle text-success me-2"></i>
                Siguiente Paso: Generar Plan de Trabajo
            </h5>
            <p class="text-muted">
                Las etapas están aprobadas. Ahora puede generar las actividades del Plan de Trabajo Anual
                basadas en estas etapas.
            </p>
            <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/checklist-pta") ?>"
               class="btn btn-success">
                <i class="bi bi-clipboard-check me-1"></i>Generar Actividades del PTA
            </a>
            <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/generar-indicadores") ?>"
               class="btn btn-outline-primary ms-2">
                <i class="bi bi-graph-up me-1"></i>Generar Indicadores
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Sin etapas -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No hay etapas definidas</h4>
            <p class="text-muted">
                Genere las etapas del proceso de inducción basándose en los peligros
                identificados y el contexto del cliente.
            </p>
            <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/generar") ?>"
               class="btn btn-success btn-lg">
                <i class="bi bi-magic me-1"></i>Generar Etapas con IA
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Agregar Tema -->
<div class="modal fade" id="modalAgregarTema" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Tema
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="agregarTemaEtapaId">
                <p class="text-muted mb-3">Agregando tema a: <strong id="agregarTemaEtapaNombre"></strong></p>

                <div class="mb-3">
                    <label class="form-label">Nombre del Tema <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nuevoTemaNombre" required
                           placeholder="Ej: Manejo seguro de herramientas">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" id="nuevoTemaDescripcion" rows="2"
                              placeholder="Descripción breve del tema"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarAgregarTema">
                    <i class="bi bi-plus-lg me-1"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    // Aprobar etapa individual
    document.querySelectorAll('.btn-aprobar-etapa').forEach(btn => {
        btn.addEventListener('click', function() {
            const idEtapa = this.dataset.id;
            const button = this;

            if (!confirm('¿Aprobar esta etapa?')) return;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`<?= base_url('induccion-etapas/etapa') ?>/${idEtapa}/aprobar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ [csrfToken]: csrfHash })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.mensaje || 'Error al aprobar la etapa');
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-check-lg"></i> Aprobar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-check-lg"></i> Aprobar';
            });
        });
    });

    // Eliminar tema individual
    document.querySelectorAll('.btn-eliminar-tema').forEach(btn => {
        btn.addEventListener('click', function() {
            const idEtapa = this.dataset.etapaId;
            const temaIdx = this.dataset.temaIdx;
            const temaNombre = this.dataset.temaNombre;
            const button = this;
            const row = button.closest('tr');

            if (!confirm(`¿Eliminar el tema "${temaNombre}"?\n\nEsta acción no se puede deshacer.`)) return;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`<?= base_url('induccion-etapas/etapa') ?>/${idEtapa}/tema/${temaIdx}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ [csrfToken]: csrfHash })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animar y eliminar fila
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Renumerar filas restantes
                        const tbody = document.querySelector(`[data-etapa-id="${idEtapa}"]`)?.closest('table')?.querySelector('tbody');
                        if (tbody) {
                            tbody.querySelectorAll('tr').forEach((tr, idx) => {
                                tr.querySelector('td:first-child').textContent = idx + 1;
                            });
                        }
                    }, 300);
                } else {
                    alert(data.mensaje || 'Error al eliminar el tema');
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-trash"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-trash"></i>';
            });
        });
    });

    // Agregar tema - abrir modal
    const modalAgregarTema = new bootstrap.Modal(document.getElementById('modalAgregarTema'));

    document.querySelectorAll('.btn-agregar-tema').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('agregarTemaEtapaId').value = this.dataset.etapaId;
            document.getElementById('agregarTemaEtapaNombre').textContent = this.dataset.etapaNombre;
            document.getElementById('nuevoTemaNombre').value = '';
            document.getElementById('nuevoTemaDescripcion').value = '';
            modalAgregarTema.show();
        });
    });

    // Confirmar agregar tema
    document.getElementById('btnConfirmarAgregarTema').addEventListener('click', function() {
        const idEtapa = document.getElementById('agregarTemaEtapaId').value;
        const nombre = document.getElementById('nuevoTemaNombre').value.trim();
        const descripcion = document.getElementById('nuevoTemaDescripcion').value.trim();
        const button = this;

        if (!nombre) {
            alert('El nombre del tema es obligatorio');
            return;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Agregando...';

        fetch(`<?= base_url('induccion-etapas/etapa') ?>/${idEtapa}/tema`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                [csrfToken]: csrfHash,
                nombre: nombre,
                descripcion: descripcion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalAgregarTema.hide();
                location.reload();
            } else {
                alert(data.mensaje || 'Error al agregar el tema');
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Agregar';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Agregar';
        });
    });

    // Desaprobar etapa
    document.querySelectorAll('.btn-desaprobar-etapa').forEach(btn => {
        btn.addEventListener('click', function() {
            const idEtapa = this.dataset.id;
            const button = this;

            if (!confirm('¿Desaprobar esta etapa para poder editarla?')) return;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`<?= base_url('induccion-etapas/etapa') ?>/${idEtapa}/desaprobar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ [csrfToken]: csrfHash })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.mensaje || 'Error al desaprobar la etapa');
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-pencil"></i> Editar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-pencil"></i> Editar';
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
