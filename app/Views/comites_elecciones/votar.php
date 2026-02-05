<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votacion - <?= esc($proceso['tipo_comite']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card-candidato {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
        }
        .card-candidato:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card-candidato.selected {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .card-candidato .foto-candidato {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .foto-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .check-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 2rem;
            color: #28a745;
            display: none;
        }
        .card-candidato.selected .check-icon {
            display: block;
        }
        .header-votacion {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .tiempo-restante {
            font-size: 1.2rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="header-votacion text-center shadow">
            <h2 class="mb-2">
                <i class="bi bi-check2-square text-primary me-2"></i>
                Votacion <?= esc($proceso['tipo_comite']) ?>
            </h2>
            <h4 class="text-muted"><?= esc($cliente['nombre_cliente']) ?></h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Votante:</strong> <?= esc($votante['nombres'] . ' ' . $votante['apellidos']) ?></p>
                    <p class="mb-0"><strong>Documento:</strong> <?= esc($votante['documento_identidad']) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="tiempo-restante mb-0">
                        <i class="bi bi-clock me-1"></i>
                        Votacion cierra: <?= date('d/m/Y H:i', strtotime($proceso['fecha_fin_votacion'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Instrucciones -->
        <div class="alert alert-info shadow-sm">
            <h5><i class="bi bi-info-circle me-2"></i>Instrucciones</h5>
            <ul class="mb-0">
                <li>Seleccione <strong>UN</strong> candidato haciendo clic en su tarjeta</li>
                <li>Revise su seleccion y haga clic en "Confirmar Voto"</li>
                <li>Su voto es secreto y anonimo</li>
                <li>Solo puede votar una vez</li>
            </ul>
        </div>

        <!-- Formulario de votacion -->
        <form action="<?= base_url('votar/registrar') ?>" method="post" id="formVotar">
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <input type="hidden" name="id_candidato" id="id_candidato_seleccionado" value="">

            <h4 class="text-white mb-4 text-center">
                <i class="bi bi-people me-2"></i>Candidatos Representantes de Trabajadores
            </h4>

            <?php if (empty($candidatos)): ?>
            <div class="alert alert-warning text-center">
                No hay candidatos disponibles para votar.
            </div>
            <?php else: ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($candidatos as $c): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card card-candidato h-100 shadow text-center position-relative"
                         data-id="<?= $c['id_candidato'] ?>"
                         onclick="seleccionarCandidato(this, <?= $c['id_candidato'] ?>)">

                        <i class="bi bi-check-circle-fill check-icon"></i>

                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-center">
                                <?php if (!empty($c['foto'])): ?>
                                <img src="<?= base_url($c['foto']) ?>" alt="Foto" class="foto-candidato">
                                <?php else: ?>
                                <div class="foto-placeholder">
                                    <i class="bi bi-person"></i>
                                </div>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title mb-1"><?= esc($c['nombres']) ?></h5>
                            <h5 class="card-title"><?= esc($c['apellidos']) ?></h5>

                            <p class="text-muted mb-2"><?= esc($c['cargo']) ?></p>

                            <?php if (!empty($c['area'])): ?>
                            <span class="badge bg-secondary"><?= esc($c['area']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Boton de confirmar -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-success btn-lg px-5 py-3 shadow" id="btnVotar" disabled>
                    <i class="bi bi-check-circle me-2"></i>Confirmar Voto
                </button>
                <p class="text-white mt-2">
                    <small>Al confirmar, su voto sera registrado de forma anonima</small>
                </p>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let candidatoSeleccionado = null;

    function seleccionarCandidato(elemento, idCandidato) {
        // Remover seleccion anterior
        document.querySelectorAll('.card-candidato').forEach(card => {
            card.classList.remove('selected');
        });

        // Seleccionar nuevo
        elemento.classList.add('selected');
        candidatoSeleccionado = idCandidato;
        document.getElementById('id_candidato_seleccionado').value = idCandidato;
        document.getElementById('btnVotar').disabled = false;
    }

    document.getElementById('formVotar').addEventListener('submit', function(e) {
        if (!candidatoSeleccionado) {
            e.preventDefault();
            alert('Debe seleccionar un candidato');
            return false;
        }

        if (!confirm('¿Esta seguro de confirmar su voto? Esta accion no se puede deshacer.')) {
            e.preventDefault();
            return false;
        }

        document.getElementById('btnVotar').disabled = true;
        document.getElementById('btnVotar').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando voto...';
    });
    </script>
</body>
</html>
