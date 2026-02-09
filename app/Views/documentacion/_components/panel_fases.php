<?php
/**
 * Componente: Panel de Fases de Dependencia
 * Variables requeridas: $fasesInfo, $tipoCarpetaFases, $cliente, $carpeta, $documentoExistente (opcional)
 */
if (!isset($fasesInfo) || !$fasesInfo || !$fasesInfo['tiene_fases']) {
    return;
}
?>
<!-- Panel de Fases de Dependencia -->
<div class="fases-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fases-titulo mb-0">
            <i class="bi bi-diagram-3 me-2"></i>Fases para Generacion del Documento
        </h5>
        <?php if ($fasesInfo['todas_completas']): ?>
            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Listo para generar</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Fases pendientes</span>
        <?php endif; ?>
    </div>

    <div class="fases-timeline">
        <?php foreach ($fasesInfo['fases'] as $fase): ?>
            <div class="fase-item <?= $fase['estado'] ?>">
                <div class="fase-circulo <?= $fase['estado'] ?>">
                    <?php
                    $icono = match($fase['estado']) {
                        'completo' => 'bi-check-lg',
                        'en_proceso' => 'bi-arrow-repeat',
                        'bloqueado' => 'bi-lock-fill',
                        default => 'bi-circle'
                    };
                    ?>
                    <i class="bi <?= $icono ?>"></i>
                </div>
                <div class="fase-nombre"><?= esc($fase['nombre']) ?></div>
                <div class="fase-mensaje"><?= esc($fase['mensaje']) ?></div>
                <?php if ($fase['cantidad'] > 0): ?>
                    <div class="fase-cantidad"><?= $fase['cantidad'] ?> registros</div>
                <?php endif; ?>
                <div class="fase-acciones">
                    <?php if ($fase['estado'] !== 'bloqueado'): ?>
                        <?php if ($fase['puede_generar'] && $fase['url_generar']): ?>
                            <a href="<?= base_url(ltrim($fase['url_generar'], '/')) ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-magic me-1"></i>Generar
                            </a>
                        <?php endif; ?>
                        <?php if ($fase['url_modulo']): ?>
                            <a href="<?= base_url(ltrim($fase['url_modulo'], '/')) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Ver
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-secondary" disabled>
                            <i class="bi bi-lock me-1"></i>Bloqueado
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Fase Final: Documento -->
        <?php
        $tieneDocumento = isset($documentoExistente) && $documentoExistente;
        $estadoFaseDoc = $tieneDocumento ? 'completo' : ($fasesInfo['todas_completas'] ? 'en_proceso' : 'bloqueado');
        $nombreFaseDoc = (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsables_sst') ? 'Documento' : 'Documento IA';
        ?>
        <div class="fase-item <?= $estadoFaseDoc ?>">
            <div class="fase-circulo <?= $estadoFaseDoc ?>">
                <i class="bi <?= $tieneDocumento ? 'bi-check-lg' : ($fasesInfo['todas_completas'] ? 'bi-file-earmark-text-fill' : 'bi-lock-fill') ?>"></i>
            </div>
            <div class="fase-nombre"><?= $nombreFaseDoc ?></div>
            <div class="fase-mensaje">
                <?php if ($tieneDocumento): ?>
                    Documento creado
                    <?= isset($documentoExistente['estado']) && $documentoExistente['estado'] === 'aprobado' ? '(Aprobado)' : '(Borrador)' ?>
                <?php elseif ($fasesInfo['todas_completas']): ?>
                    Listo para generar documento
                <?php else: ?>
                    Complete las fases anteriores
                <?php endif; ?>
            </div>
            <div class="fase-acciones">
                <?php if ($tieneDocumento): ?>
                    <?php if (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsables_sst'): ?>
                        <a href="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/asignacion-responsable-sst/' . date('Y')) ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>Ver Documento
                        </a>
                    <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'plan_objetivos_metas'): ?>
                        <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/objetivos-sgsst') ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>Ver/Editar
                        </a>
                    <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'promocion_prevencion_salud'): ?>
                        <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/pyp-salud') ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>Ver/Editar
                        </a>
                    <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'capacitacion_sst'): ?>
                        <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/capacitacion-sst') ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>Ver/Editar
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>Ver/Editar
                        </a>
                    <?php endif; ?>
                <?php elseif ($fasesInfo['todas_completas']): ?>
                    <?php
                    if (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsables_sst') {
                        // Documento auto-generado, no usa IA
                    } elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'capacitacion_sst') {
                        $urlCrearIA = base_url('generador-ia/' . $cliente['id_cliente'] . '/capacitacion-sst');
                    } elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'promocion_prevencion_salud') {
                        $urlCrearIA = base_url('generador-ia/' . $cliente['id_cliente'] . '/pyp-salud');
                    } elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'induccion_reinduccion') {
                        $urlCrearIA = base_url('documentos/generar/programa_induccion_reinduccion/' . $cliente['id_cliente']);
                    } elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'plan_objetivos_metas') {
                        $urlCrearIA = base_url('generador-ia/' . $cliente['id_cliente'] . '/objetivos-sgsst');
                    } else {
                        $urlCrearIA = base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta'] . '&ia=1');
                    }
                    ?>
                    <?php if (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsables_sst'): ?>
                    <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-asignacion-responsable-sst') ?>" method="post" style="display:inline;">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-plus me-1"></i>Generar Documento
                        </button>
                    </form>
                    <?php else: ?>
                    <a href="<?= $urlCrearIA ?>"
                       class="btn btn-sm btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="bi bi-lock me-1"></i>Bloqueado
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$fasesInfo['todas_completas'] && isset($fasesInfo['siguiente_fase'])): ?>
    <div class="fases-alerta">
        <i class="bi bi-info-circle-fill"></i>
        <div class="fases-alerta-texto">
            <div class="fases-alerta-titulo">Siguiente paso: <?= esc($fasesInfo['siguiente_fase']['nombre']) ?></div>
            <div class="fases-alerta-desc"><?= esc($fasesInfo['siguiente_fase']['descripcion']) ?></div>
        </div>
        <?php if ($fasesInfo['siguiente_fase']['url_modulo']): ?>
        <a href="<?= base_url(ltrim($fasesInfo['siguiente_fase']['url_modulo'], '/')) ?>" class="btn btn-warning btn-sm">
            <i class="bi bi-arrow-right me-1"></i>Ir al modulo
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
