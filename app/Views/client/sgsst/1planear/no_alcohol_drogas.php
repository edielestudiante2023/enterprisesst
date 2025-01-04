<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de No Alcohol, Drogas ni Tabaco</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: white;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        @media print {
            .no-print {
                position: absolute;
                top: -9999px;
                /* Mueve el botón fuera de la página */
            }
        }



        h1,
        h2 {
            text-align: center;
            color: #2c3e50;
        }

        p {
            margin: 15px 0;
            text-align: justify;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #34495e;
        }

        .signature,
        .logo {
            margin-top: 20px;
            text-align: center;
        }

        .signature img,
        .logo img {
            max-width: 200px;
            display: block;
            margin: 0 auto;
        }

        .signature p,
        .logo p {
            margin-top: 5px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .logo {
            width: 20%;
            text-align: center;
        }

        .main-title {
            width: 50%;
            font-weight: bold;
            font-size: 14px;
        }

        .code {
            width: 30%;
            font-weight: bold;
            font-size: 14px;
        }

        .subtitle {
            font-weight: bold;
            font-size: 16px;
        }

        .right {
            text-align: left;
            padding-left: 10px;
        }

        footer {
            margin-top: 50px;
            background-color: white;
            padding: 20px;
            border-top: 1px solid #ccc;
            font-size: 14px;
            text-align: left;
        }

        footer table {
            width: 100%;
            border-collapse: collapse;
        }

        footer th,
        footer td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <td rowspan="2" class="logo">
                <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo de <?= $client['nombre_cliente'] ?>" width="100%">
            </td>
            <td class="main-title">
                SISTEMA DE GESTION EN SEGURIDAD Y SALUD EN EL TRABAJO
            </td>
            <td class="code">
                <?= $latestVersion['document_type'] ?>-<?= $latestVersion['acronym'] ?>
            </td>
        </tr>
        <tr>
            <td class="subtitle">
                <?= $policyType['type_name'] ?> <!-- Aquí se muestra el Nombre del Tipo de Política desde la tabla policy_types -->
            </td>
            <td class="code right">
                Versión: <?= $latestVersion['version_number'] ?><br>
                <?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
?>

Fecha: <?= strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>

            </td>
        </tr>
    </table>




    <div class="container">
        <h1> <?= $policyType['type_name'] ?> <!-- Aquí se muestra el Nombre del Tipo de Política desde la tabla policy_types --></h1>

        <!-- Datos del Cliente -->
        <div class="section">
            <div class="section-title">Datos del Cliente</div>
            <p>ID Cliente: <?= $client['id_cliente'] ?></p>
            <p>Fecha/Hora: <?= $client['datetime'] ?></p>
            <p>Fecha de Ingreso: <p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>
            <p>NIT Cliente: <?= $client['nit_cliente'] ?></p>
            <p>Nombre Cliente: <?= $client['nombre_cliente'] ?></p>
            <p>Usuario: <?= $client['usuario'] ?></p>
            <p>Password: <?= $client['password'] ?></p>
            <p>Correo Cliente: <?= $client['correo_cliente'] ?></p>
            <p>Teléfono 1 Cliente: <?= $client['telefono_1_cliente'] ?></p>
            <p>Teléfono 2 Cliente: <?= $client['telefono_2_cliente'] ?></p>
            <p>Dirección Cliente: <?= $client['direccion_cliente'] ?></p>
            <p>Persona de Contacto Compras: <?= $client['persona_contacto_compras'] ?></p>
            <p>Código Actividad Económica: <?= $client['codigo_actividad_economica'] ?></p>
            <p>Nombre Representante Legal: <?= $client['nombre_rep_legal'] ?></p>
            <p>Cédula Representante Legal: <?= $client['cedula_rep_legal'] ?></p>
            <p>Fecha Fin Contrato: <?= $client['fecha_fin_contrato'] ?></p>
            <p>Ciudad Cliente: <?= $client['ciudad_cliente'] ?></p>
            <p>Estado: <?= $client['estado'] ?></p>
            <p>ID Consultor: <?= $client['id_consultor'] ?></p>

            <!-- Mostrar logo del cliente -->
            <div class="logo">
                <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo de <?= $client['nombre_cliente'] ?>">
                <p>Logo de <?= $client['nombre_cliente'] ?></p>
            </div>

            <!-- Mostrar firma del representante legal -->
            <div class="signature">
                <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma del Representante Legal">
                <p>Firma del Representante Legal</p>
            </div>

            <p>Estándares: <?= $client['estandares'] ?></p>
        </div>

        <!-- Datos del Consultor -->
        <div class="section">
            <div class="section-title">Datos del Consultor</div>
            <p>ID Consultor: <?= $consultant['id_consultor'] ?></p>
            <p>Nombre Consultor: <?= $consultant['nombre_consultor'] ?></p>
            <p>Cédula Consultor: <?= $consultant['cedula_consultor'] ?></p>
            <p>Número de Licencia: <?= $consultant['numero_licencia'] ?></p>

            <!-- Mostrar foto del consultor -->
            <div class="logo">
                <img src="<?= base_url('uploads/' . $consultant['foto_consultor']) ?>" alt="Foto de <?= $consultant['nombre_consultor'] ?>">
                <p>Foto de <?= $consultant['nombre_consultor'] ?></p>
            </div>

            <!-- Mostrar firma del consultor -->
            <div class="signature">
                <img src="<?= base_url('uploads/' . $consultant['firma_consultor']) ?>" alt="Firma de <?= $consultant['nombre_consultor'] ?>">
                <p>Firma de <?= $consultant['nombre_consultor'] ?></p>
            </div>

            <p>Usuario: <?= $consultant['usuario'] ?></p>
            <p>Password: <?= $consultant['password'] ?></p>
            <p>Correo Consultor: <?= $consultant['correo_consultor'] ?></p>
            <p>Teléfono Consultor: <?= $consultant['telefono_consultor'] ?></p>
            <p>ID Cliente: <?= $consultant['id_cliente'] ?></p>
        </div>

        <!-- Datos de la Política -->
        <div class="section">
            <div class="section-title">Datos de la Política</div>
            <p>ID Política: <?= $clientPolicy['id'] ?></p>
            <p>ID Cliente: <?= $clientPolicy['client_id'] ?></p>
            <p>ID Tipo de Política: <?= $clientPolicy['policy_type_id'] ?></p>
            <p>Contenido de la Política: <?= $clientPolicy['policy_content'] ?></p>
            <p>Fecha de Creación: <?= $clientPolicy['created_at'] ?></p>
            <p>Última Actualización: <?= $clientPolicy['updated_at'] ?></p>
        </div>

    </div>

<footer>
    <h2>Historial de Versiones</h2>
    <style>
        footer table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        footer table th, footer table td {
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            word-wrap: break-word;
        }
        footer table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        footer table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        footer table tr:hover {
            background-color: #f1f1f1;
        }
        /* Ajuste del ancho de las columnas */
        footer table th:nth-child(5),
        footer table td:nth-child(5) {
            width: 35%; /* Más ancho para la columna Observaciones */
        }
        footer table th:nth-child(1),
        footer table td:nth-child(1) {
            width: 10%; /* Más estrecho para la columna Versión */
        }
        footer table th:nth-child(2),
        footer table td:nth-child(2),
        footer table th:nth-child(3),
        footer table td:nth-child(3),
        footer table th:nth-child(4),
        footer table td:nth-child(4) {
            width: 15%; /* Ancho uniforme para las demás columnas */
        }
    </style>
    <table>
        <tr>
            <th>Versión</th>
            <th>Tipo de Documento</th>
            <th>Acrónimo</th>
            <th>Fecha de Creación</th>
            <th>Observaciones</th>
        </tr>
        <?php foreach ($allVersions as $version): ?>
            <tr>
                <td><?= $version['version_number'] ?></td>
                <td><?= $version['document_type'] ?></td>
                <td><?= $version['acronym'] ?></td>
                <td><?= strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>
                <td><?= $version['change_control'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</footer>

    <br>
    <!-- <div class="no-print">
        <a href="<?= base_url('/generatePdfNoAlcoholDrogas') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>