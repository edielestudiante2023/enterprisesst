<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Matriz Cargo-Competencia - <?= esc($cliente['nombre_cliente'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultantDashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url("diccionario-competencias/{$cliente['id_cliente']}") ?>">Diccionario</a></li>
            <li class="breadcrumb-item active">Matriz</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0"><i class="fas fa-table me-2 text-warning"></i> Matriz cargo-competencia</h3>
            <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong></small>
        </div>
        <div>
            <label class="form-label small mb-0 me-1">Cargo:</label>
            <select id="selectCargo" class="form-select form-select-sm d-inline-block" style="width:auto;min-width:260px">
                <option value="">-- selecciona un cargo --</option>
                <?php foreach ($cargos as $cg): ?>
                    <option value="<?= (int)$cg['id'] ?>"><?= esc($cg['nombre_cargo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (empty($cargos)): ?>
        <div class="alert alert-warning">
            Este cliente aun no tiene cargos definidos. Crea cargos desde el modulo de Maestros del cliente.
        </div>
    <?php elseif (empty($competencias)): ?>
        <div class="alert alert-warning">
            El cliente no tiene competencias en su diccionario.
        </div>
    <?php else: ?>

    <div id="matrizPanel" class="card" style="display:none">
        <div class="card-body">
            <div class="mb-2">
                <small class="text-muted">Define el <strong>nivel requerido (1-5)</strong> de cada competencia para el cargo seleccionado.
                Dejar el nivel en blanco o 0 elimina la asignacion.</small>
            </div>
            <?php foreach ($familias as $famKey => $famLabel):
                $comps = array_values(array_filter($competencias, fn($c) => ($c['familia'] ?? '') === $famKey));
                if (empty($comps)) continue;
            ?>
            <div class="mb-3">
                <h6 class="text-uppercase text-muted small fw-bold mb-2"><?= esc($famLabel) ?></h6>
                <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Competencia</th>
                            <th style="width:120px">Nivel</th>
                            <th>Observacion</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comps as $c): ?>
                        <tr data-id-competencia="<?= (int)$c['id_competencia'] ?>">
                            <td><?= (int)$c['numero'] ?></td>
                            <td><?= esc($c['nombre']) ?></td>
                            <td>
                                <select class="form-select form-select-sm nivel-input">
                                    <option value="0">-</option>
                                    <?php for ($n = 1; $n <= 5; $n++): ?>
                                        <option value="<?= $n ?>"><?= $n ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm obs-input" placeholder="Opcional">
                            </td>
                            <td class="text-center status-cell">
                                <i class="far fa-circle text-muted"></i>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(function() {
    const idCliente = <?= (int)$cliente['id_cliente'] ?>;
    const baseUrl = '<?= base_url("diccionario-competencias/{$cliente['id_cliente']}/matriz") ?>';
    // Asignaciones precargadas: { id_cargo: { id_competencia: {nivel_requerido, observacion} } }
    const asignaciones = <?= json_encode($asignaciones ?? (object)[], JSON_UNESCAPED_UNICODE) ?>;
    const csrfName  = '<?= csrf_token() ?>';
    const csrfHash  = '<?= csrf_hash() ?>';

    const sel = document.getElementById('selectCargo');
    const panel = document.getElementById('matrizPanel');
    if (!sel || !panel) return;

    function setStatus(tr, state) {
        const cell = tr.querySelector('.status-cell');
        if (!cell) return;
        if (state === 'ok')   cell.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        else if (state === 'err') cell.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        else if (state === 'loading') cell.innerHTML = '<i class="fas fa-spinner fa-spin text-muted"></i>';
        else cell.innerHTML = '<i class="far fa-circle text-muted"></i>';
    }

    function cargar(idCargo) {
        const cargoAsign = asignaciones[idCargo] || {};
        panel.querySelectorAll('tr[data-id-competencia]').forEach(tr => {
            const idComp = tr.dataset.idCompetencia;
            const nivelSel = tr.querySelector('.nivel-input');
            const obsInp = tr.querySelector('.obs-input');
            if (cargoAsign[idComp]) {
                nivelSel.value = String(cargoAsign[idComp].nivel_requerido || 0);
                obsInp.value = cargoAsign[idComp].observacion || '';
                setStatus(tr, 'ok');
            } else {
                nivelSel.value = '0';
                obsInp.value = '';
                setStatus(tr, '');
            }
        });
    }

    async function guardarFila(tr, idCargo) {
        const idComp = tr.dataset.idCompetencia;
        const nivel = parseInt(tr.querySelector('.nivel-input').value || '0', 10);
        const obs = tr.querySelector('.obs-input').value || '';
        setStatus(tr, 'loading');

        try {
            if (nivel === 0) {
                const fd = new FormData();
                fd.append(csrfName, csrfHash);
                fd.append('id_cargo_cliente', idCargo);
                fd.append('id_competencia', idComp);
                const r = await fetch(baseUrl + '/eliminar', { method: 'POST', body: fd });
                const j = await r.json();
                if (!j.ok) throw new Error(j.error || 'Error');
                if (asignaciones[idCargo]) delete asignaciones[idCargo][idComp];
                setStatus(tr, '');
            } else {
                const fd = new FormData();
                fd.append(csrfName, csrfHash);
                fd.append('id_cargo_cliente', idCargo);
                fd.append('id_competencia', idComp);
                fd.append('nivel_requerido', String(nivel));
                fd.append('observacion', obs);
                const r = await fetch(baseUrl + '/guardar', { method: 'POST', body: fd });
                const j = await r.json();
                if (!j.ok) throw new Error(j.error || 'Error');
                if (!asignaciones[idCargo]) asignaciones[idCargo] = {};
                asignaciones[idCargo][idComp] = { nivel_requerido: nivel, observacion: obs };
                setStatus(tr, 'ok');
            }
        } catch (e) {
            console.error(e);
            setStatus(tr, 'err');
            alert('Error al guardar: ' + e.message);
        }
    }

    sel.addEventListener('change', () => {
        const id = sel.value;
        if (!id) { panel.style.display = 'none'; return; }
        panel.style.display = '';
        cargar(id);
    });

    panel.addEventListener('change', (ev) => {
        if (!ev.target.classList.contains('nivel-input')) return;
        const tr = ev.target.closest('tr[data-id-competencia]');
        const idCargo = sel.value;
        if (!tr || !idCargo) return;
        guardarFila(tr, idCargo);
    });

    panel.addEventListener('blur', (ev) => {
        if (!ev.target.classList.contains('obs-input')) return;
        const tr = ev.target.closest('tr[data-id-competencia]');
        const idCargo = sel.value;
        if (!tr || !idCargo) return;
        const nivel = parseInt(tr.querySelector('.nivel-input').value || '0', 10);
        if (nivel > 0) guardarFila(tr, idCargo);
    }, true);
})();
</script>
<?= $this->endSection() ?>
