<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$etiqueta = \App\Controllers\ComitesEleccionesController::getEtiquetaEstado($proceso['estado']);
$iconoTipo = [
    'COPASST' => 'bi-shield-check text-success',
    'COCOLAB' => 'bi-chat-heart text-warning',
    'BRIGADA' => 'bi-fire text-danger',
    'VIGIA' => 'bi-person-badge text-info'
][$proceso['tipo_comite']] ?? 'bi-people';

// Fase a visualizar (puede venir por query param para consultores)
$faseVisualizar = $_GET['fase'] ?? $proceso['estado'];
$esVistaHistorica = isset($_GET['fase']) && $_GET['fase'] !== $proceso['estado'];
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>">Comites</a>
                    </li>
                    <li class="breadcrumb-item active"><?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi <?= $iconoTipo ?> me-2"></i>
                        <?= $proceso['tipo_comite'] ?> - Proceso <?= $proceso['anio'] ?>
                    </h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <span class="badge <?= $etiqueta['clase'] ?> fs-6"><?= $etiqueta['texto'] ?></span>
            </div>
        </div>
    </div>

    <!-- Mensajes flash -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Panel principal -->
        <div class="col-lg-8">
            <!-- Progress Steps -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?php
                    if ($proceso['tipo_comite'] === 'VIGIA' || $proceso['tipo_comite'] === 'BRIGADA') {
                        $pasos = [
                            'designacion_empleador' => ['icono' => 'bi-person-plus', 'texto' => 'Designacion'],
                            'firmas' => ['icono' => 'bi-pen', 'texto' => 'Firmas'],
                            'completado' => ['icono' => 'bi-check-circle', 'texto' => 'Completado']
                        ];
                    } else {
                        $pasos = [
                            'configuracion' => ['icono' => 'bi-gear', 'texto' => 'Configuracion'],
                            'inscripcion' => ['icono' => 'bi-person-plus', 'texto' => 'Inscripcion'],
                            'votacion' => ['icono' => 'bi-check2-square', 'texto' => 'Votacion'],
                            'escrutinio' => ['icono' => 'bi-calculator', 'texto' => 'Escrutinio'],
                            'designacion_empleador' => ['icono' => 'bi-building', 'texto' => 'Empleador'],
                            'firmas' => ['icono' => 'bi-pen', 'texto' => 'Firmas'],
                            'completado' => ['icono' => 'bi-trophy', 'texto' => 'Completado']
                        ];
                    }
                    $pasosKeys = array_keys($pasos);
                    $pasoActualIdx = array_search($proceso['estado'], $pasosKeys);
                    if ($pasoActualIdx === false) $pasoActualIdx = 0;
                    ?>
                    <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
                        <?php foreach ($pasos as $key => $paso): ?>
                        <?php
                        $idx = array_search($key, $pasosKeys);
                        $completado = $idx < $pasoActualIdx;
                        $actual = $idx === $pasoActualIdx && $proceso['estado'] !== 'cancelado';
                        $pendiente = $idx > $pasoActualIdx;
                        $estiloActivo = ($faseVisualizar === $key) ? 'ring ring-2 ring-primary shadow' : '';
                        $claseCirculo = $completado ? 'bg-success text-white' : ($actual ? 'bg-primary text-white' : 'bg-light text-muted');
                        // Marcar fase que se está visualizando
                        if ($esVistaHistorica && $faseVisualizar === $key) {
                            $claseCirculo .= ' border border-3 border-warning';
                        }
                        $esClickeable = $completado || $actual; // Fases completadas y actual son clickeables
                        ?>
                        <div class="text-center" style="flex: 1;">
                            <?php if ($esClickeable): ?>
                            <a href="<?= base_url("comites-elecciones/{$cliente['id_cliente']}/proceso/{$proceso['id_proceso']}") ?>?fase=<?= $key ?>"
                               class="text-decoration-none"
                               title="Ver fase: <?= $paso['texto'] ?>">
                            <?php endif; ?>
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle <?= $claseCirculo ?>"
                                 style="width: 40px; height: 40px; <?= $esClickeable ? 'cursor: pointer; transition: transform 0.2s;' : '' ?>"
                                 <?= $esClickeable ? 'onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"' : '' ?>>
                                <?php if ($completado): ?>
                                    <i class="bi bi-check"></i>
                                <?php else: ?>
                                    <i class="bi <?= $paso['icono'] ?>"></i>
                                <?php endif; ?>
                            </div>
                            <div class="small mt-1 <?= $actual ? 'fw-bold' : '' ?> <?= $faseVisualizar === $key ? 'text-primary fw-bold' : '' ?>"><?= $paso['texto'] ?></div>
                            <?php if ($esClickeable): ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Alerta de vista histórica -->
            <?php if ($esVistaHistorica): ?>
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-clock-history fs-4 me-3"></i>
                <div class="flex-grow-1">
                    <strong>Vista Histórica</strong><br>
                    <small>Está visualizando la fase <strong><?= $pasos[$faseVisualizar]['texto'] ?? $faseVisualizar ?></strong> (solo lectura).
                    El proceso está actualmente en la fase <strong><?= $pasos[$proceso['estado']]['texto'] ?? $proceso['estado'] ?></strong>.</small>
                </div>
                <a href="<?= base_url("comites-elecciones/{$cliente['id_cliente']}/proceso/{$proceso['id_proceso']}") ?>"
                   class="btn btn-warning btn-sm ms-3">
                    <i class="bi bi-arrow-right me-1"></i>Ir a Fase Actual
                </a>
            </div>
            <?php endif; ?>

            <!-- Panel segun estado actual -->
            <?php if ($faseVisualizar === 'configuracion'): ?>
            <!-- Estado: Configuracion -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Configuracion del Proceso</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Plazas Principales</label>
                            <div class="fs-4 fw-bold"><?= $proceso['plazas_principales'] ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Plazas Suplentes</label>
                            <div class="fs-4 fw-bold"><?= $proceso['plazas_suplentes'] ?></div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Inicio Periodo</label>
                            <div><?= date('d/m/Y', strtotime($proceso['fecha_inicio_periodo'])) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Fin Periodo</label>
                            <div><?= date('d/m/Y', strtotime($proceso['fecha_fin_periodo'])) ?></div>
                        </div>
                    </div>
                    <?php if (!$esVistaHistorica): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Verifique la configuracion y cuando este listo, inicie el periodo de inscripcion de candidatos.
                    </div>
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/inscripcion') ?>" method="post">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-right me-1"></i>Iniciar Inscripcion de Candidatos
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($faseVisualizar === 'inscripcion'): ?>
            <!-- Estado: Inscripcion -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Inscripcion de Candidatos</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Registre los candidatos que representaran a los trabajadores en el comite.</p>

                    <?php if (!$esVistaHistorica): ?>
                    <!-- Boton agregar candidato -->
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/inscribir/trabajador') ?>"
                       class="btn btn-success mb-3">
                        <i class="bi bi-plus-lg me-1"></i>Inscribir Candidato Trabajadores
                    </a>
                    <?php endif; ?>

                    <!-- Lista de candidatos inscritos -->
                    <?php if (empty($candidatosTrabajadores)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No hay candidatos inscritos aun. Use el boton "Inscribir Candidato" para registrar trabajadores.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Cargo</th>
                                    <?php if (!$esVistaHistorica): ?><th>Acciones</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidatosTrabajadores as $t): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($t['foto'])): ?>
                                        <img src="<?= base_url($t['foto']) ?>" alt="Foto"
                                             class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= esc($t['nombres'] . ' ' . $t['apellidos']) ?></strong></td>
                                    <td><?= esc($t['documento_identidad']) ?></td>
                                    <td><?= esc($t['cargo']) ?></td>
                                    <?php if (!$esVistaHistorica): ?>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('comites-elecciones/candidato/' . $t['id_candidato'] . '/editar') ?>"
                                               class="btn btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="<?= base_url('comites-elecciones/candidato/' . $t['id_candidato'] . '/eliminar') ?>" method="post" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este candidato?');">
                                                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info small mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Todos los candidatos inscritos participaran en la votacion. El tipo de plaza (principal/suplente) se determinara segun los votos obtenidos.
                    </div>
                    <?php endif; ?>

                    <?php if (!$esVistaHistorica): ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/configuracion') ?>" method="post">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Volver a Configuracion
                            </button>
                        </form>
                        <?php
                        $totalCandidatos = count($candidatosTrabajadores ?? []);
                        $puedeContinuar = $totalCandidatos >= 2;
                        ?>
                        <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/votacion') ?>" method="post">
                            <button type="submit" class="btn btn-primary" <?= !$puedeContinuar ? 'disabled' : '' ?>
                                    title="<?= !$puedeContinuar ? 'Se requieren al menos 2 candidatos' : '' ?>">
                                <i class="bi bi-check2-square me-1"></i>Iniciar Votacion
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($faseVisualizar === 'votacion'): ?>
            <!-- Estado: Votacion -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-check2-square me-2"></i>Votacion Electronica</h5>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/censo') ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-people me-1"></i>Gestionar Censo
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($proceso['enlace_votacion'])): ?>
                    <div class="alert alert-success">
                        <h6><i class="bi bi-link-45deg me-2"></i>Enlace de Votacion</h6>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" readonly
                                   value="<?= base_url('votar/' . $proceso['enlace_votacion']) ?>" id="enlaceVotacion">
                            <button class="btn btn-outline-secondary" type="button" onclick="copiarEnlace()">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                        </div>
                        <?php if (!empty($proceso['fecha_fin_votacion'])): ?>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>Expira: <?= date('d/m/Y H:i', strtotime($proceso['fecha_fin_votacion'])) ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        El sistema de votacion no ha sido iniciado. Primero gestione el censo de votantes.
                    </div>
                    <?php if (!$esVistaHistorica): ?>
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/iniciar-votacion') ?>" method="post" class="mb-3">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-play-fill me-1"></i>Iniciar Sistema de Votacion
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>

                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="h2 mb-0 text-primary"><?= $proceso['votos_emitidos'] ?? 0 ?></div>
                            <small class="text-muted">Votos Emitidos</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h2 mb-0 text-info"><?= $proceso['total_votantes'] ?? 0 ?></div>
                            <small class="text-muted">Votantes en Censo</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h2 mb-0"><?= count($candidatosTrabajadores ?? []) ?></div>
                            <small class="text-muted">Candidatos</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h2 mb-0"><?= $proceso['plazas_principales'] + $proceso['plazas_suplentes'] ?></div>
                            <small class="text-muted">Plazas Totales</small>
                        </div>
                    </div>

                    <?php
                    $totalVotantes = $proceso['total_votantes'] ?? 0;
                    $votosEmitidos = $proceso['votos_emitidos'] ?? 0;
                    $porcentaje = $totalVotantes > 0 ? round(($votosEmitidos / $totalVotantes) * 100, 1) : 0;
                    ?>
                    <?php if ($totalVotantes > 0): ?>
                    <div class="mb-4">
                        <label class="form-label small text-muted">Participacion: <?= $porcentaje ?>%</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $porcentaje ?>%;">
                                <?= $votosEmitidos ?> / <?= $totalVotantes ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/resultados') ?>" class="btn btn-outline-info">
                            <i class="bi bi-bar-chart me-1"></i>Ver Resultados <?= $esVistaHistorica ? '' : 'Parciales' ?>
                        </a>
                        <?php if (!$esVistaHistorica): ?>
                        <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/escrutinio') ?>" method="post"
                              onsubmit="return confirm('¿Cerrar la votación? Los votantes ya no podrán votar.');">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-calculator me-1"></i>Cerrar Votacion e Iniciar Escrutinio
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php elseif ($faseVisualizar === 'escrutinio'): ?>
            <!-- Estado: Escrutinio -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Resultados del Escrutinio</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Revise los resultados de la votacion y confirme los ganadores.</p>

                    <?php
                    // Usar candidatosTrabajadores (tabla nueva) o trabajadores (legacy)
                    $listaCandidatos = !empty($candidatosTrabajadores) ? $candidatosTrabajadores : $trabajadores;
                    usort($listaCandidatos, fn($a, $b) => ($b['votos_obtenidos'] ?? 0) <=> ($a['votos_obtenidos'] ?? 0));
                    ?>

                    <?php if (empty($listaCandidatos)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No hay candidatos registrados en este proceso. Debe inscribir candidatos antes de la votacion.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Candidato</th>
                                    <th class="text-center" style="width:100px">Votos</th>
                                    <th class="text-center" style="width:120px">%</th>
                                    <th class="text-center" style="width:120px">Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $posicion = 1;
                                $totalVotos = array_sum(array_column($listaCandidatos, 'votos_obtenidos'));
                                foreach ($listaCandidatos as $t):
                                    $nombre = $t['nombre_completo'] ?? ($t['nombres'] . ' ' . $t['apellidos']);
                                    $votos = $t['votos_obtenidos'] ?? 0;
                                    $porcentaje = $totalVotos > 0 ? round(($votos / $totalVotos) * 100, 1) : 0;
                                ?>
                                <tr class="<?= $posicion <= $proceso['plazas_principales'] ? 'table-success' : ($posicion <= $proceso['plazas_principales'] + $proceso['plazas_suplentes'] ? 'table-info' : '') ?>">
                                    <td class="text-center fw-bold"><?= $posicion ?></td>
                                    <td>
                                        <strong><?= esc($nombre) ?></strong>
                                        <br><small class="text-muted"><?= esc($t['cargo'] ?? '') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?= $votos ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%">
                                                <?= $porcentaje ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($posicion <= $proceso['plazas_principales']): ?>
                                            <span class="badge bg-success"><i class="bi bi-star-fill me-1"></i>Principal</span>
                                        <?php elseif ($posicion <= $proceso['plazas_principales'] + $proceso['plazas_suplentes']): ?>
                                            <span class="badge bg-info"><i class="bi bi-bookmark-fill me-1"></i>Suplente</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No elegido</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $posicion++; endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total votos:</strong></td>
                                    <td class="text-center"><strong><?= $totalVotos ?></strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Resumen de plazas -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="alert alert-success mb-2 py-2">
                                <i class="bi bi-star-fill me-2"></i>
                                <strong>Principales elegidos:</strong> <?= min(count($listaCandidatos), $proceso['plazas_principales']) ?> de <?= $proceso['plazas_principales'] ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info mb-2 py-2">
                                <i class="bi bi-bookmark-fill me-2"></i>
                                <strong>Suplentes elegidos:</strong> <?= max(0, min(count($listaCandidatos) - $proceso['plazas_principales'], $proceso['plazas_suplentes'])) ?> de <?= $proceso['plazas_suplentes'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!$esVistaHistorica): ?>
                    <hr>
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/designacion_empleador') ?>" method="post">
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-building me-1"></i>Confirmar y Continuar a Designacion Empleador
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($faseVisualizar === 'designacion_empleador'): ?>
            <!-- Estado: Designacion Empleador -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Designacion de Representantes del Empleador</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Registre los representantes designados por el empleador.</p>

                    <?php if (!$esVistaHistorica): ?>
                    <!-- Boton agregar representante -->
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/inscribir/empleador') ?>"
                       class="btn btn-success mb-3">
                        <i class="bi bi-plus-lg me-1"></i>Designar Representante del Empleador
                    </a>
                    <?php endif; ?>

                    <!-- Lista de representantes empleador -->
                    <?php if (empty($candidatosEmpleador)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No hay representantes del empleador designados aun.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Cargo</th>
                                    <th>Plaza</th>
                                    <?php if (in_array($proceso['tipo_comite'], ['COPASST', 'VIGIA'])): ?>
                                    <th>Cert. 50h</th>
                                    <?php endif; ?>
                                    <?php if (!$esVistaHistorica): ?><th>Acciones</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidatosEmpleador as $e): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($e['foto'])): ?>
                                        <img src="<?= base_url($e['foto']) ?>" alt="Foto"
                                             class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= esc($e['nombres'] . ' ' . $e['apellidos']) ?></strong></td>
                                    <td><?= esc($e['documento_identidad']) ?></td>
                                    <td><?= esc($e['cargo']) ?></td>
                                    <td>
                                        <span class="badge <?= $e['tipo_plaza'] === 'principal' ? 'bg-primary' : 'bg-secondary' ?>">
                                            <?= ucfirst($e['tipo_plaza']) ?>
                                        </span>
                                    </td>
                                    <?php if (in_array($proceso['tipo_comite'], ['COPASST', 'VIGIA'])): ?>
                                    <td>
                                        <?php if ($e['tiene_certificado_50h']): ?>
                                            <span class="badge bg-success"><i class="bi bi-award"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                    <?php if (!$esVistaHistorica): ?>
                                    <td>
                                        <form action="<?= base_url('comites-elecciones/candidato/' . $e['id_candidato'] . '/eliminar') ?>" method="post" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este representante?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if (!$esVistaHistorica): ?>
                    <hr>
                    <?php
                    $plazasRequeridas = $proceso['plazas_principales'] + $proceso['plazas_suplentes'];
                    $plazasCubiertas = count($candidatosEmpleador ?? []);
                    ?>
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/firmas') ?>" method="post">
                        <button type="submit" class="btn btn-warning"
                                <?= $plazasCubiertas < $proceso['plazas_principales'] ? 'disabled' : '' ?>
                                title="<?= $plazasCubiertas < $proceso['plazas_principales'] ? 'Se requieren al menos ' . $proceso['plazas_principales'] . ' representantes principales' : '' ?>">
                            <i class="bi bi-pen me-1"></i>Continuar a Firmas
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($faseVisualizar === 'firmas'): ?>
            <!-- Estado: Firmas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Pendiente de Firmas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Genere y envie el acta de constitucion para firma electronica.</p>

                    <!-- Documentos del proceso -->
                    <?php if (empty($documentos)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No se han generado documentos aun. Use el boton para generar el acta de constitucion.
                    </div>
                    <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/acta') ?>" class="btn btn-primary mb-3">
                        <i class="bi bi-file-earmark-text me-1"></i>Generar Acta de Constitucion
                    </a>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td><?= ucwords(str_replace('_', ' ', $doc['tipo_documento'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $doc['estado_firmas'] === 'completado' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($doc['estado_firmas']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($doc['archivo_pdf']): ?>
                                        <a href="<?= esc($doc['archivo_pdf']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-file-pdf"></i> PDF
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if (!$esVistaHistorica): ?>
                    <hr>
                    <button type="button" class="btn btn-success" onclick="confirmarCompletarProceso()">
                        <i class="bi bi-check-circle me-1"></i>Marcar como Completado
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$esVistaHistorica): ?>
            <script>
            function confirmarCompletarProceso() {
                Swal.fire({
                    title: '¿Finalizar proceso electoral?',
                    html: `
                        <div class="text-start">
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Advertencia:</strong> Esta accion es <strong>IRREVERSIBLE</strong>.
                            </div>
                            <p>Al marcar el proceso como <strong>Completado</strong>:</p>
                            <ul class="text-muted">
                                <li>No podra generar nuevamente el Acta de Constitucion</li>
                                <li>No podra modificar los resultados de votacion</li>
                                <li>No podra cambiar los miembros del comite</li>
                                <li>El proceso quedara bloqueado por transparencia electoral</li>
                            </ul>
                            <p class="mt-3 mb-0"><strong>¿Esta seguro de continuar?</strong></p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Si, continuar',
                    cancelButtonText: 'Cancelar',
                    width: 500
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Segunda confirmacion
                        Swal.fire({
                            title: 'Confirmacion Final',
                            html: `
                                <div class="text-center">
                                    <i class="bi bi-lock-fill text-warning display-4 mb-3"></i>
                                    <p>Escriba <strong>COMPLETAR</strong> para confirmar:</p>
                                    <input type="text" id="confirmText" class="form-control text-center" placeholder="COMPLETAR" autocomplete="off">
                                </div>
                            `,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Finalizar Proceso',
                            cancelButtonText: 'Cancelar',
                            preConfirm: () => {
                                const confirmText = document.getElementById('confirmText').value;
                                if (confirmText !== 'COMPLETAR') {
                                    Swal.showValidationMessage('Debe escribir COMPLETAR exactamente');
                                    return false;
                                }
                                return true;
                            }
                        }).then((result2) => {
                            if (result2.isConfirmed) {
                                // Crear y enviar formulario
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/completado') ?>';
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                    }
                });
            }
            </script>
            <?php endif; ?>

            <?php elseif ($faseVisualizar === 'completado'): ?>
            <!-- Estado: Completado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Proceso Completado</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success display-1"></i>
                        <h4 class="mt-3">Comite Conformado Exitosamente</h4>
                        <p class="text-muted">
                            El <?= $proceso['tipo_comite'] ?> ha sido conformado para el periodo
                            <?= date('d/m/Y', strtotime($proceso['fecha_inicio_periodo'])) ?> -
                            <?= date('d/m/Y', strtotime($proceso['fecha_fin_periodo'])) ?>
                        </p>
                    </div>

                    <?php if ($proceso['id_comite']): ?>
                    <div class="text-center">
                        <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $proceso['id_comite']) ?>"
                           class="btn btn-primary">
                            <i class="bi bi-journal-text me-1"></i>Ver Comite y Actas
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Boton de Recomposicion del Comite -->
                    <?php if (!$esVistaHistorica): ?>
                    <hr class="my-4">
                    <div class="text-center">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Necesita reemplazar algun miembro del comite?
                        </h6>
                        <a href="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/recomposiciones') ?>"
                           class="btn btn-outline-warning">
                            <i class="bi bi-person-lines-fill me-1"></i>Recomposicion del Comite
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($faseVisualizar === 'cancelado'): ?>
            <!-- Estado: Cancelado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Proceso Cancelado</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-x-circle-fill text-danger display-1"></i>
                        <h4 class="mt-3">Este proceso fue cancelado</h4>
                    </div>
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/configuracion') ?>" method="post" class="text-center">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reactivar Proceso
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista de todos los participantes -->
            <?php if (!empty($participantes) && $proceso['estado'] !== 'configuracion'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Todos los Participantes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Representacion</th>
                                    <th>Origen</th>
                                    <th>Tipo</th>
                                    <th>Votos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participantes as $p): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($p['nombre_completo']) ?></strong>
                                        <br><small class="text-muted"><?= esc($p['cargo']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p['representacion'] === 'empleador' ? 'bg-primary' : 'bg-success' ?>">
                                            <?= ucfirst($p['representacion']) ?>
                                        </span>
                                    </td>
                                    <td><?= ucfirst($p['origen']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucfirst($p['tipo_miembro']) ?></span>
                                    </td>
                                    <td><?= $p['votos_obtenidos'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Info del proceso -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion del Proceso</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Tipo:</small>
                            <br><strong><?= $proceso['tipo_comite'] ?></strong>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Ano:</small>
                            <br><strong><?= $proceso['anio'] ?></strong>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Plazas:</small>
                            <br><strong><?= $proceso['plazas_principales'] ?> principales + <?= $proceso['plazas_suplentes'] ?> suplentes</strong>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Periodo:</small>
                            <br><strong><?= date('d/m/Y', strtotime($proceso['fecha_inicio_periodo'])) ?></strong>
                            <br><span class="text-muted">al</span>
                            <br><strong><?= date('d/m/Y', strtotime($proceso['fecha_fin_periodo'])) ?></strong>
                        </li>
                        <li>
                            <small class="text-muted">Creado:</small>
                            <br><strong><?= date('d/m/Y H:i', strtotime($proceso['created_at'])) ?></strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Jurados (si aplica) -->
            <?php if (in_array($proceso['tipo_comite'], ['COPASST', 'COCOLAB'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Jurados de Votacion</h6>
                    <span class="badge bg-secondary" id="contadorJurados"><?= count($jurados ?? []) ?></span>
                </div>
                <div class="card-body">
                    <div id="listaJurados">
                        <?php if (empty($jurados)): ?>
                        <p class="text-muted small mb-2" id="sinJurados">No hay jurados registrados.</p>
                        <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($jurados as $j): ?>
                            <li class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-center jurado-item" data-id="<?= $j['id_jurado'] ?>">
                                <div>
                                    <strong><?= esc($j['nombres'] . ' ' . $j['apellidos']) ?></strong>
                                    <br><small class="text-muted">
                                        <span class="badge bg-<?= $j['rol'] == 'presidente' ? 'primary' : ($j['rol'] == 'secretario' ? 'info' : 'secondary') ?> bg-opacity-75">
                                            <?= ucfirst($j['rol']) ?>
                                        </span>
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-jurado" data-id="<?= $j['id_jurado'] ?>" data-nombre="<?= esc($j['nombres'] . ' ' . $j['apellidos']) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#modalAgregarJurado">
                        <i class="bi bi-plus"></i> Agregar Jurado
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Acciones -->
            <?php if (!in_array($proceso['estado'], ['completado', 'cancelado'])): ?>
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Acciones</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/cambiar-estado/cancelado') ?>" method="post"
                          onsubmit="return confirm('Esta seguro de cancelar este proceso? Esta accion se puede revertir.');">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i>Cancelar Proceso
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Agregar Jurado -->
<div class="modal fade" id="modalAgregarJurado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    Agregar Jurado de Votacion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarJurado">
                    <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">

                    <!-- Buscar por documento -->
                    <div class="mb-3">
                        <label class="form-label">Buscar por documento</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="buscarDocumento" placeholder="Ingrese documento...">
                            <button type="button" class="btn btn-outline-primary" id="btnBuscarTrabajador">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <small class="text-muted">Busca en el censo de votantes</small>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="documento_identidad" id="juradoDocumento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" name="rol" id="juradoRol" required>
                                <option value="presidente">Presidente de Mesa</option>
                                <option value="secretario">Secretario</option>
                                <option value="escrutador" selected>Escrutador</option>
                                <option value="testigo">Testigo</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombres" id="juradoNombres" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="apellidos" id="juradoApellidos" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control" name="cargo" id="juradoCargo">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="juradoEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefono</label>
                            <input type="text" class="form-control" name="telefono" id="juradoTelefono">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarJurado">
                    <i class="bi bi-check-lg me-1"></i>Agregar Jurado
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function copiarEnlace() {
    const input = document.getElementById('enlaceVotacion');
    input.select();
    document.execCommand('copy');
    alert('Enlace copiado al portapapeles');
}

document.addEventListener('DOMContentLoaded', function() {
    const idProceso = <?= $proceso['id_proceso'] ?>;
    const modalJurado = document.getElementById('modalAgregarJurado');
    const bsModalJurado = new bootstrap.Modal(modalJurado);

    // Buscar trabajador
    document.getElementById('btnBuscarTrabajador').addEventListener('click', function() {
        const documento = document.getElementById('buscarDocumento').value.trim();
        if (!documento) {
            Swal.fire('', 'Ingrese un documento para buscar', 'warning');
            return;
        }

        fetch(`<?= base_url('comites-elecciones/proceso') ?>/${idProceso}/buscar-trabajador?documento=${documento}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.encontrado) {
                    const t = data.trabajador;
                    document.getElementById('juradoDocumento').value = t.documento_identidad;
                    document.getElementById('juradoNombres').value = t.nombres;
                    document.getElementById('juradoApellidos').value = t.apellidos;
                    document.getElementById('juradoCargo').value = t.cargo || '';
                    document.getElementById('juradoEmail').value = t.email || '';
                    document.getElementById('juradoTelefono').value = t.telefono || '';
                    Swal.fire({
                        icon: 'success',
                        title: 'Encontrado',
                        text: `${t.nombres} ${t.apellidos}`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    document.getElementById('juradoDocumento').value = documento;
                    Swal.fire({
                        icon: 'info',
                        title: 'No encontrado',
                        text: 'Complete los datos manualmente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al buscar', 'error');
            });
    });

    // Guardar jurado
    document.getElementById('btnGuardarJurado').addEventListener('click', function() {
        const form = document.getElementById('formAgregarJurado');
        const formData = new FormData(form);

        // Validar campos requeridos
        const documento = formData.get('documento_identidad');
        const nombres = formData.get('nombres');
        const apellidos = formData.get('apellidos');

        if (!documento || !nombres || !apellidos) {
            Swal.fire('', 'Complete los campos requeridos', 'warning');
            return;
        }

        fetch('<?= base_url('comites-elecciones/jurado/agregar') ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bsModalJurado.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Jurado agregado',
                    text: data.jurado.nombre_completo,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Error al guardar', 'error');
        });
    });

    // Limpiar modal al cerrar
    modalJurado.addEventListener('hidden.bs.modal', function() {
        document.getElementById('formAgregarJurado').reset();
        document.getElementById('buscarDocumento').value = '';
    });

    // Eliminar jurado
    document.querySelectorAll('.btn-eliminar-jurado').forEach(btn => {
        btn.addEventListener('click', function() {
            const idJurado = this.dataset.id;
            const nombre = this.dataset.nombre;

            Swal.fire({
                title: '¿Eliminar jurado?',
                html: `<p>Se eliminara a <strong>${nombre}</strong> de la mesa de votacion.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`<?= base_url('comites-elecciones/jurado') ?>/${idJurado}/eliminar`, {
                        method: 'POST'
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Error al eliminar', 'error');
                    });
                }
            });
        });
    });
});
</script>

<?= $this->endSection() ?>
