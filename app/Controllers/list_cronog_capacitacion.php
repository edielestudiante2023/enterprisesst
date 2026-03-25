<?= $this->extend('layouts/main_layout') // Asumiendo que tienes un layout principal ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cronograma de Capacitaciones</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('listcronogCapacitacion') ?>">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="anio">Filtrar por Año:</label>
                            <select name="anio" id="anio" class="form-control" onchange="this.form.submit()">
                                <option value="todos" <?= (isset($anio_filtro) && $anio_filtro == 'todos') ? 'selected' : '' ?>>
                                    Todos
                                </option>
                                <?php
                                // Genera dinámicamente el rango de años de 2025 a 2030.
                                for ($y = 2025; $y <= 2030; $y++) : ?>
                                    <option value="<?= $y ?>" <?= (isset($anio_filtro) && $anio_filtro == $y) ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                             <label for="cliente">Filtrar por Cliente:</label>
                             <select name="cliente" id="cliente" class="form-control" onchange="this.form.submit()">
                                 <option value="">Todos los Clientes</option>
                                 <?php if (!empty($clientes)): ?>
                                     <?php foreach ($clientes as $cliente): ?>
                                         <option value="<?= esc($cliente['id_cliente']) ?>" <?= (isset($cliente_filtro) && $cliente_filtro == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                             <?= esc($cliente['nombre_cliente']) ?>
                                         </option>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                             </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Resultados del Cronograma</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Nombre Capacitación</th>
                            <th>Fecha Programada</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?= esc($record['nombre_cliente'] ?? 'N/A') ?></td>
                                    <td><?= esc($record['nombre_capacitacion'] ?? 'N/A') ?></td>
                                    <td><?= esc(date('d/m/Y', strtotime($record['fecha_programada']))) ?></td>
                                    <td><span class="badge bg-<?= $record['estado'] == 'EJECUTADA' ? 'success' : ($record['estado'] == 'PROGRAMADA' ? 'info' : 'warning') ?>"><?= esc($record['estado'] ?? 'N/A') ?></span></td>
                                    <td><?= esc($record['observaciones'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No se encontraron registros para los filtros seleccionados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>