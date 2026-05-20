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

<!-- SweetAlert2: eleccion de modalidad para Asignacion de Responsable SG-SST (1.1.1) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Pregunta la modalidad de asignacion del responsable y envia el formulario.
// Usada por los botones "Generar Asignacion" (con empresa intermediaria o directa al profesional).
window.elegirModalidadAsignacion = function (form) {
    if (typeof Swal === 'undefined') { form.submit(); return; }
    Swal.fire({
        title: 'Modalidad de asignacion del responsable',
        input: 'radio',
        inputOptions: {
            con_empresa: 'A traves de empresa consultora (la empresa consultora asigna al profesional en SST)',
            directa: 'Asignacion directa al profesional en SST (sin empresa intermediaria)'
        },
        inputValue: 'con_empresa',
        showCancelButton: true,
        confirmButtonText: 'Generar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        inputValidator: function (value) {
            if (!value) { return 'Selecciona una modalidad'; }
        }
    }).then(function (result) {
        if (result.isConfirmed && result.value) {
            const input = form.querySelector('input[name="modalidad"]');
            if (input) { input.value = result.value; }
            form.submit();
        }
    });
};
</script>
