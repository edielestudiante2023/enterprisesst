<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUEVO CLIENTE GANADO — <?= esc($nombre_cliente) ?></title>
</head>
<body style="margin:0;padding:0;background:#f0ece4;font-family:'Segoe UI',Arial,sans-serif;color:#1a1a2e;">

<!-- Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0ece4;padding:32px 0;">
  <tr>
    <td align="center">
      <!-- Contenedor principal -->
      <table width="640" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);max-width:640px;width:100%;">

        <!-- ── Encabezado Dorado ── -->
        <tr>
          <td style="background:linear-gradient(135deg,#bd9751 0%,#d4ac5e 50%,#e6c066 100%);padding:40px 40px 32px;text-align:center;">
            <p style="margin:0 0 8px;font-size:40px;line-height:1;">&#127881;&#127942;&#127881;</p>
            <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;color:#ffffff;letter-spacing:.04em;text-transform:uppercase;">
              NUEVO CLIENTE GANADO
            </h1>
            <p style="margin:0;font-size:14px;color:#fff8e7;font-weight:500;">
              Felicitaciones a todo el equipo
            </p>
          </td>
        </tr>

        <!-- ── Cuerpo ── -->
        <tr>
          <td style="padding:32px 40px 24px;">

            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#374151;">
              Nos complace informar que hemos cerrado exitosamente un nuevo cliente.
              Este logro es resultado del esfuerzo y dedicaci&oacute;n de todo nuestro equipo.
            </p>

            <!-- ── Tarjeta Datos del Cliente ── -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="border-radius:12px;overflow:hidden;margin-bottom:24px;border:1px solid #e8dcc8;">

              <!-- Header tarjeta dorado -->
              <tr>
                <td style="background:linear-gradient(135deg,#bd9751,#d4ac5e);padding:12px 20px;">
                  <p style="margin:0;font-size:13px;font-weight:700;color:#ffffff;letter-spacing:.06em;text-transform:uppercase;">
                    &#128188; Datos del Nuevo Cliente
                  </p>
                </td>
              </tr>

              <!-- Fila: Cliente -->
              <tr>
                <td style="padding:16px 20px 12px;background:#fffdf7;border-bottom:1px solid #f0e6d0;">
                  <p style="margin:0;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9a8560;font-weight:600;">Cliente</p>
                  <p style="margin:4px 0 0;font-size:18px;font-weight:700;color:#1c2437;"><?= esc($nombre_cliente) ?></p>
                </td>
              </tr>

              <!-- Fila: NIT -->
              <tr>
                <td style="padding:12px 20px;background:#fffdf7;border-bottom:1px solid #f0e6d0;">
                  <p style="margin:0;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9a8560;font-weight:600;">NIT</p>
                  <p style="margin:4px 0 0;font-size:15px;font-weight:600;color:#1c2437;"><?= esc($nit) ?></p>
                </td>
              </tr>

              <!-- Fila: Ciudad -->
              <tr>
                <td style="padding:12px 20px;background:#fffdf7;border-bottom:1px solid #f0e6d0;">
                  <p style="margin:0;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9a8560;font-weight:600;">Ciudad</p>
                  <p style="margin:4px 0 0;font-size:15px;font-weight:600;color:#1c2437;"><?= esc($ciudad) ?></p>
                </td>
              </tr>

              <!-- Fila: Tipo de Servicio -->
              <tr>
                <td style="padding:12px 20px;background:#fffdf7;border-bottom:1px solid #f0e6d0;">
                  <p style="margin:0;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9a8560;font-weight:600;">Tipo de Servicio</p>
                  <p style="margin:4px 0 0;">
                    <span style="display:inline-block;background:linear-gradient(135deg,#bd9751,#d4ac5e);color:#ffffff;font-size:13px;font-weight:700;padding:4px 14px;border-radius:20px;">
                      <?= esc($frecuencia_servicio ?: 'N/A') ?>
                    </span>
                  </p>
                </td>
              </tr>

              <!-- Fila: Vendedor -->
              <tr>
                <td style="padding:12px 20px;background:#fffdf7;border-bottom:1px solid #f0e6d0;">
                  <p style="margin:0;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9a8560;font-weight:600;">Vendedor</p>
                  <p style="margin:4px 0 0;font-size:15px;font-weight:600;color:#1c2437;"><?= esc($vendedor ?: 'No asignado') ?></p>
                </td>
              </tr>

              <!-- Fila: Fecha -->
              <tr>
                <td style="padding:12px 20px 16px;background:#fffdf7;">
                  <p style="margin:0;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9a8560;font-weight:600;">Fecha de Registro</p>
                  <p style="margin:4px 0 0;font-size:15px;font-weight:600;color:#1c2437;"><?= esc($fecha) ?></p>
                </td>
              </tr>

            </table>

            <!-- ── Caja motivacional verde ── -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;margin-bottom:24px;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="margin:0;font-size:14px;line-height:1.7;color:#065f46;">
                    &#128170; Seguimos creciendo juntos. Cada nuevo cliente es una muestra de la confianza
                    que genera nuestro trabajo. &iexcl;Felicitaciones al equipo comercial!
                  </p>
                </td>
              </tr>
            </table>

            <!-- Firma -->
            <p style="margin:0 0 4px;font-size:14px;color:#6b7280;font-style:italic;">Con orgullo,</p>
            <p style="margin:0;font-size:15px;font-weight:700;color:#1c2437;">Equipo Cycloid Talent SAS</p>

          </td>
        </tr>

        <!-- ── Footer Azul Oscuro ── -->
        <tr>
          <td style="background:#1c2437;padding:24px 40px;text-align:center;">
            <p style="margin:0 0 4px;font-size:14px;font-weight:700;color:#ffffff;">Cycloid Talent SAS</p>
            <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">NIT: 901.653.912</p>
            <p style="margin:0 0 10px;font-size:12px;color:#94a3b8;">Asesores especializados en SG-SST</p>
            <p style="margin:0;font-size:11px;color:#64748b;">&copy; 2026 Todos los derechos reservados</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
