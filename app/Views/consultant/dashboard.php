<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Consultor 2025</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --shadow-deep: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-medium: 0 5px 20px rgba(0, 0, 0, 0.15);
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --border-radius-large: 25px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Navbar */
        .navbar-custom {
            background: #fffafa;
            box-shadow: var(--shadow-deep);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gold-primary);
        }

        .header-logos-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .header-logos-custom img {
            max-height: 70px;
            margin-right: 15px;
            transition: var(--transition);
        }

        .header-logos-custom img:hover {
            transform: translateY(-3px) scale(1.05);
        }

        /* Content */
        .content-wrapper-custom {
            margin-top: 120px;
            padding: 0 15px;
            animation: fadeInUp 0.8s ease;
        }

        /* Banner de bienvenida */
        .welcome-banner-custom {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 50%, var(--gold-primary) 100%);
            padding: 40px 30px;
            border-radius: var(--border-radius-large);
            text-align: center;
            color: var(--white-primary);
            box-shadow: var(--shadow-deep);
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .welcome-banner-custom::before {
            content: "";
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: repeating-conic-gradient(from 0deg at 50% 50%, transparent 0deg, rgba(255,255,255,0.1) 10deg, transparent 20deg);
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(189,151,81,0.1) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom .content-custom { position: relative; z-index: 2; }

        .welcome-banner-custom h3 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            background: linear-gradient(45deg, var(--white-primary), var(--gold-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-banner-custom h4 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--white-secondary);
        }

        .welcome-banner-custom p {
            font-size: 1.4rem;
            font-weight: 500;
            color: var(--white-primary);
        }

        /* Buscador */
        .search-container {
            max-width: 500px;
            margin: 0 auto 30px;
            position: relative;
        }

        .search-container input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border-radius: 50px;
            border: 2px solid #e0e0e0;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .search-container input:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px rgba(189, 151, 81, 0.2);
        }

        .search-container .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Sección de categoría */
        .category-section {
            margin-bottom: 28px;
            animation: fadeInUp 0.5s ease both;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(189, 151, 81, 0.3);
        }

        .category-header h5 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }

        .category-badge {
            background: var(--primary-dark);
            color: var(--gold-secondary);
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* Grid de tarjetas */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }

        /* Tarjeta individual */
        .access-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: var(--white-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            text-decoration: none;
            color: var(--primary-dark);
            transition: var(--transition);
            border-left: 4px solid transparent;
            cursor: pointer;
            position: relative;
        }

        .access-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            color: var(--primary-dark);
            text-decoration: none;
        }

        .access-card .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: white;
            font-size: 1.1rem;
        }

        .access-card .card-text { min-width: 0; }

        .access-card .card-title {
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .access-card .card-desc {
            font-size: 0.72rem;
            color: #6c757d;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .client-badge {
            position: absolute;
            top: 6px;
            right: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            opacity: 0.85;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .access-card:hover .client-badge {
            opacity: 1;
        }

        /* Banner móvil */
        .mobile-banner {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 16px 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
            border-top: 3px solid #bd9751;
        }

        /* Botón logout */
        .logout-container-custom { text-align: center; margin: 30px 0; }

        .btn-logout-custom {
            background: linear-gradient(135deg, #ff4d4d, #e63939);
            border: none;
            color: white;
            padding: 14px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .btn-logout-custom:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            color: white;
        }

        /* Footer */
        .footer-custom {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            color: var(--white-secondary);
            padding: 40px 0 20px;
            margin-top: 40px;
            text-align: center;
        }

        .footer-custom a { color: var(--gold-secondary); text-decoration: none; }
        .footer-custom a:hover { color: var(--gold-primary); }

        .social-icons-custom {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }

        .social-icons-custom img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-icons-custom img:hover { transform: scale(1.2); }

        /* No results */
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
            display: none;
        }

        /* Modal selector de cliente */
        #clienteSelectorModal .modal-header {
            border-bottom: 2px solid var(--gold-primary);
        }

        #clienteSelectorModal .btn-ir-cliente {
            padding: 10px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-banner-custom { padding: 25px 15px; }
            .welcome-banner-custom h3 { font-size: 1.6rem; }
            .welcome-banner-custom h4 { font-size: 1.2rem; }
            .welcome-banner-custom p { font-size: 1rem; }
            .cards-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
            .access-card { padding: 10px 12px; gap: 10px; }
            .access-card .card-icon { width: 36px; height: 36px; font-size: 0.95rem; }
            .access-card .card-title { font-size: 0.8rem; }
            .access-card .card-desc { display: none; }
            .header-logos-custom img { max-height: 45px; }
            .content-wrapper-custom { margin-top: 90px; }
        }

        @media (max-width: 480px) {
            .cards-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .access-card .card-icon { width: 32px; height: 32px; font-size: 0.85rem; }
        }
    </style>
