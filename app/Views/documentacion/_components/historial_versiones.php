<?php
/**
 * Componente: Historial de Versiones de un Documento
 * Variables requeridas: $versiones
 */
?>
<div class="bg-light p-3">
    <h6 class="mb-2"><i class="bi bi-clock-history me-1"></i>Historial de Versiones</h6>
    <table class="table table-sm table-bordered mb-0 bg-white">
        <thead>
            <tr class="table-secondary">
                <th style="width:80px;">Version</th>
                <th style="width:90px;">Tipo</th>
                <th>Descripcion del Cambio</th>
                <th style="width:90px;">Estado</th>
                <th style="width:150px;">Autorizado por</th>
                <th style="width:150px;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($versiones as $ver): ?>
            <?php
            $estadoVer = $ver['estado'] ?? 'historico';
            $estadoVerBadge = match($estadoVer) {
                'vigente' => 'bg-success',
                'pendiente_firma' => 'bg-info',
                'historico' => 'bg-secondary',
                default => 'bg-secondary'
            };
            $estadoVerTexto = match($estadoVer) {
                'vigente' => 'Vigente',
                'pendiente_firma' => 'Pendiente firma',
                'historico' => 'Historico',
                default => ucfirst(str_replace('_', ' ', $estadoVer))
            };
            ?>
            <tr>
                <td><span class="badge bg-primary">v<?= esc($ver['version_texto']) ?></span></td>
                <td>
                    <span class="badge <?= $ver['tipo_cambio'] === 'mayor' ? 'bg-danger' : 'bg-info' ?>">
                        <?= $ver['tipo_cambio'] === 'mayor' ? 'Mayor' : 'Menor' ?>
                    </span>
                </td>
                <td><?= esc($ver['descripcion_cambio']) ?></td>
                <td>
                    <span class="badge <?= $estadoVerBadge ?>">
                        <?= $estadoVerTexto ?>
                    </span>
                </td>
                <td><?= esc($ver['autorizado_por'] ?? '-') ?></td>
                <td><?= $ver['fecha_autorizacion'] ? date('d/m/Y H:i', strtotime($ver['fecha_autorizacion'])) : '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
