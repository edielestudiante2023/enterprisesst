<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1c2437">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $title ?? 'Inspecciones SST' ?></title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-dark: #1c2437;
            --gold-primary: #bd9751;
            --gold-hover: #a8843f;
            --bg-light: #f5f5f5;
            --text-primary: #333;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg-light);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        /* Top bar */
        .pwa-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: var(--primary-dark);
            color: white;
            display: flex;
            align-items: center;
            padding: 0 12px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .pwa-topbar .btn-back {
            color: white;
            font-size: 20px;
            padding: 8px 12px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .pwa-topbar .pwa-title {
            flex: 1;
            font-size: 17px;
            font-weight: 600;
            text-align: center;
            margin-right: 44px;
        }

        /* Main content */
        .pwa-main {
            padding-top: 64px;
            padding-bottom: 72px;
            min-height: 100vh;
        }

        /* Bottom nav */
        .pwa-bottomnav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: var(--primary-dark);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.2);
        }

        .pwa-bottomnav a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            text-align: center;
            font-size: 10px;
            padding: 4px 12px;
            transition: color 0.2s;
        }

        .pwa-bottomnav a.active,
        .pwa-bottomnav a:hover {
            color: var(--gold-primary);
        }

        .pwa-bottomnav a i {
            font-size: 20px;
            display: block;
            margin-bottom: 2px;
        }

        .pwa-bottomnav a.btn-create i {
            font-size: 28px;
            color: var(--gold-primary);
        }

        /* Cards */
        .card-inspeccion {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 12px;
            border: none;
            border-left: 4px solid var(--gold-primary);
            background: white;
        }

        .card-inspeccion.borrador {
            border-left-color: #ffc107;
        }

        .card-inspeccion.pendiente_firma {
            border-left-color: #fd7e14;
        }

        .card-inspeccion.completo {
            border-left-color: #28a745;
        }

        /* Buttons */
        .btn-pwa {
            min-height: 48px;
            font-size: 16px;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-pwa-primary {
            background: var(--gold-primary);
            color: white;
            border: none;
        }

        .btn-pwa-primary:hover, .btn-pwa-primary:active {
            background: var(--gold-hover);
            color: white;
        }

        .btn-pwa-outline {
            background: white;
            color: var(--primary-dark);
            border: 2px solid var(--primary-dark);
        }

        .btn-pwa-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        /* Forms */
        .form-control, .form-select {
            font-size: 16px;
            min-height: 44px;
            border-radius: 8px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }

        /* Accordion */
        .accordion-button {
            font-weight: 600;
            font-size: 15px;
            padding: 14px 16px;
        }

        .accordion-button:not(.collapsed) {
            background: var(--primary-dark);
            color: white;
        }

        /* Badge estados */
        .badge-borrador {
            background: #ffc107;
            color: #333;
        }

        .badge-pendiente_firma {
            background: #fd7e14;
            color: white;
        }

        .badge-completo {
            background: #28a745;
            color: white;
        }

        /* Grid de inspecciones */
        .grid-inspecciones {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 0 4px;
        }

        .grid-inspecciones .card-tipo {
            border-radius: 12px;
            padding: 20px 12px;
            text-align: center;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-decoration: none;
            color: var(--text-primary);
            transition: transform 0.2s;
        }

        .grid-inspecciones .card-tipo:active {
            transform: scale(0.96);
        }

        .grid-inspecciones .card-tipo i {
            font-size: 32px;
            color: var(--gold-primary);
            margin-bottom: 8px;
        }

        .grid-inspecciones .card-tipo .count {
            font-size: 13px;
            color: #999;
        }

        .grid-inspecciones .card-tipo.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Section titles */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
            margin: 20px 16px 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 380px) {
            .grid-inspecciones {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="pwa-topbar">
        <button class="btn-back" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span class="pwa-title"><?= $title ?? 'Inspecciones SST' ?></span>
    </div>

    <!-- Main Content -->
    <div class="pwa-main">
        <?= $content ?>
    </div>

    <!-- Bottom Navigation -->
    <div class="pwa-bottomnav">
        <a href="/inspecciones" class="<?= current_url() === base_url('inspecciones') ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
        </a>
        <a href="/inspecciones/acta-visita" class="<?= strpos(current_url(), 'acta-visita') !== false ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Actas</span>
        </a>
        <a href="/inspecciones/acta-visita/create" class="btn-create">
            <i class="fas fa-plus-circle"></i>
        </a>
        <a href="#" class="disabled">
            <i class="fas fa-list"></i>
            <span>Otras</span>
        </a>
        <a href="/logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Salir</span>
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Flash messages -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('msg')): ?>
        Swal.fire({
            icon: 'success',
            title: '<?= session()->getFlashdata('msg') ?>',
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 2500,
        });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: '<?= session()->getFlashdata('error') ?>',
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>
