<?php
/**
 * Componente genérico de tabla dinámica para documentos SST
 *
 * Variables esperadas:
 * @var array $datos Datos a mostrar en la tabla
 * @var string $tipo Tipo de tabla: tipos_documento, plantillas, listado_maestro
 * @var array|null $config Configuración de columnas (opcional, usa defaults si no existe)
 * @var string $formato 'web' o 'pdf'
 */

$formato = $formato ?? 'web';
$datos = $datos ?? [];
$tipo = $tipo ?? 'generico';

if (empty($datos)) return;

// Configuración por defecto de columnas según tipo
$columnasDefault = [
    'tipos_documento' => [
        ['key' => 'prefijo', 'titulo' => 'Prefijo', 'ancho' => '70px', 'alineacion' => 'center', 'bold' => true],
        ['key' => 'nombre', 'titulo' => 'Tipo de Documento', 'ancho' => 'auto', 'alineacion' => 'left'],
        ['key' => 'descripcion', 'titulo' => 'Descripción', 'ancho' => 'auto', 'alineacion' => 'left', 'size' => 'small']
    ],
    'plantillas' => [
        ['key' => 'codigo_sugerido', 'titulo' => 'Código', 'ancho' => '100px', 'alineacion' => 'center', 'bold' => true],
        ['key' => 'nombre', 'titulo' => 'Nombre del Documento', 'ancho' => 'auto', 'alineacion' => 'left']
    ],
    'listado_maestro' => [
        ['key' => 'codigo', 'titulo' => 'Código', 'ancho' => '80px', 'alineacion' => 'center', 'bold' => true],
        ['key' => 'titulo', 'titulo' => 'Título', 'ancho' => 'auto', 'alineacion' => 'left'],
        ['key' => 'version', 'titulo' => 'Versión', 'ancho' => '50px', 'alineacion' => 'center', 'format' => 'version'],
        ['key' => 'estado', 'titulo' => 'Estado', 'ancho' => '70px', 'alineacion' => 'center', 'format' => 'estado'],
        ['key' => 'created_at', 'titulo' => 'Fecha', 'ancho' => '75px', 'alineacion' => 'center', 'format' => 'fecha']
    ]
];

$columnas = $config['columnas'] ?? $columnasDefault[$tipo] ?? [];
$estiloEncabezado = $config['estilo_encabezado'] ?? ($tipo === 'listado_maestro' ? 'success' : 'primary');

// Colores de encabezado
$coloresEncabezado = [
    'primary' => '#0d6efd',
    'success' => '#198754',
    'info' => '#0dcaf0',
    'warning' => '#ffc107',
    'danger' => '#dc3545'
];
$colorEncabezado = $coloresEncabezado[$estiloEncabezado] ?? '#0d6efd';

// Estilos según formato
$estiloTabla = $formato === 'pdf'
    ? 'width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt;'
    : 'font-size: 0.85rem;';

$estiloTh = $formato === 'pdf'
    ? "border: 1px solid #999; padding: 5px 8px; background-color: $colorEncabezado; color: white; font-weight: bold; text-align: center;"
    : "background-color: $colorEncabezado; color: white; font-weight: 600;";

$estiloTd = $formato === 'pdf'
    ? 'border: 1px solid #999; padding: 5px 8px;'
    : '';
?>

<?php if ($formato === 'web'): ?>
<div class="table-responsive mt-3">
<?php endif; ?>

<table class="<?= $formato === 'web' ? 'table table-bordered tabla-codigos' : 'tabla-contenido' ?>" style="<?= $estiloTabla ?>">
    <thead>
        <tr>
            <?php foreach ($columnas as $col): ?>
            <th style="<?= $estiloTh ?> <?= $col['ancho'] !== 'auto' ? 'width: ' . $col['ancho'] . ';' : '' ?>">
                <?= esc($col['titulo']) ?>
            </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($datos as $fila): ?>
        <tr>
            <?php foreach ($columnas as $col):
                $valor = $fila[$col['key']] ?? '';
                $alineacion = $col['alineacion'] ?? 'left';
                $bold = !empty($col['bold']);
                $size = $col['size'] ?? 'normal';

                // Formatear valor según tipo
                if (!empty($col['format'])) {
                    switch ($col['format']) {
                        case 'version':
                            $valor = str_pad($valor, 3, '0', STR_PAD_LEFT);
                            break;
                        case 'fecha':
                            $valor = !empty($valor) ? date('d/m/Y', strtotime($valor)) : '';
                            break;
                        case 'estado':
                            $valor = ucfirst($valor);
                            if ($formato === 'web') {
                                $badgeClass = match(strtolower($fila[$col['key']] ?? '')) {
                                    'aprobado' => 'success',
                                    'firmado' => 'primary',
                                    'generado' => 'info',
                                    default => 'secondary'
                                };
                                $valor = '<span class="badge bg-' . $badgeClass . '">' . $valor . '</span>';
                            }
                            break;
                    }
                }

                $estilosCelda = $estiloTd;
                $estilosCelda .= "text-align: $alineacion;";
                if ($bold) $estilosCelda .= ' font-weight: bold;';
                if ($size === 'small') $estilosCelda .= $formato === 'pdf' ? ' font-size: 8pt;' : '';
            ?>
            <td style="<?= $estilosCelda ?>" class="<?= $alineacion === 'center' ? 'text-center' : '' ?> <?= $bold ? 'fw-bold' : '' ?>">
                <?php if (!empty($col['format']) && $col['format'] === 'estado' && $formato === 'web'): ?>
                    <?= $valor ?>
                <?php else: ?>
                    <?php if ($size === 'small' && $formato === 'web'): ?><small><?php endif; ?>
                    <?= esc($valor) ?>
                    <?php if ($size === 'small' && $formato === 'web'): ?></small><?php endif; ?>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($formato === 'web'): ?>
</div>
<?php endif; ?>

<?php if ($tipo === 'listado_maestro'): ?>
<p style="margin: 5px 0; <?= $formato === 'pdf' ? 'font-size: 8pt; color: #666;' : '' ?>" class="<?= $formato === 'web' ? 'mt-3' : '' ?>">
    <?php if ($formato === 'web'): ?>
    <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
    <?php else: ?>
    <em>
    <?php endif; ?>
        Este listado se actualiza automáticamente. Total documentos: <strong><?= count($datos) ?></strong>
    <?php if ($formato === 'web'): ?>
    </small>
    <?php else: ?>
    </em>
    <?php endif; ?>
</p>
<?php endif; ?>
