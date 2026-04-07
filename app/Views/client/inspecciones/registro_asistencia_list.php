<div class="container-fluid px-3 mt-3">
    <h5 class="mb-3">Registro de Asistencia</h5>

    <?php if (empty($registros)): ?>
    <div class="alert alert-info">No hay registros de asistencia disponibles.</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tablaAsistencia">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Tema</th>
                    <th>Consultor</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($registros as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td data-order="<?= esc($r['fecha_sesion']) ?>"><?= date('d/m/Y', strtotime($r['fecha_sesion'])) ?></td>
                    <td><?= esc($tiposReunion[$r['tipo_reunion'] ?? ''] ?? $r['tipo_reunion'] ?? '') ?></td>
                    <td><?= esc($r['tema'] ?? '') ?></td>
                    <td><?= esc($r['nombre_consultor'] ?? '') ?></td>
                    <td>
                        <span class="badge bg-<?= $r['estado'] === 'completo' ? 'success' : 'warning' ?>">
                            <?= $r['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($r['estado'] === 'completo'): ?>
                        <a href="/client/inspecciones/registro-asistencia/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    $(document).ready(function(){
        $('#tablaAsistencia').DataTable({
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
            order: [[1, 'desc']],
            pageLength: 25
        });
    });
    </script>
    <?php endif; ?>
</div>
