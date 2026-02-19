/**
 * firma-helpers.js
 * Funciones compartidas para todos los sistemas de firma digital.
 * - Copiar enlace de firma al portapapeles
 * - Enviar a email alternativo via SweetAlert2 + AJAX
 *
 * Dependencias: SweetAlert2 (ya incluido en el layout principal)
 */

/**
 * Copia la URL de firma al portapapeles y muestra toast de confirmación.
 * @param {string} url - URL pública de firma
 * @param {string} nombreFirmante - Nombre del firmante (para el toast)
 */
function copiarEnlaceFirma(url, nombreFirmante) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            mostrarToastFirma('success', 'Enlace copiado para ' + nombreFirmante);
        }).catch(function() {
            copiarEnlaceFallback(url, nombreFirmante);
        });
    } else {
        copiarEnlaceFallback(url, nombreFirmante);
    }
}

/**
 * Fallback para navegadores que no soportan navigator.clipboard
 */
function copiarEnlaceFallback(url, nombreFirmante) {
    var textarea = document.createElement('textarea');
    textarea.value = url;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        mostrarToastFirma('success', 'Enlace copiado para ' + nombreFirmante);
    } catch (err) {
        // Si todo falla, mostrar el enlace para copiar manualmente
        Swal.fire({
            title: 'Copiar enlace',
            html: '<input type="text" class="form-control" value="' + url + '" id="swal-enlace-firma" readonly>',
            confirmButtonText: 'Cerrar',
            didOpen: function() {
                document.getElementById('swal-enlace-firma').select();
            }
        });
    }
    document.body.removeChild(textarea);
}

/**
 * Abre modal SweetAlert2 para enviar la solicitud de firma a un email alternativo.
 * @param {string} urlReenvio - URL del endpoint POST de reenvío
 * @param {string} nombreFirmante - Nombre del firmante
 * @param {string} emailOriginal - Email registrado en la plataforma (referencia)
 */
function modalEmailAlternativo(urlReenvio, nombreFirmante, emailOriginal) {
    Swal.fire({
        title: 'Enviar a email alternativo',
        html:
            '<div class="text-start mb-3">' +
                '<small class="text-muted">Firmante: <strong>' + nombreFirmante + '</strong></small><br>' +
                '<small class="text-muted">Email registrado: ' + emailOriginal + '</small>' +
            '</div>' +
            '<div class="text-start">' +
                '<label class="form-label fw-bold">Email alternativo:</label>' +
                '<input type="email" id="swal-email-alt" class="form-control" ' +
                    'placeholder="correo.personal@gmail.com" autocomplete="email">' +
                '<small class="text-muted mt-1 d-block">El enlace de firma se enviará a este correo</small>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i>Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f0ad4e',
        focusConfirm: false,
        preConfirm: function() {
            var email = document.getElementById('swal-email-alt').value.trim();
            if (!email) {
                Swal.showValidationMessage('Ingrese un email');
                return false;
            }
            if (!validarEmailFirma(email)) {
                Swal.showValidationMessage('Email no válido');
                return false;
            }
            return email;
        }
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            enviarEmailAlternativo(urlReenvio, result.value, nombreFirmante);
        }
    });
}

/**
 * Envía POST AJAX al endpoint de reenvío con email alternativo.
 */
function enviarEmailAlternativo(urlReenvio, emailAlternativo, nombreFirmante) {
    // Mostrar loading
    Swal.fire({
        title: 'Enviando...',
        html: 'Enviando solicitud de firma a <strong>' + emailAlternativo + '</strong>',
        allowOutsideClick: false,
        didOpen: function() {
            Swal.showLoading();
        }
    });

    var formData = new FormData();
    formData.append('email_alternativo', emailAlternativo);

    // Obtener CSRF token si existe
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfInput = document.querySelector('input[name="csrf_test_name"]');
    if (csrfMeta) {
        formData.append('csrf_test_name', csrfMeta.getAttribute('content'));
    } else if (csrfInput) {
        formData.append('csrf_test_name', csrfInput.value);
    }

    fetch(urlReenvio, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Enviado',
                html: 'Solicitud de firma enviada a<br><strong>' + emailAlternativo + '</strong>',
                timer: 4000,
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.mensaje || 'No se pudo enviar el email'
            });
        }
    })
    .catch(function(error) {
        console.error('Error enviarEmailAlternativo:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Intente nuevamente.'
        });
    });
}

/**
 * Validación simple de email.
 */
function validarEmailFirma(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Toast reutilizable para feedback de acciones de firma.
 */
function mostrarToastFirma(tipo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
