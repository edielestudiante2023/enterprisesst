<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            margin-top: 30px;
        }
        .section-header:first-child {
            margin-top: 0;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/contracts') ?>">
                <i class="fas fa-file-contract"></i> Gestión de Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/contracts') ?>">
                    <i class="fas fa-arrow-left"></i> Volver a Contratos
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">
                <i class="fas fa-plus-circle text-success"></i> Crear Nuevo Contrato
            </h2>

            <!-- Mensajes Flash -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('contracts/store') ?>" method="POST" id="contractForm">
                <?= csrf_field() ?>

                <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Básica del Contrato</h5>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_cliente" class="form-label required-field">Cliente</label>
                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                            <option value="">Seleccione un cliente...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id_cliente'] ?>"
                                        <?= (isset($selected_client) && $selected_client == $client['id_cliente']) ? 'selected' : '' ?>>
                                    <?= esc($client['nombre_cliente']) ?> - <?= esc($client['nit_cliente']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tipo_contrato" class="form-label required-field">Tipo de Contrato</label>
                        <select class="form-select" id="tipo_contrato" name="tipo_contrato" required>
                            <option value="inicial" selected>Inicial</option>
                            <option value="renovacion">Renovación</option>
                            <option value="ampliacion">Ampliación</option>
                        </select>
                        <small class="text-muted">Seleccione "Inicial" para el primer contrato del cliente</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label required-field">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin" class="form-label required-field">Fecha de Finalización</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_contrato" class="form-label required-field">Valor Total del Contrato</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="valor_contrato" name="valor_contrato"
                                   placeholder="0" required>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="valor_mensual" class="form-label">Valor Mensual</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="valor_mensual" name="valor_mensual"
                                   placeholder="Se calcula automáticamente" readonly>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="numero_cuotas" class="form-label">Número de Cuotas</label>
                        <input type="number" class="form-control" id="numero_cuotas" name="numero_cuotas"
                               placeholder="12" min="1">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="frecuencia_visitas" class="form-label">Frecuencia de Visitas</label>
                        <select class="form-select" id="frecuencia_visitas" name="frecuencia_visitas">
                            <option value="MENSUAL" selected>Mensual</option>
                            <option value="BIMENSUAL">Bimensual</option>
                            <option value="TRIMESTRAL">Trimestral</option>
                            <option value="SEMESTRAL">Semestral</option>
                            <option value="ANUAL">Anual</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="activo" selected>Activo</option>
                            <option value="vencido">Vencido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                              placeholder="Notas adicionales sobre el contrato..."></textarea>
                </div>

                <!-- SECCIÓN: CLÁUSULA CUARTA - DURACIÓN -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Cláusula Cuarta - Duración y Plazo de Ejecución</h5>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <button type="button" class="btn btn-outline-primary" id="btnGenerarIA" onclick="abrirSweetAlertIA()">
                        <i class="fas fa-robot me-1"></i> Generar con IA
                    </button>
                    <small class="text-muted">
                        Ingrese los acuerdos contractuales y la IA redactará la cláusula por usted
                    </small>
                </div>

                <!-- Barra de herramientas post-generación (oculta hasta que se genere) -->
                <div class="gap-2 mb-2" id="toolbarIA" style="display: none;">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="abrirSweetAlertIA(true)" title="Regenerar con acuerdos modificados">
                        <i class="fas fa-sync-alt me-1"></i> Regenerar todo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="abrirRefinar()" title="Agregar instrucciones para refinar el texto">
                        <i class="fas fa-magic me-1"></i> Refinar con contexto
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarClausula()" title="Vaciar el textarea">
                        <i class="fas fa-eraser me-1"></i> Limpiar
                    </button>
                </div>

                <div class="mb-3">
                    <label for="clausula_cuarta_duracion" class="form-label">
                        <i class="fas fa-file-contract"></i> Texto de la Cláusula Cuarta
                    </label>
                    <textarea class="form-control" id="clausula_cuarta_duracion" name="clausula_cuarta_duracion" rows="12"
                              placeholder="Escriba manualmente o use el botón 'Generar con IA' para redactar esta cláusula automáticamente..."></textarea>
                    <small class="text-muted">
                        Este texto aparecerá en el PDF del contrato como la CLÁUSULA CUARTA. Puede editarlo libremente después de generarlo.
                    </small>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> Después de crear el contrato, podrá completar los datos adicionales
                    (representantes legales, datos bancarios, etc.) y generar el PDF del contrato.
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('/contracts') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Crear Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Inicializar Select2 en el selector de clientes
        $(document).ready(function() {
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cliente por nombre o NIT...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron clientes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });

        // Calcular valor mensual automáticamente
        document.getElementById('valor_contrato').addEventListener('input', calcularValorMensual);
        document.getElementById('fecha_inicio').addEventListener('change', calcularValorMensual);
        document.getElementById('fecha_fin').addEventListener('change', calcularValorMensual);

        function calcularValorMensual() {
            const valorTotal = parseFloat(document.getElementById('valor_contrato').value) || 0;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            if (valorTotal > 0 && fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);

                // Calcular diferencia en meses
                const meses = (fin.getFullYear() - inicio.getFullYear()) * 12 +
                             (fin.getMonth() - inicio.getMonth());

                if (meses > 0) {
                    const valorMensual = Math.round(valorTotal / meses);
                    document.getElementById('valor_mensual').value = valorMensual;
                    document.getElementById('numero_cuotas').value = meses;
                }
            }
        }

        // Validar que la fecha de fin sea posterior a la fecha de inicio
        document.getElementById('contractForm').addEventListener('submit', function(e) {
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaFin = new Date(document.getElementById('fecha_fin').value);

            if (fechaFin <= fechaInicio) {
                e.preventDefault();
                alert('La fecha de finalización debe ser posterior a la fecha de inicio');
                return false;
            }
        });

        // Calcular fecha de fin automáticamente (1 año después del inicio)
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            if (!document.getElementById('fecha_fin').value) {
                const fechaInicio = new Date(this.value);
                fechaInicio.setFullYear(fechaInicio.getFullYear() + 1);
                const fechaFin = fechaInicio.toISOString().split('T')[0];
                document.getElementById('fecha_fin').value = fechaFin;
                calcularValorMensual();
            }
        });

        // ============================================================
        // GENERACIÓN DE CLÁUSULA CUARTA CON IA
        // ============================================================

        // Almacenar los últimos acuerdos ingresados para pre-llenar al regenerar
        let ultimosAcuerdos = {};

        function calcularDuracionDesdeFormulario() {
            const fi = document.getElementById('fecha_inicio').value;
            const ff = document.getElementById('fecha_fin').value;
            if (fi && ff) {
                const inicio = new Date(fi);
                const fin = new Date(ff);
                const meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
                if (meses > 0) return meses + ' meses';
            }
            return '';
        }

        function abrirSweetAlertIA(precargar = false) {
            const idCliente = document.getElementById('id_cliente').value;
            if (!idCliente) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione un cliente',
                    text: 'Debe seleccionar un cliente antes de generar la cláusula con IA.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const duracionAuto = calcularDuracionDesdeFormulario();

            Swal.fire({
                title: '<i class="fas fa-robot"></i> Acuerdos Contractuales',
                html: `
                    <div class="text-start" style="font-size: 14px;">
                        <p class="text-muted mb-3">Ingrese los acuerdos negociados con el cliente. La IA redactará la cláusula con lenguaje jurídico formal.</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Plazo de ejecución</label>
                            <input type="text" id="swal_plazo" class="form-control"
                                   placeholder="Ej: 30 días calendario"
                                   value="${precargar ? (ultimosAcuerdos.plazo_ejecucion || '') : ''}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Duración del contrato</label>
                            <input type="text" id="swal_duracion" class="form-control"
                                   placeholder="Ej: 6 meses"
                                   value="${precargar ? (ultimosAcuerdos.duracion_contrato || duracionAuto) : duracionAuto}">
                            <small class="text-muted">${duracionAuto ? 'Calculado de las fechas: ' + duracionAuto : 'Complete las fechas del contrato para auto-calcular'}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Porcentaje de anticipo</label>
                            <input type="text" id="swal_anticipo" class="form-control"
                                   placeholder="Ej: 50%"
                                   value="${precargar ? (ultimosAcuerdos.porcentaje_anticipo || '') : ''}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Condiciones de pago</label>
                            <textarea id="swal_pago" class="form-control" rows="2"
                                      placeholder="Ej: 50% anticipo, 50% contra entrega del diseño documental">${precargar ? (ultimosAcuerdos.condiciones_pago || '') : ''}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Terminación anticipada</label>
                            <textarea id="swal_terminacion" class="form-control" rows="2"
                                      placeholder="Ej: Solo se reconocen honorarios causados por actividades ejecutadas">${precargar ? (ultimosAcuerdos.terminacion_anticipada || '') : ''}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Obligaciones especiales</label>
                            <textarea id="swal_obligaciones" class="form-control" rows="2"
                                      placeholder="Ej: Entrega de diseño documental, gestión ante MinTrabajo...">${precargar ? (ultimosAcuerdos.obligaciones_especiales || '') : ''}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contexto adicional <small class="text-muted">(opcional)</small></label>
                            <textarea id="swal_contexto" class="form-control" rows="2"
                                      placeholder="Cualquier otra información relevante para la cláusula...">${precargar ? (ultimosAcuerdos.contexto_adicional || '') : ''}</textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-robot me-1"></i> Generar Cláusula',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                width: '700px',
                customClass: {
                    popup: 'text-start'
                },
                preConfirm: () => {
                    const acuerdos = {
                        id_cliente: idCliente,
                        plazo_ejecucion: document.getElementById('swal_plazo').value,
                        duracion_contrato: document.getElementById('swal_duracion').value,
                        porcentaje_anticipo: document.getElementById('swal_anticipo').value,
                        condiciones_pago: document.getElementById('swal_pago').value,
                        terminacion_anticipada: document.getElementById('swal_terminacion').value,
                        obligaciones_especiales: document.getElementById('swal_obligaciones').value,
                        contexto_adicional: document.getElementById('swal_contexto').value
                    };

                    // Verificar que al menos un campo tenga datos
                    const tieneAlgo = Object.entries(acuerdos)
                        .filter(([k]) => k !== 'id_cliente')
                        .some(([, v]) => v.trim() !== '');

                    if (!tieneAlgo) {
                        Swal.showValidationMessage('Ingrese al menos un acuerdo contractual');
                        return false;
                    }

                    ultimosAcuerdos = { ...acuerdos };
                    return acuerdos;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    generarClausulaConIA(result.value, false);
                }
            });
        }

        function abrirRefinar() {
            const textoActual = document.getElementById('clausula_cuarta_duracion').value;
            if (!textoActual.trim()) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin texto para refinar',
                    text: 'Primero genere o escriba un texto de cláusula para poder refinarlo.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            Swal.fire({
                title: '<i class="fas fa-magic"></i> Refinar Cláusula',
                html: `
                    <div class="text-start" style="font-size: 14px;">
                        <p class="text-muted mb-3">Indique qué cambios desea aplicar al texto actual de la cláusula.</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Instrucciones de refinamiento</label>
                            <textarea id="swal_refinar" class="form-control" rows="4"
                                      placeholder="Ej: Hazlo más formal, agrega un parágrafo sobre renovación automática, cambia el anticipo a 30%, incluye penalización por incumplimiento..."></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-magic me-1"></i> Refinar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                width: '600px',
                preConfirm: () => {
                    const instrucciones = document.getElementById('swal_refinar').value.trim();
                    if (!instrucciones) {
                        Swal.showValidationMessage('Escriba las instrucciones de refinamiento');
                        return false;
                    }
                    return instrucciones;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const payload = {
                        id_cliente: document.getElementById('id_cliente').value,
                        contexto_adicional: result.value,
                        texto_actual: textoActual,
                        modo_refinamiento: true
                    };
                    generarClausulaConIA(payload, true);
                }
            });
        }

        function generarClausulaConIA(datos, esRefinamiento) {
            // Mostrar loading
            Swal.fire({
                title: esRefinamiento ? 'Refinando cláusula...' : 'Generando cláusula...',
                html: '<div class="d-flex align-items-center justify-content-center gap-2"><div class="spinner-border text-primary" role="status"></div><span>La IA está redactando el texto legal...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            fetch('<?= base_url("/contracts/generar-clausula-ia") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(datos)
            })
            .then(resp => {
                if (!resp.ok) throw new Error('Error del servidor: ' + resp.status);
                return resp.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('clausula_cuarta_duracion').value = data.texto;
                    document.getElementById('toolbarIA').style.display = 'flex';

                    Swal.fire({
                        icon: 'success',
                        title: esRefinamiento ? 'Cláusula refinada' : 'Cláusula generada',
                        text: 'El texto ha sido insertado. Puede editarlo libremente antes de guardar.',
                        confirmButtonColor: '#667eea',
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo generar la cláusula.',
                        confirmButtonColor: '#667eea'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                    confirmButtonColor: '#667eea'
                });
                console.error('Error:', error);
            });
        }

        function limpiarClausula() {
            Swal.fire({
                title: 'Limpiar cláusula',
                text: '¿Está seguro de vaciar el texto de la cláusula?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('clausula_cuarta_duracion').value = '';
                    document.getElementById('toolbarIA').style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
