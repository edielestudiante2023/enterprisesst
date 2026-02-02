<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($carpeta['nombre']) ?> - Documentacion SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <?= view('documentacion/_components/styles') ?>
</head>
<body class="bg-light">
    <?= view('documentacion/_components/header', [
        'cliente' => $cliente,
        'carpeta' => $carpeta,
        'ruta' => $ruta ?? []
    ]) ?>

        <!-- Alertas -->
        <?= view('documentacion/_components/alertas') ?>

        <!-- Contenido específico del tipo de carpeta -->
        <?php if (isset($vistaContenido)): ?>
            <?= view($vistaContenido, [
                'carpeta' => $carpeta,
                'cliente' => $cliente,
                'ruta' => $ruta ?? [],
                'subcarpetas' => $subcarpetas ?? [],
                'documentos' => $documentos ?? [],
                'fasesInfo' => $fasesInfo ?? null,
                'tipoCarpetaFases' => $tipoCarpetaFases ?? null,
                'documentoExistente' => $documentoExistente ?? null,
                'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
                'contextoCliente' => $contextoCliente ?? []
            ]) ?>
        <?php else: ?>
            <!-- Fallback: usar componentes directamente (compatibilidad) -->
            <?= view('documentacion/_components/card_carpeta', [
                'carpeta' => $carpeta,
                'cliente' => $cliente,
                'tipoCarpetaFases' => $tipoCarpetaFases ?? null,
                'fasesInfo' => $fasesInfo ?? null,
                'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
                'contextoCliente' => $contextoCliente ?? []
            ]) ?>

            <?= view('documentacion/_components/panel_fases', [
                'fasesInfo' => $fasesInfo ?? null,
                'tipoCarpetaFases' => $tipoCarpetaFases ?? null,
                'cliente' => $cliente,
                'carpeta' => $carpeta,
                'documentoExistente' => $documentoExistente ?? null
            ]) ?>

            <?= view('documentacion/_components/tabla_documentos_sst', [
                'tipoCarpetaFases' => $tipoCarpetaFases ?? null,
                'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
                'cliente' => $cliente
            ]) ?>

            <div class="row">
                <?= view('documentacion/_components/lista_subcarpetas', [
                    'subcarpetas' => $subcarpetas ?? []
                ]) ?>

                <?= view('documentacion/_components/lista_documentos', [
                    'documentos' => $documentos ?? [],
                    'cliente' => $cliente,
                    'carpeta' => $carpeta,
                    'tipoCarpetaFases' => $tipoCarpetaFases ?? null
                ]) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para Adjuntar Firmado -->
    <?= view('documentacion/_components/modal_adjuntar') ?>

    <!-- Scripts -->
    <?= view('documentacion/_components/scripts') ?>
</body>
</html>
