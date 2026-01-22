<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Documentación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .header-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .option-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .option-card:hover {
            border-color: #28a745;
            transform: translateY(-2px);
        }
        .option-card.selected {
            border-color: #28a745;
            background: #f0fff4;
        }
        .option-card .option-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .contract-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .contract-item {
            padding: 12px 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .contract-item:hover {
            background-color: #f8f9fa;
            border-color: #28a745;
        }
        .contract-item.selected {
            background-color: #d4edda;
            border-color: #28a745;
        }
        .contract-item .contract-dates {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .year-btn {
            margin: 5px;
            min-width: 80px;
        }
        .year-btn.active {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-continuar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
        }
        .btn-continuar:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #1aa179 100%);
            color: white;
        }
        .btn-continuar:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/reportList') ?>">
                <i class="fas fa-arrow-left"></i> Volver a Lista de Reportes
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- Header -->
        <div class="header-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-folder-open"></i> Descargar Documentación</h2>
                    <p class="mb-1"><strong><?= htmlspecialchars($client['nombre_cliente']) ?></strong></p>
                    <p class="mb-0"><i class="fas fa-id-card"></i> NIT: <?= htmlspecialchars($client['nit_cliente']) ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark fs-6">
                        <i class="fas fa-file-contract"></i> <?= count($contracts) ?> contrato(s)
                    </span>
                </div>
            </div>
        </div>

        <form id="formDocumentacion" action="<?= base_url('/contracts/filtrar-documentacion/' . $client['id_cliente']) ?>" method="GET">
            <div class="row">
                <!-- Opción 1: Por Contrato -->
                <div class="col-md-6">
                    <div class="option-card" id="optionContrato" onclick="selectOption('contrato')">
                        <div class="text-center option-icon text-success">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h4 class="text-center mb-3">Por Contrato</h4>
                        <p class="text-muted text-center mb-4">Selecciona un contrato específico para descargar toda su documentación</p>

                        <input type="radio" name="filtro_tipo" value="contrato" id="radioContrato" class="d-none">

                        <div class="contract-list" id="contractList">
                            <?php if (empty($contracts)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> No hay contratos registrados para este cliente
                                </div>
                            <?php else: ?>
                                <?php foreach ($contracts as $index => $contract): ?>
                                    <div class="contract-item" onclick="selectContract(<?= $contract['id_contrato'] ?>, event)" data-contract="<?= $contract['id_contrato'] ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i class="fas fa-file-alt"></i> <?= htmlspecialchars($contract['numero_contrato']) ?></strong>
                                                <div class="contract-dates">
                                                    <i class="fas fa-calendar"></i>
                                                    <?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <?php
                                                    $hoy = date('Y-m-d');
                                                    if ($contract['fecha_fin'] < $hoy): ?>
                                                        <span class="badge bg-secondary">Finalizado</span>
                                                <?php elseif ($contract['fecha_inicio'] <= $hoy && $contract['fecha_fin'] >= $hoy): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                        <span class="badge bg-info">Futuro</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="id_contrato" id="selectedContract" value="">
                    </div>
                </div>

                <!-- Opción 2: Por Rango de Fechas -->
                <div class="col-md-6">
                    <div class="option-card" id="optionFechas" onclick="selectOption('fechas')">
                        <div class="text-center option-icon text-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4 class="text-center mb-3">Por Rango de Fechas</h4>
                        <p class="text-muted text-center mb-4">Selecciona una anualidad o define un rango personalizado</p>

                        <input type="radio" name="filtro_tipo" value="fechas" id="radioFechas" class="d-none">

                        <!-- Años rápidos (dinámico: desde año actual hasta 2024) -->
                        <div class="text-center mb-3">
                            <label class="form-label"><strong>Anualidad:</strong></label>
                            <div id="yearButtons">
                                <?php
                                $currentYear = (int)date('Y');
                                $minYear = 2024; // Año en que inició el sistema
                                for ($y = $currentYear; $y >= $minYear; $y--):
                                ?>
                                    <button type="button" class="btn btn-outline-success year-btn" onclick="selectYear(<?= $y ?>, event)">
                                        <?= $y ?>
                                    </button>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Fechas personalizadas -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label"><strong>Desde:</strong></label>
                                <input type="date" class="form-control" name="fecha_desde" id="fechaDesde" onclick="event.stopPropagation()">
                            </div>
                            <div class="col-6">
                                <label class="form-label"><strong>Hasta:</strong></label>
                                <input type="date" class="form-control" name="fecha_hasta" id="fechaHasta" onclick="event.stopPropagation()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Continuar -->
            <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-continuar btn-lg" id="btnContinuar" disabled>
                    <i class="fas fa-search"></i> Ver Documentación
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedOption = null;
        let selectedContractId = null;

        function selectOption(option) {
            selectedOption = option;

            // Visual feedback
            document.getElementById('optionContrato').classList.remove('selected');
            document.getElementById('optionFechas').classList.remove('selected');
            document.getElementById('option' + (option === 'contrato' ? 'Contrato' : 'Fechas')).classList.add('selected');

            // Set radio
            document.getElementById('radio' + (option === 'contrato' ? 'Contrato' : 'Fechas')).checked = true;

            validateForm();
        }

        function selectContract(contractId, event) {
            event.stopPropagation();
            selectOption('contrato');
            selectedContractId = contractId;

            // Visual feedback
            document.querySelectorAll('.contract-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.querySelector(`[data-contract="${contractId}"]`).classList.add('selected');
            document.getElementById('selectedContract').value = contractId;

            // Clear date selection
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('fechaDesde').value = '';
            document.getElementById('fechaHasta').value = '';

            validateForm();
        }

        function selectYear(year, event) {
            event.stopPropagation();
            selectOption('fechas');

            // Visual feedback
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Set dates
            document.getElementById('fechaDesde').value = year + '-01-01';
            document.getElementById('fechaHasta').value = year + '-12-31';

            // Clear contract selection
            document.querySelectorAll('.contract-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.getElementById('selectedContract').value = '';
            selectedContractId = null;

            validateForm();
        }

        // Listen for manual date changes
        document.getElementById('fechaDesde').addEventListener('change', function() {
            selectOption('fechas');
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.contract-item').forEach(item => item.classList.remove('selected'));
            document.getElementById('selectedContract').value = '';
            selectedContractId = null;
            validateForm();
        });

        document.getElementById('fechaHasta').addEventListener('change', function() {
            selectOption('fechas');
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.contract-item').forEach(item => item.classList.remove('selected'));
            document.getElementById('selectedContract').value = '';
            selectedContractId = null;
            validateForm();
        });

        function validateForm() {
            const btn = document.getElementById('btnContinuar');
            let valid = false;

            if (selectedOption === 'contrato' && selectedContractId) {
                valid = true;
            } else if (selectedOption === 'fechas') {
                const desde = document.getElementById('fechaDesde').value;
                const hasta = document.getElementById('fechaHasta').value;
                valid = desde && hasta && desde <= hasta;
            }

            btn.disabled = !valid;
        }
    </script>
</body>
</html>
