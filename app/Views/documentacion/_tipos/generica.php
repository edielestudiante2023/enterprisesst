<?php
/**
 * Vista de Tipo: Carpeta Genérica
 * Para carpetas sin tipoCarpetaFases especial
 * Variables: $carpeta, $cliente, $subcarpetas, $documentos
 */
?>

<!-- Botón "Nuevo Documento" deshabilitado — módulo /documentacion/nuevo/ comentado 2026-04-07 -->
<?php $slot_botones = ''; ?>

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
