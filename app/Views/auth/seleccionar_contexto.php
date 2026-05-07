<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar contexto - Enterprise SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(to top, #0f0c29 0%, #302b63 30%, #24243e 60%, #4a3f6b 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 1rem;
            color: #fff;
        }
        .selector-wrap {
            max-width: 1100px;
            margin: 0 auto;
        }
        .saludo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .saludo h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #fff;
        }
        .saludo p {
            color: #cfd2e0;
            margin-top: 0.5rem;
        }
        .ctx-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
            color: #1c2437;
            height: 100%;
        }
        .ctx-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            border-color: #bd9751;
        }
        .ctx-card .icono {
            font-size: 2.5rem;
            color: #bd9751;
            margin-bottom: 0.75rem;
        }
        .ctx-card .tipo-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            display: inline-block;
            margin-bottom: 0.75rem;
        }
        .badge-cliente   { background: #e3f2fd; color: #0d47a1; }
        .badge-copasst   { background: #e8f5e9; color: #1b5e20; }
        .badge-cocolab   { background: #fce4ec; color: #880e4f; }
        .badge-brigada   { background: #fff3e0; color: #e65100; }
        .badge-general   { background: #f3e5f5; color: #4a148c; }
        .ctx-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }
        .ctx-card .empresa {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .ctx-card .extra {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        .alerta-cocolab {
            background: #fce4ec;
            color: #880e4f;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            margin-top: 0.6rem;
            font-size: 0.78rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .footer-actions {
            text-align: center;
            margin-top: 2.5rem;
        }
        .footer-actions a {
            color: #cfd2e0;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .footer-actions a:hover { color: #fff; }
    </style>
</head>
<body>
<div class="selector-wrap">

    <div class="saludo">
        <h1>Hola, <?= esc($user['nombre_completo'] ?? 'usuario') ?></h1>
        <p>Tu cuenta tiene varios accesos. Elige el que vas a usar ahora.</p>
        <p class="text-warning small">
            <i class="bi bi-info-circle me-1"></i>
            Para cambiar despues, usa el boton "Cambiar contexto" del menu superior o cierra sesion.
        </p>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-warning text-center"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
        <?php foreach ($contextos as $c):
            $badgeClass = match($c['codigo_comite'] ?? '') {
                'COPASST' => 'badge-copasst',
                'COCOLAB' => 'badge-cocolab',
                'BRIGADA' => 'badge-brigada',
                'GENERAL' => 'badge-general',
                default   => $c['tipo'] === 'cliente' ? 'badge-cliente' : 'badge-general',
            };
            $tipoBadgeText = $c['tipo'] === 'cliente' ? 'Cliente' : ($c['codigo_comite'] ?? 'Miembro');
        ?>
        <div class="col-md-6 col-lg-4">
            <form method="post" action="<?= base_url('seleccionar-contexto/atar') ?>" class="h-100">
                <input type="hidden" name="tipo" value="<?= esc($c['tipo']) ?>">
                <input type="hidden" name="id_cliente" value="<?= esc($c['id_cliente']) ?>">
                <?php if ($c['id_comite'] !== null): ?>
                    <input type="hidden" name="id_comite" value="<?= esc($c['id_comite']) ?>">
                <?php endif; ?>
                <button type="submit" class="ctx-card w-100 border-0 text-start" style="background: #fff;">
                    <i class="bi <?= esc(\App\Libraries\ContextoResolver::iconoContexto($c)) ?> icono"></i>
                    <div>
                        <span class="tipo-badge <?= esc($badgeClass) ?>"><?= esc($tipoBadgeText) ?></span>
                    </div>
                    <h3><?= esc($c['nombre_tipo']) ?></h3>
                    <div class="empresa"><?= esc($c['nombre_cliente']) ?></div>

                    <?php if ($c['tipo'] === 'miembro' && !empty($c['rol_comite'])): ?>
                        <div class="extra">Rol: <?= esc(ucfirst($c['rol_comite'])) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($c['es_cocolab'])): ?>
                        <div class="alerta-cocolab">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Acceso confidencial - Ley 1010 / Res. 3461</span>
                        </div>
                    <?php endif; ?>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="footer-actions">
        <a href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesion</a>
    </div>
</div>
</body>
</html>
