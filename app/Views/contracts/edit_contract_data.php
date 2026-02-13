<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Datos del Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .readonly-field {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/contracts/view/' . $contract['id_contrato']) ?>">
                <i class="fas fa-arrow-left"></i> Volver al Contrato
            </a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4"><i class="fas fa-edit"></i> Editar Datos para Generación de Contrato</h2>
        <p class="text-muted">Complete o verifique los datos antes de generar el contrato en PDF</p>

        <form action="<?= base_url('/contracts/save-and-generate/' . $contract['id_contrato']) ?>" method="post">
            <?= csrf_field() ?>

            <!-- Sección 1: Datos del Contrato -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-file-contract"></i> Datos del Contrato</h4>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Número de Contrato *</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?= htmlspecialchars($contract['numero_contrato']) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha de Inicio *</label>
                        <input type="date" name="fecha_inicio" class="form-control"
                               value="<?= $contract['fecha_inicio'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha de Finalización *</label>
                        <input type="date" name="fecha_fin" class="form-control"
                               value="<?= $contract['fecha_fin'] ?>" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Valor Total del Contrato (COP) *</label>
                        <input type="number" name="valor_contrato" id="valor_contrato" class="form-control"
                               value="<?= $contract['valor_contrato'] ?? 3000000 ?>"
                               min="0" step="1000" required>
                        <small class="text-muted">Valor total antes de IVA</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Número de Cuotas *</label>
                        <input type="number" name="numero_cuotas" id="numero_cuotas" class="form-control"
                               value="<?= $contract['numero_cuotas'] ?? 12 ?>"
                               min="1" max="24" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valor Mensual (Calculado)</label>
                        <input type="text" id="valor_mensual_display" class="form-control readonly-field" readonly>
                        <input type="hidden" name="valor_mensual" id="valor_mensual">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Frecuencia de Visitas *</label>
                        <select name="frecuencia_visitas" class="form-select" required>
                            <option value="MENSUAL" <?= ($contract['frecuencia_visitas'] ?? '') === 'MENSUAL' ? 'selected' : '' ?>>MENSUAL</option>
                            <option value="BIMENSUAL" <?= ($contract['frecuencia_visitas'] ?? 'BIMENSUAL') === 'BIMENSUAL' ? 'selected' : '' ?>>BIMENSUAL</option>
                            <option value="TRIMESTRAL" <?= ($contract['frecuencia_visitas'] ?? '') === 'TRIMESTRAL' ? 'selected' : '' ?>>TRIMESTRAL</option>
                            <option value="PROYECTO" <?= ($contract['frecuencia_visitas'] ?? '') === 'PROYECTO' ? 'selected' : '' ?>>PROYECTO (Según cronograma)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Datos del Cliente (EL CONTRATANTE) -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-building"></i> Datos del Cliente (EL CONTRATANTE)</h4>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombre o Razón Social *</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?= htmlspecialchars($contract['nombre_cliente']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIT *</label>
                        <input type="text" class="form-control readonly-field"
                               value="<?= htmlspecialchars($contract['nit_cliente']) ?>" readonly>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Representante Legal *</label>
                        <input type="text" name="nombre_rep_legal_cliente" class="form-control"
                               value="<?= htmlspecialchars($contract['nombre_rep_legal_cliente'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cédula del Representante Legal *</label>
                        <input type="text" name="cedula_rep_legal_cliente" class="form-control"
                               value="<?= htmlspecialchars($contract['cedula_rep_legal_cliente'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Dirección del Cliente *</label>
                        <input type="text" name="direccion_cliente" class="form-control"
                               value="<?= htmlspecialchars($contract['direccion_cliente'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email del Cliente *</label>
                        <input type="email" name="email_cliente" class="form-control"
                               value="<?= htmlspecialchars($contract['email_cliente'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono del Cliente</label>
                        <input type="text" name="telefono_cliente" class="form-control"
                               value="<?= htmlspecialchars($contract['telefono_cliente'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Sección 3: Datos de Cycloid Talent (EL CONTRATISTA) -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-briefcase"></i> Datos de Cycloid Talent (EL CONTRATISTA)</h4>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Estos datos están prellenados y normalmente no necesitan cambios.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Representante Legal</label>
                        <input type="text" name="nombre_rep_legal_contratista" class="form-control"
                               value="<?= $contract['nombre_rep_legal_contratista'] ?? 'DIANA PATRICIA CUESTAS NAVIA' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cédula</label>
                        <input type="text" name="cedula_rep_legal_contratista" class="form-control"
                               value="<?= $contract['cedula_rep_legal_contratista'] ?? '52.425.982' ?>">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email_contratista" class="form-control"
                               value="<?= $contract['email_contratista'] ?? 'Diana.cuestas@cycloidtalent.com' ?>">
                    </div>
                </div>
            </div>

            <!-- Sección 4: Responsable SG-SST -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-user-shield"></i> Responsable SG-SST Asignado</h4>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Seleccionar Consultor Responsable <span class="text-danger">*</span></label>
                        <select name="id_consultor_responsable" id="consultor_select" class="form-select" required>
                            <option value="">-- Seleccione un consultor --</option>
                            <?php foreach ($consultores as $consultor): ?>
                                <option value="<?= $consultor['id_consultor'] ?>"
                                        data-nombre="<?= htmlspecialchars($consultor['nombre_consultor']) ?>"
                                        data-cedula="<?= $consultor['cedula_consultor'] ?>"
                                        data-licencia="<?= htmlspecialchars($consultor['numero_licencia']) ?>"
                                        data-email="<?= htmlspecialchars($consultor['correo_consultor']) ?>"
                                        <?= (isset($contract['id_consultor_responsable']) && $contract['id_consultor_responsable'] == $consultor['id_consultor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($consultor['nombre_consultor']) ?>
                                    - Lic: <?= htmlspecialchars($consultor['numero_licencia']) ?>
                                    - CC: <?= $consultor['cedula_consultor'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Los datos del consultor seleccionado se usarán en el contrato
                        </small>
                    </div>
                </div>

                <!-- Campos ocultos que se llenarán automáticamente -->
                <input type="hidden" name="nombre_responsable_sgsst" id="nombre_responsable_sgsst" value="<?= $contract['nombre_responsable_sgsst'] ?? '' ?>">
                <input type="hidden" name="cedula_responsable_sgsst" id="cedula_responsable_sgsst" value="<?= $contract['cedula_responsable_sgsst'] ?? '' ?>">
                <input type="hidden" name="licencia_responsable_sgsst" id="licencia_responsable_sgsst" value="<?= $contract['licencia_responsable_sgsst'] ?? '' ?>">
                <input type="hidden" name="email_responsable_sgsst" id="email_responsable_sgsst" value="<?= $contract['email_responsable_sgsst'] ?? '' ?>">

                <!-- Vista previa de datos del consultor seleccionado -->
                <div class="row mt-3" id="consultor_preview" style="display: none;">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <strong><i class="fas fa-user-check"></i> Consultor Seleccionado:</strong><br>
                            <span id="preview_nombre"></span><br>
                            <small>CC: <span id="preview_cedula"></span> | Licencia: <span id="preview_licencia"></span> | Email: <span id="preview_email"></span></small>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            // Auto-llenar campos cuando se selecciona un consultor
            document.getElementById('consultor_select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    // Llenar campos ocultos
                    document.getElementById('nombre_responsable_sgsst').value = selectedOption.dataset.nombre;
                    document.getElementById('cedula_responsable_sgsst').value = selectedOption.dataset.cedula;
                    document.getElementById('licencia_responsable_sgsst').value = selectedOption.dataset.licencia;
                    document.getElementById('email_responsable_sgsst').value = selectedOption.dataset.email;

                    // Mostrar preview
                    document.getElementById('preview_nombre').textContent = selectedOption.dataset.nombre;
                    document.getElementById('preview_cedula').textContent = selectedOption.dataset.cedula;
                    document.getElementById('preview_licencia').textContent = selectedOption.dataset.licencia;
                    document.getElementById('preview_email').textContent = selectedOption.dataset.email;
                    document.getElementById('consultor_preview').style.display = 'block';
                } else {
                    // Limpiar campos
                    document.getElementById('nombre_responsable_sgsst').value = '';
                    document.getElementById('cedula_responsable_sgsst').value = '';
                    document.getElementById('licencia_responsable_sgsst').value = '';
                    document.getElementById('email_responsable_sgsst').value = '';
                    document.getElementById('consultor_preview').style.display = 'none';
                }
            });

            // Trigger change event on page load if there's a selected consultant
            window.addEventListener('DOMContentLoaded', function() {
                const consultorSelect = document.getElementById('consultor_select');
                if (consultorSelect.value) {
                    consultorSelect.dispatchEvent(new Event('change'));
                }
            });
            </script>

            <!-- Sección 5: Datos Bancarios -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-university"></i> Datos Bancarios para Pagos</h4>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Banco</label>
                        <input type="text" name="banco" class="form-control"
                               value="<?= $contract['banco'] ?? 'Davivienda' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Cuenta</label>
                        <select name="tipo_cuenta" class="form-select">
                            <option value="Ahorros" <?= ($contract['tipo_cuenta'] ?? 'Ahorros') === 'Ahorros' ? 'selected' : '' ?>>Ahorros</option>
                            <option value="Corriente" <?= ($contract['tipo_cuenta'] ?? '') === 'Corriente' ? 'selected' : '' ?>>Corriente</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Número de Cuenta</label>
                        <input type="text" name="cuenta_bancaria" class="form-control"
                               value="<?= $contract['cuenta_bancaria'] ?? '108900260762' ?>">
                    </div>
                </div>
            </div>

            <!-- Sección 6: Cláusula Cuarta - Duración -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-clock"></i> Cláusula Cuarta - Duración y Plazo de Ejecución</h4>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Cláusula Personalizable:</strong> Esta sección debe adaptarse según las condiciones específicas
                    negociadas con el cliente. Incluya información sobre plazos, anticipos, duración, y condiciones de terminación.
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
                <div class="gap-2 mb-2" id="toolbarIA" style="display: <?= !empty($contract['clausula_cuarta_duracion']) ? 'flex' : 'none' ?>;">
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
                    <label class="form-label"><i class="fas fa-file-contract"></i> Texto de la Cláusula Cuarta</label>
                    <textarea name="clausula_cuarta_duracion" id="clausula_cuarta_duracion" class="form-control" rows="12"
                              placeholder="Ejemplo:&#10;&#10;CUARTA-PLAZO DE EJECUCIÓN: El plazo para la ejecución será de 30 días calendario contados a partir de la firma del presente acuerdo y del pago inicial del anticipo del 50%, para la entrega del Diseño Documental, para la gestión del auto reporte se realizará en los tiempos estipulados por el Ministerio de protección Social.&#10;&#10;CUARTA-DURACIÓN: La duración de este contrato es de 6 meses contados a partir de la fecha de la firma y con finalización 30 de abril 2026. No obstante, el contrato podrá ser terminado de forma anticipada por parte de EL CONTRATANTE, en cualquier momento previa comunicación escrita con 30 días calendario de anticipación.&#10;&#10;PARÁGRAFO PRIMERO: En caso de terminación anticipada de este contrato, solo se reconocerán los honorarios causados por actividades ejecutadas hasta dicho momento, y para el pago respectivo EL CONTRATISTA deberá entregar todos los desarrollos, documentos físicos y digitales y demás resultados producto de la ejecución contractual realizados.&#10;&#10;PARÁGRAFO SEGUNDO: Sobre el presente contrato no opera la prórroga automática. Por lo anterior, la intención de prórroga deberá ser discutida entre las partes al finalizar el plazo inicialmente aquí pactado y deberá constar por escrito."><?= esc($contract['clausula_cuarta_duracion'] ?? '') ?></textarea>
                    <small class="text-muted">
                        Este texto aparecerá en el PDF del contrato como la CLÁUSULA CUARTA. Personalícelo según las condiciones del contrato.
                    </small>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="form-section">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-file-pdf"></i> Guardar y Generar Contrato PDF
                        </button>
                        <small class="text-muted d-block mt-2">
                            El contrato se generará y enviará automáticamente a diana.cuestas@cycloidtalent.com
                        </small>
                    </div>
                    <div class="col-md-6">
                        <a href="<?= base_url('/contracts/view/' . $contract['id_contrato']) ?>"
                           class="btn btn-secondary btn-lg w-100">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Calcular valor mensual automáticamente
        function calcularValorMensual() {
            const valorTotal = parseFloat(document.getElementById('valor_contrato').value) || 0;
            const numeroCuotas = parseInt(document.getElementById('numero_cuotas').value) || 12;
            const valorMensual = valorTotal / numeroCuotas;

            document.getElementById('valor_mensual').value = valorMensual.toFixed(2);
            document.getElementById('valor_mensual_display').value =
                '$' + valorMensual.toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' COP';
        }

        // Ejecutar al cargar y al cambiar valores
        document.getElementById('valor_contrato').addEventListener('input', calcularValorMensual);
        document.getElementById('numero_cuotas').addEventListener('input', calcularValorMensual);

        // Calcular al cargar la página
        calcularValorMensual();

        // ============================================================
        // GENERACIÓN DE CLÁUSULA CUARTA CON IA
        // ============================================================

        let ultimosAcuerdos = {};

        function calcularDuracionDesdeFormulario() {
            const fi = document.querySelector('input[name="fecha_inicio"]').value;
            const ff = document.querySelector('input[name="fecha_fin"]').value;
            if (fi && ff) {
                const inicio = new Date(fi);
                const fin = new Date(ff);
                const meses = (fin.getFullYear() - inicio.getFullYear()) * 12 + (fin.getMonth() - inicio.getMonth());
                if (meses > 0) return meses + ' meses';
            }
            return '';
        }

        function abrirSweetAlertIA(precargar = false) {
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
                customClass: { popup: 'text-start' },
                preConfirm: () => {
                    const acuerdos = {
                        id_cliente: '<?= $contract['id_cliente'] ?>',
                        plazo_ejecucion: document.getElementById('swal_plazo').value,
                        duracion_contrato: document.getElementById('swal_duracion').value,
                        fecha_inicio: document.querySelector('input[name="fecha_inicio"]').value,
                        fecha_fin: document.querySelector('input[name="fecha_fin"]').value,
                        porcentaje_anticipo: document.getElementById('swal_anticipo').value,
                        condiciones_pago: document.getElementById('swal_pago').value,
                        terminacion_anticipada: document.getElementById('swal_terminacion').value,
                        obligaciones_especiales: document.getElementById('swal_obligaciones').value,
                        contexto_adicional: document.getElementById('swal_contexto').value
                    };

                    const tieneAlgo = Object.entries(acuerdos)
                        .filter(([k]) => !['id_cliente', 'fecha_inicio', 'fecha_fin'].includes(k))
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
                                      placeholder="Ej: Hazlo más formal, agrega un parágrafo sobre renovación automática, cambia el anticipo a 30%..."></textarea>
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
                        id_cliente: '<?= $contract['id_cliente'] ?>',
                        fecha_inicio: document.querySelector('input[name="fecha_inicio"]').value,
                        fecha_fin: document.querySelector('input[name="fecha_fin"]').value,
                        contexto_adicional: result.value,
                        texto_actual: textoActual,
                        modo_refinamiento: true
                    };
                    generarClausulaConIA(payload, true);
                }
            });
        }

        function generarClausulaConIA(datos, esRefinamiento) {
            Swal.fire({
                title: esRefinamiento ? 'Refinando cláusula...' : 'Generando cláusula...',
                html: '<div class="d-flex align-items-center justify-content-center gap-2"><div class="spinner-border text-primary" role="status"></div><span>La IA está redactando el texto legal...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            const url = '<?= base_url("/contracts/generar-clausula-ia") ?>';
            console.log('[IA] Enviando a:', url, datos);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(datos)
            })
            .then(async resp => {
                const text = await resp.text();
                console.log('[IA] Status:', resp.status, 'Body:', text.substring(0, 500));
                if (!resp.ok) {
                    let errorMsg = 'Error del servidor (HTTP ' + resp.status + ')';
                    try {
                        const errJson = JSON.parse(text);
                        errorMsg = errJson.message || errorMsg;
                    } catch(e) {
                        errorMsg += ': ' + text.substring(0, 200);
                    }
                    throw new Error(errorMsg);
                }
                try {
                    return JSON.parse(text);
                } catch(e) {
                    throw new Error('Respuesta no es JSON: ' + text.substring(0, 200));
                }
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
                console.error('[IA] Error completo:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo conectar con el servidor.',
                    confirmButtonColor: '#667eea'
                });
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