</head>

<body>
    <!-- Banner móvil PWA Inspecciones -->
    <div id="mobileBanner" class="mobile-banner">
        <div style="display:flex; align-items:center; justify-content:space-between; max-width:600px; margin:0 auto;">
            <div style="flex:1;">
                <div style="font-weight:700; font-size:15px; margin-bottom:2px;">
                    <i class="fas fa-mobile-alt me-2" style="color:#bd9751;"></i>App de Inspecciones
                </div>
                <div style="font-size:12px; opacity:0.8;">Accede a la version movil optimizada</div>
            </div>
            <a href="<?= base_url('/inspecciones') ?>" target="_blank" style="background:#bd9751; color:white; padding:10px 20px; border-radius:25px; text-decoration:none; font-weight:600; font-size:14px; white-space:nowrap; margin-left:12px;">
                Abrir App <i class="fas fa-arrow-right ms-1"></i>
            </a>
            <button onclick="document.getElementById('mobileBanner').style.display='none'" style="background:none; border:none; color:rgba(255,255,255,0.6); font-size:20px; padding:4px 8px; margin-left:8px; cursor:pointer;">&times;</button>
        </div>
    </div>
    <script>
    (function(){
        if (/Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i.test(navigator.userAgent)) {
            document.getElementById('mobileBanner').style.display = 'block';
        }
    })();
    </script>

    <!-- Cabecera -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <div class="header-logos-custom">
                    <div>
                        <a href="https://dashboard.cycloidtalent.com/login" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                        </a>
                    </div>
                    <div>
                        <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                        </a>
                    </div>
                    <div>
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="container-fluid content-wrapper-custom">
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner-custom">
            <div class="content-custom">
                <h3><i class="fas fa-shield-alt me-3"></i>Enterprisesst // Consultor</h3>
                <?php if (isset($usuario) && $usuario): ?>
                    <h4><i class="fas fa-user me-2"></i>Bienvenido, <strong><?= esc($usuario['nombre_completo'] ?? $usuario['email'] ?? 'Consultor') ?></strong></h4>
                <?php else: ?>
                    <h4><i class="fas fa-user me-2"></i>Bienvenido, <strong><?= session()->get('nombre_usuario') ?? 'Consultor' ?></strong></h4>
                <?php endif; ?>
                <p class="mb-0"><i class="fas fa-globe me-2"></i>Enterprisesst - Sistemas que Evolucionan</p>
            </div>
        </div>

        <!-- Buscador -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Buscar acceso..." autocomplete="off">
        </div>

        <!-- Categorías con tarjetas -->
        <div id="categoriesContainer">
            <?php
            $catIcons = [
                'Operación por Cliente'          => 'fas fa-briefcase',
                'Dashboards y Reportes'          => 'fas fa-chart-bar',
                'Herramientas IA'                => 'fas fa-robot',
                'Cumplimiento SST - Res. 0312'   => 'fas fa-gavel',
                'Capacitación y Planificación'   => 'fas fa-calendar-alt',
                'Gestión Documental'             => 'fas fa-folder-open',
                'Carga Masiva CSV'               => 'fas fa-file-csv',
                'Plataformas Colaborativas'      => 'fas fa-project-diagram',
                'Administración del Sistema'     => 'fas fa-cogs',
            ];
            ?>
            <?php foreach ($grouped as $categoria => $items): ?>
                <div class="category-section" data-category="<?= esc($categoria) ?>">
                    <div class="category-header">
                        <i class="<?= $catIcons[$categoria] ?? 'fas fa-folder' ?>" style="color: var(--gold-primary); font-size: 1.2rem;"></i>
                        <h5><?= esc($categoria) ?></h5>
                        <span class="category-badge"><?= count($items) ?></span>
                    </div>
                    <div class="cards-grid">
                        <?php foreach ($items as $item):
                            $colors = explode(',', $item['color_gradiente'] ?? '#6c757d,#495057');
                            $color1 = trim($colors[0]);
                            $color2 = trim($colors[1] ?? $colors[0]);
                            $isModal = str_starts_with($item['accion_url'], '#');
                            $needsClient = str_contains($item['accion_url'], '{id_cliente}');
                            $target = (!$isModal && !$needsClient) ? 'target="_blank"' : '';

                            if ($isModal) {
                                $href = 'javascript:void(0)';
                                $extraAttr = 'data-bs-toggle="modal" data-bs-target="' . esc($item['accion_url']) . '"';
                            } elseif ($needsClient) {
                                $href = 'javascript:void(0)';
                                $extraAttr = 'data-client-url="' . esc($item['accion_url']) . '" data-card-title="' . esc($item['detalle']) . '" onclick="openClientSelector(this)"';
                            } else {
                                $href = base_url($item['accion_url']);
                                $extraAttr = '';
                            }
                        ?>
                            <a href="<?= $href ?>" <?= $target ?> <?= $extraAttr ?>
                               class="access-card"
                               style="border-left-color: <?= $color1 ?>;"
                               data-search="<?= esc(strtolower($item['detalle'] . ' ' . $item['descripcion'] . ' ' . $categoria)) ?>">
                                <div class="card-icon" style="background: linear-gradient(135deg, <?= $color1 ?>, <?= $color2 ?>);">
                                    <i class="<?= esc($item['icono'] ?? 'fas fa-link') ?>"></i>
                                </div>
                                <div class="card-text">
                                    <div class="card-title"><?= esc($item['detalle']) ?></div>
                                    <div class="card-desc"><?= esc($item['descripcion']) ?></div>
                                </div>
                                <?php if ($needsClient): ?>
                                    <span class="client-badge" title="Requiere seleccionar cliente">
                                        <i class="fas fa-user-check"></i>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sin resultados -->
        <div class="no-results" id="noResults">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
            <h5>No se encontraron accesos</h5>
            <p>Intenta con otro termino de busqueda</p>
        </div>

        <!-- Cerrar Sesión -->
        <div class="logout-container-custom">
            <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout-custom">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </button>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="fw-bold mb-3"><i class="fas fa-building me-2"></i>Cycloid Talent SAS</h5>
                    <p class="mb-2">Todos los derechos reservados &copy; <span id="currentYear"></span></p>
                    <p class="mb-2"><i class="fas fa-id-card me-2"></i>NIT: 901.653.912</p>
                    <p class="mb-3">
                        <i class="fas fa-globe me-2"></i>Sitio oficial:
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
                    </p>
                    <div class="mt-4">
                        <strong><i class="fas fa-share-alt me-2"></i>Nuestras Redes Sociales:</strong>
                        <div class="social-icons-custom">
                            <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                            </a>
                            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                            </a>
                            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                            </a>
                            <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                                <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Selector de Cliente -->
    <div class="modal fade" id="clienteSelectorModal" tabindex="-1" aria-labelledby="clienteSelectorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));">
                    <h5 class="modal-title text-white" id="clienteSelectorModalLabel">
                        <i class="fas fa-building me-2"></i><span id="modalTituloAcceso">Seleccionar Cliente</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold mb-2">
                        <i class="fas fa-search me-1"></i>Buscar cliente por nombre o NIT
                    </label>
                    <select id="modalSelectCliente" class="form-select" style="width: 100%;">
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach ($clientes ?? [] as $cliente): ?>
                            <option value="<?= esc($cliente['id_cliente']) ?>">
                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-ir-cliente" id="btnIrCliente" disabled
                            style="background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary)); color: white; border: none;">
                        <i class="fas fa-arrow-right me-1"></i>Ir al módulo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reseteo del Ciclo PHVA -->
    <div class="modal fade" id="resetPHVAModal" tabindex="-1" aria-labelledby="resetPHVAModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="resetPHVAModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Resetear Ciclo PHVA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Advertencia:</strong> Esta acción reseteará las evaluaciones de estándares mínimos que se renuevan cada año en el ciclo PHVA.
                    </div>
                    <div class="mb-3">
                        <label for="clienteResetSelect" class="form-label"><strong>Seleccione el Cliente:</strong></label>
                        <select class="form-select" id="clienteResetSelect" required>
                            <option value="">Cargando clientes...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarReset">
                        <i class="fas fa-redo-alt me-1"></i>Continuar con el Reseteo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación Final Reset -->
    <div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmResetModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Reseteo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-danger">¿Está completamente seguro?</h4>
                    <p class="mb-0">Esta acción <strong>NO SE PUEDE DESHACER</strong>.</p>
                    <p class="mb-3">Se resetearán las evaluaciones del cliente:</p>
                    <h5 id="clienteNombreConfirm" class="text-primary"></h5>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>No, Cancelar
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" id="btnEjecutarReset">
                        <i class="fas fa-check me-1"></i>Sí, Resetear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery + Bootstrap + Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Año actual
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Buscador en tiempo real
        var searchInput = document.getElementById('searchInput');
        var categories = document.querySelectorAll('.category-section');
        var noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            var term = this.value.toLowerCase().trim();
            var anyVisible = false;

            categories.forEach(function(section) {
                var cards = section.querySelectorAll('.access-card');
                var visibleCards = 0;

                cards.forEach(function(card) {
                    var text = card.getAttribute('data-search');
                    if (!term || text.indexOf(term) !== -1) {
                        card.style.display = '';
                        visibleCards++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleCards > 0) {
                    section.style.display = '';
                    anyVisible = true;
                } else {
                    section.style.display = 'none';
                }
            });

            noResults.style.display = anyVisible ? 'none' : 'block';
        });

        // === Modal Selector de Cliente ===
        var currentClientUrl = '';
        var clienteSelectorModal = null;

        function openClientSelector(el) {
            currentClientUrl = el.getAttribute('data-client-url');
            var title = el.getAttribute('data-card-title');
            document.getElementById('modalTituloAcceso').textContent = title;
            document.getElementById('btnIrCliente').disabled = true;

            // Reset select
            $('#modalSelectCliente').val('').trigger('change');

            if (!clienteSelectorModal) {
                clienteSelectorModal = new bootstrap.Modal(document.getElementById('clienteSelectorModal'));
            }
            clienteSelectorModal.show();

            // Inicializar Select2 dentro del modal (con dropdownParent)
            setTimeout(function() {
                $('#modalSelectCliente').select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Buscar cliente por nombre o NIT --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#clienteSelectorModal')
                });
            }, 200);
        }

        // Habilitar botón cuando se selecciona cliente
        $(document).on('change', '#modalSelectCliente', function() {
            var val = $(this).val();
            $('#btnIrCliente').prop('disabled', !val);
        });

        // Navegar al módulo con el cliente seleccionado
        $('#btnIrCliente').on('click', function() {
            var clienteId = $('#modalSelectCliente').val();
            if (clienteId && currentClientUrl) {
                var url = currentClientUrl.replace('{id_cliente}', clienteId);
                window.open('<?= base_url() ?>' + url.replace(/^\//, ''), '_blank');
                clienteSelectorModal.hide();
            }
        });

        // === Reset PHVA Modal ===
        document.getElementById('resetPHVAModal').addEventListener('show.bs.modal', function() {
            var select = document.getElementById('clienteResetSelect');
            select.innerHTML = '<option value="">Cargando clientes...</option>';

            fetch('<?= base_url('/api/getClientesParaReseteo') ?>')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    select.innerHTML = '<option value="">Seleccione un cliente...</option>';
                    data.forEach(function(cliente) {
                        var option = document.createElement('option');
                        option.value = cliente.id;
                        option.textContent = cliente.nombre;
                        select.appendChild(option);
                    });
                })
                .catch(function() {
                    select.innerHTML = '<option value="">Error al cargar clientes</option>';
                });
        });

        document.getElementById('btnConfirmarReset').addEventListener('click', function() {
            var select = document.getElementById('clienteResetSelect');
            var clienteId = select.value;
            var clienteNombre = select.options[select.selectedIndex].textContent;

            if (!clienteId) {
                alert('Seleccione un cliente primero');
                return;
            }

            document.getElementById('clienteNombreConfirm').textContent = clienteNombre;
            var resetModal = bootstrap.Modal.getInstance(document.getElementById('resetPHVAModal'));
            resetModal.hide();

            var confirmModal = new bootstrap.Modal(document.getElementById('confirmResetModal'));
            confirmModal.show();
        });

        document.getElementById('btnEjecutarReset').addEventListener('click', function() {
            var clienteId = document.getElementById('clienteResetSelect').value;
            var btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';

            fetch('<?= base_url('/api/resetearCicloPHVA') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_cliente: clienteId })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                var confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmResetModal'));
                confirmModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Sí, Resetear';

                if (data.success) {
                    alert('Reseteo completado exitosamente. Se actualizaron ' + data.registros + ' registros.');
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Sí, Resetear';
                alert('Error de conexión al servidor');
            });
        });
    </script>
</body>

</html>
