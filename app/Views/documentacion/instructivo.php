<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructivo - Modulo de Documentacion SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366F1;
            --secondary-color: #8B5CF6;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
            min-height: 100vh;
        }
        .header-gradient {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 50%, #bd9751 100%);
        }
        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }
        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }
        .table-phva th {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
        }
        .badge-planear { background: #3B82F6; }
        .badge-hacer { background: #10B981; }
        .badge-verificar { background: #F59E0B; }
        .badge-actuar { background: #EF4444; }
        .code-box {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Consolas', monospace;
            font-size: 0.9rem;
        }
        .tip-box {
            border-left: 4px solid var(--primary-color);
            background: #f0f4ff;
        }
        .faq-question {
            font-weight: 600;
            color: var(--primary-color);
        }
        .version-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
        .toc-link {
            color: #475569;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: block;
            transition: all 0.2s;
        }
        .toc-link:hover {
            background: #e2e8f0;
            color: var(--primary-color);
        }
        .print-only { display: none; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block; }
            .accordion-collapse { display: block !important; height: auto !important; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark header-gradient sticky-top no-print">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-book me-2"></i>Instructivo Modulo SST
            </span>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </button>
                <a href="<?= base_url('consultant/dashboard') ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar - Tabla de Contenidos -->
            <div class="col-lg-3 mb-4 no-print">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Contenido</h6>
                    </div>
                    <div class="card-body p-2">
                        <a href="#intro" class="toc-link"><i class="bi bi-info-circle me-2"></i>Introduccion</a>
                        <a href="#acceso" class="toc-link"><i class="bi bi-door-open me-2"></i>Acceso al Modulo</a>
                        <a href="#contexto" class="toc-link"><i class="bi bi-gear me-2"></i>Contexto SST</a>
                        <a href="#catalogo" class="toc-link"><i class="bi bi-book me-2"></i>Catalogo 60 Estandares</a>
                        <a href="#documentacion" class="toc-link"><i class="bi bi-file-earmark-text me-2"></i>Documentacion</a>
                        <a href="#cumplimiento" class="toc-link"><i class="bi bi-graph-up me-2"></i>Cumplimiento PHVA</a>
                        <a href="#plantillas" class="toc-link"><i class="bi bi-magic me-2"></i>Plantillas</a>
                        <a href="#flujo" class="toc-link"><i class="bi bi-diagram-3 me-2"></i>Flujo de Trabajo</a>
                        <a href="#faq" class="toc-link"><i class="bi bi-question-circle me-2"></i>Preguntas Frecuentes</a>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="col-lg-9">
                <!-- Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center py-4">
                        <h1 class="display-6 fw-bold text-primary mb-2">
                            <i class="bi bi-journal-check me-2"></i>
                            Instructivo del Modulo de Documentacion SST
                        </h1>
                        <p class="lead text-muted mb-0">
                            Resolucion 0312 de 2019 - Sistema de Gestion de Seguridad y Salud en el Trabajo
                        </p>
                    </div>
                </div>

                <!-- Acordeon Principal -->
                <div class="accordion" id="instructivoAccordion">

                    <!-- 1. Introduccion -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="intro">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIntro">
                                <span class="section-icon bg-primary text-white">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                                1. Introduccion
                            </button>
                        </h2>
                        <div id="collapseIntro" class="accordion-collapse collapse show" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <p>El <strong>Modulo de Documentacion SST</strong> permite gestionar el cumplimiento de los 60 estandares minimos establecidos en la Resolucion 0312 de 2019 para el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.</p>

                                <h6 class="mt-4 mb-3"><i class="bi bi-check2-square me-2 text-success"></i>Caracteristicas principales:</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                            <span>Catalogo completo de los 60 estandares organizados por ciclo PHVA</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                            <span>Seguimiento de cumplimiento por cliente</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                            <span>Generacion de documentos con IA (GPT-4o-mini)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                            <span>Clasificacion por niveles (7, 21 o 60 estandares)</span>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4 mb-3"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Ciclo PHVA</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-phva">
                                        <thead>
                                            <tr>
                                                <th width="80">Ciclo</th>
                                                <th width="120">Nombre</th>
                                                <th>Descripcion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge badge-planear">P</span></td>
                                                <td><strong>PLANEAR</strong></td>
                                                <td>Definicion de politicas, objetivos, recursos y planificacion</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-hacer">H</span></td>
                                                <td><strong>HACER</strong></td>
                                                <td>Implementacion de medidas de prevencion y control</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-verificar">V</span></td>
                                                <td><strong>VERIFICAR</strong></td>
                                                <td>Seguimiento, medicion, auditoria y revision</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-actuar">A</span></td>
                                                <td><strong>ACTUAR</strong></td>
                                                <td>Acciones correctivas, preventivas y de mejora</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Acceso al Modulo -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="acceso">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcceso">
                                <span class="section-icon bg-success text-white">
                                    <i class="bi bi-door-open"></i>
                                </span>
                                2. Acceso al Modulo
                            </button>
                        </h2>
                        <div id="collapseAcceso" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <h6 class="mb-3">Desde el Dashboard del Consultor:</h6>
                                <ol>
                                    <li>Inicie sesion en el sistema</li>
                                    <li>Acceda al <strong>Dashboard del Consultor</strong></li>
                                    <li>Ubique la seccion <strong>"Documentacion SST - Resolucion 0312/2019"</strong></li>
                                </ol>

                                <h6 class="mt-4 mb-3">Botones disponibles:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Boton</th>
                                                <th>Color</th>
                                                <th>Funcion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-book me-2"></i>Instructivo</td>
                                                <td><span class="badge" style="background: linear-gradient(135deg, #dc2626, #f97316);">Rojo/Naranja</span></td>
                                                <td>Ver este instructivo de ayuda</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-gear me-2"></i>Contexto Cliente</td>
                                                <td><span class="badge" style="background: linear-gradient(135deg, #667eea, #764ba2);">Morado</span></td>
                                                <td>Configurar informacion base del cliente</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-book me-2"></i>Catalogo 60 Estandares</td>
                                                <td><span class="badge" style="background: linear-gradient(135deg, #11998e, #38ef7d);">Verde</span></td>
                                                <td>Ver todos los estandares de referencia</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-file-earmark-text me-2"></i>Documentacion</td>
                                                <td><span class="badge" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">Azul</span></td>
                                                <td>Gestionar documentos de un cliente</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-arrow-repeat me-2"></i>Cumplimiento PHVA</td>
                                                <td><span class="badge" style="background: linear-gradient(135deg, #f2994a, #f2c94c);">Naranja</span></td>
                                                <td>Ver estado de cumplimiento por cliente</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-magic me-2"></i>Plantillas Documentos</td>
                                                <td><span class="badge" style="background: linear-gradient(135deg, #834d9b, #d04ed6);">Purpura</span></td>
                                                <td>Acceder a plantillas predefinidas</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert tip-box mt-4">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    <strong>Tip:</strong> El selector de clientes permite buscar por <strong>nombre</strong> o <strong>NIT</strong>. Todos los consultores pueden ver todos los clientes activos.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Contexto SST -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="contexto">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                                <span class="section-icon bg-info text-white">
                                    <i class="bi bi-gear"></i>
                                </span>
                                3. Contexto SST del Cliente
                            </button>
                        </h2>
                        <div id="collapseContexto" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <p>Es la informacion base de cada cliente que se utiliza para generacion de documentos con IA, calculo de estandares aplicables y configuracion de firmantes.</p>

                                <div class="code-box mb-4">
                                    <span class="text-warning">/contexto</span> → Selector de cliente<br>
                                    <span class="text-warning">/contexto/{id}</span> → Formulario de contexto
                                </div>

                                <h6 class="mb-3">Secciones del formulario:</h6>

                                <!-- Sub-acordeon para secciones -->
                                <div class="accordion accordion-flush" id="contextoSecciones">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx1">
                                                <strong>1. Datos de la Empresa</strong> <small class="text-muted ms-2">(solo lectura)</small>
                                            </button>
                                        </h2>
                                        <div id="ctx1" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                Razon Social, NIT, Ciudad, Representante Legal, Actividad Economica
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx2">
                                                <strong>2. Clasificacion Empresarial</strong>
                                            </button>
                                        </h2>
                                        <div id="ctx2" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                <ul class="mb-0">
                                                    <li><strong>Sector Economico:</strong> Seleccione el sector</li>
                                                    <li><strong>Niveles de Riesgo ARL:</strong> Seleccione TODOS los niveles que aplican (checkboxes)</li>
                                                    <li><strong>Estandares Aplicables:</strong> 7, 21 o 60 (definido por el consultor)</li>
                                                    <li><strong>ARL Actual:</strong> Seleccione la ARL</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx3">
                                                <strong>3. Tamano y Estructura</strong>
                                            </button>
                                        </h2>
                                        <div id="ctx3" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                Total de trabajadores, directos, temporales, contratistas, numero de sedes, turnos de trabajo
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx4">
                                                <strong>4. Informacion SST</strong>
                                            </button>
                                        </h2>
                                        <div id="ctx4" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                Responsable del SG-SST (consultor del sistema), COPASST, Vigia SST, Comite de Convivencia, Brigada de Emergencias
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx5">
                                                <strong>5. Peligros Identificados</strong>
                                            </button>
                                        </h2>
                                        <div id="ctx5" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                Fisicos, Quimicos, Biologicos, Biomecanicos, Psicosociales, Condiciones de Seguridad, Fenomenos Naturales
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx6">
                                                <strong>6. Contexto y Observaciones</strong> <span class="badge bg-success ms-2">Nuevo</span>
                                            </button>
                                        </h2>
                                        <div id="ctx6" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                Informacion cualitativa: operaciones reales, cultura de seguridad, riesgos no documentados, estructura informal. <strong>Esta informacion es usada por la IA para generar documentos mas relevantes.</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ctx7">
                                                <strong>7. Firmantes de Documentos</strong>
                                            </button>
                                        </h2>
                                        <div id="ctx7" class="accordion-collapse collapse" data-bs-parent="#contextoSecciones">
                                            <div class="accordion-body py-2">
                                                Toggle para Delegado SST, datos del delegado (si aplica), datos del Representante Legal
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Catalogo de 60 Estandares -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="catalogo">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCatalogo">
                                <span class="section-icon bg-warning text-dark">
                                    <i class="bi bi-book"></i>
                                </span>
                                4. Catalogo de 60 Estandares
                            </button>
                        </h2>
                        <div id="collapseCatalogo" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <p>Vista de referencia con los 60 estandares minimos del SG-SST organizados por ciclo PHVA y categoria.</p>

                                <div class="code-box mb-4">
                                    <span class="text-warning">/estandares/catalogo</span>
                                </div>

                                <h6 class="mb-3">Niveles de aplicacion:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nivel</th>
                                                <th>Aplica a</th>
                                                <th>Criterio</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-success">7 estandares</span></td>
                                                <td>Empresas ≤10 trabajadores, riesgo I-III</td>
                                                <td>Nivel basico</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">21 estandares</span></td>
                                                <td>Empresas 11-50 trabajadores, riesgo I-III</td>
                                                <td>Nivel intermedio</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-primary">60 estandares</span></td>
                                                <td>Empresas >50 trabajadores o riesgo IV-V</td>
                                                <td>Nivel completo</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <h6 class="mt-4 mb-3">Categorias por ciclo PHVA:</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white py-2">
                                                <strong>PLANEAR</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <small>I. Recursos<br>II. Gestion Integral del SG-SST</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white py-2">
                                                <strong>HACER</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <small>III. Gestion de la Salud<br>IV. Gestion de Peligros y Riesgos<br>V. Gestion de Amenazas</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning text-dark py-2">
                                                <strong>VERIFICAR</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <small>VI. Verificacion del SG-SST</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-danger">
                                            <div class="card-header bg-danger text-white py-2">
                                                <strong>ACTUAR</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <small>VII. Mejoramiento</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Documentacion por Cliente -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="documentacion">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocumentacion">
                                <span class="section-icon bg-primary text-white">
                                    <i class="bi bi-file-earmark-text"></i>
                                </span>
                                5. Documentacion por Cliente
                            </button>
                        </h2>
                        <div id="collapseDocumentacion" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <p>Gestione todos los documentos SST de un cliente, organizados en carpetas segun el tipo de documento.</p>

                                <div class="code-box mb-4">
                                    <span class="text-warning">/documentacion</span> → Selector de cliente<br>
                                    <span class="text-warning">/documentacion/{id}</span> → Dashboard de documentos
                                </div>

                                <h6 class="mb-3">Tipos de documentos:</h6>
                                <div class="row g-2">
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">POL</span>Politica</div>
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">PRG</span>Programa</div>
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">PLA</span>Plan</div>
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">PRO</span>Procedimiento</div>
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">MAN</span>Manual</div>
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">FOR</span>Formato</div>
                                    <div class="col-md-4"><span class="badge bg-secondary me-2">REG</span>Reglamento</div>
                                </div>

                                <h6 class="mt-4 mb-3">Acciones disponibles:</h6>
                                <ul>
                                    <li><i class="bi bi-eye me-2"></i>Ver documento</li>
                                    <li><i class="bi bi-pencil me-2"></i>Editar documento</li>
                                    <li><i class="bi bi-download me-2"></i>Descargar PDF</li>
                                    <li><i class="bi bi-pen me-2"></i>Solicitar firma electronica</li>
                                    <li><i class="bi bi-clock-history me-2"></i>Ver historial de versiones</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Cumplimiento PHVA -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="cumplimiento">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCumplimiento">
                                <span class="section-icon" style="background: linear-gradient(135deg, #f2994a, #f2c94c);">
                                    <i class="bi bi-graph-up text-white"></i>
                                </span>
                                6. Cumplimiento PHVA
                            </button>
                        </h2>
                        <div id="collapseCumplimiento" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <p>Dashboard con el estado de cumplimiento de los estandares para un cliente segun la Resolucion 0312/2019.</p>

                                <div class="code-box mb-4">
                                    <span class="text-warning">/estandares</span> → Selector de cliente<br>
                                    <span class="text-warning">/estandares/{id}</span> → Dashboard de cumplimiento
                                </div>

                                <h6 class="mb-3">Estados de cumplimiento:</h6>
                                <div class="d-flex flex-wrap gap-2 mb-4">
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Cumple</span>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass me-1"></i>En proceso</span>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>No cumple</span>
                                    <span class="badge bg-info"><i class="bi bi-clock me-1"></i>Pendiente</span>
                                    <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>No aplica</span>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Inicializacion Manual:</strong> Al acceder por primera vez al cumplimiento de un cliente, debera hacer clic en <strong>"Inicializar Estandares del Cliente"</strong>. El sistema solicitara <strong>doble confirmacion</strong> antes de crear los registros.
                                </div>

                                <h6 class="mb-3">Proceso de inicializacion:</h6>
                                <ol>
                                    <li>Acceda al dashboard de Cumplimiento PHVA del cliente</li>
                                    <li>Si no hay estandares, vera el boton <strong>"Inicializar Estandares del Cliente"</strong></li>
                                    <li>Al hacer clic, aparece la <strong>primera confirmacion</strong> indicando el nivel de estandares</li>
                                    <li>Si confirma, aparece una <strong>segunda confirmacion</strong> final</li>
                                    <li>Los estandares se crean segun el nivel configurado en Contexto SST (7, 21 o 60)</li>
                                </ol>

                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Importante:</strong> Esta accion solo debe realizarse <strong>una vez al ano</strong> para la autoevaluacion del SG-SST o cuando ingresa un <strong>cliente nuevo</strong> al sistema.
                                </div>

                                <h6 class="mt-4 mb-3">Calificacion automatica (Res. 0312/2019):</h6>
                                <p>La calificacion de cada estandar se calcula automaticamente segun el estado:</p>
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr><th>Estado</th><th>Calificacion</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><span class="badge bg-success">Cumple</span></td><td>100% del peso del estandar</td></tr>
                                        <tr><td><span class="badge bg-secondary">No Aplica</span></td><td>100% del peso (justificado)</td></tr>
                                        <tr><td><span class="badge bg-danger">No Cumple</span></td><td>0%</td></tr>
                                        <tr><td><span class="badge bg-warning text-dark">En Proceso</span></td><td>0%</td></tr>
                                        <tr><td><span class="badge bg-info">Pendiente</span></td><td>0%</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Plantillas de Documentos -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="plantillas">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlantillas">
                                <span class="section-icon" style="background: linear-gradient(135deg, #834d9b, #d04ed6);">
                                    <i class="bi bi-magic text-white"></i>
                                </span>
                                7. Plantillas de Documentos
                            </button>
                        </h2>
                        <div id="collapsePlantillas" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <p>Plantillas predefinidas para generar documentos SST con estructura estandarizada.</p>

                                <div class="code-box mb-4">
                                    <span class="text-warning">/documentacion/plantillas</span>
                                </div>

                                <h6 class="mb-3">Plantillas disponibles:</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #3B82F6; color: white;"><strong>Politicas (POL)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Politica de SST<br>
                                                    - Politica de No Alcohol, Drogas y Tabaco<br>
                                                    - Politica de Prevencion de Acoso Laboral<br>
                                                    - Politica de Seguridad Vial (PESV)<br>
                                                    - Politica de Elementos de Proteccion Personal
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #8B5CF6; color: white;"><strong>Reglamentos (REG)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Reglamento de Higiene y Seguridad Industrial<br>
                                                    - Reglamento Interno de Trabajo (capitulo SST)<br>
                                                    - Reglamento del COPASST<br>
                                                    - Reglamento del Comite de Convivencia
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #10B981; color: white;"><strong>Programas (PRG)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Programa de Capacitacion y Entrenamiento<br>
                                                    - Programa de Vigilancia Epidemiologica<br>
                                                    - Programa de Estilos de Vida Saludable<br>
                                                    - Programa de Riesgo Psicosocial<br>
                                                    - Programa de Orden y Aseo<br>
                                                    - Programa de Mantenimiento Preventivo
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #F59E0B; color: white;"><strong>Procedimientos (PRO)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Procedimiento IPVR<br>
                                                    - Procedimiento Investigacion de Incidentes<br>
                                                    - Procedimiento de Auditorias Internas<br>
                                                    - Procedimiento de Acciones Correctivas<br>
                                                    - Procedimiento de Comunicaciones<br>
                                                    - Procedimiento de Control Documental
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #EF4444; color: white;"><strong>Planes (PLA)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Plan de Trabajo Anual SST<br>
                                                    - Plan de Emergencias y Contingencias<br>
                                                    - Plan de Capacitacion Anual<br>
                                                    - Plan de Mejoramiento
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #6366F1; color: white;"><strong>Manuales (MAN)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Manual del SG-SST<br>
                                                    - Manual de Funciones y Responsabilidades<br>
                                                    - Manual de Contratistas<br>
                                                    - Manual de Bioseguridad
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #64748B; color: white;"><strong>Formatos (FOR)</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - Formato de Inspeccion de Seguridad<br>
                                                    - Formato de Reporte de Incidentes<br>
                                                    - Formato de Entrega de EPP<br>
                                                    - Formato de Asistencia a Capacitacion<br>
                                                    - Formato de Evaluacion de Capacitacion
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header py-2" style="background-color: #0EA5E9; color: white;"><strong>Otros Documentos</strong></div>
                                            <div class="card-body py-2">
                                                <small>
                                                    - <strong>ACT:</strong> Actas de reunion, COPASST, Convivencia<br>
                                                    - <strong>INF:</strong> Informes de gestion, auditorias<br>
                                                    - <strong>MTZ:</strong> Matrices de peligros, requisitos legales<br>
                                                    - <strong>GUA:</strong> Guias e instructivos de trabajo<br>
                                                    - <strong>OBJ:</strong> Objetivos e indicadores SST
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4 mb-3">Como usar una plantilla:</h6>
                                <ol>
                                    <li>Acceda a Plantillas de Documentos</li>
                                    <li>Seleccione la plantilla deseada</li>
                                    <li>Haga clic en "Usar Plantilla"</li>
                                    <li>Seleccione el cliente</li>
                                    <li>El sistema genera el documento con la estructura predefinida</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- 8. Flujo de Trabajo -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="flujo">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFlujo">
                                <span class="section-icon bg-dark text-white">
                                    <i class="bi bi-diagram-3"></i>
                                </span>
                                8. Flujo de Trabajo Recomendado
                            </button>
                        </h2>
                        <div id="collapseFlujo" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <h6 class="mb-3">Para un cliente nuevo:</h6>
                                <div class="code-box mb-4">
<pre class="mb-0">1. CONFIGURACION INICIAL
   ├── Crear cliente en el sistema
   └── Configurar Contexto SST
       ├── Definir niveles de riesgo ARL
       ├── Seleccionar estandares aplicables (7, 21 o 60)
       ├── Asignar consultor responsable
       ├── Identificar peligros
       └── Documentar observaciones y contexto real

2. DIAGNOSTICO <span class="text-warning">(Inicializacion Manual)</span>
   ├── Acceder a "Cumplimiento PHVA" del cliente
   ├── Hacer clic en <span class="text-info">"Inicializar Estandares del Cliente"</span>
   │   └── <span class="text-secondary">Confirmar dos veces (doble confirmacion)</span>
   ├── Revisar todos los estandares aplicables
   ├── Ver detalle de cada estandar (criterio de verificacion)
   └── Marcar estado actual de cada uno (Cumple/No Cumple/En Proceso)

3. DOCUMENTACION
   ├── Identificar documentos faltantes segun diagnostico
   ├── Usar plantillas + IA para crear documentos
   ├── Subir documentos existentes
   └── Vincular documentos a estandares

4. SEGUIMIENTO
   ├── Actualizar estados periodicamente
   │   <span class="text-success">└── La calificacion se calcula automaticamente</span>
   ├── Revisar indicadores de cumplimiento (cards en tiempo real)
   └── Generar reportes de avance</pre>
                                </div>

                                <div class="alert alert-warning mb-4">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Importante:</strong> La inicializacion de estandares requiere <strong>doble confirmacion</strong> y solo debe realizarse:
                                    <ul class="mb-0 mt-2">
                                        <li>Una vez al ano para la autoevaluacion del SG-SST</li>
                                        <li>Cuando ingresa un cliente nuevo al sistema</li>
                                    </ul>
                                </div>

                                <div class="text-center">
                                    <div class="d-inline-flex align-items-center gap-2 p-3 bg-light rounded">
                                        <span class="badge badge-planear fs-6">PLANEAR</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span class="badge badge-hacer fs-6">HACER</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span class="badge badge-verificar fs-6">VERIFICAR</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span class="badge badge-actuar fs-6">ACTUAR</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <i class="bi bi-arrow-repeat fs-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 9. Preguntas Frecuentes -->
                    <div class="accordion-item border-0 shadow-sm mb-3" id="faq">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFAQ">
                                <span class="section-icon bg-danger text-white">
                                    <i class="bi bi-question-circle"></i>
                                </span>
                                9. Preguntas Frecuentes
                            </button>
                        </h2>
                        <div id="collapseFAQ" class="accordion-collapse collapse" data-bs-parent="#instructivoAccordion">
                            <div class="accordion-body">
                                <div class="accordion accordion-flush" id="faqAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                <span class="faq-question">¿Como se que nivel de estandares aplica a mi cliente?</span>
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body py-2">
                                                El nivel lo define <strong>manualmente el consultor</strong> en el Contexto SST, considerando numero de trabajadores y nivel de riesgo.
                                                <table class="table table-sm table-bordered mt-2 mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Trabajadores</th><th>Riesgo I-III</th><th>Riesgo IV-V</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td>≤10</td><td>7 estandares</td><td>60 estandares</td></tr>
                                                        <tr><td>11-50</td><td>21 estandares</td><td>60 estandares</td></tr>
                                                        <tr><td>>50</td><td>60 estandares</td><td>60 estandares</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                <span class="faq-question">¿Una empresa puede tener varios niveles de riesgo?</span>
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body py-2">
                                                <strong>Si.</strong> En el Contexto SST puede seleccionar multiples niveles de riesgo ARL usando checkboxes.<br><br>
                                                <em>Ejemplo: Una empresa de seguridad privada puede tener Riesgo I (administrativos), II (servicios generales), III (comerciales), V (escoltas).</em>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                <span class="faq-question">¿Como se calcula el porcentaje de cumplimiento?</span>
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body py-2">
                                                El cumplimiento ponderado considera el <strong>peso porcentual</strong> de cada estandar:
                                                <div class="code-box mt-2">Cumplimiento = Σ(peso de estandares cumplidos) / Σ(peso total aplicable) × 100</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                                <span class="faq-question">¿Que pasa si un estandar no aplica?</span>
                                            </button>
                                        </h2>
                                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body py-2">
                                                Marque el estandar como "No Aplica". Este <strong>no se considerara</strong> en el calculo del porcentaje de cumplimiento.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                                <span class="faq-question">¿Todos los consultores ven todos los clientes?</span>
                                            </button>
                                        </h2>
                                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body py-2">
                                                <strong>Si.</strong> Para garantizar continuidad operativa, todos los consultores pueden acceder a todos los clientes activos.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body text-center py-3">
                        <small class="text-muted">
                            <strong>Version:</strong> 1.3 |
                            <strong>Ultima actualizacion:</strong> Enero 2026 |
                            <strong>Modulo:</strong> Documentacion SST - EnterpriseSST
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Badge de version flotante -->
    <span class="badge bg-dark version-badge no-print">
        <i class="bi bi-info-circle me-1"></i>v1.3
    </span>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll para links de la tabla de contenidos
        document.querySelectorAll('.toc-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    // Abrir el acordeon correspondiente
                    const collapseId = 'collapse' + targetId.charAt(0).toUpperCase() + targetId.slice(1);
                    const collapseElement = document.getElementById(collapseId);

                    if (collapseElement) {
                        const bsCollapse = new bootstrap.Collapse(collapseElement, { show: true });
                    }

                    // Scroll suave
                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>
