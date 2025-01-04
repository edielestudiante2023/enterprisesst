<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos por Subtema - Enterprisesst</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 1200px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-container h2 {
            color: #333;
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
            width: auto;
        }

        .empty-message {
            color: #333;
            font-size: 18px;
            text-align: center;
            padding: 20px;
        }

        /* Responsive table adjustments */
        @media (max-width: 768px) {
            .table-container {
                padding: 10px;
            }

            .table-container h2 {
                font-size: 20px;
            }

            .table th,
            .table td {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">

            <!-- Logos -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>
        </div>
    </nav>

    <!-- Espacio para el Navbar Fijo -->
    <div style="height: 120px;"></div>

    <div class="container">
        <!-- Hojas de Cálculo -->
        <div class="table-container">
            <h2>Inspecciones en la Copropiedad</h2>
            <?php if (!empty($inspecciones)) : ?>
                <table id="inspecciones" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <!-- <th>Cliente</th> -->
                           
                            <th>Observaciones</th>
                            <th>Creado el</th>
                            <!-- <th>Actualizado el</th> -->

                        </tr>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inspecciones as $reporte) : ?>
                            <tr>
                                <!-- <td><?= esc($reporte['id_reporte']) ?></td> -->
                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <!-- <td><?= esc($reporte['cliente_nombre']) ?></td> -->
                                
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>
                                <!-- <td><?= esc($reporte['updated_at']) ?></td> -->

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>



        

        
        <div class="table-container">
            <h2>Actas de Visita</h2>
            <?php if (!empty($actasdevisita)) : ?>
                <table id="actasdevisitalTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actasdevisita as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        
        
       

        <div class="table-container"> <!-- *** -->
            <h2>Reportes de Cierre de Mes</h2>
            <?php if (!empty($cierredemes)) : ?>
                <table id="cierredemesTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cierredemes as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <h2>Capacitaciones</h2>
            <?php if (!empty($capacitaciones)) : ?>
                <table id="capacitacionesTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($capacitaciones as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- emergencias -->
            <h2>Plan y Gestión Ante Emergencias</h2>
            <?php if (!empty($emergencias)) : ?>
                <table id="emergenciasTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emergencias as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
        </div>
        
        
        <div class="table-container"> <!-- *** -->
            <h2>Control de las Fumigaciones</h2>
            <?php if (!empty($fumigaciones)) : ?>
                <table id="fumigacionesTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fumigaciones as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- *** -->
            <h2>Lavado de Tanques</h2>
            <?php if (!empty($lavadotanques)) : ?>
                <table id="lavadotanquesTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lavadotanques as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <h2>Proveedor de Aseo</h2>
            <?php if (!empty($aseo)) : ?>
                <table id="aseoTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aseo as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <h2>Proveedor de Vigilancia</h2>
            <?php if (!empty($vigilancia)) : ?>
                <table id="vigilanciaTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vigilancia as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        
        <div class="table-container"> <!-- *** -->
            <h2>Curso de 50 Horas</h2>
            <?php if (!empty($cincuentahoras)) : ?>
                <table id="cincuentahorasTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cincuentahoras as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- *** -->
            <h2>Reporte ante el ministerio de trabajo</h2>
            <?php if (!empty($reporteministerio)) : ?>
                <table id="reporteministerioTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reporteministerio as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- *** -->
            <h2>Gestión de Otros Proveedores Flotantes</h2>
            <?php if (!empty($otrosproveedores)) : ?>
                <table id="otrosproveedoresTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($otrosproveedores as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <h2>Reportes de Gestión</h2>
            <?php if (!empty($reportes)) : ?>
                <table id="reportesTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportes as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <h2>Gestión Ambiental</h2>
            <?php if (!empty($ambiental)) : ?>
                <table id="ambientalTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ambiental as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- *** -->
            <h2>Gestión Ante Secretaria de Salud</h2>
            <?php if (!empty($secretariasalud)) : ?>
                <table id="secretariasaludTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($secretariasalud as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- *** -->
            <h2>Gestión de Locales Comerciales</h2>
            <?php if (!empty($localescomerciales)) : ?>
                <table id="localescomercialesTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($localescomerciales as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        
        <div class="table-container"> <!-- *** -->
            <h2>Documentación Normatividad SST</h2>
            <?php if (!empty($normatividad)) : ?>
                <table id="normatividadTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($normatividad as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
        <div class="table-container"> <!-- *** -->
            <h2>Contrato y Acuerdo de Confidencialidad</h2>
            <?php if (!empty($contrato)) : ?>
                <table id="contratoTable" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>

                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Tipo de Reporte</th>
                            <th>Detalle</th>
                            <th>Observaciones</th>
                            <th>Creado el</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contrato as $reporte) : ?>
                            <tr>

                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i> Ver</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['tipo_reporte']) ?></td>
                                <td><?= esc($reporte['detalle_reporte']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                                <td><?= esc($reporte['created_at']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables for Hojas de Cálculo
            $('#inspecciones').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });

            // Initialize DataTables for Matrices
            $('#reportesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#aseoTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#vigilanciaTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#ambientalTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#actasdevisitaTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#capacitacionesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#cincuentahorasTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#reporteministerioTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#cierredemesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#emergenciasTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#otrosproveedoresTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#secretariasaludTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#lavadotanquesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#localescomercialesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#fumigacionesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#normatividadTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#contratoTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
            $('#xxTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                order: [
                    [5, 'desc']
                ], // Ordenar por la sexta columna (índice 5, que sería 'Creado el') en orden descendente
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });

            // Manejar el clic en el botón de eliminar
            $('.delete-btn').on('click', function() {
                var reporteId = $(this).data('id');
                var row = $(this).closest('tr');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí puedes agregar la lógica para eliminar el registro, por ejemplo, una solicitud AJAX
                        // Por ahora, simplemente eliminaremos la fila del DataTable

                        // Obtener la instancia del DataTable
                        var table = $(this).closest('table').DataTable();
                        // Eliminar la fila
                        table.row(row).remove().draw();

                        Swal.fire(
                            'Eliminado!',
                            'El registro ha sido eliminado.',
                            'success'
                        );
                    }
                });
            });
        });
    </script>
</body>

</html>