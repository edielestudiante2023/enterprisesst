<?php
/**
 * Componente genérico de firmas para documentos SST
 *
 * Variables esperadas:
 * @var array $firmantes Array de firmantes con estructura:
 *   [
 *     'tipo' => 'representante_legal',
 *     'columna_encabezado' => 'Aprobó / Representante Legal',
 *     'nombre' => 'Juan Pérez',
 *     'cargo' => 'Representante Legal',
 *     'cedula' => '123456789',
 *     'licencia' => '',
 *     'firma_imagen' => 'data:image/png;base64,...' (firma electrónica),
 *     'firma_archivo' => 'consultores/firma.png' (archivo en uploads),
 *     'mostrar_licencia' => false,
 *     'mostrar_cedula' => false
 *   ]
 * @var string $titulo Título de la sección (default: 'FIRMAS DE APROBACIÓN')
 * @var string $formato 'web' o 'pdf' (afecta estilos)
 */

$titulo = $titulo ?? 'FIRMAS DE APROBACIÓN';
$formato = $formato ?? 'web';
$firmantes = $firmantes ?? [];
$numFirmantes = count($firmantes);

if ($numFirmantes === 0) return;

$anchoColumna = match ($numFirmantes) {
    1 => '100%',
    2 => '50%',
    3 => '33.33%',
    default => (100 / $numFirmantes) . '%'
};

// Estilos según formato
$estiloContenedor = $formato === 'pdf'
    ? 'margin-top: 25px;'
    : 'margin-top: 40px; page-break-inside: avoid;';

$estiloTitulo = $formato === 'pdf'
    ? 'background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;'
    : 'background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;';

$estiloTabla = $formato === 'pdf'
    ? 'width: 100%; margin-top: 0; border-collapse: collapse;'
    : 'font-size: 0.85rem; border-top: none;';

$estiloCelda = $formato === 'pdf'
    ? 'vertical-align: top; padding: 12px; height: 140px; border: 1px solid #999;'
    : 'vertical-align: top; padding: 20px; height: 180px; position: relative;';

$estiloEncabezado = $formato === 'pdf'
    ? 'background-color: #e9ecef; color: #333; border: 1px solid #999; padding: 5px 8px;'
    : 'background: linear-gradient(135deg, #f8f9fa, #e9ecef); font-weight: 600; color: #495057; border-top: none;';
?>

<div class="firma-section" style="<?= $estiloContenedor ?>">
    <?php if ($formato === 'web'): ?>
    <div class="seccion-titulo" style="<?= $estiloTitulo ?>">
        <i class="bi bi-pen me-2"></i><?= esc($titulo) ?>
    </div>
    <?php else: ?>
    <div style="<?= $estiloTitulo ?>">
        <?= esc($titulo) ?>
    </div>
    <?php endif; ?>

    <table class="<?= $formato === 'web' ? 'table table-bordered mb-0' : 'tabla-contenido' ?>" style="<?= $estiloTabla ?>">
        <!-- Encabezados -->
        <tr>
            <?php foreach ($firmantes as $firmante): ?>
            <th style="width: <?= $anchoColumna ?>; text-align: center; <?= $estiloEncabezado ?>">
                <?php if ($formato === 'web'): ?>
                <i class="bi bi-person-badge me-1"></i>
                <?php endif; ?>
                <?= esc($firmante['columna_encabezado']) ?>
            </th>
            <?php endforeach; ?>
        </tr>

        <!-- Datos del firmante -->
        <tr>
            <?php foreach ($firmantes as $firmante): ?>
            <td style="<?= $estiloCelda ?>">
                <div style="margin-bottom: <?= $formato === 'pdf' ? '5px' : '8px' ?>;">
                    <strong<?= $formato === 'web' ? ' style="color: #495057;"' : '' ?>>Nombre:</strong>
                    <?php if ($formato === 'web'): ?>
                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px;">
                        <?= !empty($firmante['nombre']) ? esc($firmante['nombre']) : '' ?>
                    </span>
                    <?php else: ?>
                    <?= !empty($firmante['nombre']) ? esc($firmante['nombre']) : '________________________' ?>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: <?= $formato === 'pdf' ? '5px' : '8px' ?>;">
                    <strong<?= $formato === 'web' ? ' style="color: #495057;"' : '' ?>>Cargo:</strong>
                    <span><?= esc($firmante['cargo']) ?></span>
                </div>

                <?php if (!empty($firmante['mostrar_licencia']) && !empty($firmante['licencia'])): ?>
                <div style="margin-bottom: <?= $formato === 'pdf' ? '5px' : '8px' ?>;">
                    <strong<?= $formato === 'web' ? ' style="color: #495057;"' : '' ?>>Licencia SST:</strong>
                    <span><?= esc($firmante['licencia']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($firmante['mostrar_cedula']) && !empty($firmante['cedula'])): ?>
                <div style="margin-bottom: <?= $formato === 'pdf' ? '5px' : '8px' ?>;">
                    <strong<?= $formato === 'web' ? ' style="color: #495057;"' : '' ?>>Documento:</strong>
                    <span><?= esc($firmante['cedula']) ?></span>
                </div>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>

        <!-- Firmas -->
        <tr>
            <?php foreach ($firmantes as $firmante): ?>
            <td style="padding: <?= $formato === 'pdf' ? '10px 12px' : '15px 20px' ?>; text-align: center; vertical-align: bottom; <?= $formato === 'pdf' ? 'border: 1px solid #999;' : '' ?>">
                <?php
                // Firma electrónica (imagen base64)
                if (!empty($firmante['firma_imagen'])):
                ?>
                    <img src="<?= $firmante['firma_imagen'] ?>" alt="Firma"
                         style="max-height: <?= $formato === 'pdf' ? '56px' : '50px' ?>; max-width: <?= $formato === 'pdf' ? '168px' : '150px' ?>; margin-bottom: 5px;">
                    <br>
                <?php
                // Firma desde archivo (uploads)
                elseif (!empty($firmante['firma_archivo'])):
                    if ($formato === 'pdf' && !empty($firmaConsultorBase64)):
                ?>
                    <img src="<?= $firmaConsultorBase64 ?>" alt="Firma"
                         style="max-height: 56px; max-width: 168px; margin-bottom: 5px;">
                    <br>
                <?php elseif ($formato === 'web'): ?>
                    <img src="<?= base_url('uploads/' . $firmante['firma_archivo']) ?>" alt="Firma"
                         style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                    <br>
                <?php
                    endif;
                endif;
                ?>

                <div style="border-top: 1px solid #333; width: <?= $formato === 'pdf' ? '80%' : '85%' ?>; margin: 5px auto 0; padding-top: <?= $formato === 'pdf' ? '3px' : '5px' ?>;">
                    <small style="color: #666; font-size: <?= $formato === 'pdf' ? '7pt' : '0.7rem' ?>;">Firma</small>
                </div>
            </td>
            <?php endforeach; ?>
        </tr>
    </table>
</div>
