<!DOCTYPE html>
<html>
<head>
    <title>Mostrar Foto del Consultor</title>
</head>
<body>
    <h2>Foto del Consultor</h2>

    <?php if (!empty($foto)): ?>
        <img src="<?= base_url('uploads/' . $foto) ?>" alt="Foto del Consultor" width="200">

    <?php else: ?>
        <p>No hay foto disponible.</p>
    <?php endif; ?>

    <a href="<?= base_url('/listConsultants') ?>">Volver a la lista de consultores</a>
</body>
</html>
