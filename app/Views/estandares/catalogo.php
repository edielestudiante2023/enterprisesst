<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo 60 Estándares - Resolución 0312/2019</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .phva-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        .phva-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .phva-badge.active {
            border: 2px solid #333;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
        }
        .phva-planear { background-color: #3B82F6 !important; }
        .phva-hacer { background-color: #10B981 !important; }
        .phva-verificar { background-color: #F59E0B !important; }
        .phva-actuar { background-color: #EF4444 !important; }
        .nivel-badge {
            font-size: 0.65rem;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .nivel-7 { background-color: #E0F2FE; color: #0369A1; }
        .nivel-21 { background-color: #FEF3C7; color: #B45309; }
        .nivel-60 { background-color: #FEE2E2; color: #DC2626; }
        .table-estandares th { font-size: 0.85rem; }
        .table-estandares td { font-size: 0.85rem; vertical-align: middle; }
        .categoria-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .filtro-card {
            transition: all 0.3s ease;
        }
        .filtro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .estandar-row {
            transition: all 0.2s ease;
        }
        #filtro-activo {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion">
                <i class="bi bi-book me-2"></i>Catálogo Estándares
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/documentacion">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Mensaje de configuracion automatica -->
        <?php if (!empty($setupResult['datos_insertados'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Configuracion automatica completada:</strong>
                <?= implode(', ', $setupResult['datos_insertados']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><i class="bi bi-list-check text-primary me-2"></i>60 Estándares Mínimos SG-SST</h4>
                        <p class="text-muted mb-0">Resolución 0312 de 2019 - Ministerio de Trabajo de Colombia</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-secondary btn-sm" onclick="filtrarNivel('todos')" id="btn-todos">
                            <i class="bi bi-eye me-1"></i>Mostrar Todos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de filtro por nivel -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm filtro-card" style="cursor: pointer;" onclick="filtrarNivel(7)" id="card-nivel-7">
                    <div class="card-body text-center py-4" style="background: linear-gradient(135deg, #E0F2FE 0%, #BAE6FD 100%); border-radius: 0.375rem;">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <span class="badge rounded-pill fs-4 px-4 py-2" style="background-color: #0369A1; color: white;">7</span>
                        </div>
                        <h5 class="mb-1" style="color: #0369A1;">Estándares Básicos</h5>
                        <p class="text-muted mb-2 small">Hasta 10 trabajadores</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-light text-dark"><i class="bi bi-building me-1"></i>Microempresas</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-shield-check me-1"></i>Riesgo I-III</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm filtro-card" style="cursor: pointer;" onclick="filtrarNivel(21)" id="card-nivel-21">
                    <div class="card-body text-center py-4" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-radius: 0.375rem;">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <span class="badge rounded-pill fs-4 px-4 py-2" style="background-color: #B45309; color: white;">21</span>
                        </div>
                        <h5 class="mb-1" style="color: #B45309;">Estándares Intermedios</h5>
                        <p class="text-muted mb-2 small">De 11 a 50 trabajadores</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-light text-dark"><i class="bi bi-buildings me-1"></i>Pequeñas empresas</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-shield-check me-1"></i>Riesgo I-III</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm filtro-card" style="cursor: pointer;" onclick="filtrarNivel(60)" id="card-nivel-60">
                    <div class="card-body text-center py-4" style="background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); border-radius: 0.375rem;">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <span class="badge rounded-pill fs-4 px-4 py-2" style="background-color: #DC2626; color: white;">60</span>
                        </div>
                        <h5 class="mb-1" style="color: #DC2626;">Estándares Completos</h5>
                        <p class="text-muted mb-2 small">Más de 50 trabajadores o Riesgo IV-V</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-light text-dark"><i class="bi bi-building me-1"></i>Medianas/Grandes</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Alto riesgo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicador de filtro activo -->
        <div id="filtro-activo" class="alert alert-info d-none mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-funnel-fill me-2"></i>
                    <span id="filtro-texto">Mostrando todos los estándares</span>
                </div>
                <button class="btn btn-sm btn-outline-info" onclick="quitarFiltros()">
                    <i class="bi bi-x-circle me-1"></i>Quitar filtro
                </button>
            </div>
        </div>

        <!-- Leyenda PHVA (clickeable) -->
        <div class="d-flex gap-3 mb-4 flex-wrap align-items-center">
            <small class="text-muted me-2">Filtrar por ciclo:</small>
            <span class="badge phva-badge phva-planear" onclick="filtrarCiclo('planear')" data-ciclo="planear" title="Filtrar solo PLANEAR">
                <i class="bi bi-clipboard-check me-1"></i>PLANEAR
            </span>
            <span class="badge phva-badge phva-hacer" onclick="filtrarCiclo('hacer')" data-ciclo="hacer" title="Filtrar solo HACER">
                <i class="bi bi-gear me-1"></i>HACER
            </span>
            <span class="badge phva-badge phva-verificar" onclick="filtrarCiclo('verificar')" data-ciclo="verificar" title="Filtrar solo VERIFICAR">
                <i class="bi bi-search me-1"></i>VERIFICAR
            </span>
            <span class="badge phva-badge phva-actuar" onclick="filtrarCiclo('actuar')" data-ciclo="actuar" title="Filtrar solo ACTUAR">
                <i class="bi bi-arrow-repeat me-1"></i>ACTUAR
            </span>
            <span class="ms-auto text-muted small" id="contador-estandares"></span>
        </div>

        <!-- Tabla de estándares -->
        <?php if (empty($estandaresAgrupados)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No hay estándares cargados. Ejecute el script de inicialización de la base de datos.
            </div>
        <?php else: ?>
            <?php foreach ($estandaresAgrupados as $ciclo => $categorias): ?>
                <?php
                    $phvaClass = match(strtolower($ciclo)) {
                        'planear' => 'phva-planear',
                        'hacer' => 'phva-hacer',
                        'verificar' => 'phva-verificar',
                        'actuar' => 'phva-actuar',
                        default => 'bg-secondary'
                    };
                    $phvaIcon = match(strtolower($ciclo)) {
                        'planear' => 'bi-clipboard-check',
                        'hacer' => 'bi-gear',
                        'verificar' => 'bi-search',
                        'actuar' => 'bi-arrow-repeat',
                        default => 'bi-folder'
                    };
                ?>
                <div class="card border-0 shadow-sm mb-4 ciclo-card" data-ciclo="<?= strtolower($ciclo) ?>">
                    <div class="card-header <?= $phvaClass ?> text-white">
                        <h5 class="mb-0">
                            <i class="bi <?= $phvaIcon ?> me-2"></i>
                            <?= strtoupper($ciclo) ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($categorias as $categoria => $estandares): ?>
                            <div class="categoria-header px-3 py-2">
                                <strong><?= esc($categoria) ?></strong>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-estandares mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">Núm.</th>
                                            <th style="width: 100px;">Código</th>
                                            <th>Estándar</th>
                                            <th style="width: 60px;" class="text-center">Peso</th>
                                            <th style="width: 80px;" class="text-center">Nivel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estandares as $est): ?>
                                            <?php
                                                $nivelMin = $est['nivel_minimo'] ?? 60;
                                                $nivelClass = match($nivelMin) {
                                                    7 => 'nivel-7',
                                                    21 => 'nivel-21',
                                                    default => 'nivel-60'
                                                };
                                            ?>
                                            <tr class="estandar-row" data-nivel="<?= $nivelMin ?>">
                                                <td><span class="badge bg-secondary"><?= $est['numero_estandar'] ?></span></td>
                                                <td><code><?= esc($est['codigo']) ?></code></td>
                                                <td><?= esc($est['nombre']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark"><?= $est['peso'] ?>%</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge nivel-badge <?= $nivelClass ?>"><?= $nivelMin ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let filtroActual = 'todos';
        let filtroCicloActual = 'todos';

        function filtrarCiclo(ciclo) {
            filtroCicloActual = ciclo;
            const cicloCards = document.querySelectorAll('.ciclo-card');
            const filtroActivo = document.getElementById('filtro-activo');
            const filtroTexto = document.getElementById('filtro-texto');
            const contadorEstandares = document.getElementById('contador-estandares');

            // Resetear badges PHVA
            document.querySelectorAll('.phva-badge').forEach(badge => {
                badge.classList.remove('active');
            });

            // Resetear filtro de nivel
            filtroActual = 'todos';
            document.querySelectorAll('.filtro-card').forEach(card => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });

            let visibles = 0;
            const total = document.querySelectorAll('.estandar-row').length;

            if (ciclo === 'todos') {
                // Mostrar todos los ciclos
                cicloCards.forEach(card => {
                    card.style.display = '';
                });
                document.querySelectorAll('.estandar-row').forEach(fila => {
                    fila.style.display = '';
                    visibles++;
                });
                document.querySelectorAll('.categoria-header, .table-responsive').forEach(el => {
                    el.style.display = '';
                });
                filtroActivo.classList.add('d-none');
            } else {
                // Filtrar por ciclo
                cicloCards.forEach(card => {
                    if (card.dataset.ciclo === ciclo) {
                        card.style.display = '';
                        // Contar filas visibles en este ciclo
                        card.querySelectorAll('.estandar-row').forEach(fila => {
                            fila.style.display = '';
                            visibles++;
                        });
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Marcar badge activo
                const badgeActivo = document.querySelector(`.phva-badge[data-ciclo="${ciclo}"]`);
                if (badgeActivo) {
                    badgeActivo.classList.add('active');
                }

                // Actualizar indicador
                filtroActivo.classList.remove('d-none');
                const cicloNombres = {
                    'planear': 'PLANEAR - Planificación del SG-SST',
                    'hacer': 'HACER - Implementación del SG-SST',
                    'verificar': 'VERIFICAR - Evaluación y auditoría',
                    'actuar': 'ACTUAR - Mejora continua'
                };
                filtroTexto.textContent = `Mostrando ciclo ${cicloNombres[ciclo] || ciclo.toUpperCase()}`;
            }

            // Actualizar contador
            contadorEstandares.textContent = `Mostrando ${visibles} de ${total} estándares`;
        }

        function filtrarNivel(nivel) {
            filtroActual = nivel;
            filtroCicloActual = 'todos';
            const filas = document.querySelectorAll('.estandar-row');
            const filtroActivo = document.getElementById('filtro-activo');
            const filtroTexto = document.getElementById('filtro-texto');
            const contadorEstandares = document.getElementById('contador-estandares');

            // Resetear estilos de las cards de nivel
            document.querySelectorAll('.filtro-card').forEach(card => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });

            // Resetear badges PHVA
            document.querySelectorAll('.phva-badge').forEach(badge => {
                badge.classList.remove('active');
            });

            // Mostrar todos los ciclos primero
            document.querySelectorAll('.ciclo-card').forEach(card => {
                card.style.display = '';
            });

            let visibles = 0;
            let total = filas.length;

            if (nivel === 'todos') {
                // Mostrar todos
                filas.forEach(fila => {
                    fila.style.display = '';
                    visibles++;
                });
                filtroActivo.classList.add('d-none');

                // Mostrar todas las secciones
                document.querySelectorAll('.card.border-0.shadow-sm.mb-4').forEach(card => {
                    if (card.querySelector('.estandar-row')) {
                        card.style.display = '';
                    }
                });
            } else {
                // Filtrar por nivel
                // Los estándares de nivel 7 aplican a todos
                // Los estándares de nivel 21 aplican a 21 y 60
                // Los estándares de nivel 60 aplican solo a 60
                filas.forEach(fila => {
                    const nivelEstandar = parseInt(fila.dataset.nivel);
                    let mostrar = false;

                    if (nivel === 7) {
                        mostrar = nivelEstandar === 7;
                    } else if (nivel === 21) {
                        mostrar = nivelEstandar <= 21;
                    } else if (nivel === 60) {
                        mostrar = true; // Nivel 60 incluye todos
                    }

                    if (mostrar) {
                        fila.style.display = '';
                        visibles++;
                    } else {
                        fila.style.display = 'none';
                    }
                });

                // Ocultar secciones vacías
                document.querySelectorAll('.card.border-0.shadow-sm.mb-4').forEach(card => {
                    const filasSeccion = card.querySelectorAll('.estandar-row');
                    if (filasSeccion.length > 0) {
                        const algunaVisible = Array.from(filasSeccion).some(f => f.style.display !== 'none');
                        card.style.display = algunaVisible ? '' : 'none';
                    }
                });

                // También ocultar categorías sin filas visibles
                document.querySelectorAll('.categoria-header').forEach(header => {
                    const tabla = header.nextElementSibling;
                    if (tabla && tabla.classList.contains('table-responsive')) {
                        const filasCategoria = tabla.querySelectorAll('.estandar-row');
                        const algunaVisible = Array.from(filasCategoria).some(f => f.style.display !== 'none');
                        header.style.display = algunaVisible ? '' : 'none';
                        tabla.style.display = algunaVisible ? '' : 'none';
                    }
                });

                // Actualizar indicador
                filtroActivo.classList.remove('d-none');
                const textos = {
                    7: 'Mostrando 7 estándares básicos (empresas hasta 10 trabajadores, Riesgo I-III)',
                    21: 'Mostrando 21 estándares intermedios (empresas de 11-50 trabajadores, Riesgo I-III)',
                    60: 'Mostrando los 60 estándares completos (más de 50 trabajadores o Riesgo IV-V)'
                };
                filtroTexto.textContent = textos[nivel];

                // Destacar card seleccionada
                const cardSeleccionada = document.getElementById(`card-nivel-${nivel}`);
                if (cardSeleccionada) {
                    cardSeleccionada.style.transform = 'scale(1.02)';
                    cardSeleccionada.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.2)';
                }
            }

            // Actualizar contador
            contadorEstandares.textContent = `Mostrando ${visibles} de ${total} estándares`;
        }

        function quitarFiltros() {
            // Resetear ambos filtros
            filtroActual = 'todos';
            filtroCicloActual = 'todos';

            const filtroActivo = document.getElementById('filtro-activo');
            const contadorEstandares = document.getElementById('contador-estandares');

            // Resetear cards de nivel
            document.querySelectorAll('.filtro-card').forEach(card => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });

            // Resetear badges PHVA
            document.querySelectorAll('.phva-badge').forEach(badge => {
                badge.classList.remove('active');
            });

            // Mostrar todos los ciclos
            document.querySelectorAll('.ciclo-card').forEach(card => {
                card.style.display = '';
            });

            // Mostrar todas las filas
            document.querySelectorAll('.estandar-row').forEach(fila => {
                fila.style.display = '';
            });

            // Mostrar todas las categorías
            document.querySelectorAll('.categoria-header, .table-responsive').forEach(el => {
                el.style.display = '';
            });

            // Ocultar indicador
            filtroActivo.classList.add('d-none');

            // Actualizar contador
            const total = document.querySelectorAll('.estandar-row').length;
            contadorEstandares.textContent = `Mostrando ${total} de ${total} estándares`;
        }

        // Inicializar contador al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const total = document.querySelectorAll('.estandar-row').length;
            document.getElementById('contador-estandares').textContent = `Mostrando ${total} de ${total} estándares`;
        });
    </script>
</body>
</html>
