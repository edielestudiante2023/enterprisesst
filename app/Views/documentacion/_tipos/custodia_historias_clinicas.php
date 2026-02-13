<?php
/**
 * Vista de Tipo: 3.1.5 Custodia de Historias Clinicas
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
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAdjuntar"><i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte</button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-danger mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-file-earmark-lock me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Custodia de Historias Clinicas Ocupacionales</h6>
            <p class="mb-0 small">Adjunte soportes de custodia de historias clinicas: contratos con IPS custodio, actas de entrega, politicas de confidencialidad, acuerdos de custodia, certificaciones, etc.</p>
        </div>
    </div>
</div>

<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $documentosSSTAprobados ?? [],
    'titulo' => 'Soportes Custodia Historias Clinicas',
    'subtitulo' => 'Documentos de custodia',
    'icono' => 'bi-file-earmark-lock',
    'colorHeader' => 'secondary',
    'codigoDefault' => 'SOP-CHC',
    'emptyIcon' => 'bi-file-earmark-lock',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar documentos.'
]) ?>

<div class="row"><?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?></div>

<div class="modal fade" id="modalAdjuntar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="<?= base_url('documentos-sst/adjuntar-soporte-custodia-hc') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <div class="mb-3"><label class="form-label fw-bold">Tipo de carga</label><div class="btn-group w-100"><input type="radio" class="btn-check" name="tipo_carga" id="tcArch" value="archivo" checked><label class="btn btn-outline-danger" for="tcArch"><i class="bi bi-file-earmark-arrow-up me-1"></i>Archivo</label><input type="radio" class="btn-check" name="tipo_carga" id="tcEnl" value="enlace"><label class="btn btn-outline-danger" for="tcEnl"><i class="bi bi-link-45deg me-1"></i>Enlace</label></div></div>
                    <div class="mb-3" id="cArch"><label class="form-label">Archivo</label><input type="file" class="form-control" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx"></div>
                    <div class="mb-3 d-none" id="cEnl"><label class="form-label">Enlace</label><input type="url" class="form-control" name="url_externa" placeholder="https://..."></div>
                    <div class="mb-3"><label class="form-label">Descripcion</label><input type="text" class="form-control" name="descripcion" required placeholder="Ej: Contrato custodia IPS, Acta entrega HC..."></div>
                    <div class="mb-3"><label class="form-label">Ano</label><select class="form-select" name="anio"><?php for($y=date('Y');$y>=2020;$y--): ?><option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option><?php endfor; ?></select></div>
                    <div class="mb-3"><label class="form-label">Observaciones</label><textarea class="form-control" name="observaciones" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger" id="btnAdj"><i class="bi bi-cloud-upload me-1"></i>Adjuntar</button></div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('input[name="tipo_carga"]').forEach(r=>r.addEventListener('change',function(){const a=this.value==='archivo';document.getElementById('cArch').classList.toggle('d-none',!a);document.getElementById('cEnl').classList.toggle('d-none',a)}));
document.querySelector('form')?.addEventListener('submit',function(){document.getElementById('btnAdj').disabled=true;document.getElementById('btnAdj').innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...'});
</script>
