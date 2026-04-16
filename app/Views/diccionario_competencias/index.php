<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Diccionario de Competencias - <?= esc($cliente['nombre_cliente'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultantDashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Diccionario de Competencias</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-0">
                <i class="fas fa-user-check me-2 text-warning"></i>
                Diccionario de Competencias
            </h3>
            <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong> &middot; <?= (int)$total ?> competencias activas</small>
        </div>
        <div class="btn-group">
            <a href="<?= base_url("diccionario-competencias/{$cliente['id_cliente']}/escala") ?>" class="btn btn-outline-secondary">
                <i class="fas fa-layer-group me-1"></i> Escala 1-5
            </a>
            <a href="<?= base_url("diccionario-competencias/{$cliente['id_cliente']}/matriz") ?>" class="btn btn-outline-primary">
                <i class="fas fa-table me-1"></i> Matriz cargo-competencia
            </a>
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalClonar">
                <i class="fas fa-clone me-1"></i> Clonar desde otro cliente
            </button>
            <a href="<?= base_url("documentos/generar/diccionario_competencias_cliente/{$cliente['id_cliente']}") ?>"
               class="btn btn-warning text-white" target="_blank">
                <i class="fas fa-magic me-1"></i> Generar con IA
            </a>
        </div>
    </div>

    <!-- Modal: Clonar diccionario desde otro cliente -->
    <div class="modal fade" id="modalClonar" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-clone me-2"></i> Clonar diccionario desde otro cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <?php if ((int)$total > 0): ?>
              <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Este cliente <strong>ya tiene <?= (int)$total ?> competencias</strong>. Clonar reemplazara el diccionario existente.
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label">Cliente origen</label>
              <select id="selectOrigen" class="form-select">
                <option value="">-- cargando --</option>
              </select>
              <div class="form-text">Solo se listan clientes con diccionario poblado.</div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="chkMatriz">
              <label class="form-check-label" for="chkMatriz">
                Incluir tambien la matriz cargo-competencia
              </label>
              <div class="form-text">Solo copia asignaciones donde el nombre del cargo existe en ambos clientes.</div>
            </div>

            <div id="clonarResultado"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-warning text-white" id="btnClonar">
              <i class="fas fa-clone me-1"></i> Clonar
            </button>
          </div>
        </div>
      </div>
    </div>

    <script>
    (function() {
      const idCliente = <?= (int)$cliente['id_cliente'] ?>;
      const totalDestino = <?= (int)$total ?>;
      const urlClientes = '<?= base_url("diccionario-competencias/{$cliente['id_cliente']}/clientes-origen") ?>';
      const urlClonarBase = '<?= base_url("diccionario-competencias/{$cliente['id_cliente']}/clonar-desde") ?>';
      const csrfName = '<?= csrf_token() ?>';
      const csrfHash = '<?= csrf_hash() ?>';

      const sel = document.getElementById('selectOrigen');
      const btn = document.getElementById('btnClonar');
      const chk = document.getElementById('chkMatriz');
      const out = document.getElementById('clonarResultado');
      if (!sel || !btn) return;

      document.getElementById('modalClonar').addEventListener('shown.bs.modal', async () => {
        sel.innerHTML = '<option value="">-- cargando --</option>';
        try {
          const r = await fetch(urlClientes);
          const j = await r.json();
          if (!j.ok) throw new Error('No se pudieron cargar clientes');
          sel.innerHTML = '<option value="">-- selecciona cliente origen --</option>';
          (j.clientes || []).forEach(c => {
            const o = document.createElement('option');
            o.value = c.id_cliente;
            o.textContent = c.nombre_cliente + ' (' + c.total + ' competencias)';
            sel.appendChild(o);
          });
        } catch (e) {
          sel.innerHTML = '<option value="">Error al cargar</option>';
        }
      });

      btn.addEventListener('click', async () => {
        const idOrigen = sel.value;
        if (!idOrigen) { alert('Selecciona un cliente origen'); return; }
        if (totalDestino > 0) {
          if (!confirm('El cliente destino ya tiene ' + totalDestino + ' competencias. Se BORRARAN y reemplazaran. Continuar?')) return;
        }
        btn.disabled = true;
        out.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-1"></i> Clonando...</div>';

        const fd = new FormData();
        fd.append(csrfName, csrfHash);
        fd.append('incluir_matriz', chk.checked ? '1' : '0');
        fd.append('forzar', totalDestino > 0 ? '1' : '0');

        try {
          const r = await fetch(urlClonarBase + '/' + idOrigen, { method: 'POST', body: fd });
          let j;
          const text = await r.text();
          try { j = JSON.parse(text); } catch(_) {
            throw new Error('Respuesta no JSON (HTTP ' + r.status + '): ' + text.substring(0, 300));
          }
          if (!j.ok) throw new Error(j.error || 'Error desconocido (HTTP ' + r.status + ')');
          out.innerHTML = '<div class="alert alert-success">'
            + '<strong>Clonado OK.</strong><br>'
            + 'Escala: ' + j.resumen.escala + ' | '
            + 'Competencias: ' + j.resumen.competencias + ' | '
            + 'Matriz copiadas: ' + j.resumen.matriz_copiadas + ' | '
            + 'Matriz omitidas (sin match de cargo): ' + j.resumen.matriz_omitidas
            + '</div>';
          setTimeout(() => location.reload(), 1500);
        } catch (e) {
          out.innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
          btn.disabled = false;
        }
      });
    })();
    </script>

    <?php if ((int)$total === 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Este cliente aun no tiene competencias en su diccionario.
        </div>
    <?php endif; ?>

    <?php foreach ($agrupadas as $famKey => $lista):
        $famLabel = $familias[$famKey] ?? ucfirst(str_replace('_', ' ', (string)$famKey));
    ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-folder-open me-2" style="color: var(--secondary-color);"></i>
                <strong><?= esc($famLabel) ?></strong>
                <span class="badge bg-secondary ms-2"><?= count($lista) ?></span>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="accordion accordion-flush" id="acc-<?= esc($famKey) ?>">
                <?php foreach ($lista as $comp):
                    $niveles = $nivelesPorComp[(int)$comp['id_competencia']] ?? [];
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#comp-<?= (int)$comp['id_competencia'] ?>">
                            <span class="badge bg-dark me-2"><?= (int)$comp['numero'] ?></span>
                            <strong><?= esc($comp['nombre']) ?></strong>
                            <?php if (!empty($comp['codigo'])): ?>
                                <span class="badge bg-warning text-dark ms-2"><?= esc($comp['codigo']) ?></span>
                            <?php endif; ?>
                            <span class="ms-auto small text-muted me-3"><?= count($niveles) ?> niveles</span>
                        </button>
                    </h2>
                    <div id="comp-<?= (int)$comp['id_competencia'] ?>"
                         class="accordion-collapse collapse"
                         data-bs-parent="#acc-<?= esc($famKey) ?>">
                        <div class="accordion-body">
                            <p class="mb-2"><strong>Definicion:</strong> <?= esc($comp['definicion']) ?></p>
                            <?php if (!empty($comp['pregunta_clave'])): ?>
                                <p class="mb-3 fst-italic text-muted"><strong>Pregunta clave:</strong> <?= esc($comp['pregunta_clave']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($niveles)): ?>
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px;">Nivel</th>
                                        <th style="width:25%;">Titulo</th>
                                        <th>Conducta observable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($niveles as $n): ?>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-info"><?= (int)$n['nivel_numero'] ?></span></td>
                                        <td><?= esc($n['titulo_corto']) ?></td>
                                        <td><?= esc($n['descripcion_conducta']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
