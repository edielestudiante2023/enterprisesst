<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:25px 30px;border-radius:15px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
.page-header h1{font-size:1.5rem;margin:0;font-weight:700;}
.card-main{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.foto-hint{background:#fff7ed;border-left:4px solid var(--gold-primary);padding:12px 16px;border-radius:8px;margin-bottom:15px;font-size:.9rem;color:#78350f;}
.foto-preview{width:200px;height:200px;object-fit:cover;border-radius:12px;border:2px dashed #d4af37;background:#f8f9fa;}
.foto-empty{width:200px;height:200px;border-radius:12px;border:2px dashed #d4af37;background:#f8f9fa;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#94a3b8;}
.label-required::after{content:" *";color:#dc2626;}
</style>
</head>
<body>
<div class="container py-4" style="max-width:1000px">
  <div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="fa-solid fa-shield-halved me-2"></i><?= esc($titulo) ?></h1>
    <a href="<?= base_url('matrizEpp/maestro') ?>" class="btn btn-light btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i>Volver al catálogo
    </a>
  </div>

  <?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
  <?php endif ?>

  <form action="<?= base_url('matrizEpp/maestro/guardar') ?>" method="post" enctype="multipart/form-data" id="formEpp">
    <?= csrf_field() ?>
    <input type="hidden" name="id_epp" value="<?= (int)($item['id_epp'] ?? 0) ?>">
    <input type="hidden" name="ia_generado" id="iaGenerado" value="<?= (int)($item['ia_generado'] ?? 0) ?>">

    <div class="row g-3">
      <!-- COLUMNA IZQ: datos -->
      <div class="col-md-8">
        <div class="card card-main p-4">
          <h5 class="mb-3"><i class="fa-solid fa-circle-info me-2"></i>Datos del elemento</h5>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label label-required">Categoría</label>
              <select name="id_categoria" class="form-select" required>
                <option value="">-- selecciona --</option>
                <?php foreach ($categorias as $c): ?>
                  <option value="<?= $c['id_categoria'] ?>" <?= (int)($item['id_categoria'] ?? 0)===(int)$c['id_categoria']?'selected':'' ?>>
                    <?= esc($c['nombre']) ?> (<?= esc($c['tipo']) ?>)
                  </option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label label-required">Elemento</label>
              <input type="text" name="elemento" id="inpElemento" class="form-control" required
                value="<?= esc($item['elemento'] ?? '') ?>"
                placeholder="Ej: Botas de seguridad dieléctricas">
            </div>

            <div class="col-12">
              <button type="button" class="btn btn-outline-warning btn-sm" id="btnIa">
                <i class="fa-solid fa-robot me-1"></i> Autocompletar con IA
              </button>
              <span class="small text-muted ms-2">Escribe el nombre del elemento y selecciona la categoría, la IA completará los 5 campos técnicos.</span>
            </div>

            <div class="col-12">
              <label class="form-label">Norma técnica</label>
              <textarea name="norma" id="inpNorma" class="form-control" rows="2"><?= esc($item['norma'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mantenimiento</label>
              <textarea name="mantenimiento" id="inpMantenimiento" class="form-control" rows="3"><?= esc($item['mantenimiento'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Frecuencia de cambio / entrega</label>
              <textarea name="frecuencia_cambio" id="inpFrecuencia" class="form-control" rows="3"><?= esc($item['frecuencia_cambio'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Motivos de cambio anticipado</label>
              <textarea name="motivos_cambio" id="inpMotivos" class="form-control" rows="3"><?= esc($item['motivos_cambio'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Momentos de uso</label>
              <textarea name="momentos_uso" id="inpMomentos" class="form-control" rows="3"><?= esc($item['momentos_uso'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- COLUMNA DER: foto -->
      <div class="col-md-4">
        <div class="card card-main p-4">
          <h5 class="mb-3"><i class="fa-solid fa-camera me-2"></i>Fotografía</h5>

          <div class="foto-hint">
            <strong>Foto recomendada:</strong><br>
            Formato: JPG o PNG<br>
            Proporción: 1:1 (cuadrada)<br>
            <strong>400×400 px</strong> (mín. 200×200)<br>
            Peso máx.: 2 MB<br>
            Fondo blanco o neutro<br>
            <em class="text-muted">El sistema la normalizará automáticamente a 400×400 JPG.</em>
          </div>

          <div class="text-center mb-3">
            <?php if (!empty($item['foto_path'])): ?>
              <img src="<?= base_url($item['foto_path']) ?>?v=<?= strtotime($item['updated_at'] ?? 'now') ?>" class="foto-preview" id="previewFoto" alt="foto">
            <?php else: ?>
              <div class="foto-empty" id="previewEmpty">
                <i class="fa-solid fa-image" style="font-size:3rem"></i>
                <div class="small mt-2">Sin foto</div>
              </div>
              <img src="" class="foto-preview d-none" id="previewFoto" alt="">
            <?php endif ?>
          </div>

          <input type="file" name="foto" id="inpFoto" class="form-control form-control-sm" accept="image/jpeg,image/png">
        </div>
      </div>
    </div>

    <div class="mt-3 d-flex justify-content-end gap-2">
      <a href="<?= base_url('matrizEpp/maestro') ?>" class="btn btn-light">Cancelar</a>
      <button type="submit" class="btn btn-gold"><i class="fa-solid fa-save me-1"></i>Guardar elemento</button>
    </div>
  </form>
</div>

<script>
// Preview de foto
document.getElementById('inpFoto').addEventListener('change', e => {
  const f = e.target.files[0];
  if (!f) return;
  const prev = document.getElementById('previewFoto');
  const empty = document.getElementById('previewEmpty');
  prev.src = URL.createObjectURL(f);
  prev.classList.remove('d-none');
  if (empty) empty.classList.add('d-none');
});

// Autocompletar IA
const camposIa = ['inpNorma','inpMantenimiento','inpFrecuencia','inpMotivos','inpMomentos'];
let valoresIa = null; // snapshot para detectar edición humana

document.getElementById('btnIa').addEventListener('click', async () => {
  const elemento = document.getElementById('inpElemento').value.trim();
  const idCat = document.querySelector('[name=id_categoria]').value;
  if (!elemento) {
    Swal.fire({icon:'warning', title:'Escribe primero el nombre del elemento'});
    return;
  }
  if (!idCat) {
    Swal.fire({icon:'warning', title:'Selecciona primero la categoría'});
    return;
  }

  Swal.fire({title:'Consultando IA...', html:'Generando norma, mantenimiento, frecuencia y más', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

  try {
    const fd = new FormData();
    fd.append('elemento', elemento);
    fd.append('id_categoria', idCat);
    const r = await fetch('<?= base_url('matrizEpp/maestro/autocompletar') ?>', {method:'POST', body:fd});
    const j = await r.json();
    Swal.close();
    if (!j.ok) {
      Swal.fire({icon:'error', title:'Error IA', text: j.error || 'Sin detalle'});
      return;
    }
    document.getElementById('inpNorma').value         = j.data.norma || '';
    document.getElementById('inpMantenimiento').value = j.data.mantenimiento || '';
    document.getElementById('inpFrecuencia').value    = j.data.frecuencia_cambio || '';
    document.getElementById('inpMotivos').value       = j.data.motivos_cambio || '';
    document.getElementById('inpMomentos').value      = j.data.momentos_uso || '';
    valoresIa = {...j.data};
    document.getElementById('iaGenerado').value = '1';
    Swal.fire({icon:'success', title:'Campos completados', text:'Revisa antes de guardar', timer:1800, showConfirmButton:false});
  } catch (err) {
    Swal.close();
    Swal.fire({icon:'error', title:'Error de red', text: String(err)});
  }
});

// Si el usuario edita cualquier campo después del autocompletar, marcamos ia_generado=0
camposIa.forEach(id => {
  document.getElementById(id).addEventListener('input', () => {
    if (!valoresIa) return;
    const actual = {
      norma: document.getElementById('inpNorma').value,
      mantenimiento: document.getElementById('inpMantenimiento').value,
      frecuencia_cambio: document.getElementById('inpFrecuencia').value,
      motivos_cambio: document.getElementById('inpMotivos').value,
      momentos_uso: document.getElementById('inpMomentos').value,
    };
    const editado = Object.keys(valoresIa).some(k => (valoresIa[k] || '') !== (actual[k] || ''));
    document.getElementById('iaGenerado').value = editado ? '0' : '1';
  });
});
</script>
</body>
</html>
