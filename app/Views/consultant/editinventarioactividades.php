<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Actividad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Editar Actividad</h1>
        
        <form action="<?= base_url('/editinventarioactividades/' . $actividad['id_inventario_actividades_plandetrabajo']) ?>" method="post">

            <div class="mb-3">
                <label for="phva_plandetrabajo" class="form-label">PHVA</label>
                <input type="text" class="form-control" id="phva_plandetrabajo" name="phva_plandetrabajo" value="<?= $actividad['phva_plandetrabajo'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="numeral_plandetrabajo" class="form-label">Numeral</label>
                <input type="text" class="form-control" id="numeral_plandetrabajo" name="numeral_plandetrabajo" value="<?= $actividad['numeral_plandetrabajo'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="actividad_plandetrabajo" class="form-label">Actividad</label>
                <textarea class="form-control" id="actividad_plandetrabajo" name="actividad_plandetrabajo" rows="3" required><?= $actividad['actividad_plandetrabajo'] ?></textarea>
            </div>
            <div class="mb-3">
                <label for="responsable_sugerido_plandetrabajo" class="form-label">Responsable Sugerido</label>
                <input type="text" class="form-control" id="responsable_sugerido_plandetrabajo" name="responsable_sugerido_plandetrabajo" value="<?= $actividad['responsable_sugerido_plandetrabajo'] ?>">
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?= base_url('/listinventarioactividades') ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
