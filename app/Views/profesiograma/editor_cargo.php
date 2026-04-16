<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:25px 30px;border-radius:15px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
.page-header h1{font-size:1.6rem;margin:0 0 4px;font-weight:700;}
.page-header .subtitulo{opacity:.85;font-size:.95rem;}
.card-main{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);margin-bottom:20px;}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.btn-outline-gold{border:2px solid var(--gold-primary);color:var(--gold-primary);font-weight:600;background:transparent;}
.btn-outline-gold:hover{background:var(--gold-primary);color:#fff;}
.tipo-header{font-size:1.1rem;font-weight:700;color:var(--primary-dark);padding:12px 0 8px;border-bottom:2px solid var(--gold-primary);margin-bottom:12px;text-transform:capitalize;}
.tipo-header i{color:var(--gold-primary);margin-right:8px;}
.examen-row{padding:10px 15px;border-radius:8px;margin-bottom:8px;background:#fafafa;border:1px solid #eee;transition:all .2s;}
.examen-row:hover{background:#fff;border-color:var(--gold-primary);box-shadow:0 2px 8px rgba(189,151,81,.15);}
.examen-row.asignado{background:#f0fdf4;border-color:#86efac;}
.examen-nombre{font-weight:600;font-size:.95rem;color:var(--primary-dark);}
.examen-desc{font-size:.82rem;color:#888;margin-top:2px;}
.examen-normativa{font-size:.78rem;color:var(--gold-primary);font-weight:500;}
.momento-checks{display:flex;gap:15px;align-items:center;flex-wrap:wrap;}
.momento-check{display:flex;align-items:center;gap:5px;font-size:.85rem;font-weight:500;}
.momento-check label{cursor:pointer;}
.momento-check input[type="checkbox"]{width:18px;height:18px;accent-color:var(--gold-primary);cursor:pointer;}
.frecuencia-select{font-size:.82rem;padding:3px 8px;border-radius:6px;border:1px solid #ddd;max-width:140px;}
.badge-momento{font-size:.75rem;padding:3px 8px;border-radius:10px;font-weight:600;}
.badge-ingreso{background:#dbeafe;color:#1e40af;}
.badge-periodico{background:#fef3c7;color:#92400e;}
.badge-retiro{background:#fce7f3;color:#9d174d;}
.badge-cambio{background:#e0e7ff;color:#3730a3;}
.callout-gtc{background:#fffbeb;border-left:4px solid var(--gold-primary);padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:20px;font-size:.9rem;}
.callout-gtc i{color:var(--gold-primary);margin-right:6px;}
.toast-container{position:fixed;bottom:20px;right:20px;z-index:9999;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <!-- HEADER -->
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1><i class="fa-solid fa-stethoscope me-2"></i><?= esc($titulo) ?></h1>
      <div class="subtitulo">
        <?= esc($cliente['nombre_cliente']) ?> &mdash;
        Cargo: <strong><?= esc($cargo['nombre_cargo']) ?></strong>
        <?php if (!empty($cargo['num_ocupantes'])): ?>
          (<?= (int)$cargo['num_ocupantes'] ?> ocupantes)
        <?php endif; ?>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-gold btn-sm" id="btnSugerirIa" onclick="sugerirConIa()">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sugerir con IA
      </button>
      <a href="<?= base_url('profesiograma/cliente/' . $cliente['id_cliente']) ?>" class="btn btn-outline-gold btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
      </a>
    </div>
  </div>

  <!-- CALLOUT -->
  <div class="callout-gtc">
    <i class="fa-solid fa-book-medical"></i>
    Marque los examenes que aplican para este cargo en cada momento de evaluacion.
    Los examenes generados desde IPEVR aparecen pre-marcados.
  </div>

  <!-- EXAMENES POR TIPO -->
  <?php
    $tipoIconos = [
        'funcional'     => 'fa-heartbeat',
        'laboratorio'   => 'fa-flask',
        'imagenologia'  => 'fa-x-ray',
        'psicologico'   => 'fa-brain',
        'especialista'  => 'fa-user-doctor',
    ];
    $tipoLabels = [
        'funcional'     => 'Examenes Funcionales',
        'laboratorio'   => 'Examenes de Laboratorio',
        'imagenologia'  => 'Imagenologia',
        'psicologico'   => 'Evaluaciones Psicologicas',
        'especialista'  => 'Valoraciones por Especialista',
    ];
    $momentos = ['ingreso', 'periodico', 'retiro'];
    $frecuencias = ['anual', 'semestral', 'cada_2_anios', 'unica_vez', 'segun_caso'];

    // Agrupar catalogo por tipo
    $porTipo = [];
    foreach ($catalogo as $ex) {
        $porTipo[$ex['tipo_examen']][] = $ex;
    }
  ?>

  <?php foreach ($porTipo as $tipo => $examenes): ?>
    <div class="card card-main">
      <div class="card-body p-3">
        <div class="tipo-header">
          <i class="fa-solid <?= $tipoIconos[$tipo] ?? 'fa-notes-medical' ?>"></i>
          <?= $tipoLabels[$tipo] ?? ucfirst($tipo) ?>
          <span class="badge bg-secondary ms-2" style="font-size:.75rem;"><?= count($examenes) ?></span>
        </div>

        <?php foreach ($examenes as $ex): ?>
          <?php
            $idEx = (int)$ex['id'];
            $asignados = $mapaAsignados[$idEx] ?? [];
            $tieneAlguno = !empty($asignados);
          ?>
          <div class="examen-row <?= $tieneAlguno ? 'asignado' : '' ?>" id="exrow-<?= $idEx ?>">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div class="flex-grow-1">
                <div class="examen-nombre"><?= esc($ex['nombre']) ?></div>
                <?php if (!empty($ex['descripcion'])): ?>
                  <div class="examen-desc"><?= esc($ex['descripcion']) ?></div>
                <?php endif; ?>
                <?php if (!empty($ex['normativa_referencia'])): ?>
                  <div class="examen-normativa">
                    <i class="fa-solid fa-scale-balanced me-1"></i><?= esc($ex['normativa_referencia']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="momento-checks">
                <?php foreach ($momentos as $mom): ?>
                  <?php
                    $asig = $asignados[$mom] ?? null;
                    $checked = $asig ? 'checked' : '';
                    $asigId = $asig ? (int)$asig['id'] : 0;
                    $freq = $asig['frecuencia'] ?? ($ex['frecuencia_sugerida'] ?? 'anual');
                  ?>
                  <div class="momento-check">
                    <input type="checkbox" <?= $checked ?>
                           id="chk-<?= $idEx ?>-<?= $mom ?>"
                           data-examen="<?= $idEx ?>"
                           data-momento="<?= $mom ?>"
                           data-asig-id="<?= $asigId ?>"
                           onchange="toggleExamen(this)">
                    <label for="chk-<?= $idEx ?>-<?= $mom ?>">
                      <span class="badge-momento badge-<?= $mom ?>"><?= ucfirst($mom) ?></span>
                    </label>
                  </div>
                <?php endforeach; ?>

                <!-- Frecuencia (para periodico) -->
                <select class="frecuencia-select"
                        id="freq-<?= $idEx ?>"
                        data-examen="<?= $idEx ?>"
                        onchange="cambiarFrecuencia(this)"
                        title="Frecuencia para examenes periodicos">
                  <?php foreach ($frecuencias as $f): ?>
                    <?php
                      $freqActual = $asignados['periodico']['frecuencia'] ?? ($ex['frecuencia_sugerida'] ?? 'anual');
                      $sel = ($f === $freqActual) ? 'selected' : '';
                      $label = str_replace('_', ' ', ucfirst($f));
                    ?>
                    <option value="<?= $f ?>" <?= $sel ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ID_CLIENTE = <?= (int)$cliente['id_cliente'] ?>;
const ID_CARGO = <?= (int)$cargo['id'] ?>;
const BASE_URL = '<?= base_url() ?>';

function toggleExamen(chk) {
    const idExamen = parseInt(chk.dataset.examen);
    const momento = chk.dataset.momento;
    const asigId = parseInt(chk.dataset.asigId) || 0;

    if (chk.checked) {
        // Asignar
        const freq = document.getElementById('freq-' + idExamen)?.value || 'anual';
        const formData = new FormData();
        formData.append('id', 0);
        formData.append('id_cliente', ID_CLIENTE);
        formData.append('id_cargo', ID_CARGO);
        formData.append('id_examen', idExamen);
        formData.append('momento', momento);
        formData.append('frecuencia', freq);
        formData.append('obligatorio', 1);
        formData.append('origen', 'manual');

        fetch(BASE_URL + 'profesiograma/asignar', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                chk.dataset.asigId = data.id;
                actualizarEstiloFila(idExamen);
                showToast('Examen asignado', 'success');
            } else {
                chk.checked = false;
                showToast(data.error || 'Error al asignar', 'danger');
            }
        })
        .catch(() => {
            chk.checked = false;
            showToast('Error de conexion', 'danger');
        });
    } else {
        // Quitar
        if (!asigId) return;
        fetch(BASE_URL + 'profesiograma/quitar/' + asigId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                chk.dataset.asigId = '0';
                actualizarEstiloFila(idExamen);
                showToast('Examen removido', 'info');
            } else {
                chk.checked = true;
                showToast(data.error || 'Error al quitar', 'danger');
            }
        })
        .catch(() => {
            chk.checked = true;
            showToast('Error de conexion', 'danger');
        });
    }
}

function cambiarFrecuencia(sel) {
    const idExamen = parseInt(sel.dataset.examen);
    // Actualizar frecuencia del periodico si esta asignado
    const chkPeriodico = document.getElementById('chk-' + idExamen + '-periodico');
    if (chkPeriodico && chkPeriodico.checked && chkPeriodico.dataset.asigId > 0) {
        const formData = new FormData();
        formData.append('id', chkPeriodico.dataset.asigId);
        formData.append('id_cliente', ID_CLIENTE);
        formData.append('id_cargo', ID_CARGO);
        formData.append('id_examen', idExamen);
        formData.append('momento', 'periodico');
        formData.append('frecuencia', sel.value);
        formData.append('obligatorio', 1);

        fetch(BASE_URL + 'profesiograma/asignar', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast('Frecuencia actualizada', 'success');
            }
        });
    }
}

function actualizarEstiloFila(idExamen) {
    const row = document.getElementById('exrow-' + idExamen);
    if (!row) return;
    const checks = row.querySelectorAll('input[type="checkbox"]');
    const alguno = Array.from(checks).some(c => c.checked);
    row.classList.toggle('asignado', alguno);
}

function sugerirConIa() {
    const btn = document.getElementById('btnSugerirIa');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Consultando IA...';

    fetch(BASE_URL + 'profesiograma/sugerir-ia/' + ID_CLIENTE + '/' + ID_CARGO, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sugerir con IA';

        if (!data.ok) {
            Swal.fire('Error', data.error || 'Error al consultar IA', 'error');
            return;
        }

        if (!data.sugerencias || data.sugerencias.length === 0) {
            Swal.fire('Sin sugerencias', 'La IA no encontro examenes adicionales para sugerir.', 'info');
            return;
        }

        mostrarSugerenciasIa(data.sugerencias, data.peligros, data.filas_ipevr);
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sugerir con IA';
        Swal.fire('Error', 'Error de conexion: ' + err.message, 'error');
    });
}

function mostrarSugerenciasIa(sugerencias, peligros, filasIpevr) {
    const prioridadColor = { alta: '#dc3545', media: '#ffc107', baja: '#198754' };
    const prioridadIcon = { alta: 'fa-circle-exclamation', media: 'fa-circle-info', baja: 'fa-circle-check' };

    let html = `
        <div style="text-align:left;max-height:60vh;overflow-y:auto;">
            <p style="font-size:.85rem;color:#666;margin-bottom:12px;">
                <i class="fa-solid fa-shield-halved me-1"></i>
                Basado en <strong>${filasIpevr}</strong> filas IPEVR con peligros: <strong>${peligros.join(', ')}</strong>
            </p>
            <div style="margin-bottom:8px;">
                <label style="font-size:.85rem;cursor:pointer;">
                    <input type="checkbox" id="chkSelectAll" onchange="toggleAllSugerencias(this)" checked>
                    <strong>Seleccionar todos</strong>
                </label>
            </div>`;

    sugerencias.forEach((s, i) => {
        const color = prioridadColor[s.prioridad] || '#888';
        const icon = prioridadIcon[s.prioridad] || 'fa-circle';
        const momentosBadges = (s.momentos || []).map(m => {
            const cls = m === 'ingreso' ? 'badge-ingreso' : m === 'periodico' ? 'badge-periodico' : 'badge-retiro';
            return `<span style="font-size:.7rem;padding:2px 6px;border-radius:8px;font-weight:600;" class="${cls}">${m}</span>`;
        }).join(' ');

        html += `
            <div style="border:1px solid #eee;border-left:4px solid ${color};border-radius:8px;padding:10px 12px;margin-bottom:8px;background:#fafafa;">
                <div style="display:flex;align-items:start;gap:8px;">
                    <input type="checkbox" class="sug-check" data-index="${i}" checked style="margin-top:3px;width:16px;height:16px;accent-color:#bd9751;">
                    <div style="flex:1;">
                        <div style="font-weight:600;font-size:.92rem;">
                            <i class="fa-solid ${icon} me-1" style="color:${color};"></i>
                            ${s.nombre_examen}
                            <span style="font-size:.72rem;color:${color};font-weight:700;text-transform:uppercase;margin-left:6px;">${s.prioridad}</span>
                        </div>
                        <div style="font-size:.82rem;color:#555;margin-top:4px;">
                            ${momentosBadges}
                            <span style="font-size:.78rem;color:#888;margin-left:8px;">Freq: ${(s.frecuencia||'anual').replace('_',' ')}</span>
                            ${s.obligatorio ? '<span style="font-size:.72rem;background:#fee2e2;color:#991b1b;padding:1px 6px;border-radius:6px;margin-left:6px;">Obligatorio</span>' : '<span style="font-size:.72rem;background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:6px;margin-left:6px;">Recomendado</span>'}
                        </div>
                        <div style="font-size:.82rem;color:#444;margin-top:6px;font-style:italic;">
                            <i class="fa-solid fa-stethoscope me-1" style="color:#bd9751;"></i>${s.justificacion}
                        </div>
                        ${s.observaciones ? `<div style="font-size:.78rem;color:#666;margin-top:4px;"><i class="fa-solid fa-clipboard-list me-1"></i>${s.observaciones}</div>` : ''}
                    </div>
                </div>
            </div>`;
    });

    html += '</div>';

    // Guardar sugerencias globalmente
    window._sugerenciasIa = sugerencias;

    Swal.fire({
        title: '<i class="fa-solid fa-wand-magic-sparkles" style="color:#bd9751;"></i> Sugerencias de la IA',
        html: html,
        width: '700px',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Aplicar seleccionados',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#bd9751',
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Recoger las seleccionadas
        const checks = document.querySelectorAll('.sug-check:checked');
        const seleccionadas = [];
        checks.forEach(chk => {
            const idx = parseInt(chk.dataset.index);
            seleccionadas.push(window._sugerenciasIa[idx]);
        });

        if (seleccionadas.length === 0) {
            showToast('No se seleccionaron sugerencias', 'warning');
            return;
        }

        aplicarSugerenciasIa(seleccionadas);
    });
}

function toggleAllSugerencias(chkAll) {
    document.querySelectorAll('.sug-check').forEach(c => c.checked = chkAll.checked);
}

function aplicarSugerenciasIa(sugerencias) {
    const formData = new FormData();
    formData.append('id_cliente', ID_CLIENTE);
    formData.append('id_cargo', ID_CARGO);
    formData.append('sugerencias', JSON.stringify(sugerencias));

    Swal.fire({
        title: 'Aplicando sugerencias...',
        html: '<i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#bd9751;"></i>',
        showConfirmButton: false,
        allowOutsideClick: false,
    });

    fetch(BASE_URL + 'profesiograma/aplicar-ia', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            Swal.fire({
                title: 'Sugerencias aplicadas',
                html: `<strong>${data.insertados}</strong> examenes asignados<br><strong>${data.duplicados}</strong> ya existian`,
                icon: 'success',
                confirmButtonColor: '#bd9751',
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.error || 'Error al aplicar', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Error de conexion: ' + err.message, 'error');
    });
}

function showToast(msg, type) {
    const container = document.getElementById('toastContainer');
    const colors = { success: '#198754', danger: '#dc3545', info: '#0dcaf0', warning: '#ffc107' };
    const toast = document.createElement('div');
    toast.className = 'toast show align-items-center text-white border-0';
    toast.style.cssText = `background:${colors[type]||'#333'};margin-bottom:8px;border-radius:8px;min-width:250px;`;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
</body>
</html>
