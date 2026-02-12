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
    // 2.1.1 Políticas de SST
    'politica_sst_general' => 'politica-sst-general/' . $docSST['anio'],
    'politica_alcohol_drogas' => 'politica-alcohol-drogas/' . $docSST['anio'],
    'politica_acoso_laboral' => 'politica-acoso-laboral/' . $docSST['anio'],
    'politica_violencias_genero' => 'politica-violencias-genero/' . $docSST['anio'],
    'politica_discriminacion' => 'politica-discriminacion/' . $docSST['anio'],
    'politica_prevencion_emergencias' => 'politica-prevencion-emergencias/' . $docSST['anio'],
    // 2.2.1 Plan de Objetivos y Metas
    'plan_objetivos_metas' => 'plan-objetivos-metas/' . $docSST['anio'],
    // 1.2.1 Programa de Capacitación (ya incluido arriba)
    // 3.1.3 Programa de Promoción y Prevención en Salud
    'programa_promocion_prevencion_salud' => 'programa-promocion-prevencion-salud/' . $docSST['anio'],
    // 3.1.1 Programa de Inducción y Reinducción
    'programa_induccion_reinduccion' => 'programa-induccion-reinduccion/' . $docSST['anio'],
    // 2.4.1 Procedimiento Matriz Legal
    'procedimiento_matriz_legal' => 'procedimiento-matriz-legal/' . $docSST['anio'],
    // 1.1.8 Manual de Convivencia Laboral
    'manual_convivencia_laboral' => 'manual-convivencia-laboral/' . $docSST['anio'],
    // 2.8.1 Mecanismos de Comunicación, Auto Reporte
    'mecanismos_comunicacion_sgsst' => 'mecanismos-comunicacion/' . $docSST['anio'],
    // 3.1.1 Procedimiento de Evaluaciones Médicas Ocupacionales
    'procedimiento_evaluaciones_medicas' => 'procedimiento-evaluaciones-medicas/' . $docSST['anio'],
    // 2.9.1 Procedimiento de Adquisiciones en SST
    'procedimiento_adquisiciones' => 'procedimiento-adquisiciones/' . $docSST['anio'],
    // 2.10.1 Evaluacion y Seleccion de Proveedores y Contratistas
    'procedimiento_evaluacion_proveedores' => 'procedimiento-evaluacion-proveedores/' . $docSST['anio'],
    // 2.11.1 Procedimiento de Gestion del Cambio
    'procedimiento_gestion_cambio' => 'procedimiento-gestion-cambio/' . $docSST['anio'],
    // 3.1.7 Programa de Estilos de Vida Saludable
    'programa_estilos_vida_saludable' => 'programa-estilos-vida-saludable/' . $docSST['anio'],
    // 3.1.4 Programa de Evaluaciones Medicas Ocupacionales
    'programa_evaluaciones_medicas_ocupacionales' => 'programa-evaluaciones-medicas-ocupacionales/' . $docSST['anio'],
    // 3.2.1 Procedimiento de Investigacion de Accidentes
    'procedimiento_investigacion_accidentes' => 'procedimiento-investigacion-accidentes/' . $docSST['anio'],
    // 3.2.2 Investigacion de Incidentes, Accidentes y Enfermedades Laborales
    'procedimiento_investigacion_incidentes' => 'procedimiento-investigacion-incidentes/' . $docSST['anio'],
    // 4.1.1 Metodologia Identificacion de Peligros y Valoracion de Riesgos
    'metodologia_identificacion_peligros' => 'metodologia-identificacion-peligros/' . $docSST['anio'],
    // 4.1.3 Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
    'identificacion_sustancias_cancerigenas' => 'identificacion-sustancias-cancerigenas/' . $docSST['anio'],
    // 4.2.5 Mantenimiento Periodico de Instalaciones, Equipos, Maquinas, Herramientas
    'programa_mantenimiento_periodico' => 'programa-mantenimiento-periodico/' . $docSST['anio'],
    // 4.2.3 PVE Riesgo Biomecanico y Psicosocial
    'pve_riesgo_biomecanico' => 'pve-riesgo-biomecanico/' . $docSST['anio'],
    'pve_riesgo_psicosocial' => 'pve-riesgo-psicosocial/' . $docSST['anio'],
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
} elseif ($tipoDoc === 'politica_sst_general') {
    $urlEditar = base_url('documentos/generar/politica_sst_general/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'politica_prevencion_emergencias') {
    $urlEditar = base_url('documentos/generar/politica_prevencion_emergencias/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'plan_objetivos_metas') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/objetivos-sgsst');
} elseif ($tipoDoc === 'politica_alcohol_drogas') {
    $urlEditar = base_url('documentos/generar/politica_alcohol_drogas/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'politica_acoso_laboral') {
    $urlEditar = base_url('documentos/generar/politica_acoso_laboral/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'politica_violencias_genero') {
    $urlEditar = base_url('documentos/generar/politica_violencias_genero/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'politica_discriminacion') {
    $urlEditar = base_url('documentos/generar/politica_discriminacion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'programa_promocion_prevencion_salud') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/pyp-salud');
} elseif ($tipoDoc === 'programa_induccion_reinduccion') {
    $urlEditar = base_url('documentos/generar/programa_induccion_reinduccion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_matriz_legal') {
    $urlEditar = base_url('documentos/generar/procedimiento_matriz_legal/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'manual_convivencia_laboral') {
    $urlEditar = base_url('documentos/generar/manual_convivencia_laboral/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'mecanismos_comunicacion_sgsst') {
    $urlEditar = base_url('documentos/generar/mecanismos_comunicacion_sgsst/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_evaluaciones_medicas') {
    $urlEditar = base_url('documentos/generar/procedimiento_evaluaciones_medicas/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_adquisiciones') {
    $urlEditar = base_url('documentos/generar/procedimiento_adquisiciones/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_evaluacion_proveedores') {
    $urlEditar = base_url('documentos/generar/procedimiento_evaluacion_proveedores/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_gestion_cambio') {
    $urlEditar = base_url('documentos/generar/procedimiento_gestion_cambio/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'programa_estilos_vida_saludable') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/estilos-vida-saludable');
} elseif ($tipoDoc === 'programa_evaluaciones_medicas_ocupacionales') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/evaluaciones-medicas-ocupacionales');
} elseif ($tipoDoc === 'procedimiento_investigacion_accidentes') {
    $urlEditar = base_url('documentos/generar/procedimiento_investigacion_accidentes/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'procedimiento_investigacion_incidentes') {
    $urlEditar = base_url('documentos/generar/procedimiento_investigacion_incidentes/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'metodologia_identificacion_peligros') {
    $urlEditar = base_url('documentos/generar/metodologia_identificacion_peligros/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'identificacion_sustancias_cancerigenas') {
    $urlEditar = base_url('documentos/generar/identificacion_sustancias_cancerigenas/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'programa_mantenimiento_periodico') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/mantenimiento-periodico');
} elseif ($tipoDoc === 'pve_riesgo_biomecanico') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/pve-riesgo-biomecanico');
} elseif ($tipoDoc === 'pve_riesgo_psicosocial') {
    $urlEditar = base_url('generador-ia/' . $cliente['id_cliente'] . '/pve-riesgo-psicosocial');
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
