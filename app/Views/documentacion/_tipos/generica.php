<?php
/**
 * Vista de Tipo: Carpeta Genérica
 * Para carpetas sin tipoCarpetaFases especial
 * Variables: $carpeta, $cliente, $subcarpetas, $documentos
 */
?>

<!-- Botón específico para carpeta genérica -->
<?php $slot_botones = '
<a href="' . base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) . '"
   class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
</a>'; ?>

<!-- Card de Carpeta -->
<?= view('documentacion/_components/card_carpeta_simple', [
    'carpeta' => $carpeta,
    'slot_botones' => $slot_botones
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Documentos Genéricos -->
<?= view('documentacion/_components/lista_documentos_generica', [
    'documentos' => $documentos ?? [],
    'cliente' => $cliente,
    'carpeta' => $carpeta
]) ?>
