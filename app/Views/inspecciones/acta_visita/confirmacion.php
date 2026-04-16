<div class="container-fluid px-3 text-center" style="padding-top: 40px;">
    <div class="mb-4">
        <div style="width:80px; height:80px; border-radius:50%; background:#28a745; display:inline-flex; align-items:center; justify-content:center;">
            <i class="fas fa-check" style="font-size:40px; color:#fff;"></i>
        </div>
    </div>

    <h5 class="mb-2">Acta generada exitosamente</h5>
    <p class="text-muted" style="font-size:14px;">
        <?= esc($cliente['nombre_cliente'] ?? '') ?><br>
        <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?>
    </p>

    <div class="d-grid gap-2 mt-4" style="max-width:300px; margin:0 auto;">
        <?php if (!empty($acta['ruta_pdf'])): ?>
        <a href="<?= site_url('inspecciones/acta-visita/pdf/' . $acta['id']) ?>" target="_blank" class="btn btn-pwa btn-pwa-primary" style="background:#bd9751; color:#fff; border:none;">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>

        <button type="button" class="btn btn-pwa btn-pwa-outline" id="btnCompartir" style="border:2px solid #1c2437;">
            <i class="fas fa-share-alt"></i> Compartir
        </button>

        <a href="<?= site_url('inspecciones/acta-visita/create') ?>" class="btn btn-pwa btn-pwa-outline" style="border:2px solid #1c2437;">
            <i class="fas fa-plus"></i> Nueva acta
        </a>

        <a href="<?= site_url('inspecciones') ?>" class="btn btn-pwa btn-pwa-outline" style="border:2px solid #1c2437;">
            <i class="fas fa-home"></i> Volver al inicio
        </a>
    </div>
</div>

<script>
document.getElementById('btnCompartir')?.addEventListener('click', function() {
    const pdfUrl = '<?= base_url('/inspecciones/acta-visita/pdf/' . $acta['id']) ?>';
    if (navigator.share) {
        navigator.share({
            title: 'Acta de Visita - <?= esc($cliente['nombre_cliente'] ?? '') ?>',
            text: 'Acta de visita del <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?>',
            url: pdfUrl
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(pdfUrl).then(() => {
            Swal.fire({ icon: 'success', title: 'Link copiado', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        });
    }
});
</script>
