<style>
    .filter-pills {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 2px 0 8px;
        -webkit-overflow-scrolling: touch;
    }
    .filter-pills::-webkit-scrollbar { display: none; }
    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        border: 2px solid #bd9751;
        background: white;
        color: #bd9751;
        transition: all .2s;
    }
    .filter-pill.active {
        background: #bd9751;
        color: white;
    }
    .filter-pill .pill-count {
        background: rgba(0,0,0,0.15);
        border-radius: 10px;
        padding: 0 6px;
        font-size: 11px;
        min-width: 18px;
        text-align: center;
    }
    .filter-pill.active .pill-count {
        background: rgba(255,255,255,0.3);
    }

    .card-vencimiento {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 12px;
        border: none;
        border-left: 4px solid var(--gold-primary);
        background: white;
    }
    .card-vencimiento.vencido { border-left-color: #dc3545; }
    .card-vencimiento.proximo { border-left-color: #ffc107; }
    .card-vencimiento.normal { border-left-color: #bd9751; }
    .card-vencimiento.ejecutado { border-left-color: #28a745; }
    .card-vencimiento.cerrada { border-left-color: #6c757d; }

    .badge-vencido { background: #dc3545; color: white; }
    .badge-proximo { background: #ffc107; color: #333; }
    .badge-normal { background: #bd9751; color: white; }
    .badge-ejecutado { background: #28a745; color: white; }
    .badge-cerrada { background: #6c757d; color: white; }

    .btn-action {
        min-height: 36px;
        font-size: 13px;
        border-radius: 6px;
        padding: 4px 10px;
    }

    .empty-state {
        text-align: center;
        color: #999;
        padding: 32px 16px;
        font-size: 15px;
    }
    .empty-state i {
        font-size: 48px;
        color: #ddd;
        display: block;
        margin-bottom: 12px;
    }
</style>

<div class="container-fluid px-3">
    <!-- Selector de cliente -->
    <div class="mt-2 mb-3">
        <label class="form-label">Cliente</label>
        <select id="selectCliente" class="form-select" style="width:100%;">
            <option value="">Seleccionar cliente...</option>
        </select>
    </div>

    <?php if ($idCliente): ?>

        <?php
        $conteoSinEjecutar = $conteos['sin ejecutar'] ?? 0;
        $conteoEjecutado = $conteos['ejecutado'] ?? 0;
        $conteoCerrada = ($conteos['CERRADA'] ?? 0) + ($conteos['CERRADA POR FIN CONTRATO'] ?? 0);
        $conteoTotal = count($vencimientos);
        ?>

        <!-- Filter pills -->
        <div class="filter-pills">
            <div class="filter-pill active" data-filter="sin ejecutar">
                Sin ejecutar <span class="pill-count"><?= $conteoSinEjecutar ?></span>
            </div>
            <div class="filter-pill" data-filter="ejecutado">
                Ejecutados <span class="pill-count"><?= $conteoEjecutado ?></span>
            </div>
            <?php if ($conteoCerrada > 0): ?>
            <div class="filter-pill" data-filter="cerrada">
                Cerradas <span class="pill-count"><?= $conteoCerrada ?></span>
            </div>
            <?php endif; ?>
            <div class="filter-pill" data-filter="todos">
                Todos <span class="pill-count"><?= $conteoTotal ?></span>
            </div>
        </div>

        <?php if (empty($vencimientos)): ?>
            <div class="empty-state">
                <i class="fas fa-wrench"></i>
                No hay vencimientos registrados para este cliente.
            </div>
        <?php endif; ?>

        <!-- Cards -->
        <div id="cards-container">
        <?php foreach ($vencimientos as $v):
            $estado = $v['estado_actividad'];
            $esSinEjecutar = ($estado === 'sin ejecutar');
            $esCerrada = in_array($estado, ['CERRADA', 'CERRADA POR FIN CONTRATO']);

            // CSS class para borde
            if ($esSinEjecutar) {
                $cssClass = $v['color'] === 'danger' ? 'vencido' : ($v['color'] === 'warning' ? 'proximo' : 'normal');
                $badgeClass = 'badge-' . $cssClass;
            } elseif ($esCerrada) {
                $cssClass = 'cerrada';
                $badgeClass = 'badge-cerrada';
            } else {
                $cssClass = 'ejecutado';
                $badgeClass = 'badge-ejecutado';
            }
        ?>
        <div class="card card-vencimiento <?= $cssClass ?>"
             id="card-<?= $v['id_vencimientos_mmttos'] ?>"
             data-estado="<?= $esCerrada ? 'cerrada' : esc($estado) ?>">
            <div class="card-body py-3 px-3">
                <div>
                    <strong><?= esc($v['detalle_mantenimiento'] ?? 'Sin detalle') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?php if ($esSinEjecutar): ?>
                            Vence: <?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?>
                            &middot;
                            <span class="badge <?= $badgeClass ?>" style="font-size: 11px;"><?= $v['label'] ?></span>
                        <?php else: ?>
                            <?= !empty($v['fecha_realizacion']) ? 'Realizado: ' . date('d/m/Y', strtotime($v['fecha_realizacion'])) : '' ?>
                            &middot;
                            <span class="badge <?= $badgeClass ?>" style="font-size: 11px;"><?= esc($estado) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($v['observaciones'])): ?>
                    <div class="text-muted mt-1" style="font-size: 12px;">
                        <i class="fas fa-comment-dots"></i> <?= esc($v['observaciones']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($esSinEjecutar): ?>
                <div class="d-flex gap-1 mt-2">
                    <a href="/inspecciones/mantenimientos/edit/<?= $v['id_vencimientos_mmttos'] ?>" class="btn btn-sm btn-outline-dark btn-action">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button type="button" class="btn btn-sm btn-success btn-action btn-ejecutar" data-id="<?= $v['id_vencimientos_mmttos'] ?>">
                        <i class="fas fa-check"></i> Ejecutado
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar" data-id="<?= $v['id_vencimientos_mmttos'] ?>" data-nombre="<?= esc($v['detalle_mantenimiento'] ?? '') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- Boton nuevo -->
        <div class="mt-3 mb-4">
            <a href="/inspecciones/mantenimientos/create/<?= $idCliente ?>" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-plus"></i> Nuevo Vencimiento
            </a>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            Selecciona un cliente para ver sus mantenimientos.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Filter pills ===
    var pills = document.querySelectorAll('.filter-pill');
    var cards = document.querySelectorAll('.card-vencimiento');

    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            pills.forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');

            var filter = this.dataset.filter;
            cards.forEach(function(card) {
                if (filter === 'todos' || card.dataset.estado === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Aplicar filtro default al cargar
    var defaultPill = document.querySelector('.filter-pill[data-filter="sin ejecutar"]');
    if (defaultPill) defaultPill.click();

    // === Select2 clientes via AJAX ===
    $('#selectCliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        ajax: {
            url: '/inspecciones/api/clientes',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(function(c) {
                        return { id: c.id_cliente, text: c.nombre_cliente };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // Pre-seleccionar cliente si viene en URL
    <?php if ($idCliente && $clienteSeleccionado): ?>
    var option = new Option('<?= esc($clienteSeleccionado['nombre_cliente']) ?>', '<?= $idCliente ?>', true, true);
    $('#selectCliente').append(option).trigger('change');
    <?php endif; ?>

    // Navegar al cambiar cliente
    $('#selectCliente').on('change', function() {
        var id = $(this).val();
        if (id) {
            window.location.href = '/inspecciones/mantenimientos/cliente/' + id;
        } else {
            window.location.href = '/inspecciones/mantenimientos';
        }
    });

    // === Marcar como ejecutado (AJAX) ===
    document.querySelectorAll('.btn-ejecutar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;

            Swal.fire({
                title: 'Marcar como ejecutado?',
                text: 'Se registrara la fecha de hoy como fecha de realizacion.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, ejecutado',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('/inspecciones/mantenimientos/ejecutado/' + id, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marcado como ejecutado',
                                toast: true,
                                position: 'top',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    })
                    .catch(function() {
                        Swal.fire('Error', 'No se pudo actualizar', 'error');
                    });
                }
            });
        });
    });

    // === Eliminar ===
    document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var nombre = this.dataset.nombre;

            Swal.fire({
                title: 'Eliminar vencimiento?',
                text: nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '/inspecciones/mantenimientos/delete/' + id;
                }
            });
        });
    });
});
</script>
