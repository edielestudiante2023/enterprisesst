<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:20px 25px;border-radius:12px;margin-bottom:18px;box-shadow:0 8px 25px rgba(0,0,0,.18);}
.page-header h1{font-size:1.35rem;margin:0 0 4px;font-weight:700;}
.page-header .subtitulo{opacity:.85;font-size:.9rem;}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.estado-badge{font-size:.7rem;padding:5px 10px;border-radius:20px;font-weight:700;text-transform:uppercase;}
.estado-borrador{background:#e5e7eb;color:#374151;}
.estado-revision{background:#fef3c7;color:#92400e;}
.estado-aprobada{background:#dbeafe;color:#1e40af;}
.estado-vigente{background:#d1fae5;color:#065f46;}
.estado-historica{background:#f3f4f6;color:#6b7280;}

.card-editor{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);}

table.matriz-ipevr{font-size:.8rem;}
table.matriz-ipevr thead th{background:var(--primary-dark);color:#fff;padding:8px 6px;font-weight:600;text-align:center;white-space:nowrap;font-size:.75rem;}
table.matriz-ipevr thead tr.grupo th{background:var(--gold-primary);font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;}
table.matriz-ipevr tbody td{padding:6px 8px;vertical-align:top;}
table.matriz-ipevr tbody tr:hover{background:#fffbea;}
.dataTables_wrapper .dataTables_filter input{border-radius:8px;border:1px solid #d1d5db;padding:5px 12px;}
.dataTables_wrapper .dataTables_length select{border-radius:8px;}
.dataTables_wrapper{margin-top:10px;}
.guia-gtc45{background:#f0f7ff;border:1px solid #bfdbfe;border-left:4px solid #3b82f6;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:.82rem;color:#1e3a5f;}
.guia-gtc45 .guia-titulo{font-weight:700;margin-bottom:4px;color:#1e40af;}
.guia-gtc45 .guia-titulo i{margin-right:5px;}
.guia-gtc45 ul{margin:4px 0 0;padding-left:18px;}
.guia-gtc45 li{margin-bottom:2px;}
.guia-gtc45 .guia-ejemplo{color:#6b7280;font-style:italic;}

.empty-state{text-align:center;padding:60px 20px;color:#888;}
.empty-state .big-icon{font-size:5rem;color:#d1d5db;margin-bottom:20px;}

.badge-nr{font-weight:700;padding:6px 12px;border-radius:6px;color:#fff;font-size:.8rem;}

.toolbar{display:flex;gap:8px;flex-wrap:wrap;}
.info-chip{background:#f3f4f6;padding:6px 12px;border-radius:20px;font-size:.8rem;color:#374151;}
.info-chip strong{color:var(--primary-dark);}
</style>
</head>
<body>
<div class="container-fluid py-3">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1><i class="fa-solid fa-shield-halved me-2"></i><?= esc($matriz['nombre']) ?></h1>
      <div class="subtitulo">
        <?= esc($cliente['nombre_cliente']) ?> ·
        <span class="version-badge">v<?= esc($matriz['version']) ?></span> ·
        <span class="estado-badge estado-<?= esc($matriz['estado']) ?>"><?= esc($matriz['estado']) ?></span>
      </div>
    </div>
    <div class="toolbar">
      <a href="<?= base_url('ipevr/cliente/' . $matriz['id_cliente']) ?>" class="btn btn-light btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver
      </a>
      <a href="<?= base_url('maestros-cliente/' . $matriz['id_cliente']) ?>" class="btn btn-light btn-sm">
        <i class="fa-solid fa-database me-1"></i> Maestros
      </a>
      <a href="<?= base_url('ipevr/matriz/' . $matriz['id'] . '/acelerador') ?>" class="btn btn-sm" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;">
        <i class="fa-solid fa-rocket me-1"></i> Acelerador Plan Maestro
      </a>
      <button class="btn btn-warning btn-sm" onclick="sugerirConIa()">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sugerir con IA
      </button>
      <a href="<?= base_url('ipevr/matriz/' . $matriz['id'] . '/exportar/xlsx') ?>" class="btn btn-success btn-sm">
        <i class="fa-solid fa-file-excel me-1"></i> Excel
      </a>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if ($matriz['estado'] === 'borrador'): ?>
            <li><a class="dropdown-item" href="#" onclick="enviarARevision();return false;"><i class="fa-solid fa-clipboard-check me-1"></i>Cambiar a revisión</a></li>
          <?php endif; ?>
          <?php if ($matriz['estado'] === 'revision'): ?>
            <li><a class="dropdown-item" href="#" onclick="aprobar();return false;"><i class="fa-solid fa-check-double me-1"></i>Aprobar y marcar vigente</a></li>
          <?php endif; ?>
          <?php if ($matriz['estado'] === 'vigente'): ?>
            <li><a class="dropdown-item" href="#" onclick="nuevaVersion();return false;"><i class="fa-solid fa-code-branch me-1"></i>Crear nueva versión</a></li>
          <?php endif; ?>
          <li><a class="dropdown-item" href="<?= base_url('ipevr/matriz/' . $matriz['id'] . '/exportar/pdf') ?>" target="_blank"><i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF</a></li>
        </ul>
      </div>
      <a href="<?= base_url('ipevr/tablas-gtc45') ?>" target="_blank" class="btn btn-info btn-sm">
        <i class="fa-solid fa-table-list me-1"></i> Tablas GTC 45
      </a>
      <a href="https://posipedia.com.co/wp-content/uploads/2021/04/15-MARZO-.-MATERIAL-DE-APOYO-PREVENCIO%CC%81N-DE-PELIGROS-EN-EL-ADMINISTRACIO%CC%81N-PUBLICA-GENERALIDADES.pdf" target="_blank" class="btn btn-outline-info btn-sm">
        <i class="fa-solid fa-book me-1"></i> Norma GTC 45
      </a>
      <button class="btn btn-gold btn-sm" onclick="abrirNuevaFila()">
        <i class="fa-solid fa-plus me-1"></i> Agregar fila
      </button>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap mb-3">
    <div class="info-chip"><i class="fa-solid fa-list me-1"></i> Filas: <strong id="contador-filas"><?= count($filas) ?></strong></div>
    <div class="info-chip"><i class="fa-solid fa-diagram-project me-1"></i> Procesos: <strong><?= count($maestros['procesos']) ?></strong></div>
    <div class="info-chip"><i class="fa-solid fa-user-tie me-1"></i> Cargos: <strong><?= count($maestros['cargos']) ?></strong></div>
    <div class="info-chip"><i class="fa-solid fa-location-dot me-1"></i> Zonas: <strong><?= count($maestros['zonas']) ?></strong></div>
  </div>

  <div class="card card-editor p-3">
    <?php if (empty($filas)): ?>
      <div class="empty-state">
        <div class="big-icon"><i class="fa-solid fa-table-list"></i></div>
        <h5>La matriz está vacía</h5>
        <p>Agrega filas manualmente o usa "Sugerir con IA" para pre-poblar desde el contexto del cliente.</p>
        <button class="btn btn-gold" onclick="abrirNuevaFila()">
          <i class="fa-solid fa-plus me-1"></i> Agregar primera fila
        </button>
      </div>
    <?php else: ?>
        <table class="table table-striped table-hover matriz-ipevr" id="tablaMatriz" style="width:100%">
          <thead>
            <tr>
              <th>Proceso</th>
              <th>Actividad</th>
              <th>Tarea</th>
              <th>Zona</th>
              <th>Rut.</th>
              <th>Cargos</th>
              <th>N°</th>
              <th>Descripción</th>
              <th>Clasif.</th>
              <th>Efectos</th>
              <th>Fuente</th>
              <th>Medio</th>
              <th>Individuo</th>
              <th>ND</th>
              <th>NE</th>
              <th>NP</th>
              <th>Interp.NP</th>
              <th>NC</th>
              <th>NR</th>
              <th>Nivel</th>
              <th>Aceptab.</th>
              <th>Peor cons.</th>
              <th>Req. legal</th>
              <th>Elim.</th>
              <th>Sust.</th>
              <th>Ing.</th>
              <th>Admin.</th>
              <th>EPP</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php
            // Maps para resolver IDs a nombres sin queries extra
            $mapProc = array_column($maestros['procesos'], 'nombre_proceso', 'id');
            $mapZona = array_column($maestros['zonas'], 'nombre_zona', 'id');
            $mapTarea = array_column($maestros['tareas'], 'nombre_tarea', 'id');
            $mapClasif = array_column($catalogo['clasificaciones'], 'nombre', 'id');
            $mapND = array_column($catalogo['nd'], null, 'id');
            $mapNE = array_column($catalogo['ne'], null, 'id');
            $mapNC = array_column($catalogo['nc'], null, 'id');
            $mapNP = array_column($catalogo['np'], null, 'id');
            $mapNR = array_column($catalogo['nr'], null, 'id');
          ?>
          <?php foreach ($filas as $f):
            $proceso = $f['proceso_texto'] ?: ($mapProc[$f['id_proceso']] ?? '');
            $zona    = $f['zona_texto']    ?: ($mapZona[$f['id_zona']] ?? '');
            $tarea   = $f['tarea_texto']   ?: ($mapTarea[$f['id_tarea']] ?? '');
            $clasif  = $mapClasif[$f['id_clasificacion']] ?? '';
            $nd      = $mapND[$f['id_nd']] ?? null;
            $ne      = $mapNE[$f['id_ne']] ?? null;
            $nc      = $mapNC[$f['id_nc']] ?? null;
            $np      = $mapNP[$f['id_np']] ?? null;
            $nr      = $mapNR[$f['id_nivel_riesgo']] ?? null;
            $cargos  = [];
            if (!empty($f['cargos_expuestos'])) {
                $tmp = json_decode($f['cargos_expuestos'], true);
                if (is_array($tmp)) $cargos = $tmp;
            }
          ?>
            <tr data-id="<?= (int)$f['id'] ?>">
              <td><?= esc($proceso) ?></td>
              <td><?= esc($f['actividad'] ?? '') ?></td>
              <td><?= esc($tarea) ?></td>
              <td><?= esc($zona) ?></td>
              <td class="text-center"><?= $f['rutinaria'] ? 'Sí' : 'No' ?></td>
              <td>
                <?php foreach ($cargos as $c): ?>
                  <span class="badge bg-secondary me-1"><?= esc($c) ?></span>
                <?php endforeach; ?>
              </td>
              <td class="text-center"><?= (int)$f['num_expuestos'] ?></td>
              <td><?= esc($f['descripcion_peligro'] ?? '') ?></td>
              <td><span class="badge bg-info text-dark"><?= esc($clasif) ?></span></td>
              <td><?= esc($f['efectos_posibles'] ?? '') ?></td>
              <td><?= esc($f['control_fuente'] ?? '') ?></td>
              <td><?= esc($f['control_medio'] ?? '') ?></td>
              <td><?= esc($f['control_individuo'] ?? '') ?></td>
              <td class="text-center"><?= $nd ? esc($nd['codigo']).' ('.$nd['valor'].')' : '—' ?></td>
              <td class="text-center"><?= $ne ? esc($ne['codigo']).' ('.$ne['valor'].')' : '—' ?></td>
              <td class="text-center"><strong><?= $f['np'] !== null ? (int)$f['np'] : '—' ?></strong></td>
              <td class="text-center"><?= $np ? esc($np['nombre']) : '—' ?></td>
              <td class="text-center"><?= $nc ? esc($nc['codigo']).' ('.$nc['valor'].')' : '—' ?></td>
              <td class="text-center"><strong><?= $f['nr'] !== null ? (int)$f['nr'] : '—' ?></strong></td>
              <td class="text-center">
                <?php if ($nr): ?>
                  <span class="badge-nr" style="background:<?= esc($nr['color_hex']) ?>"><?= esc($nr['nombre']) ?></span>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td class="small"><?= esc($f['aceptabilidad'] ?? '') ?></td>
              <td><?= esc($f['peor_consecuencia'] ?? '') ?></td>
              <td><?= esc($f['requisito_legal'] ?? '') ?></td>
              <td><?= esc($f['medida_eliminacion'] ?? '') ?></td>
              <td><?= esc($f['medida_sustitucion'] ?? '') ?></td>
              <td><?= esc($f['medida_ingenieria'] ?? '') ?></td>
              <td><?= esc($f['medida_administrativa'] ?? '') ?></td>
              <td><?= esc($f['medida_epp'] ?? '') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="editarFila(<?= (int)$f['id'] ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarFila(<?= (int)$f['id'] ?>)" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL FILA -->
<div class="modal fade" id="modalFila" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="formFila" onsubmit="guardarFila(event)">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fa-solid fa-table-list me-2"></i><span id="modalFilaTitulo">Nueva fila</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="f_id">
        <input type="hidden" name="id_matriz" value="<?= (int)$matriz['id'] ?>">

        <ul class="nav nav-tabs mb-3">
          <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#s1">1. Proceso</button></li>
          <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s2">2. Peligros</button></li>
          <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s3">3. Controles</button></li>
          <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s4">4. Evaluación</button></li>
          <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s5">5. Criterios</button></li>
          <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s6">6. Medidas</button></li>
        </ul>
        <div class="tab-content">

          <!-- SECCION 1 -->
          <div class="tab-pane fade show active" id="s1">
            <div class="guia-gtc45">
              <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — Información del proceso y la tarea</div>
              <ul>
                <li><b>Proceso:</b> actividad principal de la organización (ej: Producción, Administrativo, Logística). Seleccione del maestro o escriba texto libre.</li>
                <li><b>Zona o lugar:</b> sitio físico donde se realiza la tarea (ej: Bodega, Oficina, Planta).</li>
                <li><b>Actividad:</b> qué se hace dentro del proceso (ej: Empaque de producto, Gestión contable).</li>
                <li><b>Tarea:</b> acción específica del trabajador (ej: Levantar cajas, Digitar facturas).</li>
                <li><b>Rutinaria:</b> <b>Sí</b> = se realiza habitualmente/diario. <b>No</b> = esporádica (mantenimientos, emergencias, inventarios).</li>
                <li><b>Cargos expuestos:</b> quiénes realizan esta tarea. <b>N° expuestos:</b> cuántos trabajadores.</li>
              </ul>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Proceso (maestro)</label>
                <select class="form-select" name="id_proceso" id="f_id_proceso"></select>
              </div>
              <div class="col-md-6">
                <label class="form-label">O texto libre de proceso</label>
                <input class="form-control" name="proceso_texto" id="f_proceso_texto">
              </div>
              <div class="col-md-6">
                <label class="form-label">Zona (maestro)</label>
                <select class="form-select" name="id_zona" id="f_id_zona"></select>
              </div>
              <div class="col-md-6">
                <label class="form-label">O texto libre de zona</label>
                <input class="form-control" name="zona_texto" id="f_zona_texto">
              </div>
              <div class="col-md-12">
                <label class="form-label">Actividad</label>
                <input class="form-control" name="actividad" id="f_actividad">
              </div>
              <div class="col-md-6">
                <label class="form-label">Tarea (maestro)</label>
                <select class="form-select" name="id_tarea" id="f_id_tarea"></select>
              </div>
              <div class="col-md-6">
                <label class="form-label">O texto libre de tarea</label>
                <input class="form-control" name="tarea_texto" id="f_tarea_texto">
              </div>
              <div class="col-md-3">
                <label class="form-label">¿Rutinaria?</label>
                <select class="form-select" name="rutinaria" id="f_rutinaria">
                  <option value="1">Sí</option>
                  <option value="0">No</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Cargos expuestos (separar por coma)</label>
                <input class="form-control" name="cargos_expuestos" id="f_cargos_expuestos" placeholder="Auxiliar, Supervisor">
              </div>
              <div class="col-md-3">
                <label class="form-label">N° expuestos</label>
                <input type="number" min="0" class="form-control" name="num_expuestos" id="f_num_expuestos" value="0">
              </div>
            </div>
          </div>

          <!-- SECCION 2 -->
          <div class="tab-pane fade" id="s2">
            <div class="guia-gtc45">
              <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — Identificación de peligros</div>
              <ul>
                <li><b>Peligro:</b> fuente o situación con potencial de causar daño. Seleccione del catálogo GTC 45 (agrupa 7 clasificaciones).</li>
                <li><b>Las 7 clasificaciones son:</b> Biológico · Físico · Químico · Psicosocial · Biomecánico · Condiciones de seguridad · Fenómenos naturales.</li>
                <li><b>Descripción:</b> detalle cómo se manifiesta el peligro en esta tarea. <span class="guia-ejemplo">Ej: "Ruido continuo generado por maquinaria troqueladora".</span></li>
                <li><b>Efectos posibles:</b> consecuencias en la salud o seguridad. <span class="guia-ejemplo">Ej: "Hipoacusia neurosensorial, pérdida auditiva permanente".</span></li>
              </ul>
              <small>Al seleccionar un peligro del catálogo, la clasificación se asigna automáticamente.</small>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Peligro (catálogo GTC 45)</label>
                <select class="form-select" name="id_peligro_catalogo" id="f_id_peligro_catalogo"></select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Clasificación</label>
                <select class="form-select" name="id_clasificacion" id="f_id_clasificacion"></select>
              </div>
              <div class="col-md-12">
                <label class="form-label">Descripción del peligro</label>
                <textarea class="form-control" name="descripcion_peligro" id="f_descripcion_peligro" rows="2"></textarea>
              </div>
              <div class="col-md-12">
                <label class="form-label">Efectos posibles</label>
                <textarea class="form-control" name="efectos_posibles" id="f_efectos_posibles" rows="2"></textarea>
              </div>
            </div>
          </div>

          <!-- SECCION 3 -->
          <div class="tab-pane fade" id="s3">
            <div class="guia-gtc45">
              <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — Controles existentes</div>
              <ul>
                <li><b>Fuente:</b> controles que eliminan o reducen el peligro en su origen. <span class="guia-ejemplo">Ej: "Mantenimiento preventivo de equipos", "Sustitución de sustancia química".</span></li>
                <li><b>Medio:</b> barreras entre el peligro y el trabajador. <span class="guia-ejemplo">Ej: "Guardas de protección en maquinaria", "Encerramientos acústicos", "Ventilación localizada".</span></li>
                <li><b>Individuo:</b> controles sobre la persona expuesta. <span class="guia-ejemplo">Ej: "Uso de protección auditiva", "Capacitación en manejo de cargas", "Pausas activas".</span></li>
              </ul>
              <small>Si no existe control, escriba "Ninguno". Registre solo lo que YA está implementado, no lo que se planea.</small>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Control en la fuente</label>
                <textarea class="form-control" name="control_fuente" id="f_control_fuente" rows="4"></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label">Control en el medio</label>
                <textarea class="form-control" name="control_medio" id="f_control_medio" rows="4"></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label">Control en el individuo</label>
                <textarea class="form-control" name="control_individuo" id="f_control_individuo" rows="4"></textarea>
              </div>
            </div>
          </div>

          <!-- SECCION 4 -->
          <div class="tab-pane fade" id="s4">
            <div class="guia-gtc45">
              <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — Evaluación del riesgo</div>
              <ul>
                <li><b>Nivel de Deficiencia (ND):</b> ¿Qué tan deficientes son los controles existentes?
                  <br>Muy Alto (10) = sin controles · Alto (6) = controles ineficaces · Medio (2) = controles moderados · Bajo (0) = controles eficaces.</li>
                <li><b>Nivel de Exposición (NE):</b> ¿Con qué frecuencia se expone el trabajador?
                  <br>Continua (4) = todo el día · Frecuente (3) = varias veces/día · Ocasional (2) = alguna vez/día · Esporádica (1) = eventual.</li>
                <li><b>Nivel de Consecuencia (NC):</b> ¿Qué tan grave sería si ocurre?
                  <br>Mortal (100) = muerte · Muy grave (60) = incapacidad permanente · Grave (25) = incapacidad temporal · Leve (10) = sin incapacidad.</li>
              </ul>
              <small>NP y NR se calculan automáticamente. El color indica la aceptabilidad del riesgo.
              <a href="<?= base_url('ipevr/tablas-gtc45') ?>" target="_blank">Ver todas las tablas GTC 45 →</a></small>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Nivel de Deficiencia (ND)</label>
                <select class="form-select" name="id_nd" id="f_id_nd" onchange="recalcular()"></select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Nivel de Exposición (NE)</label>
                <select class="form-select" name="id_ne" id="f_id_ne" onchange="recalcular()"></select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Nivel de Consecuencia (NC)</label>
                <select class="form-select" name="id_nc" id="f_id_nc" onchange="recalcular()"></select>
              </div>
            </div>
            <div class="row g-3 mt-2">
              <div class="col-md-3">
                <div class="p-3 rounded bg-light text-center">
                  <div class="text-muted small">NP = ND × NE</div>
                  <div class="fs-3 fw-bold" id="calc_np">—</div>
                  <div class="small" id="calc_np_interp">—</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="p-3 rounded bg-light text-center">
                  <div class="text-muted small">NR = NP × NC</div>
                  <div class="fs-3 fw-bold" id="calc_nr">—</div>
                  <div class="small" id="calc_nr_interp">—</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded text-center text-white" id="calc_aceptabilidad_box" style="background:#9ca3af;">
                  <div class="small text-uppercase opacity-75">Nivel de Riesgo / Aceptabilidad</div>
                  <div class="fs-4 fw-bold" id="calc_nivel">—</div>
                  <div id="calc_aceptabilidad">—</div>
                </div>
              </div>
            </div>
          </div>

          <!-- SECCION 5 -->
          <div class="tab-pane fade" id="s5">
            <div class="guia-gtc45">
              <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — Criterios para priorizar controles</div>
              <ul>
                <li><b>Peor consecuencia:</b> el daño más grave que puede ocurrir si falla todo. <span class="guia-ejemplo">Ej: "Pérdida auditiva permanente", "Muerte por electrocución", "Lumbalgia con incapacidad permanente parcial".</span></li>
                <li><b>Requisito legal:</b> cite la norma colombiana aplicable a este peligro. <span class="guia-ejemplo">Ej: "Resolución 2400/1979 Art. 88-89 (ruido)", "Resolución 2844/2007 (enfermedades laborales)", "Decreto 1072/2015 Art. 2.2.4.6".</span></li>
              </ul>
              <small>Estos criterios ayudan a priorizar qué riesgos intervenir primero.</small>
            </div>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label">Peor consecuencia</label>
                <textarea class="form-control" name="peor_consecuencia" id="f_peor_consecuencia" rows="2"></textarea>
              </div>
              <div class="col-md-12">
                <label class="form-label">Requisito legal aplicable</label>
                <textarea class="form-control" name="requisito_legal" id="f_requisito_legal" rows="2"></textarea>
              </div>
            </div>
          </div>

          <!-- SECCION 6 -->
          <div class="tab-pane fade" id="s6">
            <div class="guia-gtc45">
              <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — Medidas de intervención (jerarquía de controles)</div>
              <ul>
                <li><b>1° Eliminación:</b> quitar el peligro por completo. <span class="guia-ejemplo">Ej: "Automatizar el proceso para eliminar manipulación manual".</span></li>
                <li><b>2° Sustitución:</b> reemplazar por algo menos peligroso. <span class="guia-ejemplo">Ej: "Sustituir solvente tóxico por base acuosa".</span></li>
                <li><b>3° Control de ingeniería:</b> aislar o confinar el peligro. <span class="guia-ejemplo">Ej: "Instalar guardas en maquinaria", "Ventilación localizada".</span></li>
                <li><b>4° Control administrativo:</b> procedimientos, señalización, capacitación. <span class="guia-ejemplo">Ej: "Rotación de turnos", "Pausas activas programadas", "Señalización de riesgo eléctrico".</span></li>
                <li><b>5° EPP:</b> última línea de defensa. <span class="guia-ejemplo">Ej: "Protección auditiva tipo copa", "Guantes de nitrilo", "Arnés de seguridad".</span></li>
              </ul>
              <small>Siga el orden de prioridad: siempre intente eliminar antes de poner EPP. Documente solo las medidas <b>propuestas o planeadas</b>, no las ya existentes (esas van en la pestaña 3).</small>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Eliminación</label>
                <textarea class="form-control" name="medida_eliminacion" id="f_medida_eliminacion" rows="3"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Sustitución</label>
                <textarea class="form-control" name="medida_sustitucion" id="f_medida_sustitucion" rows="3"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Control de ingeniería</label>
                <textarea class="form-control" name="medida_ingenieria" id="f_medida_ingenieria" rows="3"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Controles administrativos</label>
                <textarea class="form-control" name="medida_administrativa" id="f_medida_administrativa" rows="3"></textarea>
              </div>
              <div class="col-md-12">
                <label class="form-label">EPP</label>
                <textarea class="form-control" name="medida_epp" id="f_medida_epp" rows="2"></textarea>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-gold"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar fila</button>
      </div>
    </form>
  </div>
</div>

<script>
window.IPEVR_CTX = {
  id_matriz: <?= (int)$matriz['id'] ?>,
  id_cliente: <?= (int)$matriz['id_cliente'] ?>,
  estado: <?= json_encode($matriz['estado']) ?>,
  base: '<?= base_url() ?>',
};
window.GTC45_CATALOGO = <?= json_encode($catalogo) ?>;
window.MAESTROS_CLIENTE = <?= json_encode($maestros) ?>;
window.FILAS_DATA = <?= json_encode($filas) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url('js/ipevr_calculadora.js') ?>"></script>
<script>
const BASE = window.IPEVR_CTX.base;
const CAT = window.GTC45_CATALOGO;
const MAE = window.MAESTROS_CLIENTE;
const FILAS = Object.fromEntries((window.FILAS_DATA||[]).map(f => [String(f.id), f]));

// ---------- POBLADO DE SELECTS ----------
function opt(value, label, selected){
  return `<option value="${value}" ${String(selected)===String(value)?'selected':''}>${label}</option>`;
}
function poblarSelect(id, lista, labelKey, idKey='id', placeholder='(Ninguno)'){
  const el = document.getElementById(id);
  el.innerHTML = `<option value="">${placeholder}</option>` + lista.map(x => opt(x[idKey], x[labelKey])).join('');
}
function poblarPeligros(){
  const el = document.getElementById('f_id_peligro_catalogo');
  const porClasif = {};
  CAT.clasificaciones.forEach(c => porClasif[c.id] = { nombre: c.nombre, items: [] });
  CAT.peligros.forEach(p => { if (porClasif[p.id_clasificacion]) porClasif[p.id_clasificacion].items.push(p); });
  let html = '<option value="">(Seleccione peligro)</option>';
  Object.values(porClasif).forEach(g => {
    if (!g.items.length) return;
    html += `<optgroup label="${g.nombre}">`;
    g.items.forEach(p => { html += `<option value="${p.id}" data-clasif="${p.id_clasificacion}">${p.nombre}</option>`; });
    html += '</optgroup>';
  });
  el.innerHTML = html;
}
function inicializarSelects(){
  poblarSelect('f_id_proceso', MAE.procesos, 'nombre_proceso');
  poblarSelect('f_id_zona',    MAE.zonas,    'nombre_zona');
  poblarSelect('f_id_tarea',   MAE.tareas,   'nombre_tarea');
  poblarSelect('f_id_clasificacion', CAT.clasificaciones, 'nombre');
  poblarPeligros();
  poblarSelect('f_id_nd', CAT.nd, 'nombre', 'id', 'Seleccione ND');
  poblarSelect('f_id_ne', CAT.ne, 'nombre', 'id', 'Seleccione NE');
  poblarSelect('f_id_nc', CAT.nc, 'nombre', 'id', 'Seleccione NC');
}

// ---------- CALCULADORA EN VIVO ----------
function recalcular(){
  const ndId = document.getElementById('f_id_nd').value;
  const neId = document.getElementById('f_id_ne').value;
  const ncId = document.getElementById('f_id_nc').value;

  const rNP = IPEVR.calcularNP(ndId, neId);
  const rNR = IPEVR.calcularNR(rNP.np, ncId);

  document.getElementById('calc_np').textContent = rNP.np ?? '—';
  document.getElementById('calc_np_interp').textContent = rNP.interpretacion ? rNP.interpretacion.nombre : '—';
  document.getElementById('calc_nr').textContent = rNR.nr ?? '—';
  document.getElementById('calc_nr_interp').textContent = rNR.interpretacion ? ('Nivel ' + rNR.interpretacion.nombre) : '—';

  const box = document.getElementById('calc_aceptabilidad_box');
  if (rNR.interpretacion) {
    box.style.background = rNR.color || '#9ca3af';
    document.getElementById('calc_nivel').textContent = 'Nivel ' + rNR.interpretacion.nombre + ' (NR=' + rNR.nr + ')';
    document.getElementById('calc_aceptabilidad').textContent = rNR.aceptabilidad;
  } else {
    box.style.background = '#9ca3af';
    document.getElementById('calc_nivel').textContent = '—';
    document.getElementById('calc_aceptabilidad').textContent = '—';
  }
}

// ---------- AUTO-CLASIFICACION AL SELECCIONAR PELIGRO ----------
document.addEventListener('change', (ev) => {
  if (ev.target.id === 'f_id_peligro_catalogo' && ev.target.value) {
    const opt = ev.target.selectedOptions[0];
    const clasifId = opt?.dataset?.clasif;
    if (clasifId) document.getElementById('f_id_clasificacion').value = clasifId;
  }
});

// ---------- LIMPIAR / ABRIR / EDITAR ----------
const CAMPOS_FORM = [
  'id','id_proceso','proceso_texto','id_zona','zona_texto','actividad','id_tarea','tarea_texto',
  'rutinaria','cargos_expuestos','num_expuestos',
  'id_peligro_catalogo','descripcion_peligro','id_clasificacion','efectos_posibles',
  'control_fuente','control_medio','control_individuo',
  'id_nd','id_ne','id_nc',
  'peor_consecuencia','requisito_legal',
  'medida_eliminacion','medida_sustitucion','medida_ingenieria','medida_administrativa','medida_epp'
];
function limpiarForm(){
  CAMPOS_FORM.forEach(k => {
    const el = document.getElementById('f_' + k);
    if (!el) return;
    if (el.tagName === 'SELECT') el.value = '';
    else el.value = '';
  });
  document.getElementById('f_rutinaria').value = '1';
  document.getElementById('f_num_expuestos').value = '0';
  recalcular();
}
function cargarEnForm(fila){
  CAMPOS_FORM.forEach(k => {
    const el = document.getElementById('f_' + k);
    if (!el) return;
    let v = fila[k];
    if (k === 'cargos_expuestos') {
      try { v = Array.isArray(v) ? v.join(', ') : (JSON.parse(v || '[]').join(', ')); }
      catch(e){ v = v || ''; }
    }
    if (v == null) v = '';
    el.value = v;
  });
  recalcular();
}
function abrirNuevaFila(){
  limpiarForm();
  document.getElementById('modalFilaTitulo').textContent = 'Nueva fila';
  new bootstrap.Modal(document.getElementById('modalFila')).show();
}
function editarFila(id){
  const f = FILAS[String(id)];
  if (!f) return alert('Fila no encontrada en memoria');
  limpiarForm();
  cargarEnForm(f);
  document.getElementById('modalFilaTitulo').textContent = 'Editar fila #' + id;
  new bootstrap.Modal(document.getElementById('modalFila')).show();
}

// ---------- GUARDAR / ELIMINAR ----------
async function guardarFila(ev){
  ev.preventDefault();
  const fd = new FormData(document.getElementById('formFila'));
  const r = await fetch(BASE + 'ipevr/fila/upsert', { method:'POST', body: fd });
  const j = await r.json();
  if (j.ok) {
    bootstrap.Modal.getInstance(document.getElementById('modalFila')).hide();
    location.reload();
  } else {
    alert('Error: ' + (j.error || 'no se pudo guardar la fila'));
  }
}
async function eliminarFila(id){
  if (!confirm('¿Eliminar esta fila de la matriz?')) return;
  const r = await fetch(BASE + 'ipevr/fila/' + id + '/eliminar', { method:'POST' });
  const j = await r.json();
  if (j.ok) location.reload();
  else alert('Error: ' + (j.error || 'no se pudo eliminar'));
}

// ---------- SUGERIR CON IA ----------
async function sugerirConIa(){
  const { value: modo } = await Swal.fire({
    title: 'Generar filas con IA',
    html: 'La IA analizará el contexto del cliente (sector, procesos, peligros) para generar filas pre-diligenciadas.<br><br><b>¿Cuántas filas desea generar?</b>',
    input: 'select',
    inputOptions: {
      'auto': '🤖 Recomendadas por IA (la IA decide según el contexto)',
      '5': '5 filas',
      '10': '10 filas',
      '15': '15 filas',
      '20': '20 filas',
      '25': '25 filas',
      '30': '30 filas',
      '40': '40 filas',
      '50': '50 filas',
    },
    inputValue: 'auto',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Generar',
    confirmButtonColor: '#bd9751',
    inputValidator: (v) => !v ? 'Seleccione una opción' : null,
  });
  if (!modo) return;
  const n = modo === 'auto' ? 'auto' : parseInt(modo, 10);
  const labelCantidad = modo === 'auto' ? 'las filas recomendadas' : n + ' filas';
  const { isConfirmed } = await Swal.fire({
    icon: 'question',
    title: '¿Continuar?',
    html: `Se generarán <b>${labelCantidad}</b> usando el contexto del cliente.<br>Puede tardar 30-90 segundos.`,
    confirmButtonText: 'Sí, generar',
    confirmButtonColor: '#bd9751',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
  });
  if (!isConfirmed) return;

  // Mostrar loading con SweetAlert
  Swal.fire({
    title: 'Generando filas con IA...',
    html: `
      <div style="margin:20px 0">
        <i class="fa-solid fa-wand-magic-sparkles fa-beat-fade" style="font-size:3rem;color:#bd9751;"></i>
      </div>
      <p>La IA está analizando el contexto del cliente, procesos, cargos, tareas y peligros identificados.</p>
      <p class="text-muted small">Esto puede tardar entre 30 y 90 segundos. No cierre esta ventana.</p>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => { Swal.showLoading(); },
  });

  try {
    const fd = new FormData();
    fd.append('cantidad', String(n === 'auto' ? 0 : n));
    const r = await fetch(BASE + 'ipevr/matriz/' + window.IPEVR_CTX.id_matriz + '/sugerir-ia', { method: 'POST', body: fd });
    const j = await r.json();
    if (j.ok) {
      let msg = `Se insertaron <b>${j.insertadas}</b> filas en la matriz.`;
      if (j.cantidad_solicitada > 0 && j.cantidad_generada && j.cantidad_generada !== j.cantidad_solicitada) {
        if (j.cantidad_generada > j.cantidad_solicitada) {
          msg += `<br><br><small>Solicitaste ${j.cantidad_solicitada} filas, pero la IA generó <b>${j.cantidad_generada}</b> para cubrir adecuadamente todos los procesos y peligros identificados en el contexto del cliente.</small>`;
        } else {
          msg += `<br><br><small>Solicitaste ${j.cantidad_solicitada} filas. La IA generó <b>${j.cantidad_generada}</b> según los peligros relevantes para este sector.</small>`;
        }
      } else if (j.cantidad_solicitada === 0) {
        msg += `<br><br><small>La IA determinó que <b>${j.cantidad_generada}</b> filas son necesarias para cubrir los procesos y peligros del contexto del cliente.</small>`;
      }
      if (j.rechazadas > 0) msg += `<br><span class="text-danger">${j.rechazadas} rechazadas por datos inválidos</span>`;
      if (j.errores && j.errores.length) msg += '<br><br><small>' + j.errores.slice(0,5).join('<br>') + '</small>';
      Swal.fire({
        icon: 'success',
        title: 'Generación completada',
        html: msg,
        confirmButtonColor: '#bd9751',
      }).then(() => location.reload());
    } else if (j.error === 'contexto_faltante') {
      Swal.fire({
        icon: 'warning',
        title: 'Contexto del cliente no diligenciado',
        html: 'Debe diligenciar primero el <b>Contexto del Cliente</b> (sector económico, peligros, N° trabajadores, etc.) para que la IA pueda generar filas coherentes.',
        confirmButtonText: 'Ir al Contexto',
        confirmButtonColor: '#bd9751',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed && j.url_contexto) {
          window.open(j.url_contexto, '_blank');
        }
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Error IA', text: j.error || j.message || 'desconocido' });
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: e.message });
  }
}

// ---------- VERSIONAMIENTO ----------
async function cambiarEstado(accion, mensaje){
  const desc = prompt(mensaje + '\n\nDescripción del cambio:');
  if (desc === null) return;
  const fd = new FormData();
  fd.append('accion', accion);
  fd.append('descripcion', desc);
  const r = await fetch(BASE + 'ipevr/matriz/' + window.IPEVR_CTX.id_matriz + '/version', { method:'POST', body: fd });
  const j = await r.json();
  if (j.ok) {
    if (j.redirect) window.location.href = j.redirect;
    else location.reload();
  } else {
    alert('Error: ' + (j.error || 'no se pudo cambiar estado'));
  }
}
function enviarARevision(){ cambiarEstado('enviar_revision', 'Enviar esta matriz a revisión.'); }
function aprobar(){ cambiarEstado('aprobar', 'Aprobar y marcar como vigente.'); }
function nuevaVersion(){ cambiarEstado('nueva_version', 'Crear una nueva versión (la actual pasará a histórica).'); }

// ---------- INIT ----------
document.addEventListener('DOMContentLoaded', () => {
  inicializarSelects();

  // DataTables con columnas fijas y scroll horizontal
  if (document.getElementById('tablaMatriz') && typeof jQuery !== 'undefined') {
    $('#tablaMatriz').DataTable({
      scrollX: true,
      autoWidth: false,
      pageLength: 25,
      order: [],
      columnDefs: [
        { targets: [0,1,2], width: '140px' },   // Proceso, Actividad, Tarea
        { targets: [3], width: '100px' },         // Zona
        { targets: [4], width: '40px' },          // Rut.
        { targets: [5], width: '120px' },         // Cargos
        { targets: [6], width: '40px' },          // N°
        { targets: [7], width: '180px' },         // Descripción peligro
        { targets: [8], width: '90px' },          // Clasif.
        { targets: [9], width: '150px' },         // Efectos
        { targets: [10,11,12], width: '100px' },  // Controles
        { targets: [13,14], width: '60px' },      // ND, NE
        { targets: [15], width: '45px' },         // NP
        { targets: [16], width: '70px' },         // Interp.NP
        { targets: [17], width: '60px' },         // NC
        { targets: [18], width: '50px' },         // NR
        { targets: [19], width: '80px' },         // Nivel
        { targets: [20], width: '120px' },        // Aceptab.
        { targets: [21,22], width: '130px' },     // Peor cons., Req. legal
        { targets: [23,24,25,26,27], width: '120px' }, // Medidas
        { targets: [28], width: '90px', orderable: false }, // Acciones
      ],
      language: {
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_ filas',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ filas',
        infoEmpty: 'Sin filas',
        infoFiltered: '(filtrado de _MAX_ total)',
        paginate: { first: '«', last: '»', next: '›', previous: '‹' },
        zeroRecords: 'No se encontraron filas',
      },
    });
  }
});
</script>
</body>
</html>
