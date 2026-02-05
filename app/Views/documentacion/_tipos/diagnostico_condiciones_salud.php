<?php
/**
 * Vista de Tipo: 3.1.1 Descripcion sociodemografica - Diagnostico de Condiciones de Salud
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1"><i class="bi bi-folder-fill text-warning me-2"></i><?= esc($carpeta['nombre']) ?></h4>
                <?php if (!empty($carpeta['codigo'])): ?><span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span><?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?><p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p><?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntar"><i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte</button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-success mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-heart-pulse me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Descripcion Sociodemografica y Diagnostico de Condiciones de Salud</h6>
            <p class="mb-0 small">Adjunte soportes del perfil sociodemografico y diagnostico de salud: encuestas, analisis estadisticos, informes de morbilidad, ausentismo, condiciones de salud, etc.</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Soportes Diagnostico Condiciones de Salud</h6></div>
    <div class="card-body">
        <?php if (!empty($documentosSSTAprobados)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th style="width:120px">Codigo</th><th>Descripcion</th><th style="width:80px">Ano</th><th style="width:100px">Fecha</th><th style="width:100px">Tipo</th><th style="width:150px">Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($documentosSSTAprobados as $s): $esEnlace = !empty($s['url_externa']); $url = $esEnlace ? $s['url_externa'] : ($s['archivo_pdf'] ?? '#'); ?>
                        <tr>
                            <td><code><?= esc($s['codigo'] ?? 'SOP-DCS') ?></code></td>
                            <td><strong><?= esc($s['titulo']) ?></strong><?php if (!empty($s['observaciones'])): ?><br><small class="text-muted"><?= esc($s['observaciones']) ?></small><?php endif; ?></td>
                            <td><span class="badge bg-secondary"><?= esc($s['anio']) ?></span></td>
                            <td><small><?= date('d/m/Y', strtotime($s['created_at'] ?? 'now')) ?></small></td>
                            <td><?= $esEnlace ? '<span class="badge bg-primary"><i class="bi bi-link-45deg me-1"></i>Enlace</span>' : '<span class="badge bg-secondary"><i class="bi bi-file-earmark me-1"></i>Archivo</span>' ?></td>
                            <td><div class="btn-group btn-group-sm"><a href="<?= esc($url) ?>" class="btn btn-outline-primary" target="_blank"><i class="bi bi-eye"></i></a><?php if (!$esEnlace): ?><a href="<?= esc($url) ?>" class="btn btn-danger" download><i class="bi bi-download"></i></a><?php endif; ?></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4"><i class="bi bi-heart-pulse text-muted" style="font-size:2.5rem"></i><p class="text-muted mt-2 mb-0">No hay soportes adjuntados aun.</p></div>
        <?php endif; ?>
    </div>
</div>

<div class="row"><?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?></div>

<div class="modal fade" id="modalAdjuntar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="<?= base_url('documentos-sst/adjuntar-soporte-diagnostico-salud') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <div class="mb-3"><label class="form-label fw-bold">Tipo de carga</label><div class="btn-group w-100"><input type="radio" class="btn-check" name="tipo_carga" id="tcArch" value="archivo" checked><label class="btn btn-outline-success" for="tcArch"><i class="bi bi-file-earmark-arrow-up me-1"></i>Archivo</label><input type="radio" class="btn-check" name="tipo_carga" id="tcEnl" value="enlace"><label class="btn btn-outline-success" for="tcEnl"><i class="bi bi-link-45deg me-1"></i>Enlace</label></div></div>
                    <div class="mb-3" id="cArch"><label class="form-label">Archivo</label><input type="file" class="form-control" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx"></div>
                    <div class="mb-3 d-none" id="cEnl"><label class="form-label">Enlace</label><input type="url" class="form-control" name="url_externa" placeholder="https://..."></div>
                    <div class="mb-3"><label class="form-label">Descripcion</label><input type="text" class="form-control" name="descripcion" required placeholder="Ej: Perfil sociodemografico 2026, Diagnostico salud..."></div>
                    <div class="mb-3"><label class="form-label">Ano</label><select class="form-select" name="anio"><?php for($y=date('Y');$y>=2020;$y--): ?><option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option><?php endfor; ?></select></div>
                    <div class="mb-3"><label class="form-label">Observaciones</label><textarea class="form-control" name="observaciones" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success" id="btnAdj"><i class="bi bi-cloud-upload me-1"></i>Adjuntar</button></div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('input[name="tipo_carga"]').forEach(r=>r.addEventListener('change',function(){const a=this.value==='archivo';document.getElementById('cArch').classList.toggle('d-none',!a);document.getElementById('cEnl').classList.toggle('d-none',a)}));
document.querySelector('form')?.addEventListener('submit',function(){document.getElementById('btnAdj').disabled=true;document.getElementById('btnAdj').innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...'});
</script>
