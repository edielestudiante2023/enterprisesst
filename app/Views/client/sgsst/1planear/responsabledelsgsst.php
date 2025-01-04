<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta de Asignación de Responsabilidad en SG-SST</title>
</head>
<body>
    <table>
        <tr>
            <td><img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo Cliente" width="100"></td>
            <td>
                <h1>SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</h1>
                <p>Código: FT-SST-002</p>
                <p>Versión: 001</p>
                <p>Fecha: <p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>
            </td>
        </tr>
    </table>

    <p><?= $client['ciudad_cliente'] ?>, <p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>

    <h2>ASIGNACION PARA EL DISEÑO E IMPLEMENTACION DE SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</h2>

    <p>Yo, <?= $client['nombre_rep_legal'] ?> con documento de identidad <?= $client['cedula_rep_legal'] ?> como representante legal de <?= $client['nombre_cliente'] ?> con Nit. <?= $client['nit_cliente'] ?> he delegado para el diseño y administración del Sistema de Gestión de Seguridad y Salud en el Trabajo bajo la Resolución 0312 del 2019 a la empresa CYCLOID TALENT S.A.S con Nit. 901653912, asignando a la profesional <?= $consultant['nombre_consultor'] ?> con documento de identidad <?= $consultant['cedula_consultor'] ?> con número de licencia <?= $consultant['numero_licencia'] ?>, asignada para la responsabilidad ejecutiva de dicho sistema, para lo cual deberá planificar, organizar y dirigir una evaluación, informar a la alta dirección sobre el funcionamiento y los resultados del SG-SST, y actualización de acuerdo con la normatividad vigente.</p>

    <p><img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma Representante Legal" width="100"></p>
    <p><img src="<?= base_url('uploads/' . $consultant['firma_consultor']) ?>" alt="Firma Consultor" width="100"></p>

    <p><?= $client['nombre_rep_legal'] ?></p>
    <p><?= $client['cedula_rep_legal'] ?></p>
    <p><?= $consultant['nombre_consultor'] ?></p>
    <p><?= $consultant['cedula_consultor'] ?></p>

    <p>Representante Legal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Responsable del SG-SST</p>

    <br><br>

    <!-- Botón para ir a la Dashboard del Cliente -->
<a href="<?= base_url('/dashboardclient') ?>">
    <button type="button">Volver al Dashboard del Cliente</button>
</a>

<br><br>

<!-- Botón para cerrar la sesión -->
<a href="<?= base_url('/logout') ?>">
    <button type="button">Cerrar Sesión</button>
</a>


</body>
</html>
