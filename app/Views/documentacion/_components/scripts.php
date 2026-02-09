<?php
/**
 * Componente: Scripts JavaScript
 * Incluye jQuery para DataTables y Bootstrap
 */
?>
<script>
// Cargar jQuery solo si no está definido
if (typeof jQuery === 'undefined') {
    document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Manejar apertura del modal de adjuntar firmado
document.getElementById('modalAdjuntarFirmado')?.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const idDocumento = button.getAttribute('data-id-documento');
    const titulo = button.getAttribute('data-titulo');

    document.getElementById('adjuntar_id_documento').value = idDocumento;
    document.getElementById('adjuntar_titulo_documento').textContent = titulo;
});

// Manejar envio del formulario
document.getElementById('formAdjuntarFirmado')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarFirmado');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
