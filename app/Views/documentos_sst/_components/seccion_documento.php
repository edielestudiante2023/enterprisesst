<?php
/**
 * Componente genérico de sección para documentos SST
 *
 * Variables esperadas:
 * @var array $seccion Datos de la sección:
 *   [
 *     'titulo' => '6. Tipos de Documentos',
 *     'key' => 'tipos_documentos',
 *     'contenido' => 'Texto de la sección...',
 *     'tipo_contenido' => 'texto|tabla_dinamica|mixto',
 *     'tabla_dinamica' => 'tipos_documento' (si aplica)
 *   ]
 * @var array $tablasDinamicas Datos de tablas dinámicas precargados
 * @var string $formato 'web' o 'pdf'
 */

$formato = $formato ?? 'web';
$titulo = $seccion['titulo'] ?? $seccion['nombre'] ?? 'Sin título';
$key = $seccion['key'] ?? '';
$contenido = $seccion['contenido'] ?? '';
$tipoContenido = $seccion['tipo_contenido'] ?? 'texto';
$tablaDinamica = $seccion['tabla_dinamica'] ?? null;

// Función para convertir Markdown a HTML (debe estar definida en el contexto padre)
if (!function_exists('convertirMarkdownAHtmlGenerico')) {
    function convertirMarkdownAHtmlGenerico($texto) {
        if (empty($texto)) return '';

        // Si ya tiene HTML, devolverlo
        if (preg_match('/<(p|ol|ul|li|div|table|br)\b[^>]*>/i', $texto)) {
            return $texto;
        }

        // Conversión básica de Markdown
        $texto = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $texto);
        $texto = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $texto);
        $texto = nl2br(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'));

        return $texto;
    }
}
?>

<div class="seccion" style="margin-bottom: <?= $formato === 'pdf' ? '8px' : '25px' ?>; page-break-inside: avoid;">
    <!-- Título de sección -->
    <div class="seccion-titulo" style="
        font-size: <?= $formato === 'pdf' ? '11pt' : '1.1rem' ?>;
        font-weight: bold;
        color: #0d6efd;
        border-bottom: <?= $formato === 'pdf' ? '1px' : '2px' ?> solid #e9ecef;
        padding-bottom: <?= $formato === 'pdf' ? '3px' : '8px' ?>;
        margin-bottom: <?= $formato === 'pdf' ? '5px' : '15px' ?>;
        <?= $formato === 'pdf' ? 'margin-top: 8px;' : '' ?>
    ">
        <?= esc($titulo) ?>
    </div>

    <div class="seccion-contenido" style="text-align: justify; line-height: <?= $formato === 'pdf' ? '1.2' : '1.7' ?>;">
        <?php
        // Mostrar contenido de texto
        if (!empty($contenido)):
            if (function_exists('convertirMarkdownAHtml')) {
                echo convertirMarkdownAHtml($contenido);
            } elseif (function_exists('convertirMarkdownAHtmlPdf')) {
                echo convertirMarkdownAHtmlPdf($contenido);
            } else {
                echo convertirMarkdownAHtmlGenerico($contenido);
            }
        endif;

        // Si es sección mixta o tabla_dinamica, mostrar tabla
        if (in_array($tipoContenido, ['mixto', 'tabla_dinamica']) && $tablaDinamica):
            $datosTabla = $tablasDinamicas[$tablaDinamica] ?? [];
            $configTabla = $configTablasDinamicas[$tablaDinamica] ?? null;

            if (!empty($datosTabla)):
                // Texto introductorio para la tabla
                $textoIntro = match($tablaDinamica) {
                    'tipos_documento' => 'A continuación se presenta la clasificación de tipos de documentos del SG-SST:',
                    'plantillas' => 'Los códigos de los documentos del SG-SST son los siguientes:',
                    'listado_maestro' => 'El siguiente es el Listado Maestro de Documentos del SG-SST:',
                    default => ''
                };

                if ($textoIntro):
        ?>
                <p style="margin: <?= $formato === 'pdf' ? '8px 0 3px 0' : '15px 0 10px 0' ?>;"><?= $textoIntro ?></p>
        <?php
                endif;

                // Renderizar tabla según tipo
                echo view('documentos_sst/_components/tabla_dinamica', [
                    'datos' => $datosTabla,
                    'tipo' => $tablaDinamica,
                    'config' => $configTabla,
                    'formato' => $formato
                ]);
            endif;
        endif;

        // Mensaje si no hay contenido
        if (empty($contenido) && $tipoContenido === 'texto'):
        ?>
            <p class="<?= $formato === 'web' ? 'text-muted fst-italic' : '' ?>" style="<?= $formato === 'pdf' ? 'color: #666; font-style: italic;' : '' ?>">
                <?php if ($formato === 'web'): ?>
                <i class="bi bi-exclamation-circle me-1"></i>
                <?php endif; ?>
                Sección pendiente de generar.
            </p>
        <?php
        endif;
        ?>
    </div>
</div>
