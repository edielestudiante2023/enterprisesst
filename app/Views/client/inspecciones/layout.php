<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Inspecciones') ?> - Enterprisesst</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1c2437;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 3px solid #bd9751;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }
        .navbar-content img {
            max-height: 70px;
            transition: transform 0.3s ease;
        }
        .navbar-content img:hover {
            transform: scale(1.05);
        }
        .content-wrapper {
            margin-top: 120px;
            padding-bottom: 60px;
        }
        .page-header {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }
        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.25);
            color: white;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .card-title {
            font-size: 14px;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-pdf {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
            color: white;
        }
        footer {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: #ffffff;
            padding: 15px 0;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .navbar-content img { max-height: 50px; }
            .content-wrapper { margin-top: 100px; }
            .page-header h1 { font-size: 1.3rem; }
            .page-header { padding: 1rem 1.2rem; flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprisesst">
            </a>
            <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
            </a>
            <a href="https://cycloidtalent.com/" target="_blank">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
            </a>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="container">
            <?= $content ?>
        </div>
    </div>

    <footer class="text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
