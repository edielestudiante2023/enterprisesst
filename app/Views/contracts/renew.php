<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renovar Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .contract-info {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/contracts') ?>">
                <i class="fas fa-file-contract"></i> Gestión de Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/contracts') ?>">
                    <i class="fas fa-arrow-left"></i> Volver a Contratos
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">
                <i class="fas fa-sync-alt text-primary"></i> Renovar Contrato
            </h2>

            <!-- Información del Contrato Actual -->
            <div class="contract-info">
                <h5><i class="fas fa-info-circle"></i> Contrato Actual</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número:</strong> <?= esc($contract['numero_contrato']) ?></p>
                        <p><strong>Cliente:</strong> <?= esc($client['nombre_cliente']) ?></p>
                        <p><strong>NIT:</strong> <?= esc($client['nit_cliente']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha Inicio:</strong> <?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?></p>
                        <p><strong>Fecha Fin:</strong> <?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?></p>
                        <p><strong>Valor:</strong> $<?= number_format($contract['valor_contrato'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Formulario de Renovación -->
            <form action="<?= base_url('contracts/process-renewal') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id_contrato" value="<?= $contract['id_contrato'] ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin" class="form-label">
                            <i class="fas fa-calendar"></i> Nueva Fecha de Finalización *
                        </label>
                        <input type="date"
                               class="form-control"
                               id="fecha_fin"
                               name="fecha_fin"
                               required
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        <small class="text-muted">Fecha en la que finalizará el nuevo contrato</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="valor_contrato" class="form-label">
                            <i class="fas fa-dollar-sign"></i> Nuevo Valor del Contrato
                        </label>
                        <input type="number"
                               class="form-control"
                               id="valor_contrato"
                               name="valor_contrato"
                               placeholder="<?= $contract['valor_contrato'] ?>"
                               value="<?= $contract['valor_contrato'] ?>">
                        <small class="text-muted">Dejar en blanco para mantener el valor actual</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">
                        <i class="fas fa-comment"></i> Observaciones
                    </label>
                    <textarea class="form-control"
                              id="observaciones"
                              name="observaciones"
                              rows="3"
                              placeholder="Motivo de la renovación, cambios en el contrato, etc."></textarea>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Importante:</strong> Al renovar el contrato:
                    <ul class="mb-0 mt-2">
                        <li>El contrato actual pasará a estado "vencido"</li>
                        <li>Se creará un nuevo contrato con estado "activo"</li>
                        <li>La fecha de inicio será la fecha actual</li>
                        <li>El nuevo contrato será de tipo "renovación"</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('contracts/view/' . $contract['id_contrato']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Renovar Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
