<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procedimiento de Prevención, Atención y Sanción del Acoso Sexual Laboral</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
            background-color: white;
        }
        footer {
            text-align: center;
            margin-top: 50px;
            background-color: white;
            padding: 20px;
            border-top: 1px solid #ccc;
            font-size: 14px;
        }
        footer table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        footer th, footer td {
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            word-wrap: break-word;
        }
        footer th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        footer tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        footer tr:hover {
            background-color: #f1f1f1;
        }
        footer th:nth-child(5), footer td:nth-child(5) {
            width: 35%;
        }
        footer th:nth-child(1), footer td:nth-child(1) {
            width: 10%;
        }
        footer th:nth-child(2), footer td:nth-child(2),
        footer th:nth-child(3), footer td:nth-child(3),
        footer th:nth-child(4), footer td:nth-child(4) {
            width: 15%;
        }

        .centered-content {
            width: 100%;
            margin: 0 auto 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .centered-content table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }
        .logo { width: 20%; text-align: center; }
        .main-title { width: 50%; font-weight: bold; font-size: 14px; text-align: center; }
        .code { width: 30%; font-weight: bold; font-size: 14px; }
        .subtitle { font-weight: bold; font-size: 16px; text-align: center; }
        .right { text-align: left; padding-left: 10px; }

        .alfa-title { font-size: 1.5em; font-weight: bold; margin-top: 20px; }
        .beta-parrafo { margin-bottom: 10px; text-align: justify; }
        .delta-lista { margin-left: 20px; margin-bottom: 10px; }
        .zeta-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        .zeta-table, .zeta-th, .zeta-td { border: 1px solid black; }
        .zeta-th, .zeta-td { padding: 8px; text-align: left; }
    </style>
</head>

