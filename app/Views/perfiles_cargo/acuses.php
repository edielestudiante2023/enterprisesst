<?= $this->extend('layouts/base') ?>
<?= $this->section('title') ?>Acuses - <?= esc($cargo['nombre_cargo'] ?? '') ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
    $idCliente = (int)$cliente['id_cliente'];
    $idPerfil  = (int)$perfil['id_perfil_cargo'];
?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url("perfiles-cargo/{$idCliente}") ?>">Perfiles de Cargo</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url("perfiles-cargo/{$idCliente}/editor/{$idPerfil}") ?>"><?= esc($cargo['nombre_cargo'] ?? '') ?></a></li>
            <li class="breadcrumb-item active">Acuses y firmas</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-pen me-2 text-primary"></i> Acuses de recibo</h3>
        <a class="btn btn-outline-danger" href="<?= base_url("perfiles-cargo/{$idPerfil}/pdf") ?>" target="_blank">
            <i class="bi bi-file-pdf"></i> Descargar PDF del perfil
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-2 mb-4">
        <div class="col"><div class="card text-center"><div class="card-body py-2">
            <h4 class="mb-0"><?= $stats['total'] ?></h4><small class="text-muted">Total</small></div></div></div>
        <div class="col"><div class="card text-center border-secondary"><div class="card-body py-2">
            <h4 class="mb-0 text-secondary"><?= $stats['pendiente'] ?></h4><small class="text-muted">Pendientes</small></div></div></div>
        <div class="col"><div class="card text-center border-info"><div class="card-body py-2">
            <h4 class="mb-0 text-info"><?= $stats['enviado'] ?></h4><small class="text-muted">Enviados</small></div></div></div>
        <div class="col"><div class="card text-center border-success"><div class="card-body py-2">
            <h4 class="mb-0 text-success"><?= $stats['firmado'] ?></h4><small class="text-muted">Firmados</small></div></div></div>
    </div>

    <!-- Generar acuses -->
    <div class="card mb-4">
        <div class="card-header"><strong>Generar acuses</strong></div>
        <div class="card-body">
            <p class="text-muted small mb-2">Seleccione los trabajadores a los que debe aplicarse este perfil. Se generara un acuse por cada uno con su link individual de firma.</p>
            <?php if (empty($trabajadoresCliente)): ?>
                <div class="alert alert-warning mb-0">
                    No hay trabajadores registrados en el cliente.
                    <a href="<?= base_url("perfiles-cargo/{$idCliente}/trabajadores") ?>">Crear trabajadores primero</a>.
                </div>
            <?php else: ?>
                <div class="small text-muted mb-2">
                    <i class="bi bi-info-circle"></i> Los trabajadores asignados a <strong><?= esc($cargo['nombre_cargo'] ?? '') ?></strong>
                    aparecen primero y <strong>pre-seleccionados</strong>.
                    Puedes marcar adicionales de otros cargos si aplican (suplentes, temporales, etc.).
                </div>
                <div class="table-responsive" style="max-height:350px; overflow-y:auto">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top"><tr>
                            <th style="width:30px"><input type="checkbox" id="chk-todos" title="Seleccionar/deseleccionar todos"></th>
                            <th>Cedula</th><th>Nombre</th><th>Cargo actual</th>
                        </tr></thead>
                        <tbody>
                        <?php
                        $idCargoPerfil = (int)$perfil['id_cargo_cliente'];
                        foreach ($trabajadoresCliente as $t):
                            $idCargoTrab = (int)($t['id_cargo_cliente'] ?? 0);
                            $match = ($idCargoTrab === $idCargoPerfil);
                            $cargoNombre = $cargosMap[$idCargoTrab] ?? null;
                        ?>
                            <tr class="<?= $match ? 'table-success' : '' ?>">
                                <td>
                                    <input type="checkbox" class="chk-trab"
                                           value="<?= $t['id_trabajador'] ?>"
                                           <?= $match ? 'checked' : '' ?>>
                                </td>
                                <td><small><?= esc($t['cedula']) ?></small></td>
                                <td><?= esc($t['nombres'] . ' ' . $t['apellidos']) ?></td>
                                <td>
                                    <?php if ($match): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-lg"></i> <?= esc($cargoNombre) ?></span>
                                    <?php elseif ($cargoNombre): ?>
                                        <small class="text-muted"><?= esc($cargoNombre) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted fst-italic">(sin cargo asignado)</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-primary mt-3" id="btn-generar">
                    <i class="bi bi-plus-circle"></i> Generar acuses seleccionados
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lista de acuses -->
    <div class="card">
        <div class="card-header"><strong>Acuses generados</strong></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Trabajador</th><th>Cedula</th><th>Estado</th><th>Fecha firma</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($acuses)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Aun no se han generado acuses.</td></tr>
                <?php else: foreach ($acuses as $a):
                    $badge = match($a['estado']) {
                        'firmado'   => 'success',
                        'enviado'   => 'info',
                        'rechazado' => 'danger',
                        default     => 'secondary',
                    };
                ?>
                    <tr>
                        <td><?= esc($a['nombre_trabajador']) ?></td>
                        <td><small><?= esc($a['cedula_trabajador']) ?></small></td>
                        <td><span class="badge bg-<?= $badge ?>"><?= esc($a['estado']) ?></span></td>
                        <td><small><?= esc($a['fecha_firma'] ?? '—') ?></small></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary btn-copiar" data-token="<?= esc($a['token_firma']) ?>">
                                <i class="bi bi-clipboard"></i> Copiar link
                            </button>
                            <?php if ($a['estado'] === 'firmado'): ?>
                                <a class="btn btn-sm btn-outline-danger" target="_blank" href="<?= base_url("perfil-acuse/{$a['token_firma']}/pdf") ?>">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ID_PERFIL = <?= $idPerfil ?>;
const URL_BASE  = '<?= base_url('perfiles-cargo') ?>';
const URL_ACUSE_PUB = '<?= base_url('perfil-acuse') ?>';

document.getElementById('chk-todos')?.addEventListener('change', e => {
    document.querySelectorAll('.chk-trab').forEach(c => c.checked = e.target.checked);
});

document.getElementById('btn-generar')?.addEventListener('click', async () => {
    const ids = Array.from(document.querySelectorAll('.chk-trab:checked')).map(c => +c.value);
    if (ids.length === 0) { alert('Seleccione al menos un trabajador.'); return; }
    const r = await fetch(URL_BASE + '/' + ID_PERFIL + '/acuses/generar', {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ids_trabajadores: ids })
    });
    const j = await r.json();
    if (j.ok) { alert(`Creados: ${j.creados}, ya existian: ${j.saltados}`); location.reload(); }
    else alert('Error: ' + (j.error || 'desconocido'));
});

document.querySelectorAll('.btn-copiar').forEach(b => b.addEventListener('click', () => {
    const token = b.dataset.token;
    const url = URL_ACUSE_PUB + '/' + token;
    navigator.clipboard.writeText(url);
    b.innerHTML = '<i class="bi bi-check"></i> Copiado';
    setTimeout(() => b.innerHTML = '<i class="bi bi-clipboard"></i> Copiar link', 2000);
}));
</script>
<?= $this->endSection() ?>
