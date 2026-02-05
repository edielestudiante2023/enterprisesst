<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admindashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>">Comites</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>">Proceso</a></li>
            <li class="breadcrumb-item active">Censo de Votantes</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Censo de Votantes
            </h2>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= esc($proceso['tipo_comite']) ?></p>
        </div>
        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Proceso
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Estadisticas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <h1 class="display-4 text-primary"><?= $totalVotantes ?></h1>
                    <p class="text-muted mb-0">Total Votantes</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <h1 class="display-4 text-success"><?= $yaVotaron ?></h1>
                    <p class="text-muted mb-0">Ya Votaron</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <?php $participacion = $totalVotantes > 0 ? round(($yaVotaron / $totalVotantes) * 100, 1) : 0; ?>
                    <h1 class="display-4 text-info"><?= $participacion ?>%</h1>
                    <p class="text-muted mb-0">Participacion</p>
                </div>
            </div>
        </div>

        <!-- Formularios y Lista -->
        <div class="col-md-8">
            <!-- Agregar votante individual -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Agregar Votante</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/proceso/agregar-votante') ?>" method="post">
                        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Documento *</label>
                                <input type="text" name="documento_identidad" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombres *</label>
                                <input type="text" name="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" name="apellidos" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="cargo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <input type="text" name="area" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Importar masivo CSV -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar desde CSV</h5>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/plantilla-csv') ?>"
                       class="btn btn-light btn-sm">
                        <i class="bi bi-download me-1"></i>Descargar Plantilla
                    </a>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/proceso/importar-csv') ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">

                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-1"></i>Instrucciones:</h6>
                            <ol class="mb-0 small">
                                <li>Descargue la plantilla CSV haciendo clic en "Descargar Plantilla"</li>
                                <li>Complete los datos de los votantes en Excel o Google Sheets</li>
                                <li>Guarde el archivo como CSV (separado por comas o punto y coma)</li>
                                <li>Suba el archivo aqui</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Archivo CSV *</label>
                            <input type="file" name="archivo_csv" class="form-control" accept=".csv,.txt" required>
                            <div class="form-text">Formatos aceptados: .csv, .txt (max 5MB)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Separador de columnas</label>
                            <select name="separador" class="form-select" style="width: auto;">
                                <option value=";">Punto y coma (;)</option>
                                <option value=",">Coma (,)</option>
                                <option value="\t">Tabulador</option>
                            </select>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="tiene_encabezado" id="tieneEncabezado" checked>
                            <label class="form-check-label" for="tieneEncabezado">
                                La primera fila contiene encabezados (nombres de columnas)
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i>Importar CSV
                        </button>
                    </form>
                </div>
            </div>

            <!-- Importar manual (alternativa rapida) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white" data-bs-toggle="collapse" data-bs-target="#importeManual" style="cursor:pointer;">
                    <h6 class="mb-0">
                        <i class="bi bi-keyboard me-2"></i>Importar Manual (copiar/pegar)
                        <i class="bi bi-chevron-down float-end"></i>
                    </h6>
                </div>
                <div class="collapse" id="importeManual">
                    <div class="card-body">
                        <form action="<?= base_url('comites-elecciones/proceso/importar-votantes') ?>" method="post">
                            <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Lista de Votantes</label>
                                <textarea name="lista_votantes" class="form-control font-monospace" rows="5"
                                          placeholder="documento;nombres;apellidos;email;cargo&#10;123456789;Juan;Perez;juan@email.com;Operario&#10;987654321;Maria;Lopez;maria@email.com;Auxiliar"></textarea>
                                <small class="text-muted">Un votante por linea. Campos separados por punto y coma (;)</small>
                            </div>
                            <button type="submit" class="btn btn-secondary">
                                <i class="bi bi-upload me-1"></i>Importar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Envio masivo de emails -->
            <?php if ($totalVotantes > 0 && $proceso['estado'] === 'votacion'): ?>
            <div class="card border-0 shadow-sm mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>Notificar Votantes por Email</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-0">
                                Enviar enlace de votacion personalizado a todos los votantes pendientes que tengan email registrado.
                            </p>
                            <small class="text-muted">
                                <?php
                                $conEmail = 0;
                                $pendientesConEmail = 0;
                                foreach ($votantes as $v) {
                                    if (!empty($v['email'])) {
                                        $conEmail++;
                                        if (!$v['ha_votado']) $pendientesConEmail++;
                                    }
                                }
                                ?>
                                <i class="bi bi-info-circle me-1"></i>
                                <?= $conEmail ?> votantes con email | <?= $pendientesConEmail ?> pendientes por notificar
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/enviar-enlaces-todos') ?>"
                                  method="post" onsubmit="return confirm('¿Enviar email a todos los votantes pendientes con email?');">
                                <button type="submit" class="btn btn-info text-white" <?= $pendientesConEmail === 0 ? 'disabled' : '' ?>>
                                    <i class="bi bi-send me-1"></i>Enviar a Todos (<?= $pendientesConEmail ?>)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista de votantes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Votantes Registrados (<?= $totalVotantes ?>)</h5>
                    <?php if ($totalVotantes > 0): ?>
                    <span class="badge bg-secondary"><?= $yaVotaron ?> de <?= $totalVotantes ?> han votado</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($votantes)): ?>
                    <div class="alert alert-warning m-3">
                        No hay votantes registrados. Agregue votantes usando los formularios anteriores.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Documento</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Cargo</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($votantes as $v): ?>
                                <tr class="<?= $v['ha_votado'] ? 'table-success' : '' ?>">
                                    <td><code><?= esc($v['documento_identidad']) ?></code></td>
                                    <td><?= esc($v['nombres'] . ' ' . $v['apellidos']) ?></td>
                                    <td>
                                        <?php if (!empty($v['email'])): ?>
                                            <small><?= esc($v['email']) ?></small>
                                            <?php if (!empty($v['email_enviado'])): ?>
                                                <span class="badge bg-info ms-1" title="Email enviado"><i class="bi bi-check"></i></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= esc($v['cargo'] ?? '-') ?></small></td>
                                    <td>
                                        <?php if ($v['ha_votado']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Voto</span>
                                        <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$v['ha_votado']): ?>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Copiar enlace -->
                                            <button type="button" class="btn btn-outline-secondary" title="Copiar enlace"
                                                    onclick="copiarEnlace('<?= base_url('votar/emitir/' . $v['token_acceso']) ?>')">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                            <!-- Enviar email individual -->
                                            <?php if (!empty($v['email'])): ?>
                                            <form action="<?= base_url('comites-elecciones/votante/' . $v['id_votante'] . '/enviar-enlace') ?>"
                                                  method="post" class="d-inline">
                                                <button type="submit" class="btn btn-outline-info" title="Enviar email">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
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
        </div>
    </div>
</div>

<script>
function copiarEnlace(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Enlace copiado al portapapeles');
    });
}
</script>

<?= $this->endSection() ?>