<body>
    <div class="centered-content">
        <table>
            <tr>
                <td rowspan="2" class="logo">
                    <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo de <?= $client['nombre_cliente'] ?>" width="100%">
                </td>
                <td class="main-title">
                    SISTEMA DE GESTIÓN EN SEGURIDAD Y SALUD EN EL TRABAJO
                </td>
                <td class="code">
                    <?= $latestVersion['document_type'] ?>-<?= $latestVersion['acronym'] ?>
                </td>
            </tr>
            <tr>
                <td class="subtitle">
                    PROCEDIMIENTO DE PREVENCIÓN, ATENCIÓN Y SANCIÓN DEL ACOSO SEXUAL LABORAL
                </td>
                <td class="code right">
                    Versión: <?= $latestVersion['version_number'] ?><br>
                    <?php setlocale(LC_TIME, 'es_ES.UTF-8'); ?>
                    Fecha: <?= strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="beta-parrafo">
        <p class="alfa-title">1. Objetivo</p>
        <p class="beta-parrafo">Establecer el procedimiento corporativo para prevenir, atender e investigar actos de acoso sexual en el entorno laboral de <strong><?= $client['nombre_cliente'] ?></strong>, garantizando la dignidad, integridad y derechos de todas las personas vinculadas a la organización.</p>

        <p class="alfa-title">2. Alcance</p>
        <p class="beta-parrafo">Aplica a trabajadores/as, contratistas, pasantes, aprendices, proveedores, visitantes y cualquier persona que, de forma presencial o virtual, interactúe en espacios laborales, instalaciones, eventos o canales digitales de la empresa.</p>

        <p class="alfa-title">3. Marco normativo</p>
        <ul class="delta-lista">
            <li>Ley 1010 de 2006 – Acoso laboral.</li>
            <li>Ley 1257 de 2008 – Violencia contra las mujeres.</li>
            <li>Ley 2365 de 2024 – Protección integral frente al acoso sexual en ámbitos laboral y educativo.</li>
            <li>Decreto 0405 de 2025 – Sanciones por despido retaliatorio.</li>
            <li>Circular 0076 de 2025 (MinTrabajo) – Contenidos mínimos de protocolos.</li>
            <li>Código Sustantivo del Trabajo, Código Penal art. 210 A.</li>
        </ul>

        <p class="alfa-title">4. Definiciones</p>
        <ul class="delta-lista">
            <li><b>Acoso sexual laboral:</b> Conducta de naturaleza sexual, verbal, no verbal o física, no deseada, que afecta la dignidad o genera un ambiente intimidatorio, hostil o humillante.</li>
            <li><b>Víctima:</b> Persona que sufre o denuncia la conducta.</li>
            <li><b>Denunciante:</b> Víctima o tercero que reporta.</li>
            <li><b>Testigo:</b> Quien presencia o conoce los hechos.</li>
            <li><b>Medidas de protección:</b> Acciones inmediatas para salvaguardar a víctima y testigos.</li>
            <li><b>SIVIGE:</b> Sistema de Vigilancia de Violencia de Género del MinTrabajo.</li>
        </ul>

        <p class="alfa-title">5. Principios rectores</p>
        <ul class="delta-lista">
            <li>Tolerancia cero · Confidencialidad · Debida diligencia · No revictimización · Presunción de inocencia · Prohibición de represalias.</li>
        </ul>

        <p class="alfa-title">6. Roles y responsabilidades</p>
        <table class="zeta-table">
            <thead>
                <tr><th class="zeta-th">Rol</th><th class="zeta-th">Funciones clave</th></tr>
            </thead>
            <tbody>
                <tr><td class="zeta-td">Alta Dirección</td><td class="zeta-td">Aprobar política y recursos; liderar cultura de respeto.</td></tr>
                <tr><td class="zeta-td">Talento Humano</td><td class="zeta-td">Difusión, recepción de denuncias, medidas de protección, capacitación.</td></tr>
                <tr><td class="zeta-td">Comité de Convivencia Laboral</td><td class="zeta-td">Canal de recepción y análisis preliminar.</td></tr>
                <tr><td class="zeta-td">Investigador/a</td><td class="zeta-td">Investigación imparcial y confidencial.</td></tr>
                <tr><td class="zeta-td">ARL</td><td class="zeta-td">Apoyo en sensibilización y acompañamiento psicosocial.</td></tr>
                <tr><td class="zeta-td">Colaboradores/as</td><td class="zeta-td">Cumplir y reportar.</td></tr>
                <tr><td class="zeta-td">Testigos</td><td class="zeta-td">Cooperar y pueden acceder a protección.</td></tr>
            </tbody>
        </table>

        <p class="alfa-title">7. Estrategias de prevención</p>
        <p class="beta-parrafo"><b>7.1 Sensibilización y capacitación:</b></p>
        <ul class="delta-lista">
            <li>Inducción obligatoria en los primeros 5 días de ingreso.</li>
            <li>Reinducción anual.</li>
            <li>Campañas permanentes (carteleras, correos, charlas).</li>
        </ul>
        <p class="beta-parrafo"><b>7.2 Evaluación de clima laboral:</b></p>
        <ul class="delta-lista">
            <li>Encuesta anónima semestral.</li>
            <li>Plan de mejora según resultados.</li>
        </ul>
        <p class="beta-parrafo"><b>7.3 Comunicación:</b></p>
        <ul class="delta-lista">
            <li>Publicación de la política en intranet y zonas comunes.</li>
            <li>Material gráfico con canales de denuncia.</li>
        </ul>

        <p class="alfa-title">8. Canales de denuncia y recepción</p>
        <table class="zeta-table">
            <thead>
                <tr><th class="zeta-th">Canal</th><th class="zeta-th">Medio</th><th class="zeta-th">Responsable</th></tr>
            </thead>
            <tbody>
                <tr><td class="zeta-td">Comité de Convivencia</td><td class="zeta-td">Verbal, digital o escrito</td><td class="zeta-td">Miembros de comité</td></tr>
            </tbody>
        </table>

        <p class="alfa-title">9. Procedimiento de atención</p>
        <p class="beta-parrafo">El proceso se inicia con la <b>Recepción</b>, seguida de la <b>Evaluación</b> (≤ 2 días), luego las <b>Medidas de protección</b> (≤ 3 días), la <b>Investigación</b> (≤ 10 días), el <b>Informe y decisión</b> (≤ 5 días), la <b>Comunicación</b>, aplicación de <b>Sanciones o Cierre</b>, y finalmente <b>Seguimiento</b> (30 y 90 días).</p>
        <ul class="delta-lista">
            <li><b>9.1 Recepción y evaluación inicial (≤ 2 días):</b> Registrar, verificar competencia y convocar CCL.</li>
            <li><b>9.2 Medidas de protección (≤ 3 días):</b> Reubicación, licencia remunerada, apoyo psicosocial, restricción de acercamiento.</li>
            <li><b>9.3 Investigación (≤ 10 días):</b> Designar investigador, recolectar pruebas, entrevistas, derecho a contradicción.</li>
            <li><b>9.4 Informe y decisión (≤ 5 días):</b> Informe IN AS 01 y decisión de Alta Dirección.</li>
            <li><b>9.5 Comunicación:</b> Entrega de resultados a víctima y presunto agresor; derecho de apelación (≤ 3 días).</li>
            <li><b>9.6 Sanciones y medidas correctivas:</b> Llamado de atención, suspensión, despido, acciones restaurativas.</li>
            <li><b>9.7 Cierre y seguimiento:</b> Registro en SIVIGE, verificación de bienestar a 30 y 90 días.</li>
        </ul>

        <p class="alfa-title">10. Medidas de protección adicionales</p>
        <ul class="delta-lista">
            <li>Botón de pánico en intranet.</li>
            <li>Acompañamiento médico legal.</li>
            <li>Prohibición de represalias por 12 meses.</li>
        </ul>

        <p class="alfa-title">11. Sanciones disciplinarias</p>
        <ul class="delta-lista">
            <li>Escala del RIT; despido de la víctima dentro de 6 meses sin autorización del MinTrabajo genera multa (Decreto 0405/2025).</li>
        </ul>

        <p class="alfa-title">12. Registro y reporte al SIVIGE</p>
        <ul class="delta-lista">
            <li>Responsable: Analista Bienestar.</li>
            <li>Frecuencia: Enero y julio.</li>
            <li>Conservación de registros: 10 años.</li>
        </ul>

        <p class="alfa-title">13. Confidencialidad</p>
        <ul class="delta-lista">
            <li>Información clasificada como Confidencial – Sensible. Acceso restringido.</li>
        </ul>

        <p class="alfa-title">14. Integración con el SG SST</p>
        <ul class="delta-lista">
            <li>Incorporado al Módulo de Gestión Psicosocial (Res. 0312/2019) y al Plan Anual de Trabajo.</li>
        </ul>

        <p class="alfa-title">15. Monitoreo y mejora continua</p>
        <ul class="delta-lista">
            <li>Indicadores: nº de quejas/100 empleados, tiempo de cierre, % personal capacitado. Revisión anual y auditoría interna cada 24 meses.</li>
        </ul>

        <p class="alfa-title">16. Vigencia y actualizaciones</p>
        <ul class="delta-lista">
            <li>Vigente desde la fecha de aprobación; actualización obligatoria dentro de 60 días ante cambios normativos.</li>
        </ul>
    </div>

    <footer>
        <h2>Historial de Versiones</h2>
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
</body>

</html>
