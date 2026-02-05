<?php
/**
 * Componente: Acciones de un Documento SST
 * Variables requeridas: $docSST, $cliente
 */
$tipoDoc = $docSST['tipo_documento'] ?? 'programa_capacitacion';
$urlEditar = null;

// Mapa de tipos de documento a rutas
$mapaRutas = [
    'asignacion_responsable_sgsst' => 'asignacion-responsable-sst/' . $docSST['anio'],
    'responsabilidades_rep_legal_sgsst' => 'responsabilidades-rep-legal/' . $docSST['anio'],
    'responsabilidades_responsable_sgsst' => 'responsabilidades-responsable-sst/' . $docSST['anio'],
    'responsabilidades_trabajadores_sgsst' => 'responsabilidades-trabajadores/' . $docSST['anio'],
    'programa_capacitacion' => 'programa-capacitacion/' . $docSST['anio'],
    'procedimiento_control_documental' => 'procedimiento-control-documental/' . $docSST['anio'],
    'presupuesto_sst' => 'presupuesto/preview/' . $docSST['anio'],
    'identificacion_alto_riesgo' => 'identificacion-alto-riesgo/' . $docSST['anio'],
];

// Presupuesto SST tiene ruta diferente
if ($tipoDoc === 'presupuesto_sst') {
    $urlVer = base_url('documentos-sst/presupuesto/preview/' . $cliente['id_cliente'] . '/' . $docSST['anio']);
} elseif (isset($mapaRutas[$tipoDoc])) {
    $urlVer = base_url('documentos-sst/' . $cliente['id_cliente'] . '/' . $mapaRutas[$tipoDoc]);
} else {
    $urlVer = base_url('documentos-sst/' . $cliente['id_cliente'] . '/programa-capacitacion/' . $docSST['anio']);
}

// Documentos que tienen editor
if ($tipoDoc === 'programa_capacitacion') {
    $urlEditar = base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_control_documental') {
    $urlEditar = base_url('documentos/generar/procedimiento_control_documental/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'presupuesto_sst') {
    $urlEditar = base_url('documentos-sst/presupuesto/' . $cliente['id_cliente'] . '/' . $docSST['anio']);
} elseif ($tipoDoc === 'identificacion_alto_riesgo') {
    $urlEditar = base_url('documentos/generar/identificacion_alto_riesgo/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
?>
<div class="btn-group btn-group-sm">
    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $docSST['id_documento']) ?>"
       class="btn btn-danger" title="Descargar PDF" target="_blank">
        <i class="bi bi-file-earmark-pdf"></i>
    </a>
    <?php if (!empty($docSST['archivo_pdf'])): ?>
    <a href="<?= esc($docSST['archivo_pdf']) ?>"
       class="btn btn-outline-danger" title="PDF firmado publicado" target="_blank">
        <i class="bi bi-patch-check-fill"></i>
    </a>
    <?php endif; ?>
    <a href="<?= $urlVer ?>"
       class="btn btn-outline-primary" title="Ver documento" target="_blank">
        <i class="bi bi-eye"></i>
    </a>
    <?php if ($urlEditar): ?>
    <a href="<?= $urlEditar ?>"
       class="btn btn-outline-warning" title="Editar documento" target="_blank">
        <i class="bi bi-pencil"></i>
    </a>
    <?php endif; ?>
    <?php if ($tipoDoc === 'responsabilidades_trabajadores_sgsst'): ?>
    <!-- Documento de firma fisica -->
    <?php
    $archivoEscaneado = null;
    if (!empty($docSST['versiones'])) {
        foreach ($docSST['versiones'] as $ver) {
            if (($ver['estado'] ?? '') === 'vigente' && !empty($ver['archivo_pdf'])) {
                $archivoEscaneado = $ver['archivo_pdf'];
                break;
            }
        }
    }
    ?>
    <?php if ($archivoEscaneado): ?>
    <a href="<?= esc($archivoEscaneado) ?>"
       class="btn btn-outline-success" title="Ver documento firmado (escaneado)" target="_blank">
        <i class="bi bi-file-earmark-check"></i>
    </a>
    <?php endif; ?>
    <button type="button" class="btn btn-outline-info" title="<?= $archivoEscaneado ? 'Reemplazar documento escaneado' : 'Adjuntar documento firmado (escaneado)' ?>"
       data-bs-toggle="modal" data-bs-target="#modalAdjuntarFirmado"
       data-id-documento="<?= $docSST['id_documento'] ?>"
       data-titulo="<?= esc($docSST['titulo']) ?>">
        <i class="bi bi-paperclip"></i>
    </button>
    <?php else: ?>
    <!-- Documentos con firma electronica -->
    <a href="<?= base_url('firma/estado/' . $docSST['id_documento']) ?>"
       class="btn btn-outline-success" title="Firmas y Audit Log" target="_blank">
        <i class="bi bi-pen"></i>
    </a>
    <a href="<?= base_url('documentos-sst/publicar-pdf/' . $docSST['id_documento']) ?>"
       class="btn btn-outline-dark" title="Publicar nueva versión en Reportes (se mantiene historial)"
       onclick="return confirm('¿Publicar nueva versión de este documento en Reportes?\n\nSe creará un nuevo registro manteniendo el historial de publicaciones anteriores.')">
        <i class="bi bi-cloud-upload"></i>
    </a>
    <?php endif; ?>
</div>
